<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ProcessImk;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\Catch_;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Retrieve only tenant_id and customer columns
            $processImks = ProcessImk::select('customer', 'tenant_id')->get()->load('tenant');

            return response()->json([
                'success' => true,
                'processImks' => $processImks,
            ], 200);
        } catch (\Exception $e) {
            // Handle potential exceptions
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve Process IMK data.',
                'error' => $e->getMessage(),
            ], 500);
        }
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
            $req = $request->validate([
                'number_tenant' => 'required',
                'name_customer' => 'required',
                'address' => 'required',
                'email' => 'required|email|unique:customers',
                'pic' => 'required',
                'pic_number' => 'required',
                'upload_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ]);

            if ($request->hasFile('upload_file')) {
                $file = $request->file('upload_file');
                $filename = strtolower(str_replace(' ', '-', $request->name_customer)) . '_surat.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('dokumen', $filename, 'public');
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found',
                ], 404);
            }

            DB::beginTransaction();
            $password = str_replace('@gmail.com', '', $request->email) . rand(000, 999);
            $user = User::create([
                'email' => $request->email,
                'password' => bcrypt($password),
            ]);

            $customer = Customer::create([
                'user_id' => $user->id,
                'number_tenant' => $request->number_tenant,
                'name_customer' => $request->name_customer,
                'address' => $request->address,
                'email' => $request->email,
                'pic' => $request->pic,
                'pic_number' => $request->pic_number,
                'upload_file' => $path,
            ]);

            $customer['password'] = $password;

            DB::commit();
            if ($customer) {
                return response()->json([
                    'success' => true,
                    'message' => 'Customer created successfully',
                    'customer' => $customer,
                ], 201);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Customer creation failed',
                'details' => $e->getMessage()
            ], 409);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer) {}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::find($id);
        dd($customer);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
            ], 404);
        }

        try {
            if ($request->hasFile('upload_file')) {
                $file = $request->file('upload_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('dokumen', $filename, 'public');
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found',
                ], 404);
            }

            $updated = $customer->update([
                'number_tenant' => $request->number_tenant,
                'name_customer' => $request->name_customer,
                'address' => $request->address,
                'email' => $request->email,
                'pic' => $request->pic,
                'pic_number' => $request->pic_number,
                'upload_file' => $request->$path,
            ]);

            return response()->json([
                'success' => $updated,
                'customer' => $customer,
            ], 200);
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
        $customer = Customer::find($id);

        if ($customer) {
            $user = $customer->user;
            $customer->delete();
            if ($user) {
                $user->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Customer and User deleted successfully',
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
            ], 404);
        }
    }
}
