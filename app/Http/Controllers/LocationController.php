<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'locations' => Location::all(),
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'location' => 'required',
            ], [
                'location.required' => 'Please enter location name',
            ]);

            $location = Location::create([
                'location' => $request->location
            ]);

            return response()->json([
                'success' => true,
                'message' => $location
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'details' => $e->getMessage(),
            ], 409);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Location $location)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Location $location)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $location = Location::find($id);

        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'Location not found',
            ], 404);
        }

        try{
            $request->validate([
                'location' => 'required',
            ], [
                'location.required' => 'Please enter location name',
            ]);

            $updated = $location->update([
                'location' => $request->location,
            ]);
    
            return response()->json([
                'success' => true,
                'location' => $location,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'details' => $e->getMessage(),
            ], 409);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $location = Location::find($id);

        if ($location) {
            $location->delete();
            return response()->json(['message' => 'Location deleted'], 200);
        } else {
            return response()->json(['message' => 'Location not found'], 404);
        }
    }
}
