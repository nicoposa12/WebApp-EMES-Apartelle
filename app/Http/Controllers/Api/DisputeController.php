<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DisputeController extends Controller
{
    /**
     * Guest: View all disputes filed by the authenticated user.
     */
    public function index(Request $request)
    {
        $disputes = Dispute::where('user_id', $request->user()->id)
            ->with(['reservation.room'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($disputes);
    }

    /**
     * Guest: File a new dispute regarding a reservation.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reservation_id' => 'required|exists:reservations,id',
            'reason' => 'required|string|in:billing,room_condition,service_issue,other',
            'description' => 'required|string|min:10|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $userId = $request->user()->id;

        // Verify reservation belongs to this guest
        $reservation = Reservation::where('id', $request->reservation_id)
            ->where('user_id', $userId)
            ->first();

        if (!$reservation) {
            return response()->json(['message' => 'Unauthorized or booking not found.'], 403);
        }

        // Check if there is already a pending or open dispute for this reservation
        $alreadyFiled = Dispute::where('reservation_id', $request->reservation_id)
            ->whereIn('status', ['pending', 'under_investigation'])
            ->exists();

        if ($alreadyFiled) {
            return response()->json([
                'message' => 'There is already an open dispute under investigation for this reservation.'
            ], 422);
        }

        $dispute = Dispute::create([
            'user_id' => $userId,
            'reservation_id' => $request->reservation_id,
            'reason' => $request->reason,
            'description' => $request->description,
            'status' => 'pending'
        ]);

        $dispute->load(['user', 'reservation.room']);

        // Notify Admins & Staff
        $adminsAndStaff = \App\Models\User::whereIn('role', ['admin', 'staff'])->get();
        \Illuminate\Support\Facades\Notification::send($adminsAndStaff, new \App\Notifications\DisputeFiledForAdmin($dispute));

        return response()->json([
            'message' => 'Dispute successfully filed. Our management will investigate it immediately.',
            'dispute' => $dispute
        ], 201);
    }

    /**
     * Admin/Staff: List all disputes across the system.
     */
    public function adminIndex(Request $request)
    {
        $user = $request->user();
        if (!in_array($user->role, ['admin', 'staff'])) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $disputes = Dispute::with(['user', 'reservation.room', 'resolver'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($disputes);
    }

    /**
     * Admin/Staff: Update status and add remarks to resolve/investigate a dispute.
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!in_array($user->role, ['admin', 'staff'])) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:under_investigation,resolved,rejected',
            'admin_remarks' => 'required|string|min:5|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $dispute = Dispute::findOrFail($id);

        $updateData = [
            'status' => $request->status,
            'admin_remarks' => $request->admin_remarks
        ];

        if (in_array($request->status, ['resolved', 'rejected'])) {
            $updateData['resolved_by'] = $user->id;
            $updateData['resolved_at'] = now();
        }

        $dispute->update($updateData);

        $dispute->load(['user', 'reservation.room', 'resolver']);
        if ($dispute->user) {
            $dispute->user->notify(new \App\Notifications\DisputeUpdatedForGuest($dispute));
        }

        return response()->json([
            'message' => 'Dispute status updated successfully.',
            'dispute' => $dispute
        ]);
    }
}
