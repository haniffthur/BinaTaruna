<?php

use Illuminate\Support\Facades\Route;

// Controller dari file Anda
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Dashboard;
use App\Http\Controllers\UserController;

// Controller yang kita buat bersama
use App\Http\Controllers\MasterCardController;
use App\Http\Controllers\AccessRuleController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\CoachController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\TicketController;

use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TaplogsController;
use App\Http\Controllers\TicketScanLogController;
use App\Http\Controllers\Api\MemberDetailController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    // Sebaiknya arahkan ke login jika belum terotentikasi
    return redirect()->route('login');
});

// Login (Struktur Anda dipertahankan)
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/logout', [LoginController::class, 'logout'])->name('logout'); // Method POST lebih disarankan untuk logout

// routes/web.php
// ... (semua rute Anda yang sudah ada) ...


    Route::get('/members/download-template', [MemberController::class, 'downloadTemplate'])->name('members.download.template');
    Route::post('/members/import', [MemberController::class, 'import'])->name('members.import');
    Route::get('/members/export-report', [MemberController::class, 'exportReport'])->name('members.export.report');


// Grup untuk semua halaman yang memerlukan login
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard/report', [Dashboard::class, 'generateReport'])->name('dashboard.report');


    // Rute Dashboard Anda
    Route::get('/ticket-scan-logs', [TicketScanLogController::class, 'index'])->name('ticket-scan-logs.index');

    Route::get('/dashboard', [Dashboard::class, 'index'])->name('dashboard');
Route::get('/api/dashboard/chart-data', [Dashboard::class, 'getChartData'])->name('api.dashboard.chart-data');

    Route::get('/tap-logs', [TaplogsController::class, 'index'])->name('tap-logs.index');
    Route::get('/api/ticket-scan-logs/latest', [TicketScanLogController::class, 'fetchLatest'])->name('api.ticket-scan-logs.latest');

    // Rute UserController Anda (untuk mengelola akun login)
    Route::resource('users', UserController::class);

    Route::get('/api/members/{member}', [MemberDetailController::class, 'show'])->name('api.members.show');
    Route::get('/api/tap-logs/latest', [TaplogsController::class, 'fetchLatest'])->name('api.tap-logs.latest');
     Route::get('/tap-logs/export-excel', [TaplogsController::class, 'exportExcel'])->name('tap-logs.export.excel');

    // === RUTE BARU DITAMBAHKAN DI SINI ===

    // Rute Master Data
    Route::resource('master-cards', MasterCardController::class);
    Route::resource('access-rules', AccessRuleController::class);

    // Rute Manajemen Peran
    Route::resource('members', MemberController::class);


 Route::get('/transactions/export-excel', [TransactionController::class, 'exportExcel'])->name('transactions.export.excel');

    
    Route::resource('coaches', CoachController::class);
    Route::resource('staffs', StaffController::class);

     Route::get('/non-member-receipt/{id}', [TransactionController::class, 'showNonMemberReceipt'])->name('non-member-receipt.show');

    // Rute Produk
    Route::resource('classes', SchoolClassController::class);
    Route::resource('tickets', TicketController::class);

    Route::get('/non-member-transactions/{id}', [TransactionController::class, 'showNonMemberDetail'])->name('non-member-transactions.show');
});
Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');

// Rute Transaksi
Route::prefix('transactions')->name('transactions.')->group(function () {
    Route::get('/member/create', [TransactionController::class, 'createMemberTransaction'])->name('member.create');
    Route::post('/member', [TransactionController::class, 'storeMemberTransaction'])->name('member.store');

    Route::get('/non-member/create', [TransactionController::class, 'createNonMemberTransaction'])->name('non-member.create');
    Route::post('/non-member', [TransactionController::class, 'storeNonMemberTransaction'])->name('non-member.store');



});