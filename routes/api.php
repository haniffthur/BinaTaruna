<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TapValidationController;
use App\Http\Controllers\Api\TicketValidationController;
use App\Http\Controllers\Api\CardRegistrationController;
use App\Http\Controllers\Api\TappedCardController; // Tambahkan ini

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- CATATAN KEAMANAN PENTING ---
// Untuk production, endpoint ini WAJIB dilindungi menggunakan Sanctum
// atau metode otentikasi API lainnya. Contoh:
// Route::middleware('auth:sanctum')->group(function () {
//     Route::post('/validate-tap', [TapValidationController::class, 'validateTap']);
//     Route::post('/validate-ticket', [TicketValidationController::class, 'validateTicket']);
// });


// Endpoint untuk validasi tap kartu RFID dari ESP8266
Route::post('/tap/validate', [TapValidationController::class, 'validateTap']);



Route::post('/cards/tapped-uid', [TappedCardController::class, 'storeTappedUid']);

// Endpoint BARU untuk halaman web agar bisa mengambil UID yang baru di-tap
Route::get('/cards/get-tapped-uid', [TappedCardController::class, 'getTappedUid']);

Route::post('/cards/register', [CardRegistrationController::class, 'register']);
// Endpoint untuk validasi tiket QR Code dari pemindai
Route::post('/validate-ticket', [TicketValidationController::class, 'validateTicket']);


// Rute default dari file Anda
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});