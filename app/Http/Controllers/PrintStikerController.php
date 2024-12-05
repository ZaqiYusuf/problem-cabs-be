<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\ProcessImk;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PermittedVehicle;
use App\Http\Controllers\Controller;

class PrintStikerController extends Controller
{
    public function generatePdf(Request $request)
    {
        // Validasi input
        $request->validate([
            'vehicle_id' => 'required|integer',
        ]);
    
        // Ambil data kendaraan berdasarkan vehicle_id
        $vehicle = Vehicle::find($request->vehicle_id);
        if (!$vehicle) {
            return response()->json(['error' => 'Vehicle not found'], 404);
        }
    
        // Ambil data customer terkait kendaraan
        $customer = $vehicle->customer()->first();
        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], 404);
        }
    
        // Ambil data izin kendaraan dari tabel PermittedVehicle
        $permittedVehicle = PermittedVehicle::where('vehicle_id', $request->vehicle_id)->first();
    if (!$permittedVehicle) {
        return response()->json(['error' => 'Permitted Vehicle not found'], 404);
    }

    // Format tanggal expired_at ke format "12 Desember 2024"
    $expiredAtFormatted = Carbon::parse($permittedVehicle->expired_at)->translatedFormat('d F Y');

    // Ambil alamat dari customer
    $address = $customer->address;

    // Siapkan data untuk dikirim ke view
    $data = [
        'nomor_stiker' => $vehicle->number_stiker, // Nomor stiker dari kendaraan
        'name_customer' => $customer->name_customer,
        'no_lambung' => $vehicle->no_lambung,
        'plate_number' => $vehicle->plate_number,
        'area' => $address, // Alamat dari customer
        // 'expired_at' => $expiredAtFormatted, // Tanggal expired dengan format baru
        'expired_at' => Carbon::parse($permittedVehicle->expired_at)->translatedFormat('d F Y'),
    ];

        
    
        // Ambil alamat dari customer
        $address = $customer->address;
    
        // Siapkan data untuk dikirim ke view
        $data = [
            'nomor_stiker' => $vehicle->number_stiker, // Nomor stiker dari kendaraan
            'name_customer' => $customer->name_customer,
            'no_lambung' => $vehicle->no_lambung,
            'plate_number' => $vehicle->plate_number,
            'area' => $address, // Alamat dari customer
            'expired_at' => $permittedVehicle->expired_at, // Tanggal expired
        ];
    
        // Generate PDF
        $pdf = PDF::loadView('pdf.stiker', $data);
    
        // Simpan perubahan status stiker menjadi "printed" setelah PDF berhasil dibuat
        if ($vehicle->status_stiker == 'unprinted') {
            $vehicle->status_stiker = 'printed'; // Update status menjadi printed
            $vehicle->save(); // Simpan perubahan status
        }
    
        // Kembalikan file PDF sebagai respons
        return $pdf->download('stiker.pdf');
    }
    }    