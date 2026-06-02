<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected static function booted()
    {
        $clearStatsCaches = function ($payment) {
            \Illuminate\Support\Facades\Cache::forget('admin_stats_admin');
            \Illuminate\Support\Facades\Cache::forget('admin_stats_staff');
            \Illuminate\Support\Facades\Cache::forget('admin_reports');
        };

        static::created($clearStatsCaches);
        static::updated($clearStatsCaches);
        static::deleted($clearStatsCaches);
    }

    protected $fillable = [
        'reservation_id',
        'paymongo_payment_id',
        'amount',
        'method',
        'status',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
