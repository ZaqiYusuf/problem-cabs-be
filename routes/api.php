<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProcessImkController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PermittedVehicleController;
use App\Http\Controllers\PrintStikerController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\SettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/**
 * Authentication
 */
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/login', [App\Http\Controllers\Auth\AuthController::class, 'login']);
    Route::post('/register', [App\Http\Controllers\Auth\AuthController::class, 'register']);
    Route::post('/logout', [App\Http\Controllers\Auth\AuthController::class, 'logout']);
    Route::post('/refresh', [App\Http\Controllers\Auth\AuthController::class, 'refresh']);
    Route::post('/me', [App\Http\Controllers\Auth\AuthController::class, 'me']);
    Route::post('/change-password/{id}', [App\Http\Controllers\Auth\AuthController::class, 'changePassword']);
    Route::post('/reset-password/{id}', [App\Http\Controllers\Auth\AuthController::class, 'resetPassword']);
});

Route::middleware(['auth:api'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:api'])->group(function () {
    Route::apiResource('users', UserController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('packages', PackageController::class);
    Route::apiResource('process-imks', ProcessImkController::class);
    Route::post('process-imks/{id}', [ProcessImkController::class, 'update']);
    Route::apiResource('permitted-vehicles', PermittedVehicleController::class);
    Route::apiResource('permitted-personnels', PersonnelController::class);
    Route::apiResource('locations', LocationController::class);
    Route::apiResource('tenants', TenantController::class);
    Route::post('/packages/search', [PackageController::class, 'search']);
    Route::post('/imk/{id}', [PaymentController::class, 'store']);
    Route::get('/imk/{id}', [PaymentController::class, 'show']);
    Route::post('/approve/{id}', [PaymentController::class, 'update']);
    Route::post('/pdf', [PrintStikerController::class, 'generatePdf']);
    Route::apiResource('settings', SettingController::class);
});

Route::post('/midtrans/callback', [PaymentController::class, 'handleCallback']);