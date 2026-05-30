<?php
  
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class AmenityController extends Controller
{
    /**
     * Display a listing of the resource.
     * Cached for 10 minutes since amenities rarely change.
     */
    public function index()
    {
        $amenities = Cache::remember('amenities_list', 600, function () {
            return Amenity::all();
        });

        return response()->json($amenities);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'icon' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $amenity = Amenity::create($request->all());

        // Invalidate cache after mutation
        Cache::forget('amenities_list');

        return response()->json($amenity, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $amenity = Amenity::find($id);

        if (!$amenity) {
            return response()->json(['message' => 'Amenity not found'], 404);
        }

        return response()->json($amenity);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $amenity = Amenity::find($id);

        if (!$amenity) {
            return response()->json(['message' => 'Amenity not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'icon' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $amenity->update($request->all());

        // Invalidate cache after mutation
        Cache::forget('amenities_list');

        return response()->json($amenity);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $amenity = Amenity::find($id);

        if (!$amenity) {
            return response()->json(['message' => 'Amenity not found'], 404);
        }

        $amenity->delete();

        // Invalidate cache after mutation
        Cache::forget('amenities_list');

        return response()->json(['message' => 'Amenity deleted successfully']);
    }
}
