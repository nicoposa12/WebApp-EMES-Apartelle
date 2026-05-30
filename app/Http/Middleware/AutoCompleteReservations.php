<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Reservation;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AutoCompleteReservations
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $now = Carbon::now();

        // Fetch active reservations whose check_out date has passed
        $pastReservations = Reservation::whereIn('status', ['confirmed', 'checked-in'])
            ->where('check_out', '<', $now)
            ->get();

        foreach ($pastReservations as $reservation) {
            $reservation->update([
                'status' => 'completed',
                'payment_status' => 'paid'
            ]);

            // Calculate and record any remaining balance (especially for downpayment bookings)
            $totalPaid = Payment::where('reservation_id', $reservation->id)
                ->where('status', 'Succeeded')
                ->sum('amount');

            $remainingBalance = $reservation->total_amount - $totalPaid;

            if ($remainingBalance > 0) {
                Payment::create([
                    'reservation_id' => $reservation->id,
                    'paymongo_payment_id' => 'AUTO-' . strtoupper(Str::random(10)),
                    'amount' => $remainingBalance,
                    'method' => 'Cash/Manual',
                    'status' => 'Succeeded',
                ]);
            }
        }

        return $next($request);
    }
}
