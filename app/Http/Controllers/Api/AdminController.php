<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Payment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function getStats()
    {
        $today = Carbon::today();
        
        $stats = [
            'today_check_ins' => Reservation::whereDate('check_in', $today)->count(),
            'today_check_outs' => Reservation::whereDate('check_out', $today)->count(),
            'active_reservations' => Reservation::whereIn('status', ['confirmed', 'checked-in'])->count(),
            'available_rooms' => Room::where('status', 'available')->count(),
            'total_revenue' => Payment::where('status', 'Succeeded')->sum('amount'),
            'total_reservations' => Reservation::count(),
            'monthly_revenue' => Payment::where('status', 'Succeeded')
                ->whereMonth('created_at', Carbon::now()->month)
                ->sum('amount'),
        ];

        return response()->json($stats);
    }

    public function getRecentReservations()
    {
        $recent = Reservation::with(['room', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        return response()->json($recent);
    }

    public function getGuests()
    {
        $guests = \App\Models\User::whereIn('role', ['client', 'guest'])
            ->withCount('reservations')
            ->orderBy('name', 'asc')
            ->get();
            
        return response()->json($guests);
    }

    public function storeGuest(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = \App\Models\User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'name' => $request->first_name . ' ' . $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => 'guest',
            'password' => \Illuminate\Support\Facades\Hash::make('Welcome123!'), // Default password
        ]);

        return response()->json($user, 201);
    }

    public function getPayments()
    {
        $payments = Payment::with(['reservation.user', 'reservation.room'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json($payments);
    }

    public function getGuestHistory(Request $request)
    {
        $email = $request->query('email');
        if (!$email) {
            return response()->json(['message' => 'Email is required'], 400);
        }

        $history = Reservation::with(['room'])
            ->whereHas('user', function ($query) use ($email) {
                $query->where('email', $email);
            })
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json($history);
    }

    public function suspendGuest(Request $request, $id)
    {
        $user = \App\Models\User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Guest not found'], 404);
        }

        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $user->update([
            'is_suspended' => true,
            'suspension_reason' => $request->reason,
        ]);

        $user->notify(new \App\Notifications\AccountSuspended($request->reason));

        return response()->json(['message' => 'Guest suspended successfully']);
    }

    public function unsuspendGuest(Request $request, $id)
    {
        $user = \App\Models\User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Guest not found'], 404);
        }

        $user->update([
            'is_suspended' => false,
            'suspension_reason' => null,
        ]);

        $user->notify(new \App\Notifications\AccountRestored());

        return response()->json(['message' => 'Guest unsuspended successfully']);
    }
}
