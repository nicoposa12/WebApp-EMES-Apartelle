<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $amenities = [
            [
                'icon' => 'bi-wifi',
                'name' => 'Free High-Speed WiFi',
                'description' => 'Stay connected with complimentary high-speed internet access throughout the property.',
            ],
            [
                'icon' => 'bi-car-front',
                'name' => 'Free Parking',
                'description' => 'Secure parking space available for all guests at no additional charge.',
            ],
            [
                'icon' => 'bi-shield-check',
                'name' => '24/7 Security',
                'description' => 'Round-the-clock security personnel and CCTV monitoring for your safety.',
            ],
            [
                'icon' => 'bi-clock',
                'name' => '24-Hour Reception',
                'description' => 'Our friendly staff is available 24/7 to assist with any requests or inquiries.',
            ],
            [
                'icon' => 'bi-cup-hot',
                'name' => 'Complimentary Breakfast',
                'description' => 'Start your day with a delicious Filipino breakfast included in select rooms.',
            ],
            [
                'icon' => 'bi-grid-3x3',
                'name' => 'Kitchen Facilities',
                'description' => 'Fully equipped kitchenette in suites for guests who prefer home-cooked meals.',
            ],
            [
                'icon' => 'bi-stars',
                'name' => 'Daily Housekeeping',
                'description' => 'Fresh linens and towels with daily room cleaning services.',
            ],
            [
                'icon' => 'bi-geo-alt',
                'name' => 'Prime Location',
                'description' => 'Conveniently located near restaurants, shops, and transportation hubs.',
            ],
        ];

        foreach ($amenities as $amenity) {
            Amenity::create($amenity);
        }
    }
}
