<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Room;
use App\Services\XenditService;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ReservationController extends Controller
{
    protected $xenditService;

    public function __construct(XenditService $xenditService)
    {
        $this->xenditService = $xenditService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Reservation::with(['room', 'user']);
        
        // If user is not admin, they can only see their own reservations
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }
        
        $reservations = $query->orderBy('created_at', 'desc')->get();
            
        return response()->json($reservations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $userId = $request->user_id ?? ($request->user() ? $request->user()->id : null);
        $user = $request->user();

        // ❌ BLOCK: If guest is suspended
        if ($user && $user->is_suspended) {
            return response()->json([
                'message' => 'Your account is suspended: ' . ($user->suspension_reason ?? 'Violation of house rules.') . ' You are restricted from making new bookings.',
                'is_suspended' => true,
                'reason' => $user->suspension_reason
            ], 403);
        }

        // Check System Availability Settings
        $maintenanceMode = \App\Models\SystemSetting::get('maintenance_mode', false);
        if ($maintenanceMode) {
            return response()->json([
                'message' => 'The booking system is currently under maintenance. Please try again later.'
            ], 503);
        }

        $onlineBooking = \App\Models\SystemSetting::get('online_booking', true);
        if (!$onlineBooking && (!$user || $user->role !== 'admin')) {
            return response()->json([
                'message' => 'Online booking is temporarily disabled. Please contact the front desk for reservations.'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
        ]);

        if (!$userId && !$request->has('user_id')) {
             return response()->json(['user_id' => ['The user id field is required.']], 422);
        }

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $checkIn = Carbon::parse($request->check_in);
        $checkOut = Carbon::parse($request->check_out);

        // Check if room is available
        $isAvailable = !Reservation::where('room_id', $request->room_id)
            ->where(function ($query) use ($checkIn, $checkOut) {
                // Modified overlap logic for better accuracy
                $query->where(function($q) use ($checkIn, $checkOut) {
                    $q->where('check_in', '<', $checkOut)
                      ->where('check_out', '>', $checkIn);
                });
            })
            ->whereIn('status', ['pending', 'confirmed', 'checked-in'])
            ->exists();

        if (!$isAvailable) {
            return response()->json(['message' => 'Room is already reserved or pending confirmation for these dates.'], 422);
        }

        $room = Room::find($request->room_id);
        $days = $checkIn->diffInDays($checkOut);
        $totalAmount = $room->price_per_night * $days;

        $reservation = Reservation::create([
            'user_id' => $userId,
            'room_id' => $request->room_id,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        // Send Notification immediately
        $reservation->load('room');
        $request->user()->notify(new \App\Notifications\BookingCreated($reservation));

        // Create Xendit Invoice
        $checkoutData = [
            'reservation_id' => $reservation->id,
            'total_amount' => $totalAmount,
            'room_type' => $room->room_type,
            'room_number' => $room->room_number,
            'customer_name' => $request->user()->name,
            'customer_email' => $request->user()->email,
            'customer_phone' => $request->user()->phone,
        ];

        try {
            $checkoutResponse = $this->xenditService->createInvoice($checkoutData);

            if ($checkoutResponse['status'] === 'success' && isset($checkoutResponse['invoice_url'])) {
                return response()->json([
                    'reservation' => $reservation,
                    'checkout_url' => $checkoutResponse['invoice_url']
                ], 201);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Xendit Invoice Creation Failed: ' . $e->getMessage());
        }

        // Fallback: If payment integration fails or returns no URL, allow booking to proceed as "Pay at Hotel"
        return response()->json([
            'reservation' => $reservation,
            'message' => 'Reservation created successfully. Please settle payment at the hotel.',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $reservation = Reservation::with(['room', 'user'])->find($id);

        if (!$reservation) {
            return response()->json(['message' => 'Reservation not found'], 404);
        }

        return response()->json($reservation);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $reservation = Reservation::find($id);

        if (!$reservation) {
            return response()->json(['message' => 'Reservation not found'], 404);
        }

        $user = $request->user();
        // ❌ BLOCK: If guest is suspended (Admins can still update)
        if ($user && $user->role !== 'admin' && $user->is_suspended) {
            return response()->json([
                'message' => 'Your account is suspended. You cannot modify reservations.',
                'is_suspended' => true
            ], 403);
        }

        // Rule: Completed and Cancelled reservations are not editable (History only)
        if (in_array($reservation->status, ['completed', 'cancelled'])) {
            return response()->json([
                'message' => "This reservation is already {$reservation->status} and cannot be modified."
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|required|in:pending,confirmed,checked-in,cancelled,completed',
            'payment_status' => 'sometimes|required|in:unpaid,paid,refunded',
            'check_in' => 'sometimes|required|date',
            'check_out' => 'sometimes|required|date|after:check_in',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // If changing dates, check availability again
        if ($request->has('check_in') || $request->has('check_out')) {
            $checkIn = Carbon::parse($request->check_in ?? $reservation->check_in);
            $checkOut = Carbon::parse($request->check_out ?? $reservation->check_out);
            
            $isAvailable = !Reservation::where('room_id', $reservation->room_id)
                ->where('id', '!=', $id) // Exclude current reservation
                ->where(function ($query) use ($checkIn, $checkOut) {
                    $query->where('check_in', '<', $checkOut)
                          ->where('check_out', '>', $checkIn);
                })
                ->whereIn('status', ['pending', 'confirmed', 'checked-in'])
                ->exists();

            if (!$isAvailable) {
                return response()->json(['message' => 'The room is not available for the new dates.'], 422);
            }
        }

        return DB::transaction(function () use ($request, $reservation) {
            $oldStatus = $reservation->status;
            $reservation->update($request->all());

            // Senior logic: If status changed to completed, ensure a payment record exists
            if ($reservation->status === 'completed' && $oldStatus !== 'completed') {
                // Update payment status as well
                $reservation->update(['payment_status' => 'paid']);

                // Check if a payment record already exists (e.g. from Xendit)
                $existingPayment = Payment::where('reservation_id', $reservation->id)
                    ->where('status', 'Succeeded')
                    ->exists();

                if (!$existingPayment) {
                    Payment::create([
                        'reservation_id' => $reservation->id,
                        'paymongo_payment_id' => 'MANUAL-' . strtoupper(Str::random(10)), // Reusing column for manual payments
                        'amount' => $reservation->total_amount,
                        'method' => 'Cash/Manual',
                        'status' => 'Succeeded',
                    ]);
                }
            }

            // Notify user if status changed
            if ($oldStatus !== $reservation->status) {
                $reservation->load('room');
                if ($reservation->status === 'confirmed') {
                    $reservation->user->notify(new \App\Notifications\BookingConfirmed($reservation));
                } else if ($reservation->status === 'cancelled') {
                    $reservation->user->notify(new \App\Notifications\BookingCancelled($reservation));
                }
            }

            return response()->json($reservation);
        });
    }

    /**
     * Cancel the specified reservation.
     */
    public function cancel(Request $request, string $id)
    {
        $userId = $request->user()->id;
        $reservation = Reservation::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$reservation) {
            return response()->json(['message' => 'Reservation not found'], 404);
        }

        $user = $request->user();
        // ❌ BLOCK: If guest is suspended
        if ($user && $user->is_suspended) {
            return response()->json([
                'message' => 'Your account is suspended. You cannot cancel reservations. Please contact support.',
                'is_suspended' => true
            ], 403);
        }

        $now = Carbon::now();
        $checkIn = Carbon::parse($reservation->check_in);
        
        // Recommended Cancellation Rules:
        // Pending: ✅ Yes -> Just cancel + free room
        // Confirmed: ✅ Yes (before 24h cutoff) -> Cancel + refund logic
        // Checked-in / Completed / Cancelled: ❌ No

        if ($reservation->status === 'cancelled') {
            return response()->json(['message' => 'Reservation is already cancelled.'], 400);
        }

        if (in_array($reservation->status, ['checked-in', 'completed'])) {
            return response()->json(['message' => 'Cannot cancel after check-in or completion. Please contact admin.'], 400);
        }

        if ($reservation->status === 'confirmed') {
            // Check cutoff (24 hours before check-in)
            // We'll use 24 hours as the standard cutoff
            if ($now->greaterThanOrEqualTo($checkIn->copy()->subHours(24))) {
                return response()->json([
                    'message' => 'Cancellation period for confirmed bookings has passed (24h cutoff). Please contact admin for assistance.'
                ], 400);
            }
            
            // If it's confirmed and before cutoff, we mark it as cancelled and set payment as refunded
            $reservation->update([
                'status' => 'cancelled',
                'payment_status' => 'refunded'
            ]);
            
            $reservation->load('room');
            $request->user()->notify(new \App\Notifications\BookingCancelled($reservation));
            
            return response()->json([
                'message' => 'Reservation cancelled successfully. Your refund is being processed.',
                'reservation' => $reservation
            ]);
        }

        if ($reservation->status === 'pending') {
            $reservation->update([
                'status' => 'cancelled'
            ]);

            $reservation->load('room');
            $request->user()->notify(new \App\Notifications\BookingCancelled($reservation));

            return response()->json([
                'message' => 'Reservation cancelled successfully.',
                'reservation' => $reservation
            ]);
        }

        return response()->json(['message' => 'This reservation cannot be cancelled.'], 400);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $reservation = Reservation::find($id);

        if (!$reservation) {
            return response()->json(['message' => 'Reservation not found'], 404);
        }

        $reservation->delete();

        return response()->json(['message' => 'Reservation deleted successfully']);
    }
}
