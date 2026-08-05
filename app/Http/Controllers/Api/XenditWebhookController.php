<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class XenditWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();
        $status = $payload['status'] ?? null;
        $externalId = $payload['external_id'] ?? null;
        $paymentId = $payload['id'] ?? null;

        Log::info('Xendit Webhook Received: Status=' . $status . ', ExternalID=' . $externalId);

        // Verification (Xendit callback token)
        $xenditCallbackToken = env('XENDIT_CALLBACK_TOKEN');
        if ($request->header('x-callback-token') !== $xenditCallbackToken) {
            Log::warning('Xendit Webhook Token Mismatch');
            // In production, you'd return 401 or 403, but let's just log for now if token is not set
            if ($xenditCallbackToken) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
        }

        if ($status === 'PAID' || $status === 'SETTLED') {
            if ($externalId) {
                $reservationId = explode('-', $externalId)[0];
                $reservation = Reservation::find($reservationId);
                if ($reservation) {
                    $isExtension = str_contains($externalId, '-ext-');
                    
                    // 1. Determine payment amount
                    $paidAmount = $payload['paid_amount'] ?? $payload['amount'] ?? 0;
                    if ($paidAmount == 0) {
                        $paidAmount = ($reservation->payment_option === 'half')
                            ? ($reservation->downpayment_amount ?: ($reservation->total_amount / 2))
                            : $reservation->total_amount;
                    }

                    // 2. Record the payment
                    // Check if payment already recorded
                    $exists = Payment::where('reservation_id', $reservation->id)
                        ->where('paymongo_payment_id', $paymentId)
                        ->first();

                    if (!$exists) {
                        // Check for MANUAL- payment only if NOT a stay extension
                        $manualPayment = null;
                        if (!$isExtension) {
                            $manualPayment = Payment::where('reservation_id', $reservation->id)
                                ->where('paymongo_payment_id', 'LIKE', 'MANUAL-%')
                                ->where('status', 'Succeeded')
                                ->first();
                        }

                        if ($manualPayment) {
                            $manualPayment->update([
                                'paymongo_payment_id' => $paymentId,
                                'amount' => $paidAmount,
                                'method' => $payload['payment_channel'] ?? ($payload['payment_method'] ?? 'Xendit'),
                            ]);
                        } else {
                            Payment::create([
                                'reservation_id' => $reservation->id,
                                'paymongo_payment_id' => $paymentId, 
                                'amount' => $paidAmount,
                                'method' => $payload['payment_channel'] ?? ($payload['payment_method'] ?? 'Xendit'),
                                'status' => 'Succeeded',
                            ]);
                        }
                    }

                    // 3. Recalculate payment status dynamically based on total paid amount
                    $totalPaid = Payment::where('reservation_id', $reservation->id)
                        ->where('status', 'Succeeded')
                        ->sum('amount');

                    if ($totalPaid >= $reservation->total_amount) {
                        $paymentStatus = 'paid';
                    } else if ($totalPaid > 0) {
                        $paymentStatus = 'partially_paid';
                    } else {
                        $paymentStatus = 'unpaid';
                    }

                    // 4. Update reservation status safely
                    $updateData = ['payment_status' => $paymentStatus];
                    
                    // Only transition status to confirmed if it is currently pending
                    if ($reservation->status === 'pending') {
                        $updateData['status'] = 'confirmed';
                    }

                    $reservation->update($updateData);

                    $reservation->load('room', 'user');
                    if ($reservation->user && $reservation->status === 'confirmed') {
                        $reservation->user->notify(new \App\Notifications\BookingConfirmed($reservation));
                    }

                    Log::info('Reservation payment updated via Xendit: ' . $externalId . ', Payment Status: ' . $paymentStatus);
                }
            }
        }

        return response()->json(['status' => 'success']);
    }
}
