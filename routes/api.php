<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProyekController;
use App\Http\Controllers\Api\AbsensisController;
use App\Http\Controllers\Api\IzinController;
use App\Http\Controllers\Api\CutiController;
use App\Http\Controllers\Api\NotifikasiController;
use App\Http\Controllers\Api\UserProyekController;;

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/updatePegawai', [AuthController::class, 'updateProfile']);
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
});



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
    Route::get('/user/today', [AbsensisController::class, 'getMasukHariIni']);
    Route::get('/user/semua', [AbsensisController::class, 'getAllMasukByUser']);
    Route::put('/user/pulang', [AbsensisController::class, 'pulang']);
    Route::post('/user/masuk', [AbsensisController::class, 'masuk']);
    Route::post('/pulang/{id_pegawai}', [AbsensisController::class, 'pulang']);
});


/*
|--------------------------------------------------------------------------
| IZIN
|--------------------------------------------------------------------------
*/
Route::prefix('izin')->group(function () {
    Route::get('/', [IzinController::class, 'index']);
    Route ::get('/user', [IzinController::class, 'getAllIzinByUser']);
    Route::post('/kerja', [IzinController::class, 'requestbuatizin']);
    Route::post('/{id_surat}/disetujui', [IzinController::class, 'disetujui']);
    Route::post('/{id_surat}/ditolak', [IzinController::class, 'ditolak']);
    Route::delete('/{id}', [IzinController::class, 'destroy']);
});
/*
    |--------------------------------------------------------------------------
    | USERS PROYEK
    |--------------------------------------------------------------------------
    */
Route::prefix('usersproyek')->group(function () {
    Route::get('/', [UserProyekController::class, 'index']);
    Route::post('/', [UserProyekController::class, 'store']);
    Route::get('/user', [UserProyekController::class, 'show']);
    Route::delete('/destroyProyek/{idPegawai}', [UserProyekController::class, 'destroyAll']);
    Route::delete('/delete/{idPegawai}', [UserProyekController::class, 'destroy']);
});


/*
    |--------------------------------------------------------------------------
    | CUTI
    |--------------------------------------------------------------------------
    */
Route::prefix('cuti')->group(function () {
    Route::post('/', [CutiController::class, 'store']);
    Route::get('/totalcuti/user', [CutiController::class, 'totalCuti']);
    Route::put('/{id}/approve', [CutiController::class, 'approve']);
    Route::post('/buat_cuti', [CutiController::class, 'store']);
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
