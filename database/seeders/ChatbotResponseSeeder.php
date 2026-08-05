<?php

namespace Database\Seeders;

use App\Models\ChatbotResponse;
use Illuminate\Database\Seeder;

class ChatbotResponseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rules = [
            [
                'trigger' => 'hello',
                'response' => "Hello! Welcome to EME's Apartelle virtual assistant. How can I help you today?",
                'follow_up_question' => 'Would you like to explore our rooms or check rates?',
                'suggested_triggers' => 'Rooms, Rates, Location',
                'match_type' => 'contains',
                'is_active' => true,
            ],
            [
                'trigger' => 'hi',
                'response' => "Hi there! Welcome to EME's Apartelle. I'm here to answer your questions and guide your booking.",
                'follow_up_question' => 'What would you like to know about?',
                'suggested_triggers' => 'Rooms, Contact Us, WiFi',
                'match_type' => 'contains',
                'is_active' => true,
            ],
            [
                'trigger' => 'rooms',
                'response' => "We offer comfortable rooms tailored for your needs:\n- Couple Room: Cozy and perfect for 1-2 guests.\n- Family Room: Spacious, starting at ₱500 per head/night, ideal for 2-4 guests.\n- Barkadahan Room: Group-friendly, starting at ₱350 per head/night, perfect for 4-6 guests.",
                'follow_up_question' => 'Would you like to check specific room availability or rates?',
                'suggested_triggers' => 'Rates, Book Now, WiFi',
                'match_type' => 'contains',
                'is_active' => true,
            ],
            [
                'trigger' => 'rates',
                'response' => "Our room pricing structure is as follows:\n- Couple Room (#101): ₱1,500/night\n- Couple Room (#102 with Queen Bed): ₱2,500/night\n- Family Room: ₱500 per head/night\n- Barkadahan Room: ₱350 per head/night",
                'follow_up_question' => 'Are you ready to book your stay?',
                'suggested_triggers' => 'Book Now, Rooms, Location',
                'match_type' => 'contains',
                'is_active' => true,
            ],
            [
                'trigger' => 'location',
                'response' => "EME's Apartelle is located in the scenic General Luna, Philippines, offering easy access to surfing spots, local restaurants, and transport hubs.",
                'follow_up_question' => 'Would you like contact details to reach our front desk?',
                'suggested_triggers' => 'Contact Us, Rooms',
                'match_type' => 'contains',
                'is_active' => true,
            ],
            [
                'trigger' => 'wifi',
                'response' => 'Yes, we offer complimentary high-speed WiFi internet access throughout the entire apartelle property for all registered guests.',
                'follow_up_question' => 'Would you like to know about other amenities we offer?',
                'suggested_triggers' => 'Amenities, Location',
                'match_type' => 'contains',
                'is_active' => true,
            ],
            [
                'trigger' => 'amenities',
                'response' => "To ensure a pleasant stay, we provide:\n- Free High-Speed WiFi\n- Secure Free Parking\n- 24/7 Security & CCTV\n- Kitchen Facilities (in select suites)\n- Daily Housekeeping",
                'follow_up_question' => 'Do you have questions about parking or check-in rules?',
                'suggested_triggers' => 'Parking, Location, WiFi',
                'match_type' => 'contains',
                'is_active' => true,
            ],
            [
                'trigger' => 'parking',
                'response' => 'Complimentary secure parking is available on-site for all EME\'s Apartelle guests.',
                'follow_up_question' => 'Would you like to get our physical address and location details?',
                'suggested_triggers' => 'Location, Contact Us',
                'match_type' => 'contains',
                'is_active' => true,
            ],
            [
                'trigger' => 'cancel',
                'response' => "Our cancellation policies are:\n- Bookings can be cancelled within 1 hour of creation.\n- Confirmed reservations have a 24-hour cutoff before the scheduled check-in time.\n- After check-in or completion, bookings cannot be cancelled.",
                'follow_up_question' => 'Do you need help with cancellation or disputing a booking?',
                'suggested_triggers' => 'Contact Us, Rooms',
                'match_type' => 'contains',
                'is_active' => true,
            ],
            [
                'trigger' => 'contact',
                'response' => "You can reach us through our contact page or speak directly to the front desk at +63 912 345 6789 or via email at support@emesapartelle.com.",
                'follow_up_question' => 'Is there anything else I can help you with?',
                'suggested_triggers' => 'Rooms, Rates',
                'match_type' => 'contains',
                'is_active' => true,
            ],
        ];

        foreach ($rules as $rule) {
            ChatbotResponse::create($rule);
        }
    }
}
