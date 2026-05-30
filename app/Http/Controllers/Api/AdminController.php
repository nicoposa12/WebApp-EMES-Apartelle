<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Get dashboard statistics.
     * Cached for 1 minute per user role (staff sees limited data).
     */
    public function getStats(Request $request)
    {
        $isStaff = $request->user() && $request->user()->role === 'staff';
        $cacheKey = 'admin_stats_' . ($isStaff ? 'staff' : 'admin');

        $stats = Cache::remember($cacheKey, 60, function () use ($isStaff) {
            $today = Carbon::today();

            return [
                'today_check_ins' => Reservation::whereDate('check_in', $today)->count(),
                'today_check_outs' => Reservation::whereDate('check_out', $today)->count(),
                'active_reservations' => Reservation::whereIn('status', ['confirmed', 'checked-in'])->count(),
                'available_rooms' => Room::where('status', 'available')->count(),
                'total_revenue' => $isStaff ? 0 : Payment::where('status', 'Succeeded')->sum('amount'),
                'total_reservations' => Reservation::count(),
                'monthly_revenue' => $isStaff ? 0 : Payment::where('status', 'Succeeded')
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->sum('amount'),
            ];
        });

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

    /**
     * Get reports data.
     * Cached for 5 minutes — expensive aggregate queries.
     */
    public function getReports()
    {
        $reports = Cache::remember('admin_reports', 300, function () {
            $firstPayment = Payment::where('status', 'Succeeded')->orderBy('created_at', 'asc')->first();
            
            // Ensure we don't modify the model's created_at by using copy()
            $startDate = $firstPayment 
                ? $firstPayment->created_at->copy()->startOfMonth() 
                : Carbon::now()->subMonths(6)->startOfMonth();
                
            $totalMonths = (int) $startDate->diffInMonths(Carbon::now());
            $monthCount = max(0, $totalMonths);
            
            $allMonths = collect(range(0, $monthCount))->map(function ($i) use ($startDate) {
                return $startDate->copy()->addMonths($i)->format('Y-m');
            });

            $revenueData = Payment::where('status', 'Succeeded')
                ->get()
                ->groupBy(function ($payment) {
                    return $payment->created_at->format('Y-m');
                })
                ->map(function ($group) {
                    return $group->sum('amount');
                });

            $monthlyRevenueResult = $allMonths->map(function ($month) use ($revenueData) {
                return [
                    'month' => Carbon::createFromFormat('Y-m', $month)->format('M Y'),
                    'revenue' => (float) ($revenueData->get($month) ?? 0)
                ];
            })->values();

            $statusDistribution = Reservation::select('status', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get();

            $roomTypePerformance = Reservation::join('rooms', 'reservations.room_id', '=', 'rooms.id')
                ->select('rooms.room_type', \Illuminate\Support\Facades\DB::raw('count(*) as count'), \Illuminate\Support\Facades\DB::raw('sum(total_amount) as total_revenue'))
                ->where('reservations.status', '!=', 'cancelled')
                ->groupBy('rooms.room_type')
                ->get();

            $weeklyBookings = Reservation::where('created_at', '>=', Carbon::now()->subDays(7))
                ->select(\Illuminate\Support\Facades\DB::raw('DATE(created_at) as date'), \Illuminate\Support\Facades\DB::raw('count(*) as count'))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $totalReservations = Reservation::count();

            return [
                'monthly_revenue' => $monthlyRevenueResult,
                'status_distribution' => $statusDistribution,
                'room_performance' => $roomTypePerformance,
                'weekly_trends' => $weeklyBookings,
                'summary' => [
                    'total_revenue' => (float) Payment::where('status', 'Succeeded')->sum('amount'),
                    'total_bookings' => $totalReservations,
                    'avg_booking_value' => (float) Reservation::avg('total_amount'),
                    'cancellation_rate' => $totalReservations > 0 ? (Reservation::where('status', 'cancelled')->count() / $totalReservations) * 100 : 0
                ]
            ];
        });

        return response()->json($reports);
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

    public function getStaffList()
    {
        $staff = \App\Models\User::where('role', 'staff')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json($staff);
    }

    public function storeStaff(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8',
        ]);

        $user = \App\Models\User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'name' => $request->first_name . ' ' . $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => 'staff',
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        ]);

        return response()->json($user, 201);
    }

    public function deleteStaff($id)
    {
        $user = \App\Models\User::where('role', 'staff')->find($id);
        if (!$user) {
            return response()->json(['message' => 'Staff member not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'Staff account deleted successfully']);
    }
}
