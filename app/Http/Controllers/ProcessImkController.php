<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Package;
use App\Models\Personnel;
use App\Models\ProcessImk;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\PermittedVehicle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ProcessImkController extends Controller
{
    public function index()
{
    try {
        // Ambil user yang sedang login
        $user = Auth::user();

        // Cek apakah user adalah admin
        $isAdmin = $user->level === 'admin'; // Ganti sesuai ENUM role Anda

        // Ambil data ProcessImk yang terkait dengan user atau semua data jika admin
        $query = ProcessImk::with([
            'customer',
            'vehicles.location',
            'vehicles.package',
            'personnels.package',
            'personnels.location',
            'tenant'
        ]);

        if (!$isAdmin) {
            // Jika bukan admin, filter hanya data milik user yang login
            $query->whereHas('customer', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $imks = $query->get();

        // Proses qty_vehicles dan total_cost
        $imks->map(function ($imk) {
            // Hitung jumlah kendaraan
            $imk->qty_vehicles = count($imk->vehicles);

            // Hitung total price dari vehicles packages
            $totalVehiclesPrice = $imk->vehicles->sum(function ($vehicle) {
                return $vehicle->package->price ?? 0;
            });

            // Hitung total price dari personnel packages
            $totalPersonnelPrice = $imk->personnels->sum(function ($personnel) {
                return $personnel->package->price ?? 0;
            });

            // Jumlahkan total harga kendaraan dan personnel
            $imk->total_cost = $totalVehiclesPrice + $totalPersonnelPrice;

            return $imk;
        });

        $response = [
            'success' => true,
            'process_imks' => $imks,
        ];

        if ($isAdmin) {
            // Jika admin, tambahkan informasi tambahan
            $statusCount = ProcessImk::where('status_imk', 1)->count();
            $now = Carbon::now();
            $overdueCount = PermittedVehicle::whereMonth('expired_at', $now->month)
                ->where('expired_at', '<', $now)
                ->count();

            $response['jumlah entry permits (active)'] = $statusCount;
            $response['jumlah terlambat bulan ini (overdue)'] = $overdueCount;
        }

        return response()->json($response, 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to retrieve Process IMK data.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


public function store(Request $request)
{
    try {
        DB::beginTransaction();

        $romanMonths = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII'
        ];

        $latestIMK = ProcessImk::latest()->first();
        $imk_number = $latestIMK
            ? "IMK " . str_pad((int) explode(' ', $latestIMK->imk_number)[1] + 1, 3, '0', STR_PAD_LEFT) . "/KIE/" . $romanMonths[date('n')] . "/" . date('Y')
            : "IMK 001/KIE/" . $romanMonths[date('n')] . "/" . date('Y');

        $request->validate([
            'document_number' => 'required',
            'registration_date' => 'required',
            'tenant_id' => 'required|exists:tenants,id',
            'customer_id' => 'required|exists:customers,id',
            'item' => 'required',
            'vehicles' => 'required|array',
            'vehicles.*.plate_number' => 'required',
            'vehicles.*.no_lambung' => 'required',
            'vehicles.*.stnk' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'vehicles.*.driver_name' => 'required',
            'vehicles.*.sim' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'vehicles.*.package_id' => 'required',
            'vehicles.*.location_id' => 'required',
            'vehicles.*.vehicle_id' => 'required',
            'vehicles.*.cargo' => 'required',
            'vehicles.*.origin' => 'required',
            'vehicles.*.start_date' => 'required',
            'personnels' => 'sometimes|array',
            'personnels.*.name' => 'required_with:personnels|string|nullable',
            'personnels.*.identity_number' => 'required_with:personnels|integer|nullable',
            'personnels.*.location_id' => 'required_with:personnels|integer|nullable',
            'personnels.*.package_id' => 'required_with:personnels|integer|nullable',
        ]);

        $process = ProcessImk::create([
            'imk_number' => $imk_number,
            'document_number' => $request->document_number,
            'registration_date' => $request->registration_date,
            'customer_id' => $request->customer_id,
            'tenant_id' => $request->tenant_id,
            'total_cost' => $request->total_cost,
            'item' => $request->item,
            'status_imk' => 0
        ]);

        foreach ($request->vehicles as $vehicle) {
            $simPath = $vehicle['sim']->storeAs('sim', strtolower(str_replace(' ', '-', $vehicle['driver_name'])) . '_sim.' . $vehicle['sim']->getClientOriginalExtension(), 'public');
            $stnkPath = $vehicle['stnk']->storeAs('stnk', strtolower(str_replace(' ', '-', $vehicle['plate_number'])) . '_stnk.' . $vehicle['stnk']->getClientOriginalExtension(), 'public');

            $number = PermittedVehicle::max('number_stiker') ?? 0;
            $newNumber = $number + 1;

            $package = Package::find($vehicle['package_id']);

if (preg_match('/(\d+)\s*(\w+)/', $package->periode, $matches)) {
    $amount = (int) $matches[1];
    $unit = strtolower($matches[2]);

    // Default expired_at jika tidak ada case yang cocok
    $expired_at = null;

    switch ($unit) {
        case 'month':
        case 'months':
            $expired_at = Carbon::parse($vehicle['start_date'])->addMonths($amount);
            break;
        case 'year':
        case 'years':
            $expired_at = Carbon::parse($vehicle['start_date'])->addYears($amount);
            break;
        case 'day':
        case 'days':
            $expired_at = Carbon::parse($vehicle['start_date'])->addDays($amount);
            break;
        case 'accidental':
        case 'accidentals':
            // Perhitungan khusus untuk accidental (opsional, bisa dihapus jika tidak diperlukan)
            $expired_at = Carbon::parse($vehicle['start_date'])->addDays($amount);
            break;
        default:
            return response()->json([
                'success' => false,
                'errors' => "Invalid time unit in package period: $unit",
            ], 422);
    }

    // Pastikan expired_at terdefinisi sebelum disimpan
    if (!$expired_at) {
        return response()->json([
            'success' => false,
            'errors' => 'Failed to calculate expired_at due to invalid package period.',
        ], 422);
    }

    // Simpan data ke PermittedVehicle
    PermittedVehicle::create([
        'id_imk' => $process->id,
        'package_id' => $vehicle['package_id'],
        'plate_number' => $vehicle['plate_number'],
        'no_lambung' => $vehicle['no_lambung'],
        'stnk' => $stnkPath,
        'driver_name' => $vehicle['driver_name'],
        'sim' => $simPath,
        'number_stiker' => $newNumber,
        'location_id' => $vehicle['location_id'],
        'vehicle_id' => $vehicle['vehicle_id'],
        'cargo' => $vehicle['cargo'],
        'origin' => $vehicle['origin'],
        'start_date' => $vehicle['start_date'],
        'expired_at' => $expired_at,
    ]);
} else {
    // Jika format periode tidak valid
    return response()->json([
        'success' => false,
        'errors' => 'Invalid package period format.',
    ], 422);
}
        }
        if ($request->has('personnels')) {
            foreach ($request->personnels as $personnel) {
                Personnel::create([
                    'id_imk' => $process->id,
                    'name' => $personnel['name'],
                    'identity_number' => $personnel['identity_number'],
                    'location_id' => $personnel['location_id'],
                    'package_id' => $personnel['package_id'],
                ]);
            }
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'process' => $process->load(['vehicles', 'personnels']),
        ], 201);
    } catch (\Exception $e) {
        Log::error('IMK Process Failed: ' . $e->getMessage());

        DB::rollBack();
        return response()->json([
            'success' => false,
            'errors' => $e->getMessage(),
        ], 422);
    }
}






public function update(Request $request, $id)
{
    $process = ProcessImk::find($id);

    if (!$process) {
        return response()->json([
            'success' => false,
            'message' => 'Process IMK not found',
        ], 404);
    }

    try {
        DB::beginTransaction();

        // Validasi request
        $request->validate([
            'document_number' => 'required',
            'registration_date' => 'required',
            'tenant_id' => 'required|exists:tenants,id',
            'item' => 'required',
            'vehicles' => 'required|array',
            'vehicles.*.plate_number' => 'required',
            'vehicles.*.no_lambung' => 'required',
            'vehicles.*.stnk' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'vehicles.*.driver_name' => 'required',
            'vehicles.*.sim' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'vehicles.*.package_id' => 'required',
            'vehicles.*.location_id' => 'required',
            'vehicles.*.cargo' => 'required',
            'vehicles.*.origin' => 'required',
            'vehicles.*.start_date' => 'required|date',
            'vehicles.*.expired_at' => 'nullable|date',
            'personnels' => 'sometimes|array',
            'personnels.*.name' => 'required_with:personnels|string|nullable',
            'personnels.*.identity_number' => 'required_with:personnels|integer|nullable',
            'personnels.*.location_id' => 'required_with:personnels|integer|nullable',
            'personnels.*.package_id' => 'required_with:personnels|integer|nullable',
        ]);

        // Update data process
        $process->update([
            'document_number' => $request->document_number,
            'registration_date' => $request->registration_date,
            'customer_id' => $request->customer_id,
            'tenant_id' => $request->tenant_id,
            'total_cost' => $request->total_cost,
            'item' => $request->item,
            'status_imk' => 0,
        ]);

        // Update atau tambahkan kendaraan
        foreach ($request->vehicles as $vehicle) {
            // Simpan file SIM dan STNK
            $simPath = $vehicle['sim']->storeAs(
                'sim',
                strtolower(str_replace(' ', '-', $vehicle['driver_name'])) . '_sim.' . $vehicle['sim']->getClientOriginalExtension(),
                'public'
            );
            $stnkPath = $vehicle['stnk']->storeAs(
                'stnk',
                strtolower(str_replace(' ', '-', $vehicle['plate_number'])) . '_stnk.' . $vehicle['stnk']->getClientOriginalExtension(),
                'public'
            );

            // Cek apakah kendaraan sudah ada
            $vehicleRecord = PermittedVehicle::where('id_imk', $process->id)
                ->where('no_lambung', $vehicle['no_lambung'])
                ->first();

            // Hitung expired_at jika tidak disediakan
            if (empty($vehicle['expired_at'])) {
                $package = Package::find($vehicle['package_id']);
                if ($package && preg_match('/(\d+)\s*(\w+)/', $package->periode, $matches)) {
                    $amount = (int) $matches[1];
                    $unit = strtolower($matches[2]);

                    switch ($unit) {
                        case 'month':
                        case 'months':
                            $expired_at = Carbon::parse($vehicle['start_date'])->addMonths($amount);
                            break;
                        case 'year':
                        case 'years':
                            $expired_at = Carbon::parse($vehicle['start_date'])->addYears($amount);
                            break;
                        case 'day':
                        case 'days':
                            $expired_at = Carbon::parse($vehicle['start_date'])->addDays($amount);
                            break;
                        default:
                            throw new \Exception("Invalid time unit in package period: $unit");
                    }
                } else {
                    throw new \Exception('Invalid package period format.');
                }
            } else {
                $expired_at = $vehicle['expired_at'];
            }

            if ($vehicleRecord) {
                $vehicleRecord->update([
                    'plate_number' => $vehicle['plate_number'],
                    'package_id' => $vehicle['package_id'],
                    'no_lambung' => $vehicle['no_lambung'],
                    'stnk' => $stnkPath,
                    'driver_name' => $vehicle['driver_name'],
                    'sim' => $simPath,
                    'number_stiker' => $vehicleRecord->number_stiker,
                    'location_id' => $vehicle['location_id'],
                    'cargo' => $vehicle['cargo'],
                    'origin' => $vehicle['origin'],
                    'start_date' => $vehicle['start_date'],
                    'expired_at' => $expired_at,
                ]);
            } else {
                PermittedVehicle::create([
                    'id_imk' => $process->id,
                    'plate_number' => $vehicle['plate_number'],
                    'package_id' => $vehicle['package_id'],
                    'no_lambung' => $vehicle['no_lambung'],
                    'stnk' => $stnkPath,
                    'driver_name' => $vehicle['driver_name'],
                    'sim' => $simPath,
                    'number_stiker' => PermittedVehicle::max('number_stiker') + 1,
                    'location_id' => $vehicle['location_id'],
                    'cargo' => $vehicle['cargo'],
                    'origin' => $vehicle['origin'],
                    'start_date' => $vehicle['start_date'],
                    'expired_at' => $expired_at,
                ]);
            }
        }

        // Update atau tambahkan personnel
        if ($request->has('personnels')) {
            foreach ($request->personnels as $personnel) {
                $personnelRecord = Personnel::where('id_imk', $process->id)
                    ->where('identity_number', $personnel['identity_number'])
                    ->first();

                if ($personnelRecord) {
                    $personnelRecord->update([
                        'name' => $personnel['name'],
                        'location_id' => $personnel['location_id'],
                        'package_id' => $personnel['package_id'],
                    ]);
                } else {
                    Personnel::create([
                        'id_imk' => $process->id,
                        'name' => $personnel['name'],
                        'identity_number' => $personnel['identity_number'],
                        'location_id' => $personnel['location_id'],
                        'package_id' => $personnel['package_id'],
                    ]);
                }
            }
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'process' => $process->load(['vehicles', 'personnels']),
        ], 200);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 422);
    }
}



    public function destroy($id)
    {
        $process = ProcessImk::find($id);

        if ($process) {
            $process->delete();
            return response()->json(['message' => 'Process Imk deleted successfully'], 200);
        } else {
            return response()->json(['message' => 'Process Imk not found'], 404);
        }
    }
}


// public function store(Request $request)
//     {
//         try {
//             DB::beginTransaction();

//             $romanMonths = [
//                 1 => 'I',
//                 2 => 'II',
//                 3 => 'III',
//                 4 => 'IV',
//                 5 => 'V',
//                 6 => 'VI',
//                 7 => 'VII',
//                 8 => 'VIII',
//                 9 => 'IX',
//                 10 => 'X',
//                 11 => 'XI',
//                 12 => 'XII'
//             ];

//             $latestIMK = ProcessImk::latest()->first();
//             $imk_number = $latestIMK
//                 ? "IMK " . str_pad((int)explode(' ', $latestIMK->imk_number)[1] + 1, 3, '0', STR_PAD_LEFT) . "/KIE/" . $romanMonths[date('n')] . "/" . date('Y')
//                 : "IMK 001/KIE/" . $romanMonths[date('n')] . "/" . date('Y');

//             $request->validate([
//                 'document_number' => 'required',
//                 // 'registration_date' => 'required',
//                 'tenant_id' => 'required|exists:tenants,id',
//                 'item' => 'required',
//                 'vehicles' => 'required|array',
//                 'vehicles.*.plate_number' => 'required',
//                 'vehicles.*.no_lambung' => 'required',
//                 'vehicles.*.stnk' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
//                 'vehicles.*.driver_name' => 'required',
//                 'vehicles.*.sim' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
//                 'vehicles.*.package_id' => 'required',
//                 'vehicles.*.location_id' => 'required',
//                 'vehicles.*.cargo' => 'required',
//                 'vehicles.*.origin' => 'required',
//                 'vehicles.*.start_date' => 'required',
//                 // 'vehicles.*.expired_at' => 'required'
//                 'personnels' => 'sometimes|array',
//                 'personnels.*.name' => 'required_with:personnels|string|nullable',
//                 'personnels.*.identity_number' => 'required_with:personnels|integer|nullable',
//                 'personnels.*.location_id' => 'required_with:personnels|integer|nullable',
//                 'personnels.*.package_id' => 'required_with:personnels|integer|nullable',
//             ], [
//                 'document_number.required' => 'The document number is required.',
//                 // 'registration_date.required' => 'The registration date is required.',
//                 'tenant_id.required' => 'The tenant ID is required.',
//                 'tenant_id.exists' => 'The selected tenant not found.',
//                 'vehicles.required' => 'At least 1 vehicle is required.',
//                 'vehicles.array' => 'Vehicles must be an array.',
//                 'vehicles.min' => 'At least 1 vehicle is required.',
//                 'vehicles.*.plate_number.required' => 'The plate number is required.',
//                 'vehicles.*.no_lambung.required' => 'The no lambung is required.',
//                 'vehicles.*.stnk.required' => 'The STNK file is required.',
//                 'vehicles.*.driver_name.required' => 'The driver name is required.',
//                 'vehicles.*.sim.required' => 'The SIM file is required.',
//                 'vehicles.*.package_id.required' => 'The package ID is required.',
//                 'vehicles.*.location_id.required' => 'The location ID is required.',
//                 'vehicles.*.cargo.required' => 'The cargo is required.',
//                 'vehicles.*.origin.required' => 'The origin is required.',
//                 'vehicles.*.start_date.required' => 'The start date is required.',
//                 // 'vehicles.*.expired_at.required' => 'The expired date is required.',
//             ]);

//             $process = ProcessImk::create([
//                 'imk_number' => $imk_number,
//                 'document_number' => $request->document_number,
//                 'registration_date' => date('Y-m-d'),
//                 'customer' => $request->customer,
//                 'tenant_id' => $request->tenant_id,
//                 'total_cost' => $request->total_cost,
//                 'item' => $request->item,
//                 'status_imk' => 0
//             ]);

//             foreach ($request->vehicles as $vehicle) {
//                 // Process sim and stnk for each vehicle
//                 $simPath = $vehicle['sim']->storeAs('sim', strtolower(str_replace(' ', '-', $vehicle['driver_name'])) . '_sim.' . $vehicle['sim']->getClientOriginalExtension(), 'public');
//                 $stnkPath = $vehicle['stnk']->storeAs('stnk', strtolower(str_replace(' ', '-', $vehicle['plate_number'])) . '_stnk.' . $vehicle['stnk']->getClientOriginalExtension(), 'public');

//                 // Determine the new sticker number
//                 $number = PermittedVehicle::max('number_stiker') ?? 0;
//                 $newNumber = $number + 1;

//                 $package = Package::find($vehicle['package_id']);
//                 preg_match('/(\d+)\s*(\w+)/', $package->periode, $matches);

//                 $amount = (int) $matches[1];
//                 $unit = strtolower($matches[2]);

//                 switch ($unit) {
//                     case 'month':
//                     case 'months':
//                         $expired_at = Carbon::parse($vehicle['start_date'])->addMonths($amount);
//                         break;
//                     case 'year':
//                     case 'years':
//                         $expired_at = Carbon::parse($vehicle['start_date'])->addYears($amount);
//                         break;
//                     case 'accidental':
//                     case 'accidentals':
//                         $expired_at = Carbon::parse($vehicle['start_date'])->addDays($amount);
//                         break;
//                     default:
//                         throw new Exception("Unsupported periode type: $unit");
//                 }
//                 // dd($expired_at);

//                 // Create PermittedVehicle entry
//                 PermittedVehicle::create([
//                     'id_imk' => $process->id,
//                     'package_id' => $vehicle['package_id'],
//                     'plate_number' => $vehicle['plate_number'],
//                     'no_lambung' => $vehicle['no_lambung'],
//                     'stnk' => $stnkPath,
//                     'driver_name' => $vehicle['driver_name'],
//                     'sim' => $simPath,
//                     'number_stiker' => $newNumber,
//                     'location_id' => $vehicle['location_id'],
//                     'cargo' => $vehicle['cargo'],
//                     'origin' => $vehicle['origin'],
//                     'start_date' => $vehicle['start_date'],
//                     'expired_at' => $expired_at,
//                 ]);
//             }

//             if ($request->has('personnels')) {
//                 foreach ($request->personnels as $personnel) {
//                     Personnel::create([
//                         'id_imk' => $process->id,
//                         'name' => $personnel['name'],
//                         'identity_number' => $personnel['identity_number'],
//                         'location_id' => $personnel['location_id'],
//                         'package_id' => $personnel['package_id'],
//                     ]);
//                 }
//             }

//             DB::commit();

//             return response()->json([
//                 'success' => true,
//                 'process' => $process->load(['vehicles', 'personnels']),
//             ], 201);
//         } catch (\Exception $e) {
//             DB::rollBack();
//             return response()->json([
//                 'success' => false,
//                 'errors' => $e->getMessage(),
//             ], 422);
//         }
//     }