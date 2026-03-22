<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function download(string $id)
    {
        $reservation = Reservation::with(['room', 'user'])->find($id);

        if (!$reservation) {
            return response()->json(['message' => 'Reservation not found'], 404);
        }

        $data = [
            'reservation' => $reservation,
            'date' => date('m/d/Y'),
        ];

        $pdf = Pdf::loadView('invoices.reservation', $data);

        return $pdf->download('invoice-' . $reservation->id . '.pdf');
    }

    public function view(string $id)
    {
        $reservation = Reservation::with(['room', 'user'])->find($id);

        if (!$reservation) {
            return response()->json(['message' => 'Reservation not found'], 404);
        }

        return view('invoices.reservation', [
            'reservation' => $reservation,
            'date' => date('m/d/Y'),
        ]);
    }
}
