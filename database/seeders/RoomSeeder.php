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
                'room_type' => 'Couple Room',
                'description' => 'A cozy couple room with standard amenities.',
                'price_per_night' => 1500.00,
                'price_per_head' => 0.00,
                'min_occupancy' => 1,
                'max_occupancy' => 2,
                'status' => 'available',
            ],
            [
                'room_number' => '102',
                'room_type' => 'Couple Room',
                'description' => 'Perfect for couples, featuring a queen-sized bed.',
                'price_per_night' => 2500.00,
                'price_per_head' => 0.00,
                'min_occupancy' => 1,
                'max_occupancy' => 2,
                'status' => 'available',
            ],
            [
                'room_number' => '201',
                'room_type' => 'Family Room',
                'description' => 'Luxurious family room with a mountain view and private balcony.',
                'price_per_night' => 0.00,
                'price_per_head' => 500.00,
                'min_occupancy' => 2,
                'max_occupancy' => 4,
                'status' => 'available',
            ],
            [
                'room_number' => '202',
                'room_type' => 'Barkadahan Room',
                'description' => 'Spacious room with multiple beds, perfect for group of friends.',
                'price_per_night' => 0.00,
                'price_per_head' => 350.00,
                'min_occupancy' => 4,
                'max_occupancy' => 6,
                'status' => 'available',
            ],
        ];

        foreach ($rooms as $room) {
            Room::create($room);
        }
    }
}
