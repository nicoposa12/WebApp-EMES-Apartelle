<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'start_date',
        'end_date',
        'reason',
    ];

    protected static function booted()
    {
        $clearCaches = function ($blockedDate) {
            \Illuminate\Support\Facades\Cache::forget('rooms_public_list');
            \Illuminate\Support\Facades\Cache::forget('all_booked_dates');
            if ($blockedDate->room_id) {
                \Illuminate\Support\Facades\Cache::forget("room_detail_{$blockedDate->room_id}");
            }
            \Illuminate\Support\Facades\Cache::forget('admin_stats_admin');
            \Illuminate\Support\Facades\Cache::forget('admin_stats_staff');
            \Illuminate\Support\Facades\Cache::forget('admin_reports');
        };

        static::created($clearCaches);
        static::updated($clearCaches);
        static::deleted($clearCaches);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
