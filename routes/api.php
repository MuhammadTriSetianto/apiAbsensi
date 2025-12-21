<?php

use App\Http\Controllers\AbsensisController;
use Illuminate\Support\Facades\Auth;        
use App\Http\Controllers\Api\AuthController;  
use App\Http\Controllers\Api\ProyekController;
use App\Http\Controllers\IzinController;
use App\Models\Absensi;
use Illuminate\Support\Facades\Route;

Route :: post('/register',[AuthController::class,'register']);
Route :: post('/login',[AuthController::class,'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route :: get('/profile',[AuthController::class,'profile']);
    Route :: post('/logout',[AuthController::class,'logout']);

    //proyek
}); 
Route :: get('/proyek',[ProyekController::class,'index']);
Route :: get('/proyek/{id}',[ProyekController::class,'show']);
Route :: post('/proyek',[ProyekController::class,'create_project']);

Route :: get('/absen',[AbsensisController::class,'index']);
Route :: get('/absen/{id}',[AbsensisController::class,'masuk']);
Route :: put('/absen/{id}',[AbsensisController::class,'pulang']);
Route :: delete('/absen/{id}',[AbsensisController::class,'destroy']);

Route :: get('/izin',[IzinController::class,'index']);
Route :: post('/izin',[IzinController::class,'izin']);
Route :: put('/detailIzin/{id}',[IzinController::class,'show']);
Route :: delete('/hapusizin/{id}',[IzinController::class,'destroy']);

Route :: get('/usersproyek',[IzinController::class,'index']);
Route :: post('/usersproyek',[IzinController::class,'store']);
Route :: get('/usersproyek/{id}',[IzinController::class,'show']);
Route :: put('/usersproyek/{id}',[IzinController::class,'upate']);
Route :: delete('/usersproyek/{id}',[IzinController::class,'destroy']);

Route :: get('/lihatfoto/{id}',[AbsensisController::class,'show']);