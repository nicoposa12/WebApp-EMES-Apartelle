<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlockedDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BlockedDateController extends Controller
{
    /**
     * Get a list of all blocked dates.
     */
    public function index()
    {
        $blocked = BlockedDate::with('room')
            ->orderBy('start_date', 'asc')
            ->get();

        return response()->json($blocked);
    }

    /**
     * Block a new date range.
     */
    public function store(Request $request)
    {
        $request->validate([
            'room_id' => 'nullable|exists:rooms,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'reason' => 'nullable|string|max:255',
        ]);

        // Formats to match standard database datetime
        $startDate = \Carbon\Carbon::parse($request->start_date)->format('Y-m-d H:i:s');
        $endDate = \Carbon\Carbon::parse($request->end_date)->format('Y-m-d H:i:s');

        $block = BlockedDate::create([
            'room_id' => $request->room_id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => $request->reason,
        ]);

        // Clear availability cache
        Cache::forget('all_booked_dates');
        Cache::forget('rooms_public_list');
        if ($request->room_id) {
            Cache::forget("room_detail_{$request->room_id}");
        }

        return response()->json([
            'message' => 'Dates blocked successfully.',
            'blocked_date' => $block->load('room'),
        ], 201);
    }

    /**
     * Unblock a date range.
     */
    public function destroy($id)
    {
        $block = BlockedDate::find($id);

        if (!$block) {
            return response()->json(['message' => 'Blocked date record not found.'], 404);
        }

        $roomId = $block->room_id;
        $block->delete();

        // Clear availability cache
        Cache::forget('all_booked_dates');
        Cache::forget('rooms_public_list');
        if ($roomId) {
            Cache::forget("room_detail_{$roomId}");
        }

        return response()->json([
            'message' => 'Dates unblocked successfully.'
        ]);
    }
}
