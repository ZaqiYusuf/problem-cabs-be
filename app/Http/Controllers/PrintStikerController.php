<?php
namespace App\Http\Controllers;

use App\Models\Vehicle;
// use Barryvdh\DomPDF\PDF;
use App\Models\Customer;
// use Barryvdh\DomPDF\PDF;
use App\Models\ProcessImk;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PermittedVehicle;
use Illuminate\Routing\Controller;
use App\Models\Tenant; // Tambahkan model Tenant

class PrintStikerController extends Controller
{
    public function generatePdf($id)
    {
        try {
            // Cari data PermittedVehicle berdasarkan ID
            $permittedVehicle = PermittedVehicle::where('id', $id)
                ->with(['location' => function ($query) {
                    $query->select('id', 'location'); // Ambil kolom id dan lokasi
                }])
                ->first();
    
            if (!$permittedVehicle) {
                return response()->json(['error' => 'Permitted Vehicle not found'], 404);
            }
    
            // Tentukan area berdasarkan relasi location
            $address = $permittedVehicle->location
                ? $permittedVehicle->location->location // Nama lokasi dari relasi
                : '-'; // Default jika tidak ada lokasi
    
            // Ambil data dari tabel ProcessImk
            $processImk = ProcessImk::where('id', $permittedVehicle->id_imk)->first();
            $customerId = $processImk ? $processImk->customer_id : null;
            $tenantId = $processImk ? $processImk->tenant_id : null;
    
            // Ambil nama customer berdasarkan customer_id
            $customer = $customerId ? Customer::find($customerId) : null;
    
            // Jika nama customer tidak ditemukan, ambil nama tenant dari tenant_id
            $nameCustomer = $customer && $customer->name_customer
                ? $customer->name_customer // Nama customer
                : ($tenantId
                    ? Tenant::find($tenantId)->name_tenant // Nama tenant
                    : 'Unknown Customer'); // Default jika tenant juga tidak ditemukan
    
            // Cek nomor stiker
            if ($permittedVehicle->number_stiker >= 1 && $permittedVehicle->number_stiker <= 50) {
                $nameCustomer = 'KIE'; // Ganti name_customer menjadi KIE jika number_stiker antara 1-50
            }
    
            // Format tanggal expired_at ke format "12 Desember 2024"
            $expiredAtFormatted = Carbon::parse($permittedVehicle->expired_at)->translatedFormat('d F Y');
    
            // Siapkan data untuk dikirim ke view
            $data = [
                'nomor_stiker' => $permittedVehicle->number_stiker, // Nomor stiker
                'name_customer' => $nameCustomer, // Nama customer atau tenant
                'no_lambung' => $permittedVehicle->no_lambung, // Nomor lambung
                'plate_number' => $permittedVehicle->plate_number, // Nomor plat
                'area' => $address, // Alamat atau lokasi
                'expired_at' => $expiredAtFormatted, // Tanggal expired
            ];
    
            // Generate PDF
            $pdf = PDF::loadView('pdf.stiker', $data);
    
            // Simpan file PDF sementara untuk debugging
            $debugPath = storage_path('app/public/debug_stiker.pdf');
            $pdf->save($debugPath); // File disimpan di folder public untuk pengecekan manual
    
            // Simpan perubahan status stiker menjadi "printed"
            if ($permittedVehicle->status_stiker == 'unprinted') {
                $permittedVehicle->status_stiker = 'printed'; // Update status menjadi printed
                $permittedVehicle->save(); // Simpan perubahan status
            }
    
            // Kembalikan file PDF sebagai respons
            return response()->streamDownload(
                fn() => print($pdf->output()), // Output isi PDF
                'stiker_' . $permittedVehicle->number_stiker . '.pdf',
                ['Content-Type' => 'application/pdf'] // Header respons
            );
        } catch (\Exception $e) {
            // Tangani jika ada error
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
