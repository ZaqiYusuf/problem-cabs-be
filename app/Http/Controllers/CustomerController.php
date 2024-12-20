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
    public function index(Request $request)
    {
        try {
            $tenantName = $request->query('tenant');

            $query = ProcessImk::select('customer_id', 'tenant_id')->with('customer', 'tenant');

            if ($tenantName) {
                $query->whereHas('tenant', function ($q) use ($tenantName) {
                    $q->where('name_tenant', 'like', '%' . $tenantName . '%');
                });
            }

            // Ambil data yang difilter
            $processImks = $query->get();

            return response()->json([
                'success' => true,
                'processImks' => $processImks,
            ], 200);
        } catch (\Exception $e) {
            // Tangani potensi error
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve Process IMK data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function getAllData(Request $request)
    {
        return response()->json([
            'success' => true,
            'customers' => Customer::with('tenants')->get(),
        ], 200);

    }


    public function getById(Request $request, $id)
    {
        // Ambil data Customer berdasarkan user_id, sertakan relasi tenants
        $customer = Customer::with('tenants')->where('user_id', $id)->first();
    
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
            ], 404);
        }
    
        return response()->json([
            'success' => true,
            'customer' => $customer,
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
            // Validasi input dari request
            $req = $request->validate([
                'tenant_id' => 'required',
                'name_customer' => 'required',
                'address' => 'required',
                'email' => 'required|email|unique:customers',
                'pic' => 'required',
                'pic_number' => 'required',
                'upload_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ]);

            // Memeriksa apakah file di-upload
            if ($request->hasFile('upload_file')) {
                $file = $request->file('upload_file');
                $filename = strtolower(str_replace(' ', '-', $request->name_customer)) . '_surat.' . $file->getClientOriginalExtension();
                // Menyimpan file ke storage
                $path = $file->storeAs('dokumen', $filename, 'public');
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found',
                ], 404);
            }

            DB::beginTransaction();

            // Menetapkan status ke 'pending'
            $status = 'pending';

            // Membuat data customer dengan status pending
            $customer = Customer::create([
                'tenant_id' => $request->tenant_id,
                'name_customer' => $request->name_customer,
                'address' => $request->address,
                'email' => $request->email,
                'upload_file' => $path,
                'pic' => $request->pic,
                'status' => $status,
                'pic_number' => $request->pic_number,
            ]);

            DB::commit();

            // Mengembalikan respon sukses
            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully. Awaiting admin approval.',
                'customer' => $customer,
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Customer creation failed',
                'details' => $e->getMessage(),
            ], 409);
        }
    }





    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
    }

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
        // dd($customer);

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
                // 'number_tenant' => $request->number_tenant,
                // 'number_tenant' => $request->number_tenant,
                'tenant_id' => $request->number_tenant,
                'name_customer' => $request->name_customer,
                'address' => $request->address,
                'email' => $request->email,
                'pic' => $request->pic,
                'status' => $request->status,
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


    public function approve(Request $request, $id)
{
    try {
        DB::beginTransaction();

        // Mencari data customer berdasarkan ID
        $customer = Customer::findOrFail($id);

        // Memeriksa apakah status customer masih pending
        if ($customer->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Customer already approved or rejected',
            ], 400);
        }

        // Membuat password berdasarkan email customer
        $password = str_replace('@gmail.com', '', $customer->email) . rand(000, 999);

        // Membuat akun user
        $user = User::create([
            'email' => $customer->email,
            'password' => bcrypt($password),
        ]);

        // Menyambungkan customer dengan akun user dan memperbarui status customer
        $customer->update([
            'status' => 'approved',
            'user_id' => $user->id, // Gunakan ID dari user yang baru dibuat
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Customer approved and account created successfully',
            'customer' => $customer,
            'password' => $password, // Untuk keperluan debugging, hapus di production
        ], 200);
    } catch (Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Customer approval failed',
            'details' => $e->getMessage(),
        ], 409);
    }
}

}
