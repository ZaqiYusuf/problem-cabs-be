<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'packages' => Package::all(),
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
                'item' => 'required',
                'type' => 'required',
                'periode' => 'required',
                'periodeType' => 'required',
                'price' => 'required',
                'detail' => 'required',
            ], [
                'item.required' => 'Please enter item name',
                'type.required' => 'Please enter type',
                'periode.required' => 'Please enter periode',
                'periodeType.required' => 'Please enter periode type',
                'price.required' => 'Please enter price',
                'detail.required' => 'Please enter details',
            ]);

            $package = Package::create([
                'type' => $request->type,
                'item' => $request->item,
                'periode' => $request->periode,
                'periodeType' => $request->periodeType,
                'price' => $request->price,
                'detail' => $request->detail,
            ]);

            return response()->json([
                'success' => true,
                'message' => $package
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
    public function show(Package $package)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Package $package)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $package = Package::find($id);

        if (!$package) {
            return response()->json([
                'success' => false,
                'message' => 'Package not found'
            ], 404);
        }

        try {
            $request->validate([
                'item' => 'required',
                'periode' => 'required',
                'periodeType' => 'required',
                'type' => 'required',
                'price' => 'required',
                'detail' => 'required',
            ], [
                'item.required' => 'Please enter item name',
                'periode.required' => 'Please enter periode',
                'periodeType.required' => 'Please enter periode type',
                'type.required' => 'Please enter type',
                'price.required' => 'Please enter price',
                'detail.required' => 'Please enter details',
            ]);

            $package->update([
                'type' => $request->type,
                'item' => $request->item,
                'periode' => $request->periode,
                'periodeType' => $request->periodeType,
                'price' => $request->price,
                'detail' => $request->detail,
            ]);

            return response()->json([
                'success' => true,
                'message' => $package
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
        $package = Package::find($id);

        if (!$package) {
            return response()->json([
                'success' => false,
                'message' => 'Package not found'
            ], 404);
        }

        try {
            $package->delete();

            return response()->json([
                'success' => true,
                'message' => 'Package deleted'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'details' => $e->getMessage(),
            ], 409);
        }
    }
}
