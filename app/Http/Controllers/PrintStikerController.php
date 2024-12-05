<?php

namespace App\Http\Controllers;

use App\Models\PermittedVehicle;
use App\Models\Vehicle;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\ProcessImk;

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
    
        // Ambil data IMK jika diperlukan
        // $imk = ProcessImk::where('customer_id', $customer->id)->first();
    
        // Ambil alamat dari customer
        $address = $customer->address;
    
        // Siapkan data untuk dikirim ke view
        $data = [
            'nomor_stiker' => $vehicle->number_stiker, // Nomor stiker dari kendaraan
            'name_customer' => $customer->name_customer,
            'no_lambung' => $vehicle->no_lambung,
            'plate_number' => $vehicle->plate_number,
            'area' => $address, // Alamat dari customer
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