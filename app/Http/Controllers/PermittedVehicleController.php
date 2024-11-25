<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\PermittedVehicle;
use Carbon\Carbon;
use App\Models\Periode;
use Illuminate\Http\Request;

class PermittedVehicleController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'Permitted Vehicle' => PermittedVehicle::all()->load('package', 'processImk', 'location'),
        ]);
    }
    public function store(Request $request)
{
    try {
        $request->validate([
            'id_imk' => 'required',
            'plate_number' => 'required',
            'no_lambung' => 'required',
            'stnk' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'driver_name' => 'required',
            'sim' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'package_id' => 'required',
            'location_id' => 'required',
            'cargo' => 'required',
            'origin' => 'required',
            'start_date' => 'required|date',
            'expired_at' => 'required|date',
        ], [
            'id_imk.required' => 'Please enter id imk',
            'plate_number.required' => 'Please enter plate number',
            'no_lambung.required' => 'Please enter no lambung',
            'stnk.required' => 'Please upload the STNK file',
            'driver_name.required' => 'Please enter driver name',
            'sim.required' => 'Please upload the SIM file',
            'package_id.required' => 'Please enter package id',
            'location_id.required' => 'Please enter location id',
            'cargo.required' => 'Please enter cargo',
            'origin.required' => 'Please enter origin',
            'start_date.required' => 'Please enter start date',
            'expired_at.required' => 'Please enter expiration date',
        ]);

        // Simpan file SIM
        $simFile = $request->file('sim');
        $simFilename = strtolower(str_replace(' ', '-', $request->driver_name)) . '_sim.' . $simFile->getClientOriginalExtension();
        $simPath = $simFile->storeAs('sim', $simFilename, 'public');

        // Simpan file STNK
        $stnkFile = $request->file('stnk');
        $stnkFilename = strtolower(str_replace(' ', '-', $request->plate_number)) . '_stnk.' . $stnkFile->getClientOriginalExtension();
        $stnkPath = $stnkFile->storeAs('stnk', $stnkFilename, 'public');

        // Generate nomor stiker baru
        $number = PermittedVehicle::max('number_stiker') ?? 0;
        $newNumber = $number + 1;

        // Simpan data kendaraan
        PermittedVehicle::create([
            'package_id' => $request->package_id,
            'plate_number' => $request->plate_number,
            'no_lambung' => $request->no_lambung,
            'stnk' => $stnkPath,
            'driver_name' => $request->driver_name,
            'sim' => $simPath,
            'number_stiker' => $newNumber,
            'location_id' => $request->location_id,
            'cargo' => $request->cargo,
            'origin' => $request->origin,
            'start_date' => $request->start_date,
            'expired_at' => $request->expired_at,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data kendaraan berhasil disimpan',
        ], 201);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}


    // public function store(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'id_imk' => 'required',
    //             'plate_number' => 'required',
    //             'no_lambung' => 'required',
    //             'stnk' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
    //             'package_id' => 'required',
    //             'location_id' => 'required',
    //             'cargo' => 'required',
    //             'origin' => 'required',
    //             'start_date' => 'required',
    //             'expired_at' => 'required',
    //         ], [
    //             'id_imk.required' => 'Please enter id imk',
    //             'plate_number.required' => 'Please enter plate number',
    //             'no_lambung.required' => 'Please enter no lambung',
    //             'stnk.required' => 'Please enter stnk',
    //             'driver_name.required' => 'Please enter driver name',
    //             'sim.required' => 'Please enter sim',
    //             'package_id.required' => 'Please enter package id',
    //             'location_id.required' => 'Please enter location id',
    //             'cargo.required' => 'Please enter cargo',
    //             'origin.required' => 'Please enter origin',
    //             'start_date.required' => 'Please enter start date',
    //             'expired_at.required' => 'Please enter expired at',
    //         ]);

    //         dd($request->all());
    //         foreach ($request->vehicles as $vehicle) {
    //             if ($request->hasFile('sim')) {
    //                 // 'sim' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
    //                 $simFile = $request->file('sim');
    //             // 'driver_name' => 'required',
    //                 $simFilename = strtolower(str_replace(' ', '-', $request->name_driver)) . '_sim.' . $simFile->getClientOriginalExtension();
    //                 $simPath = $simFile->storeAs('sim', $simFilename, 'public');
    //             } else {
    //                 return response()->json([
    //                     'success' => false,
    //                     'message' => 'SIM file not found',
    //                 ], 404);
    //             }
    
    //             if ($request->hasFile('stnk')) {
    //                 $stnkFile = $request->file('stnk');
    //                 $stnkFilename = strtolower(str_replace(' ', '-', $request->plate_number)) . '_stnk.' . $stnkFile->getClientOriginalExtension();
    //                 $stnkPath = $stnkFile->storeAs('stnk', $stnkFilename, 'public');
    //             } else {
    //                 return response()->json([
    //                     'success' => false,
    //                     'message' => 'STNK file not found',
    //                 ], 404);
    //             }
        
    //             // Vehicle::where('id_customer', $request->id_customer)
    //             //     ->where('status', true)
    //             //     ->update(['status' => false]);
        
    //             // $number = Vehicle::max('number_stiker') ?? 0;
    //             $number = PermittedVehicle::max('number_stiker') ?? 0;
    //             $newNumber = $number + 1;
    
    //             PermittedVehicle::create([
    //                 'package_id' => $vehicle['package_id'],
    //                 'plate_number' => $vehicle['plate_number'],
    //                 'no_lambung' => $vehicle['no_lambung'],
    //                 'stnk' => $stnkPath,
    //                 'driver_name' => $vehicle['driver_name'],
    //                 'sim' => $simPath,
    //                 'number_stiker' => $newNumber,
    //                 'location_id' => $vehicle['location_id'],
    //                 'cargo' => $vehicle['cargo'],
    //                 'origin' => $vehicle['origin'],
    //                 'start_date' => $vehicle['start_date'],
    //                 'expired_at' => $vehicle['expired_at'],
    //             ]);
    //         }
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function update(Request $request, $id)
    {
        $permitted = PermittedVehicle::find($id);

        if (!$permitted) {
            return response()->json([
                'success' => true,
                'message' => 'Permitted not found'
            ]);
        }

        $updated = $permitted->update([
            'expired_at' => $request->expired_at
        ]);

        return response()->json([
            'success' => $updated,
            'periode' => $permitted
        ]);
    }

    public function destroy($id)
    {
        $permitted = PermittedVehicle::find($id);

        if ($permitted) {
            $permitted->delete();
            return response()->json(['message' => 'Permitted deleted'], 200);
        } else {
            return response()->json(['message' => 'Permitted not found']);
        }
    }
}
