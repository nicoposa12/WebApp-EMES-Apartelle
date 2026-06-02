<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected static function booted()
    {
        $clearCaches = function ($reservation) {
            \Illuminate\Support\Facades\Cache::forget('rooms_public_list');
            \Illuminate\Support\Facades\Cache::forget('all_booked_dates');
            if ($reservation->room_id) {
                \Illuminate\Support\Facades\Cache::forget("room_detail_{$reservation->room_id}");
            }
            \Illuminate\Support\Facades\Cache::forget('admin_stats_admin');
            \Illuminate\Support\Facades\Cache::forget('admin_stats_staff');
            \Illuminate\Support\Facades\Cache::forget('admin_reports');
        };

        static::created($clearCaches);
        static::updated($clearCaches);
        static::deleted($clearCaches);
    }

    protected $fillable = [
        'user_id',
        'room_id',
        'guests',
        'check_in',
        'check_out',
        'total_amount',
        'status',
        'payment_status',
        'xendit_invoice_id',
        'payment_option',
        'downpayment_amount',
        'cancellation_reason',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function disputes()
    {
        return $this->hasMany(Dispute::class);
    }
}
