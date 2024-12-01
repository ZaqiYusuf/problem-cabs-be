<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Vehicle;

class VehicleController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'vehicles' => Vehicle::all()->load('category','customer'),
        ], 200);
    }

    public function store(Request $request)
    {
        try{
            $request->validate([
                'plate_number' => 'required',
                'no_lambung' => 'required',
                'stnk' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
                'category_id' => 'required',
                'customer_id' => 'required'
            ], [
                'plate_number.required' => 'The plate number is required.',
                'no_lambung.required' => 'The no lambung is required.',
                'stnk.required' => 'The STNK file is required.',
                'category_id.required' => 'The category is required.',
                'customer_id.required' => 'The customer is required.'
            ]);
    
    
            if ($request->hasFile('stnk')) {
                $stnkFile = $request->file('stnk');
                $stnkFilename = strtolower(str_replace(' ', '-', $request->plate_number)) . '_stnk.' . $stnkFile->getClientOriginalExtension();
                $stnkPath = $stnkFile->storeAs('stnk', $stnkFilename, 'public');
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'STNK file not found',
                ], 404);
            }
    
            // Vehicle::where('id_customer', $request->id_customer)
            //     ->where('status', true)
            //     ->update(['status' => false]);
    
            $number = Vehicle::max('number_stiker') ?? 0;
            $newNumber = $number + 1;
    
            $vehicle = Vehicle::create([
                'plate_number' => $request->plate_number,
                'no_lambung' => $request->no_lambung,
                'number_stiker' => $newNumber,
                'stnk' => $stnkPath,
                'category_id' => $request->category_id,
                'customer_id' => $request->customer_id,
                'status' => true
            ]);
    
            return response()->json([
                'success' => true,
                'vehicle' => $vehicle
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::find($id);
        
        if (!$vehicle) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle not found'
            ], 404);
        }
        
        $request->validate([
            'customer_id' => 'required',
            'plate_number' => 'required',
            'no_lambung' => 'required',
            'name_driver' => 'required',
            'cargo' => 'required',
            'origin' => 'required',
        ]);

        $vehicle->update([
            'customer_id' => $request->customer_id,
            'plate_number' => $request->plate_number,
            'no_lambung' => $request->no_lambung,
            'name_driver' => $request->name_driver,
            'cargo' => $request->cargo,
            'origin' => $request->origin,
        ]);

        return response()->json([
            'success' => true,
            'vehicle' => $vehicle
        ], 200);
    }

    public function destroy($id)
    {
        $vehicle = Vehicle::find($id);

        if ($vehicle) {
            $vehicle->delete();

            return response()->json([
                'success' => true,
                'message' => 'Vehicle deleted successfully'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle not found'
            ], 404);
        }
    }
}
