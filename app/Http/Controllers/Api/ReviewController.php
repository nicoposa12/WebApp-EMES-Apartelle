<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Room;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Get all reviews and average rating for a specific room.
     */
    public function index($roomId)
    {
        $room = Room::findOrFail($roomId);
        
        $reviews = Review::where('room_id', $roomId)
            ->with(['user:id,name,profile_photo_path'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        $avgRating = round(Review::where('room_id', $roomId)->avg('rating') ?: 0, 1);
        $totalReviews = $reviews->count();

        // Count distribution of stars
        $distribution = [
            5 => Review::where('room_id', $roomId)->where('rating', 5)->count(),
            4 => Review::where('room_id', $roomId)->where('rating', 4)->count(),
            3 => Review::where('room_id', $roomId)->where('rating', 3)->count(),
            2 => Review::where('room_id', $roomId)->where('rating', 2)->count(),
            1 => Review::where('room_id', $roomId)->where('rating', 1)->count(),
        ];

        return response()->json([
            'room_id' => $roomId,
            'average_rating' => $avgRating,
            'total_reviews' => $totalReviews,
            'star_distribution' => $distribution,
            'reviews' => $reviews
        ]);
    }

    /**
     * Submit a feedback/review for a room.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|exists:rooms,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:5|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $userId = auth()->id();

        // Security check: Must have a completed booking for this room to leave a review
        $hasCompletedBooking = Reservation::where('user_id', $userId)
            ->where('room_id', $request->room_id)
            ->where('status', 'completed')
            ->exists();

        if (!$hasCompletedBooking) {
            return response()->json([
                'message' => 'You can only leave reviews for rooms you have successfully stayed in.'
            ], 403);
        }

        // Check if user already reviewed this room for their latest stay
        $latestBooking = Reservation::where('user_id', $userId)
            ->where('room_id', $request->room_id)
            ->where('status', 'completed')
            ->orderBy('check_out', 'desc')
            ->first();

        // Optional: limit to 1 review per booking
        $alreadyReviewed = Review::where('user_id', $userId)
            ->where('reservation_id', $latestBooking->id)
            ->exists();

        if ($alreadyReviewed) {
            return response()->json([
                'message' => 'You have already submitted a review for your latest stay in this room.'
            ], 422);
        }

        $review = Review::create([
            'user_id' => $userId,
            'room_id' => $request->room_id,
            'reservation_id' => $latestBooking->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'message' => 'Thank you for your feedback!',
            'review' => $review->load('user:id,name,profile_photo_path')
        ], 201);
    }

    /**
     * Submit or update an admin/staff reply to a guest review.
     */
    public function reply(Request $request, $reviewId)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['admin', 'staff'])) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'admin_reply' => 'required|string|min:5|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $review = Review::findOrFail($reviewId);
        $review->update([
            'admin_reply' => $request->admin_reply
        ]);

        return response()->json([
            'message' => 'Reply posted successfully.',
            'review' => $review->load('user:id,name,profile_photo_path')
        ]);
    }
}
