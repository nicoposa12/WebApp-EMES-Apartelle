<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Get all settings
     */
    public function index()
    {
        $settings = SystemSetting::all()->pluck('value', 'key');
        
        // Ensure defaults if not set
        $defaults = [
            'store_name' => "EME's Apartelle",
            'email' => "admin@emesapartelle.com",
            'phone' => "+63 912 345 6789",
            'online_booking' => true,
            'maintenance_mode' => false,
            'email_notifications' => true
        ];

        // Merge defaults with DB values (DB values take precedence)
        $result = array_merge($defaults, $settings->toArray());

        // Cast booleans
        $booleanKeys = ['online_booking', 'maintenance_mode', 'email_notifications'];
        foreach ($booleanKeys as $key) {
            if (isset($result[$key])) {
                $result[$key] = filter_var($result[$key], FILTER_VALIDATE_BOOLEAN);
            }
        }

        return response()->json($result);
    }

    /**
     * Save multiple settings
     */
    public function store(Request $request)
    {
        $data = $request->all();

        foreach ($data as $key => $value) {
            SystemSetting::set($key, $value);
        }

        return response()->json(['message' => 'Settings updated successfully']);
    }

    /**
     * Get only public settings
     */
    public function public()
    {
        $publicKeys = ['store_name', 'online_booking', 'maintenance_mode'];
        $settings = SystemSetting::whereIn('key', $publicKeys)->pluck('value', 'key');
        
        $defaults = [
            'store_name' => "EME's Apartelle",
            'online_booking' => true,
            'maintenance_mode' => false
        ];

        $result = array_merge($defaults, $settings->toArray());

        // Cast booleans
        foreach (['online_booking', 'maintenance_mode'] as $key) {
            if (isset($result[$key])) {
                $result[$key] = filter_var($result[$key], FILTER_VALIDATE_BOOLEAN);
            }
        }

        return response()->json($result);
    }
}
