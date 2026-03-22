<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Room::with(['amenities', 'images']);

        // Optional date-based availability filtering
        if ($request->has(['check_in', 'check_out'])) {
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

        // Add dynamic occupancy status for Admin view
        if ($request->user() && $request->user()->role === 'admin') {
            $today = \Carbon\Carbon::today();
            foreach ($rooms as $room) {
                $currentRes = \App\Models\Reservation::where('room_id', $room->id)
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
            'price_per_night' => 'required|numeric',
            'max_occupancy' => 'required|integer',
            'status' => 'required|in:available,unavailable,maintenance',
            'image' => 'nullable|string',
            'bed_type' => 'nullable|string',
            'room_size' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $request->all();

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

        return response()->json($room->load(['amenities', 'images']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $room = Room::with(['amenities', 'images', 'reservations' => function($q) {
            $q->whereIn('status', ['pending', 'confirmed', 'checked-in'])
              ->where('check_out', '>=', now())
              ->orderBy('check_in', 'asc')
              ->select('id', 'room_id', 'check_in', 'check_out', 'status');
        }])->find($id);

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
            'price_per_night' => 'sometimes|required|numeric',
            'max_occupancy' => 'sometimes|required|integer',
            'status' => 'sometimes|required|in:available,unavailable,maintenance',
            'image' => 'nullable|string',
            'bed_type' => 'nullable|string',
            'room_size' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $request->all();

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

        $room->delete();

        return response()->json(['message' => 'Room deleted successfully']);
    }
}
