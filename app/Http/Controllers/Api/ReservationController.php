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
use Illuminate\Support\Facades\Notification;
use App\Notifications\BookingCreated;
use App\Notifications\NewBookingForAdmin;
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
        $query = Reservation::with(['room', 'user', 'review']);
        
        // If user is not admin or staff, they can only see their own reservations
        if (!in_array($user->role, ['admin', 'staff'])) {
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
        if (!$onlineBooking && (!$user || !in_array($user->role, ['admin', 'staff']))) {
            return response()->json([
                'message' => 'Online booking is temporarily disabled. Please contact the front desk for reservations.'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'payment_option' => 'sometimes|required|in:full,half',
            'guests' => 'nullable|integer|min:1',
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
        
        $guests = $request->input('guests', 1);
        if (in_array($room->room_type, ['Family Room', 'Barkadahan Room'])) {
            $totalAmount = $room->price_per_head * $guests * $days;
        } else {
            $totalAmount = $room->price_per_night * $days;
        }

        $paymentOption = $request->payment_option ?? 'full';
        $downpaymentAmount = ($paymentOption === 'half') ? ($totalAmount / 2) : null;

        $reservation = Reservation::create([
            'user_id' => $userId,
            'room_id' => $request->room_id,
            'guests' => $guests,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'total_amount' => $totalAmount,
            'payment_option' => $paymentOption,
            'downpayment_amount' => $downpaymentAmount,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        // Send Notification immediately
        $reservation->load(['room', 'user']);
        if ($request->user()) {
            $request->user()->notify(new BookingCreated($reservation));
        }

        // Notify Admins & Staff (other than the one who made the booking, if any)
        $adminsAndStaff = \App\Models\User::whereIn('role', ['admin', 'staff']);
        if ($request->user()) {
            $adminsAndStaff->where('id', '!=', $request->user()->id);
        }
        Notification::send($adminsAndStaff->get(), new NewBookingForAdmin($reservation));

        $amountToCharge = ($paymentOption === 'half') ? $downpaymentAmount : $totalAmount;

        // Create Xendit Invoice
        $checkoutData = [
            'reservation_id' => $reservation->id,
            'total_amount' => $amountToCharge,
            'room_type' => $room->room_type,
            'room_number' => $room->room_number,
            'customer_name' => $request->user()->name,
            'customer_email' => $request->user()->email,
            'customer_phone' => $request->user()->phone,
        ];

        try {
            $checkoutResponse = $this->xenditService->createInvoice($checkoutData);

            if ($checkoutResponse['status'] === 'success' && isset($checkoutResponse['invoice_url'])) {
                $reservation->update(['xendit_invoice_id' => $checkoutResponse['id']]);
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
        
        // ❌ BLOCK: If guest is suspended (Admins and Staff can still update)
        if ($user && !in_array($user->role, ['admin', 'staff']) && $user->is_suspended) {
            return response()->json([
                'message' => 'Your account is suspended. You cannot modify reservations.',
                'is_suspended' => true
            ], 403);
        }

        // Authorization Check for non-admin/staff users
        if ($user && !in_array($user->role, ['admin', 'staff'])) {
            // Guests can only modify their own reservations
            if ($reservation->user_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            
            // Guests can ONLY update the check_out date
            $restrictedKeys = ['status', 'payment_status', 'check_in', 'room_id', 'total_amount', 'user_id'];
            if ($request->hasAny($restrictedKeys)) {
                return response()->json(['message' => 'Guests are not authorized to modify booking statuses, room types, check-in dates, or payment details.'], 403);
            }
            
            // Stays can only be extended if the booking is currently active (confirmed or checked-in)
            if (!in_array($reservation->status, ['confirmed', 'checked-in'])) {
                return response()->json(['message' => 'Stays can only be extended for active (confirmed or checked-in) reservations.'], 422);
            }

            // Ensure they are actually extending (new checkout must be strictly after the current checkout)
            if ($request->has('check_out')) {
                $currentCheckOut = Carbon::parse($reservation->check_out);
                
                // Block stay extension if current checkout is in the past
                if (Carbon::now()->greaterThan($currentCheckOut)) {
                    return response()->json(['message' => 'Stays cannot be extended after the checkout date has passed.'], 422);
                }

                $newCheckOut = Carbon::parse($request->check_out);
                if ($newCheckOut->lessThanOrEqualTo($currentCheckOut)) {
                    return response()->json(['message' => 'The new checkout date must be after your current checkout date.'], 422);
                }
            }
        }

        // Rule: Completed and Cancelled reservations are not editable (History only)
        if (in_array($reservation->status, ['completed', 'cancelled'])) {
            return response()->json([
                'message' => "This reservation is already {$reservation->status} and cannot be modified."
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|required|in:pending,confirmed,checked-in,cancelled,completed,cancellation_pending',
            'payment_status' => 'sometimes|required|in:unpaid,partially_paid,paid,refunded',
            'check_in' => 'sometimes|required|date',
            'check_out' => 'sometimes|required|date|after:check_in',
            'cancellation_reason' => 'sometimes|nullable|string|max:1000',
            'guests' => 'sometimes|required|integer|min:1',
        ]);

        $validator->sometimes('cancellation_reason', 'required|string|min:5|max:1000', function ($input) use ($reservation) {
            return $input->status === 'cancelled' && ($reservation->status === 'confirmed' || in_array($reservation->payment_status, ['paid', 'partially_paid']));
        });

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $newTotalAmount = null;
        // If changing dates or guest count, check availability again and calculate new total price
        if ($request->has('check_in') || $request->has('check_out') || $request->has('guests')) {
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

            // Calculate new total amount based on new date duration and room type
            $room = Room::find($reservation->room_id);
            $days = $checkIn->diffInDays($checkOut);
            
            if (in_array($room->room_type, ['Family Room', 'Barkadahan Room'])) {
                $guests = $request->input('guests') ?? $reservation->guests ?? $room->min_occupancy;
                $newTotalAmount = $room->price_per_head * $guests * $days;
            } else {
                $newTotalAmount = $room->price_per_night * $days;
            }
        }

        $oldCheckout = $reservation->check_out;

        return DB::transaction(function () use ($request, $reservation, $newTotalAmount, $oldCheckout) {
            $oldStatus = $reservation->status;
            $oldPaymentStatus = $reservation->payment_status;
            
            $updateData = $request->all();
            if ($newTotalAmount !== null) {
                $updateData['total_amount'] = $newTotalAmount;
                
                // If extension increases price, reset payment status to unpaid for the additional nights
                if ($newTotalAmount > $reservation->total_amount) {
                    $updateData['payment_status'] = 'unpaid';
                }
            }

            $reservation->update($updateData);

            // Notify Admins & Staff when a stay is extended
            if ($request->has('check_out')) {
                $newCheckOut = Carbon::parse($reservation->check_out);
                $originalCheckOut = Carbon::parse($oldCheckout);
                if ($newCheckOut->greaterThan($originalCheckOut)) {
                    $reservation->load(['room', 'user']);
                    $adminsAndStaff = \App\Models\User::whereIn('role', ['admin', 'staff']);
                    if ($request->user()) {
                        $adminsAndStaff->where('id', '!=', $request->user()->id);
                    }
                    Notification::send($adminsAndStaff->get(), new \App\Notifications\StayExtendedForAdmin($reservation, $oldCheckout));
                }
            }

            // Auto-refund if cancelled from paid/partially_paid
            if ($reservation->status === 'cancelled' && $oldStatus !== 'cancelled') {
                if (in_array($reservation->payment_status, ['paid', 'partially_paid'])) {
                    $reservation->update(['payment_status' => 'refunded']);
                }
            }

            // Auto-confirm if paid
            if ($reservation->payment_status === 'paid' && $reservation->status === 'pending') {
                $reservation->update(['status' => 'confirmed']);
                // Notify user
                $reservation->load(['room', 'user']);
                if ($reservation->user) {
                    $reservation->user->notify(new \App\Notifications\BookingConfirmed($reservation));
                }
            }

            // Transition from unpaid to partially_paid (automatically record downpayment in payments)
            if ($reservation->payment_status === 'partially_paid' && $oldPaymentStatus === 'unpaid') {
                $paymentExists = Payment::where('reservation_id', $reservation->id)->exists();
                if (!$paymentExists) {
                    $downpayment = $reservation->downpayment_amount ?: ($reservation->total_amount / 2);
                    Payment::create([
                        'reservation_id' => $reservation->id,
                        'paymongo_payment_id' => 'MANUAL-DP-' . strtoupper(Str::random(10)),
                        'amount' => $downpayment,
                        'method' => 'Cash/Manual',
                        'status' => 'Succeeded',
                    ]);
                }
            }

            // Transition from unpaid/partially_paid to paid (automatically record remaining balance in payments)
            if ($reservation->payment_status === 'paid' && in_array($oldPaymentStatus, ['unpaid', 'partially_paid'])) {
                $totalPaid = Payment::where('reservation_id', $reservation->id)
                    ->where('status', 'Succeeded')
                    ->sum('amount');
                
                $remainingBalance = $reservation->total_amount - $totalPaid;
                
                if ($remainingBalance > 0) {
                    Payment::create([
                        'reservation_id' => $reservation->id,
                        'paymongo_payment_id' => 'MANUAL-BAL-' . strtoupper(Str::random(10)),
                        'amount' => $remainingBalance,
                        'method' => 'Cash/Manual',
                        'status' => 'Succeeded',
                    ]);
                }
            }

            // Senior logic: If status changed to completed, ensure full payment record exists
            if ($reservation->status === 'completed' && $oldStatus !== 'completed') {
                // Update payment status as well
                $reservation->update(['payment_status' => 'paid']);

                // Check if a payment record already exists (e.g. from Xendit) and record remaining balance
                $totalPaid = Payment::where('reservation_id', $reservation->id)
                    ->where('status', 'Succeeded')
                    ->sum('amount');

                $remainingBalance = $reservation->total_amount - $totalPaid;

                if ($remainingBalance > 0) {
                    Payment::create([
                        'reservation_id' => $reservation->id,
                        'paymongo_payment_id' => 'MANUAL-' . strtoupper(Str::random(10)), // Reusing column for manual payments
                        'amount' => $remainingBalance,
                        'method' => 'Cash/Manual',
                        'status' => 'Succeeded',
                    ]);
                }
            }

            // Auto-create payment entry if status changed to confirmed (e.g. manual confirmation)
            if ($reservation->status === 'confirmed' && $oldStatus !== 'confirmed') {
                // Only create if a payment record doesn't already exist
                $paymentExists = Payment::where('reservation_id', $reservation->id)->exists();
                if (!$paymentExists) {
                    Payment::create([
                        'reservation_id' => $reservation->id,
                        'paymongo_payment_id' => 'MANUAL-' . strtoupper(Str::random(10)),
                        'amount' => $reservation->total_amount,
                        'method' => 'Cash/Manual',
                        'status' => 'Succeeded',
                    ]);
                    // Also ensure payment_status is set to paid
                    $reservation->update(['payment_status' => 'paid']);
                }
            }

            // Notify user if status changed
            if ($oldStatus !== $reservation->status) {
                $reservation->load('room');
                if ($reservation->status === 'confirmed') {
                    if ($oldStatus === 'cancellation_pending') {
                        $reservation->update(['cancellation_reason' => null]);
                        $reservation->user->notify(new \App\Notifications\CancellationRejectedForGuest($reservation));
                    } else {
                        $reservation->user->notify(new \App\Notifications\BookingConfirmed($reservation));
                    }
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

        // ❌ BLOCK: Limit cancellation to 1 hour after booking creation
        $createdAt = Carbon::parse($reservation->created_at);
        if ($createdAt->addHour()->isPast()) {
            return response()->json([
                'message' => 'Reservations can only be cancelled within 1 hour of booking.'
            ], 400);
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

        if ($reservation->status === 'cancellation_pending') {
            return response()->json(['message' => 'Cancellation request is already pending approval.'], 400);
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
            
            $request->validate([
                'reason' => 'required|string|min:5|max:1000'
            ], [
                'reason.required' => 'A cancellation reason is required for paid bookings.',
                'reason.min' => 'The cancellation reason must be at least 5 characters.'
            ]);

            // Set status to cancellation_pending, awaiting admin approval
            $reservation->update([
                'status' => 'cancellation_pending',
                'cancellation_reason' => $request->input('reason')
            ]);
            
            $reservation->load(['room', 'user']);
            
            // Notify admins and staff of the request
            $admins = \App\Models\User::whereIn('role', ['admin', 'staff'])->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\CancellationRequestedForAdmin($reservation));
            }
            
            return response()->json([
                'message' => 'Cancellation request submitted successfully. Awaiting administrator approval.',
                'reservation' => $reservation
            ]);
        }

        if ($reservation->status === 'pending') {
            $reservation->update([
                'status' => 'cancelled',
                'cancellation_reason' => $request->input('reason')
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

    /**
     * Sync payment status with Xendit (useful for localhost or failed webhooks)
     */
    public function syncPayment($id)
    {
        $reservation = Reservation::findOrFail($id);
        
        if (!$reservation->xendit_invoice_id) {
            return response()->json(['message' => 'No Xendit invoice associated with this reservation.'], 400);
        }

        $invoice = $this->xenditService->getInvoice($reservation->xendit_invoice_id);

        if (!$invoice) {
            return response()->json(['message' => 'Could not fetch invoice from Xendit.'], 500);
        }

        $status = $invoice['status'] ?? 'PENDING';
        
        if ($status === 'PAID' || $status === 'SETTLED') {
            $paymentStatus = ($reservation->payment_option === 'half') ? 'partially_paid' : 'paid';
            $reservation->update([
                'status' => 'confirmed',
                'payment_status' => $paymentStatus,
            ]);

            // Check if payment already recorded
            $paymentId = $invoice['id'];
            $exists = Payment::where('reservation_id', $reservation->id)
                ->where(function($q) use ($paymentId) {
                    $q->where('paymongo_payment_id', $paymentId)
                      ->orWhere('paymongo_payment_id', 'LIKE', 'MANUAL-%');
                })->first();

            if ($exists) {
                // Update existing record if it was manual
                if (str_starts_with($exists->paymongo_payment_id, 'MANUAL-')) {
                    $exists->update([
                        'paymongo_payment_id' => $paymentId,
                        'method' => $invoice['payment_channel'] ?? ($invoice['payment_method'] ?? 'Xendit'),
                    ]);
                }
            } else {
                $paidAmount = $invoice['paid_amount'] ?? ($reservation->payment_option === 'half' ? $reservation->downpayment_amount : $reservation->total_amount);
                Payment::create([
                    'reservation_id' => $reservation->id,
                    'paymongo_payment_id' => $paymentId,
                    'amount' => $paidAmount,
                    'method' => $invoice['payment_channel'] ?? ($invoice['payment_method'] ?? 'Xendit'),
                    'status' => 'Succeeded',
                ]);
            }

            return response()->json([
                'message' => 'Payment synchronized and confirmed.',
                'status' => 'confirmed'
            ]);
        }

        return response()->json([
            'message' => 'Invoice status is: ' . $status,
            'status' => $reservation->status
        ]);
    }
}
