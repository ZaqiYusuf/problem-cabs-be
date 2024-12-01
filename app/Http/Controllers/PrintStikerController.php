<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\ProcessImk;

class PrintStikerController extends Controller
{
    public function generatePdf(Request $request)
    {
        // Validasi body form-data
        $request->validate([
            'vehicle_id' => 'required|integer', // Pastikan vehicle_id dikirim
        ]);
    
        // Ambil vehicle_id dari body form-data
        $vehicleId = $request->input('vehicle_id');
    
        // Cari vehicle berdasarkan vehicle_id
        $vehicle = Vehicle::find($vehicleId);
    
        if (!$vehicle) {
            return response()->json(['error' => 'Vehicle not found'], 404);
        }
    
        // Ambil customer terkait vehicle
        $customer = $vehicle->customer()->first();
    
        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], 404);
        }
    
        // Ambil IMK terkait customer
        $imk = ProcessImk::where('customer_id', $customer->id)->first();
    
        if (!$imk) {
            return response()->json(['error' => 'IMK not found'], 404);
        }
    
        // Data untuk PDF
        $data = [
            // 'nomor_stiker' => $imk->number_stiker, // Pastikan kolom ini ada
            'nomor_stiker' => $vehicle->number_stiker, // Pastikan kolom ini ada
            'name_customer' => $customer->name_customer,
            'no_lambung' => $vehicle->no_lambung,
            'plate_number' => $vehicle->plate_number,
            'area' => 'Tj Harapan',
        ];
    
        // Generate PDF
        $pdf = PDF::loadView('pdf.stiker', $data);
    
        // Return PDF sebagai respons
        return $pdf->download('stiker.pdf');
    }
}
