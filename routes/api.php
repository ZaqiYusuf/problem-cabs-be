<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\ProcessImkController;
use App\Http\Controllers\PrintStikerController;
use App\Http\Controllers\PermittedVehicleController;

/*
|---------------------------------------------------------------------------|
| API Routes                                                                |
|---------------------------------------------------------------------------|
| Here is where you can register API routes for your application.           |
*/

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/me', [AuthController::class, 'me']);
    Route::post('/change-password/{id}', [AuthController::class, 'changePassword']);
    Route::post('/reset-password/{id}', [AuthController::class, 'resetPassword']);
});

Route::middleware(['auth:api'])->get('/user', function (Request $request) {
    return $request->user();
});

// Middleware 'auth:api' untuk rute yang memerlukan autentikasi
Route::middleware(['auth:api', 'role:admin'])->group(function () {
    // Semua route yang bisa diakses oleh admin
    Route::apiResource('users', UserController::class);
    Route::apiResource('category', CategoryController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::get('customers/all/data', [CustomerController::class, 'getAllData']);
    Route::post('customers/{id}', [CustomerController::class, 'update']);
    Route::post('customers/approve/{id}', [CustomerController::class, 'approve']);
    Route::apiResource('packages', PackageController::class);
    Route::apiResource('process-imks', ProcessImkController::class);
    Route::post('process-imks/{id}', [ProcessImkController::class, 'update']);
    Route::apiResource('permitted-vehicles', PermittedVehicleController::class);
    Route::apiResource('permitted-personnels', PersonnelController::class);
    Route::apiResource('locations', LocationController::class);
    Route::apiResource('tenants', TenantController::class);
    Route::apiResource('vehicles', VehicleController::class);
    Route::apiResource('personnels', PersonnelController::class);
    Route::post('/packages/search', [PackageController::class, 'search']);
    Route::post('/imk/{id}', [PaymentController::class, 'store']);
    Route::get('/imk/{id}', [PaymentController::class, 'index']);
    Route::get('imk/all/data', [PaymentController::class, 'getAllData']);
    Route::post('/approve/{id}', [PaymentController::class, 'update']);
    Route::post('/pdf', [PrintStikerController::class, 'generatePdf']);
    Route::apiResource('settings', SettingController::class);
});

Route::middleware(['auth:api', 'role:user'])->group(function () {
    // Semua route yang hanya bisa diakses oleh user
    Route::get('/user/process-imks', [ProcessImkController::class, 'index']); // Data khusus user
    Route::get('/user/imk', [PaymentController::class, 'getAllData']); // Data khusus user
});

Route::post('/midtrans/callback', [PaymentController::class, 'handleCallback']);
