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
        // Validate the request
        $request->validate([
            'vehicle_id' => 'required|integer',
        ]);

        // Fetch the necessary data using the vehicle ID from the request
        $vehicle = Vehicle::find($request->vehicle_id);
        if (!$vehicle) {
            return response()->json(['error' => 'Vehicle not found'], 404);
        }

        $customer = $vehicle->customer()->first();
        // Pass data to the view

        $imk = ProcessImk::where('id_customer', $customer->id)->first();

        // dd($imk, $customer, $vehicle);

        $data = [
            'nomor_stiker' => $imk->number_stiker, // Assuming you have this field
            'name_customer' => $customer->name_customer,
            'no_lambung' => $vehicle->no_lambung,
            'plate_number' => $vehicle->plate_number,
            'area' => 'Tj Harapan',
        ];

        // dd($data);

        // Load the view and generate the PDF
        $pdf = PDF::loadView('pdf.stiker', $data);

        // Return the PDF as a response
        return $pdf->download('stiker.pdf');
    }
}
