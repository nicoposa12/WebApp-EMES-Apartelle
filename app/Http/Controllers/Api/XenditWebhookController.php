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
                $reservation = Reservation::find($externalId);
                if ($reservation) {
                    $paymentStatus = ($reservation->payment_option === 'half') ? 'partially_paid' : 'paid';
                    $reservation->update([
                        'status' => 'confirmed',
                        'payment_status' => $paymentStatus,
                    ]);

                    $paidAmount = $payload['paid_amount'] ?? ($reservation->payment_option === 'half' ? $reservation->downpayment_amount : $reservation->total_amount);

                    Payment::create([
                        'reservation_id' => $reservation->id,
                        'paymongo_payment_id' => $paymentId, 
                        'amount' => $paidAmount,
                        'method' => $payload['payment_channel'] ?? ($payload['payment_method'] ?? 'Xendit'),
                        'status' => 'Succeeded',
                    ]);

                    $reservation->load('room', 'user');
                    if ($reservation->user) {
                        $reservation->user->notify(new \App\Notifications\BookingConfirmed($reservation));
                    }

                    Log::info('Reservation Confirmed via Xendit: ' . $externalId);
                }
            }
        }

        return response()->json(['status' => 'success']);
    }
}
