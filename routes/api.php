<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\PenugasanController;

Route::post('/register', [UserController::class, 'regis']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/loginuser', [UserController::class, 'loginUser']);



Route::middleware('auth:sanctum')->group(function () {
    // Route::get('/user', function (Request $request) {
    //     return $request->user();
    // });

    //absensi
    Route::post('/absensi/store', [AbsensiController::class, 'store_datang']);
    Route::post('/absensi/store_pulang', [AbsensiController::class, 'store_pulang']);
    Route::get('/absensi/show/{id}', [AbsensiController::class, 'show']);
    Route::get('/user/kembali/{id}', [AbsensiController::class, 'kembali']);
    Route::post('/absensi/store_penudatang', [AbsensiController::class, 'store_penudatang']);
    Route::post('/absensi/store_penupulang', [AbsensiController::class, 'store_penupulang']);
    Route::get('/absensi/history', [AbsensiController::class, 'history']);
    Route::post('/absensi/absence', [AbsensiController::class, 'store_absence']);
    Route::get('absensi/index', [AbsensiController::class, 'index']);
    Route::get('absensi/user', [AbsensiController::class, 'user_absence']);
    Route::get('/absence/laporan', [AbsensiController::class, 'laporan']);
    Route::get('/absence/laporan_bulanan', [AbsensiController::class, 'laporanBulanan']);
    Route::delete('/absence/delete/{id}', [AbsensiController::class, 'destroy']);
    Route::post('/absence/update/{id}', [AbsensiController::class, 'update']);

    //DASHBOARD
    Route::get('/dashboard', [AbsensiController::class, 'dashboard']);
    Route::get('/dashboard/presensi-hari-ini', [AbsensiController::class, 'presensiHariIni']);
    Route::get('/dashboard/bar', [AbsensiController::class, 'bar_grafik']);



    //user
    Route::get('/user/index', [UserController::class, 'index']);
    Route::get('/user/show/{id}', [UserController::class, 'show']);
    Route::post('/user/update_penugasan/{id}', [UserController::class, 'update_penugasan']);
    Route::post('/user/update/{id}', [UserController::class, 'update']);
    Route::DELETE('/user/destroy/{id}', [UserController::class, 'destroy']);
    Route::get('user/validasi', [UserController::class, 'ceking']);
    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/search_nama/{nama}', [UserController::class, 'search_nama']);

    
    //lokasi
    Route::get('/lokasi/index', [LokasiController::class, 'index']);
    Route::post('/lokasi/store', [LokasiController::class, 'store']);
    Route::get('/lokasi/show/{id}', [LokasiController::class, 'show']);
    Route::post('/lokasi/update/{id}', [LokasiController::class, 'update']);
    Route::DELETE('/lokasi/destroy/{id}', [LokasiController::class, 'destroy']);

    //penugasan
    Route::get('/penugasan/index', [PenugasanController::class, 'index']);
    Route::get('/penugasan/show/{id}', [PenugasanController::class, 'show']);
    Route::post('/penugasan/store', [PenugasanController::class, 'store']);
    Route::post('/penugasan/update/{id}', [PenugasanController::class, 'update']);
    Route::delete('/penugasan/destroy/{id}', [PenugasanController::class, 'destroy']);
    Route::get('/penugasan/user', [PenugasanController::class, 'user']);
    Route::get('/penugasan/status', [PenugasanController::class, 'status']);

    
    
   // php artisan serve --host=192.168.137.1 --port=8000

});

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
