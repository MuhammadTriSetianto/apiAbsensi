<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProyekController;
use App\Http\Controllers\AbsensisController;
use App\Http\Controllers\IzinController;
use App\Http\Controllers\CutiController;
use App\Http\Controllers\NotifikasiController;

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {});
Route::post('/login', [AuthController::class, 'login']);
Route::get('/profile', [AuthController::class, 'profile']);
Route::post('/logout', [AuthController::class, 'logout']);



/*
|--------------------------------------------------------------------------
| PROYEK
|--------------------------------------------------------------------------
*/
Route::prefix('proyek')->group(function () {
    Route::get('/', [ProyekController::class, 'index']);
    Route::get('/{id}', [ProyekController::class, 'show']);
    Route::post('/', [ProyekController::class, 'create_project']);
    Route::put('/{id}', [ProyekController::class, 'update_project']);
});

/*
|--------------------------------------------------------------------------
| ABSENSI
|--------------------------------------------------------------------------
*/
Route::prefix('absen')->group(function () {
    Route::get('/', [AbsensisController::class, 'index']);
    Route::get('/{id}', [AbsensisController::class, 'masuk']);
    Route::put('/{id}', [AbsensisController::class, 'pulang']);
    Route::delete('/{id}', [AbsensisController::class, 'destroy']);
    Route::get('/{id}/foto', [AbsensisController::class, 'show']);
});


/*
|--------------------------------------------------------------------------
| IZIN
|--------------------------------------------------------------------------
*/
Route::prefix('izin')->group(function () {
    Route::get('/', [IzinController::class, 'index']);
    Route::post('/', [IzinController::class, 'izin']);
    Route::get('/{id}', [IzinController::class, 'show']);
    Route::delete('/{id}', [IzinController::class, 'destroy']);
});


/*
    |--------------------------------------------------------------------------
    | USERS PROYEK
    |--------------------------------------------------------------------------
    */
Route::prefix('usersproyek')->group(function () {
    Route::get('/', [IzinController::class, 'index']);
    Route::post('/', [IzinController::class, 'store']);
    Route::get('/{id}', [IzinController::class, 'show']);
    Route::put('/{id}', [IzinController::class, 'update']);
    Route::delete('/{id}', [IzinController::class, 'destroy']);
});

/*
    |--------------------------------------------------------------------------
    | CUTI
    |--------------------------------------------------------------------------
    */
Route::prefix('cuti')->group(function () {
    Route::post('/', [CutiController::class, 'store']);
    Route::put('/{id}/approve', [CutiController::class, 'approve']);
    Route::put('/{id}/reject', [CutiController::class, 'reject']);
});

/*
    |--------------------------------------------------------------------------
    | NOTIFIKASI
    |--------------------------------------------------------------------------
    */
Route::prefix('notifikasi')->group(function () {
    Route::get('/', [NotifikasiController::class, 'index']);
    Route::post('/', [NotifikasiController::class, 'store']);
    Route::get('/{id}', [NotifikasiController::class, 'show']);
    Route::put('/{id}/read', [NotifikasiController::class, 'markAsRead']);
    Route::delete('/{id}', [NotifikasiController::class, 'destroy']);
});
