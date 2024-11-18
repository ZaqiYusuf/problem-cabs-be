<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'settings' => Setting::all(),
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
        // dd($request->all());
        try{
            $request->validate([
                'merchant_id' => 'required',
                'client_key' => 'required',
                'server_key' => 'required',
                'environment' => 'required',
                'status' => 'required',
            ], [
                'merchant_id.required' => 'Please enter merchant id',
                'client_key.required' => 'Please enter client key',
                'server_key.required' => 'Please enter server key',
                'environment.required' => 'Please enter environment',
                'status.required' => 'Please enter status',
            ]);

            $setting = Setting::create([
                'merchant_id' => $request->merchant_id,
                'client_key' => bcrypt($request->client_key),
                'server_key' => bcrypt($request->server_key),
                'environment' => $request->environment,
                'status' => $request->status,
            ]);

            return response()->json([
                'success' => true,
                'setting' => $setting
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
    public function show(Setting $setting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Setting $setting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $setting = Setting::find($id);

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found'
            ], 404);
        }

        try{
            $request->validate([
                'merchant_id' => 'required',
                'client_key' => 'required',
                'server_key' => 'required',
                'environment' => 'required',
                'status' => 'required',
            ], [
                'merchant_id.required' => 'Please enter merchant id',
                'client_key.required' => 'Please enter client key',
                'server_key.required' => 'Please enter server key',
                'environment.required' => 'Please enter environment',
                'status.required' => 'Please enter status',
            ]);

            $setting->update([
                'merchant_id' => $request->merchant_id,
                'client_key' => $request->client_key,
                'server_key' => $request->server_key,
                'environment' => $request->environment,
                'status' => $request->status,
            ]);

            return response()->json([
                'success' => true,
                'setting' => $setting
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
        $setting = Setting::find($id);

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found',
            ], 404);
        }

        if ($setting) {
            $setting->delete();
            return response()->json(['message' => 'Setting deleted'], 200);
        } else {
            return response()->json(['message' => 'Setting not found'], 404);
        }
    }
}
