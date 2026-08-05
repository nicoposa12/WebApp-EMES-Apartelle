<?php

use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\XenditWebhookController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SocialController;
use App\Http\Controllers\Api\AmenityController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\DisputeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/rooms/booked-dates', [RoomController::class, 'allBookedDates']);
Route::get('/rooms/{id}/booked-dates', [RoomController::class, 'roomBookedDates']);
Route::get('/auth/google', [SocialController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [SocialController::class, 'handleGoogleCallback']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/user/profile-photo', [AuthController::class, 'updateProfilePhoto']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::put('/user/change-password', [AuthController::class, 'changePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/reservations/{id}/sync-payment', [ReservationController::class, 'syncPayment']);
Route::apiResource('reservations', ReservationController::class);
    Route::post('/reservations/{id}/cancel', [ReservationController::class, 'cancel']);
    
    // Messaging Routes
    Route::get('/messages', [App\Http\Controllers\Api\MessageController::class, 'index']); // Guest: get conv with admin, Admin: get list of convs
    Route::get('/messages/unread', [App\Http\Controllers\Api\MessageController::class, 'getUnreadCount']); // Total unread count
    Route::post('/messages', [App\Http\Controllers\Api\MessageController::class, 'store']); // Send
    Route::get('/messages/{userId}', [App\Http\Controllers\Api\MessageController::class, 'show']); // Admin: Get specific conv
    Route::put('/messages/admin/read-all', [App\Http\Controllers\Api\MessageController::class, 'markAllFromAdminAsRead']); // Guest: mark admin msgs as read
    Route::put('/messages/{userId}/read', [App\Http\Controllers\Api\MessageController::class, 'markAsRead']); // Admin: Mark as read
    Route::delete('/messages/{userId}', [App\Http\Controllers\Api\MessageController::class, 'destroy']); // Admin: Delete conv

    // Notification Routes
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    Route::delete('/notifications', [NotificationController::class, 'destroyAll']);
    
    // Reviews
    Route::post('/reviews', [ReviewController::class, 'store']);

    // Disputes
    Route::get('/disputes', [DisputeController::class, 'index']);
    Route::post('/disputes', [DisputeController::class, 'store']);
});

// Protect write operations on rooms and amenities
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/rooms', [RoomController::class, 'store']);
    Route::put('/rooms/{room}', [RoomController::class, 'update']);
    Route::delete('/rooms/{room}', [RoomController::class, 'destroy']);
    
    Route::post('/amenities', [AmenityController::class, 'store']);
    Route::put('/amenities/{amenity}', [AmenityController::class, 'update']);
    Route::delete('/amenities/{amenity}', [AmenityController::class, 'destroy']);
});

Route::apiResource('rooms', RoomController::class)->only(['index', 'show']);
Route::apiResource('amenities', AmenityController::class)->only(['index', 'show']);

Route::post('/xendit/webhook', [XenditWebhookController::class, 'handle']);

// Admin Routes (Sanctum Authenticated)
Route::group(['prefix' => 'admin', 'middleware' => ['auth:sanctum']], function () {
    // Staff-accessible administrative operations
    Route::middleware('role:admin,staff')->group(function () {
        Route::get('/stats', [AdminController::class, 'getStats']);
        Route::get('/recent-reservations', [AdminController::class, 'getRecentReservations']);
        Route::get('/guest-history', [AdminController::class, 'getGuestHistory']);
        Route::get('/guests', [AdminController::class, 'getGuests']);
        Route::post('/guests', [AdminController::class, 'storeGuest']);
        Route::get('/payments', [AdminController::class, 'getPayments']);
        Route::post('/reviews/{id}/reply', [ReviewController::class, 'reply']);
        Route::delete('/reviews/{id}/reply', [ReviewController::class, 'deleteReply']);
        Route::get('/disputes', [DisputeController::class, 'adminIndex']);
        Route::put('/disputes/{id}', [DisputeController::class, 'update']);
        Route::get('/blocked-dates', [App\Http\Controllers\Api\BlockedDateController::class, 'index']);
        Route::post('/blocked-dates', [App\Http\Controllers\Api\BlockedDateController::class, 'store']);
        Route::delete('/blocked-dates/{id}', [App\Http\Controllers\Api\BlockedDateController::class, 'destroy']);
    });

    // strictly Admin-only operations
    Route::middleware('role:admin')->group(function () {
        Route::get('/staff', [AdminController::class, 'getStaffList']);
        Route::post('/staff', [AdminController::class, 'storeStaff']);
        Route::delete('/staff/{id}', [AdminController::class, 'deleteStaff']);
        
        Route::get('/reports', [AdminController::class, 'getReports']);
        Route::post('/guests/{id}/suspend', [AdminController::class, 'suspendGuest']);
        Route::post('/guests/{id}/unsuspend', [AdminController::class, 'unsuspendGuest']);
        Route::get('/settings', [SettingController::class, 'index']);
        Route::post('/settings', [SettingController::class, 'store']);
        Route::apiResource('chatbot-responses', App\Http\Controllers\Api\ChatbotResponseController::class);
    });
});

Route::get('/settings/public', [SettingController::class, 'public']);
Route::get('/chatbot-rules/public', [App\Http\Controllers\Api\ChatbotResponseController::class, 'public']);
Route::get('/rooms/{roomId}/reviews', [ReviewController::class, 'index']);
