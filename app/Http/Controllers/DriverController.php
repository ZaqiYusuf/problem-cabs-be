<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'drivers' => Driver::all(),
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
                'name_driver' => 'required',
                'sim' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ], [
                'name_driver.required' => 'The driver name is required.',
                'sim.required' => 'The SIM file is required.'
            ]);

            if ($request->hasFile('sim')) {
                $simFile = $request->file('sim');
                $simFilename = strtolower(str_replace(' ', '-', $request->name_driver)) . '_sim.' . $simFile->getClientOriginalExtension();
                $simPath = $simFile->storeAs('sim', $simFilename, 'public');
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'SIM file not found',
                ], 404);
            }

            $driver = Driver::create([
                'name_driver' => $request->name_driver,
                'sim' => $simPath
            ]);

            return response()->json([
                'success' => true,
                'driver' => $driver,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Driver $driver)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Driver $driver)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $driver = Driver::find($id);

            if (!$driver) {
                return response()->json([
                    'success' => false,
                    'message' => 'Driver not found',
                ], 404);
            }

            $request->validate([
                'name_driver' => 'required',
                'sim' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ], [
                'name_driver.required' => 'The driver name is required.',
                'sim.required' => 'The SIM file is required.'
            ]);

            if ($request->hasFile('sim')) {
                $simFile = $request->file('sim');
                $simFilename = strtolower(str_replace(' ', '-', $request->name_driver)) . '_sim.' . $simFile->getClientOriginalExtension();
                $simPath = $simFile->storeAs('sim', $simFilename, 'public');
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'SIM file not found',
                ], 404);
            }

            $updated = $driver->update([
                'name_driver' => $request->name_driver,
                'sim' => $simPath
            ]);

            return response()->json([
                'success' => $updated,
                'driver' => $driver,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 409);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $driver = Driver::find($id);

        if ($driver) {
            $driver->delete();

            return response()->json([
                'success' => true,
                'message' => 'Driver deleted successfully',
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Driver not found',
            ], 404);
        }
    }
}
