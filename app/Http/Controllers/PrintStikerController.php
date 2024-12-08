<?php
namespace App\Http\Controllers;

use App\Models\Vehicle;
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
    // public function generatePdf(Request $request)
    // {
    //     // Validasi input
    //     $request->validate([
    //         'vehicle_id' => 'required|integer',
    //     ]);

    //     // Ambil data kendaraan berdasarkan vehicle_id
    //     $vehicle = Vehicle::find($request->vehicle_id);
    //     if (!$vehicle) {
    //         return response()->json(['error' => 'Vehicle not found'], 404);
    //     }

    //     // Ambil data izin kendaraan dari tabel PermittedVehicle
    //     $permittedVehicle = PermittedVehicle::where('vehicle_id', $request->vehicle_id)
    //         ->with('location') // Ambil relasi location
    //         ->first();
    //     if (!$permittedVehicle) {
    //         return response()->json(['error' => 'Permitted Vehicle not found'], 404);
    //     }

    //     // Ambil data customer terkait kendaraan
    //     $customer = $vehicle->customer()->first(); // Customer bisa null
    //     $address = $customer ? $customer->address : null; // Default alamat jika customer null

    //     // Tentukan alamat berdasarkan kondisi
    //     if ($vehicle->number_stiker >= 1 && $vehicle->number_stiker <= 50 && !$address) {
    //         // Jika nomor stiker 1-50 dan customer tidak ada, ambil nama lokasi dari tabel locations
    //         $address = $permittedVehicle->location
    //             ? $permittedVehicle->location->location // Ambil nama lokasi
    //             : '-'; // Default jika tidak ada lokasi
    //     } elseif (!$address) {
    //         $address = '-'; // Default alamat jika tidak ada customer dan bukan nomor stiker 1-50
    //     }

    //     // Tentukan nama customer berdasarkan nomor stiker
    //     $nameCustomer = $vehicle->number_stiker >= 1 && $vehicle->number_stiker <= 50
    //         ? 'KIE'
    //         : ($customer ? $customer->name_customer : 'Unknown Customer');

    //     // Format tanggal expired_at ke format "12 Desember 2024"
    //     $expiredAtFormatted = Carbon::parse($permittedVehicle->expired_at)->translatedFormat('d F Y');

    //     // Siapkan data untuk dikirim ke view
    //     $data = [
    //         'nomor_stiker' => $vehicle->number_stiker, // Nomor stiker dari kendaraan
    //         'name_customer' => $nameCustomer,
    //         'no_lambung' => $vehicle->no_lambung,
    //         'plate_number' => $vehicle->plate_number,
    //         'area' => $address, // Alamat dari customer atau nama lokasi
    //         'expired_at' => $expiredAtFormatted, // Tanggal expired
    //     ];

    //     // Generate PDF
    //     $pdf = PDF::loadView('pdf.stiker', $data);

    //     // Simpan perubahan status stiker menjadi "printed" setelah PDF berhasil dibuat
    //     if ($vehicle->status_stiker == 'unprinted') {
    //         $vehicle->status_stiker = 'printed'; // Update status menjadi printed
    //         $vehicle->save(); // Simpan perubahan status
    //     }

    //     // Kembalikan file PDF sebagai respons
    //     return $pdf->download('stiker.pdf');
    // }

    public function generatePdf(Request $request)
    {
        // Validasi input
        $request->validate([
            'vehicle_id' => 'required|integer', // ID dari tabel PermittedVehicle
        ]);
    
        // Ambil data dari tabel PermittedVehicle berdasarkan ID
        $permittedVehicle = PermittedVehicle::where('id', $request->vehicle_id)
            ->with('location') // Ambil relasi location
            ->first();
    
        if (!$permittedVehicle) {
            return response()->json(['error' => 'Permitted Vehicle not found'], 404);
        }
    
        // Ambil data customer melalui relasi id_imk -> ProcessImk -> Customer
        $processImk = ProcessImk::where('id', $permittedVehicle->id_imk)
            ->with('customer') // Ambil relasi customer
            ->first();
    
        $customer = $processImk ? $processImk->customer : null; // Data customer bisa null
        $address = $customer ? $customer->address : null; // Default alamat jika customer null
    
        // Tentukan alamat berdasarkan kondisi
        if ($permittedVehicle->number_stiker >= 1 && $permittedVehicle->number_stiker <= 50 && !$address) {
            $address = $permittedVehicle->location
                ? $permittedVehicle->location->location // Ambil nama lokasi
                : '-'; // Default jika tidak ada lokasi
        } elseif (!$address) {
            $address = '-'; // Default alamat jika tidak ada customer dan bukan nomor stiker 1-50
        }
    
        // Tentukan nama customer berdasarkan nomor stiker
        $nameCustomer = $permittedVehicle->number_stiker >= 1 && $permittedVehicle->number_stiker <= 50
            ? 'KIE'
            : ($customer ? $customer->name_customer : 'Unknown Customer');
    
        // Format tanggal expired_at ke format "12 Desember 2024"
        $expiredAtFormatted = Carbon::parse($permittedVehicle->expired_at)->translatedFormat('d F Y');
    
        // Siapkan data untuk dikirim ke view
        $data = [
            'nomor_stiker' => $permittedVehicle->number_stiker, // Nomor stiker
            'name_customer' => $nameCustomer, // Nama customer
            'no_lambung' => $permittedVehicle->no_lambung, // Nomor lambung
            'plate_number' => $permittedVehicle->plate_number, // Nomor plat
            'area' => $address, // Alamat atau lokasi
            'expired_at' => $expiredAtFormatted, // Tanggal expired
        ];
    
        // Generate PDF
        $pdf = PDF::loadView('pdf.stiker', $data);
    
        // Simpan perubahan status stiker menjadi "printed" setelah PDF berhasil dibuat
        if ($permittedVehicle->status_stiker == 'unprinted') {
            $permittedVehicle->status_stiker = 'printed'; // Update status menjadi printed
            $permittedVehicle->save(); // Simpan perubahan status
        }
    
        // Kembalikan file PDF sebagai respons
        return $pdf->download('stiker.pdf');
    }
    
    
}
