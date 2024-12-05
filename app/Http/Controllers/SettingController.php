<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Artisan;

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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
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
                'client_key' => Crypt::encrypt($request->client_key), // Enkripsi sebelum menyimpan
                'server_key' => Crypt::encrypt($request->server_key), // Enkripsi sebelum menyimpan
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $setting = Setting::find($id);

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found',
            ], 404);
        }

        try {
            // Nonaktifkan semua pengaturan lainnya
            Setting::where('status', 'active')->update(['status' => 'inactive']);

            // Perbarui data yang dipilih menjadi active
            $setting->update(['status' => 'active']);

            // Perbarui file .env dengan data pengaturan yang diaktifkan
            $this->updateEnvFile([
                'MIDTRANS_MERCHANT_ID' => $setting->merchant_id,
                // 'MIDTRANS_CLIENT_KEY' => $setting->client_key,
                'MIDTRANS_CLIENT_KEY' => Crypt::decrypt($setting->client_key),
                'MIDTRANS_SERVER_KEY' => Crypt::decrypt($setting->server_key),
                // 'MIDTRANS_SERVER_KEY' => $setting->server_key,
                'MIDTRANS_ENVIRONMENT' => $setting->environment,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Setting activated successfully.',
                'data' => [
                    'id' => $setting->id,
                    'merchant_id' => $setting->merchant_id,
                    'client_key' => $setting->client_key,
                    'server_key' => $setting->server_key,
                    'environment' => $setting->environment,
                    'status' => $setting->status,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate setting.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update environment file.
     *
     * @param array $data
     * @return void
     */
    private function updateEnvFile(array $data)
    {
        $envPath = base_path('.env');
        $content = file_get_contents($envPath);

        foreach ($data as $key => $value) {
            $pattern = "/^{$key}=.*$/m";
            $replacement = "{$key}={$value}";

            // Jika variabel sudah ada di .env, ganti dengan nilai baru
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $replacement, $content);
            } else {
                // Jika tidak ada, tambahkan variabel baru di akhir file .env
                $content .= "\n{$replacement}";
            }
        }

        // Menyimpan kembali file .env dengan perubahan yang baru
        file_put_contents($envPath, $content);

        // Membersihkan dan menyegarkan cache konfigurasi Laravel
        Artisan::call('config:clear');
        Artisan::call('config:cache');
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

        $setting->delete();

        return response()->json(['message' => 'Setting deleted'], 200);
    }
}
