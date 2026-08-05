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
            // Core/General Amenities
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

            // Bathroom and toiletries
            [
                'icon' => 'bi-water',
                'name' => 'Bathtub',
                'description' => 'Relax in a deep soaking bathtub.',
            ],
            [
                'icon' => 'bi-brush',
                'name' => 'Cleaning products',
                'description' => 'Complimentary sanitizing and cleaning supplies.',
            ],
            [
                'icon' => 'bi-shield-lock',
                'name' => 'Private bathroom',
                'description' => 'En-suite private bathroom for your exclusive use.',
            ],
            [
                'icon' => 'bi-droplet-half',
                'name' => 'Shower',
                'description' => 'Hot and cold shower options.',
            ],
            [
                'icon' => 'bi-layers',
                'name' => 'Towels',
                'description' => 'Fresh, soft bath and hand towels.',
            ],

            // Comforts
            [
                'icon' => 'bi-wind',
                'name' => 'Air conditioning',
                'description' => 'Individually controlled air conditioning unit.',
            ],
            [
                'icon' => 'bi-grid',
                'name' => 'Linens',
                'description' => 'Premium bedsheets, blankets, and pillows.',
            ],
            [
                'icon' => 'bi-outlet',
                'name' => 'Socket near the bed',
                'description' => 'Conveniently placed electrical outlet near the nightstand.',
            ],

            // Dining, drinking, and snacking
            [
                'icon' => 'bi-fire',
                'name' => 'BBQ facilities',
                'description' => 'Access to outdoor barbecue grilling facilities.',
            ],
            [
                'icon' => 'bi-table',
                'name' => 'Dining table',
                'description' => 'Spacious dining table for meals or work.',
            ],
            [
                'icon' => 'bi-droplet',
                'name' => 'Dishwasher',
                'description' => 'Automatic dishwasher for quick cleaning.',
            ],
            [
                'icon' => 'bi-fork-knife',
                'name' => 'Full kitchen',
                'description' => 'Kitchenette equipped with essential cooking appliances.',
            ],
            [
                'icon' => 'bi-thermometer-low',
                'name' => 'Refrigerator',
                'description' => 'Keep your drinks and food fresh.',
            ],

            // Layout and furnishings
            [
                'icon' => 'bi-door-open',
                'name' => 'Balcony/terrace',
                'description' => 'Private outdoor balcony or terrace space.',
            ],
            [
                'icon' => 'bi-window-desktop',
                'name' => 'Desk',
                'description' => 'Dedicated workspace with a desk and chair.',
            ],
            [
                'icon' => 'bi-layout',
                'name' => 'Seating area',
                'description' => 'Comfortable seating area to lounge and relax.',
            ],
            [
                'icon' => 'bi-table',
                'name' => 'Separate dining area',
                'description' => 'Dedicated dining space separate from the bedroom.',
            ],
            [
                'icon' => 'bi-grid-3x3',
                'name' => 'Tile/marble flooring',
                'description' => 'Sleek and clean tiled or marble floors.',
            ],
            [
                'icon' => 'bi-trash',
                'name' => 'Trash cans',
                'description' => 'Waste disposal bins located inside the room.',
            ],

            // Clothing and laundry
            [
                'icon' => 'bi-door-closed-fill',
                'name' => 'Closet',
                'description' => 'Wardrobe closet with hanging space.',
            ],
            [
                'icon' => 'bi-suitcase',
                'name' => 'Clothes rack',
                'description' => 'Portable rack for hanging clothes and laundry.',
            ],
        ];

        foreach ($amenities as $amenity) {
            Amenity::firstOrCreate(
                ['name' => $amenity['name']],
                $amenity
            );
        }
    }
}
