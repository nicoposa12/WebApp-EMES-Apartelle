<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = [
            [
                'room_number' => '101',
                'room_type' => 'Single',
                'description' => 'A cozy single room for solo travelers.',
                'price_per_night' => 1500.00,
                'max_occupancy' => 1,
                'status' => 'available',
            ],
            [
                'room_number' => '102',
                'room_type' => 'Double',
                'description' => 'Perfect for couples, featuring a queen-sized bed.',
                'price_per_night' => 2500.00,
                'max_occupancy' => 2,
                'status' => 'available',
            ],
            [
                'room_number' => '201',
                'room_type' => 'Suite',
                'description' => 'Luxurious suite with a mountain view and private balcony.',
                'price_per_night' => 4500.00,
                'max_occupancy' => 4,
                'status' => 'available',
            ],
            [
                'room_number' => '202',
                'room_type' => 'Double',
                'description' => 'Spacious double room with twin beds.',
                'price_per_night' => 2500.00,
                'max_occupancy' => 2,
                'status' => 'maintenance',
            ],
        ];

        foreach ($rooms as $room) {
            Room::create($room);
        }
    }
}
