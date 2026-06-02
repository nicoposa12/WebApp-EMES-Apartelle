<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Reservation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
    /**
     * Get booked date ranges for a specific room.
     */
    public function roomBookedDates($id)
    {
        $dates = Reservation::where('room_id', $id)
            ->whereIn('status', ['pending', 'confirmed', 'checked-in'])
            ->where('check_out', '>=', now())
            ->get(['check_in', 'check_out']);

        return response()->json($dates);
    }

    /**
     * Get dates where no rooms are available.
     */
    /**
     * Get dates where no rooms are available.
     * Cached for 2 minutes to reduce query load.
     */
    public function allBookedDates()
    {
        $reservations = Cache::remember('all_booked_dates', 120, function () {
            return Reservation::whereIn('status', ['pending', 'confirmed', 'checked-in'])
                ->where('check_out', '>=', now())
                ->get(['room_id', 'check_in', 'check_out']);
        });

        return response()->json($reservations);
    }

    /**
     * Display a listing of the resource.
     */
    /**
     * Display a listing of the resource.
     * Public requests without date filters are cached for 5 minutes.
     */
    public function index(Request $request)
    {
        $hasDateFilter = $request->has(['check_in', 'check_out']);
        $isAdminOrStaff = $request->user() && in_array($request->user()->role, ['admin', 'staff']);

        // Cache public room listings (no date filter, no admin view)
        if (!$hasDateFilter && !$isAdminOrStaff) {
            $rooms = Cache::remember('rooms_public_list', 300, function () {
                return Room::with(['amenities', 'images'])->get();
            });
            return response()->json($rooms);
        }

        $query = Room::with(['amenities', 'images']);

        // Optional date-based availability filtering
        if ($hasDateFilter) {
            $checkIn = \Carbon\Carbon::parse($request->check_in);
            $checkOut = \Carbon\Carbon::parse($request->check_out);

            $query->whereDoesntHave('reservations', function ($q) use ($checkIn, $checkOut) {
                // Rule: Pending, Confirmed, and Checked-in reservations block dates
                $q->whereIn('status', ['pending', 'confirmed', 'checked-in'])
                  ->where(function($subQ) use ($checkIn, $checkOut) {
                      $subQ->where('check_in', '<', $checkOut)
                           ->where('check_out', '>', $checkIn);
                  });
            });
        }

        $rooms = $query->get();

        // Add dynamic occupancy status for Admin & Staff view
        if ($isAdminOrStaff) {
            $today = \Carbon\Carbon::today();
            foreach ($rooms as $room) {
                $currentRes = Reservation::where('room_id', $room->id)
                    ->whereIn('status', ['confirmed', 'checked-in'])
                    ->where('check_in', '<=', $today)
                    ->where('check_out', '>', $today)
                    ->with('user')
                    ->first();
                
                if ($currentRes) {
                    $room->current_reservation = $currentRes;
                    // If room is physically available but logically occupied today
                    if ($room->status === 'available') {
                        $room->status = 'occupied';
                    }
                }
            }
        }

        return response()->json($rooms);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_number' => 'required|string|unique:rooms',
            'room_type' => 'required|string',
            'description' => 'nullable|string',
            'price_per_night' => 'required_unless:room_type,Family Room,Barkadahan Room|nullable|numeric',
            'price_per_head' => 'required_if:room_type,Family Room,Barkadahan Room|nullable|numeric',
            'max_occupancy' => 'required|integer',
            'min_occupancy' => 'nullable|integer',
            'status' => 'required|in:available,unavailable,maintenance',
            'image' => 'nullable|string',
            'bed_type' => 'nullable|string',
            'room_size' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $request->all();
        if (in_array($data['room_type'] ?? '', ['Family Room', 'Barkadahan Room'])) {
            $data['price_per_night'] = 0.00;
        } else {
            $data['price_per_head'] = 0.00;
        }

        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('rooms', 'public');
            $data['image'] = Storage::url($path);
        }

        $room = Room::create($data);

        // Handle multiple images if provided
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('rooms', 'public');
                $url = Storage::url($path);
                $room->images()->create([
                    'image_path' => $url,
                    'is_primary' => false,
                ]);
            }
        }

        if ($request->has('amenities')) {
            // Handle amenities which might be sent as a stringified JSON array if using FormData
            $amenities = is_string($request->amenities) ? json_decode($request->amenities) : $request->amenities;
            $room->amenities()->sync($amenities);
        }

        // Invalidate room-related caches after creation
        $this->clearRoomCaches();

        return response()->json($room->load(['amenities', 'images']), 201);
    }

    /**
     * Display the specified resource.
     */
    /**
     * Display the specified resource.
     * Cached per room for 5 minutes.
     */
    public function show(string $id)
    {
        $room = Cache::remember("room_detail_{$id}", 300, function () use ($id) {
            return Room::with(['amenities', 'images', 'reservations' => function($q) {
                $q->whereIn('status', ['pending', 'confirmed', 'checked-in'])
                  ->where('check_out', '>=', now())
                  ->orderBy('check_in', 'asc')
                  ->select('id', 'room_id', 'check_in', 'check_out', 'status');
            }])->find($id);
        });

        if (!$room) {
            return response()->json(['message' => 'Room not found'], 404);
        }

        return response()->json($room);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $room = Room::find($id);

        if (!$room) {
            return response()->json(['message' => 'Room not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'room_number' => 'sometimes|required|string|unique:rooms,room_number,' . $id,
            'room_type' => 'sometimes|required|string',
            'description' => 'nullable|string',
            'price_per_night' => 'sometimes|required_unless:room_type,Family Room,Barkadahan Room|nullable|numeric',
            'price_per_head' => 'sometimes|required_if:room_type,Family Room,Barkadahan Room|nullable|numeric',
            'max_occupancy' => 'sometimes|required|integer',
            'min_occupancy' => 'sometimes|required|integer',
            'status' => 'sometimes|required|in:available,unavailable,maintenance',
            'image' => 'nullable|string',
            'bed_type' => 'nullable|string',
            'room_size' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $request->all();
        $roomType = $data['room_type'] ?? $room->room_type;
        if (in_array($roomType, ['Family Room', 'Barkadahan Room'])) {
            $data['price_per_night'] = 0.00;
        } else {
            $data['price_per_head'] = 0.00;
        }

        if ($request->hasFile('image_file')) {
            // Delete old image if it exists and is a local file
            if ($room->image && str_contains($room->image, '/storage/rooms/')) {
                $oldPath = str_replace('/storage/', '', $room->image);
                Storage::disk('public')->delete($oldPath);
            }
            
            $path = $request->file('image_file')->store('rooms', 'public');
            $data['image'] = Storage::url($path);
        }

        $room->update($data);

        // Handle multiple images if provided
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('rooms', 'public');
                $url = Storage::url($path);
                $room->images()->create([
                    'image_path' => $url,
                    'is_primary' => false,
                ]);
            }
        }

        // Handle removing specific images
        if ($request->has('remove_images')) {
            $removeIds = is_string($request->remove_images) ? json_decode($request->remove_images) : $request->remove_images;
            if (is_array($removeIds)) {
                $imagesToRemove = \App\Models\RoomImage::whereIn('id', $removeIds)->where('room_id', $room->id)->get();
                foreach ($imagesToRemove as $img) {
                    if (str_contains($img->image_path, '/storage/rooms/')) {
                        $p = str_replace('/storage/', '', $img->image_path);
                        Storage::disk('public')->delete($p);
                    }
                    $img->delete();
                }
            }
        }

        if ($request->has('amenities')) {
            $amenities = is_string($request->amenities) ? json_decode($request->amenities) : $request->amenities;
            $room->amenities()->sync($amenities);
        }

        // Invalidate room-related caches after update
        $this->clearRoomCaches($room->id);

        return response()->json($room->load(['amenities', 'images']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $room = Room::find($id);

        if (!$room) {
            return response()->json(['message' => 'Room not found'], 404);
        }

        $roomId = $room->id;
        $room->delete();

        // Invalidate room-related caches after deletion
        $this->clearRoomCaches($roomId);

        return response()->json(['message' => 'Room deleted successfully']);
    }

    /**
     * Clear all room-related caches after a mutation.
     */
    private function clearRoomCaches($roomId = null)
    {
        Cache::forget('rooms_public_list');
        Cache::forget('all_booked_dates');
        if ($roomId) {
            Cache::forget("room_detail_{$roomId}");
        }
    }
}
