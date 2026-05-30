<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_number',
        'room_type',
        'description',
        'price_per_night',
        'max_occupancy',
        'status',
        'image',
        'bed_type',
        'room_size',
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class);
    }

    public function images()
    {
        return $this->hasMany(RoomImage::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
