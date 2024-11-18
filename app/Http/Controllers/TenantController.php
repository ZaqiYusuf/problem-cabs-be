<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'tenants' => Tenant::all(),
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
        try{
            $request->validate([
                'name_tenant' => 'required',
            ], [
                'name_tenant.required' => 'The name tenant is required',
            ]);
    
            $tenant = Tenant::create([
                'name_tenant' => $request->name_tenant,
            ]);
    
            return response()->json([
                'success' => true,
                'message' => 'Tenant created',
                'tenant' => $tenant,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 409);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Tenant $tenant)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tenant $tenant)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $tenant = Tenant::find($id);

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found',
            ], 404);
        }

        try{
            $request->validate([
                'name_tenant' => 'required'
            ], [
                'name_tenant.required' => 'The name tenant is required',
            ]);
            $updated = $tenant->update([
                'name_tenant' => $request->name_tenant,
            ]);
    
            return response()->json([
                'success' => $updated,
                'message' => $tenant,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $tenant = Tenant::find($id);

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found',
            ], 404);
        }

        $deleted = $tenant->delete();

        return response()->json([
            'success' => $deleted,
            'message' => $deleted ? 'Tenant deleted' : 'Tenant delete failed',
        ], $deleted ? 200 : 409);
    }
}
