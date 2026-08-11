<?php

use App\Http\Controllers\Admin\AbsensiController as AbsensiAdminController;
use App\Http\Controllers\Admin\LogAuditController;
use App\Http\Controllers\Admin\BarcodeController;
use App\Http\Controllers\Admin\KaryawanController;
use App\Http\Controllers\Admin\PersetujuanCutiController;
use App\Http\Controllers\Admin\PersetujuanKoreksiController;
use App\Http\Controllers\Admin\PersetujuanLemburController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\PengaturanController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\JadwalKerjaController;
use App\Http\Controllers\Admin\JabatanController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\DasborController;
use App\Http\Controllers\KoreksiAbsensiSayaController;
use App\Http\Controllers\LemburSayaController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\PengajuanCutiSayaController;
use App\Http\Controllers\ProfilController;
use App\Models\Pengaturan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('beranda');
})->name('home');

Route::get('/scan/{code}', [AbsensiController::class, 'scanShow'])
    ->where('code', '.*')
    ->name('absen.scan.show');

Route::post('/absen/barcode', [AbsensiController::class, 'scanBarcode'])
    ->name('karyawan.absen.barcode');

Route::get('/dasbor', [DasborController::class, 'index'])
    ->middleware(['auth', 'verified:verifikasi-otp'])
    ->name('dasbor');

Route::middleware('auth')->group(function () {
    Route::get('/profil', [ProfilController::class, 'edit'])->name('profil.edit');
    Route::patch('/profil', [ProfilController::class, 'update'])->name('profil.update');
    Route::delete('/profil', [ProfilController::class, 'destroy'])->name('profil.destroy');

    // ===== KARYAWAN =====
    Route::middleware('peran:karyawan')->prefix('karyawan')->name('karyawan.')->group(function () {
        Route::get('/absen', [AbsensiController::class, 'absen'])->name('absen.index');
        Route::get('/absen/qr', [AbsensiController::class, 'myQr'])->name('absen.qr');
        Route::post('/absen/kamera', [AbsensiController::class, 'camera'])->name('absen.kamera');

        Route::get('/riwayat', [RiwayatController::class, 'index'])->name('riwayat');
        Route::get('/rekap', [RiwayatController::class, 'rekap'])->name('rekap');

        Route::get('/cuti', [PengajuanCutiSayaController::class, 'index'])->name('cuti.index');
        Route::get('/cuti/{leaveRequest}', [PengajuanCutiSayaController::class, 'show'])->name('cuti.show');
        Route::post('/cuti', [PengajuanCutiSayaController::class, 'store'])->name('cuti.store');
        Route::delete('/cuti/{leaveRequest}', [PengajuanCutiSayaController::class, 'destroy'])->name('cuti.destroy');

        Route::get('/lembur', [LemburSayaController::class, 'index'])->name('lembur.index');
        Route::post('/lembur', [LemburSayaController::class, 'store'])->name('lembur.store');
        Route::delete('/lembur/{lembur}', [LemburSayaController::class, 'destroy'])->name('lembur.destroy');

        Route::get('/koreksi', [KoreksiAbsensiSayaController::class, 'index'])->name('koreksi.index');
        Route::post('/koreksi', [KoreksiAbsensiSayaController::class, 'store'])->name('koreksi.store');
        Route::delete('/koreksi/{koreksi}', [KoreksiAbsensiSayaController::class, 'destroy'])->name('koreksi.destroy');
    });

    // ===== ADMIN =====
    Route::middleware('peran:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('karyawan', KaryawanController::class);
        Route::post('karyawan/{karyawan}/toggle-status', [KaryawanController::class, 'toggleStatus'])->name('karyawan.toggle-status');
        Route::post('karyawan/{karyawan}/reset-password', [KaryawanController::class, 'resetPassword'])->name('karyawan.reset-password');

        Route::resource('jadwal-kerja', JadwalKerjaController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::resource('pengguna', PenggunaController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        Route::post('pengguna/{pengguna}/toggle-status', [PenggunaController::class, 'toggleStatus'])->name('pengguna.toggle-status');
        Route::post('pengguna/{pengguna}/reset-password', [PenggunaController::class, 'resetPassword'])->name('pengguna.reset-password');

        Route::resource('jabatan', JabatanController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::get('barcode', [BarcodeController::class, 'index'])->name('barcode.index');
        Route::post('barcode/{employee}/generate', [BarcodeController::class, 'generate'])->name('barcode.generate');
        Route::get('barcode/{employee}/print', [BarcodeController::class, 'print'])->name('barcode.print');
        Route::get('barcode/{employee}/download', [BarcodeController::class, 'downloadPng'])->name('barcode.download');

        Route::get('absensi', [AbsensiAdminController::class, 'index'])->name('absensi.index');
        Route::post('absensi', [AbsensiAdminController::class, 'store'])->name('absensi.store');
        Route::get('absensi/{attendance}/edit', [AbsensiAdminController::class, 'edit'])->name('absensi.edit');
        Route::put('absensi/{attendance}', [AbsensiAdminController::class, 'update'])->name('absensi.update');
        Route::delete('absensi/{attendance}', [AbsensiAdminController::class, 'destroy'])->name('absensi.destroy');

        Route::get('pengajuan-cuti', [PersetujuanCutiController::class, 'index'])->name('pengajuan-cuti.index');
        Route::get('pengajuan-cuti/{leaveRequest}', [PersetujuanCutiController::class, 'show'])->name('pengajuan-cuti.show');
        Route::post('pengajuan-cuti/{leaveRequest}/verify', [PersetujuanCutiController::class, 'verify'])->name('pengajuan-cuti.verify');
        Route::post('pengajuan-cuti/{leaveRequest}/approve', [PersetujuanCutiController::class, 'approve'])->name('pengajuan-cuti.approve');
        Route::post('pengajuan-cuti/{leaveRequest}/reject', [PersetujuanCutiController::class, 'reject'])->name('pengajuan-cuti.reject');

        Route::get('lembur', [PersetujuanLemburController::class, 'index'])->name('lembur.index');
        Route::post('lembur/{lembur}/verify', [PersetujuanLemburController::class, 'verify'])->name('lembur.verify');
        Route::post('lembur/{lembur}/approve', [PersetujuanLemburController::class, 'approve'])->name('lembur.approve');
        Route::post('lembur/{lembur}/reject', [PersetujuanLemburController::class, 'reject'])->name('lembur.reject');

        Route::get('koreksi', [PersetujuanKoreksiController::class, 'index'])->name('koreksi.index');
        Route::post('koreksi/{koreksi}/verify', [PersetujuanKoreksiController::class, 'verify'])->name('koreksi.verify');
        Route::post('koreksi/{koreksi}/approve', [PersetujuanKoreksiController::class, 'approve'])->name('koreksi.approve');
        Route::post('koreksi/{koreksi}/reject', [PersetujuanKoreksiController::class, 'reject'])->name('koreksi.reject');

        Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');

        Route::get('pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
        Route::put('pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');

        Route::get('log-audit', [LogAuditController::class, 'index'])->name('log-audit.index');
    });

    // ===== MANAJER =====
    Route::middleware('peran:manajer')->prefix('manajer')->name('manajer.')->group(function () {
        Route::get('/karyawan', [KaryawanController::class, 'index'])->name('karyawan.index');
        Route::get('/karyawan/create', [KaryawanController::class, 'create'])->name('karyawan.create');
        Route::post('/karyawan', [KaryawanController::class, 'store'])->name('karyawan.store');
        Route::get('/karyawan/{karyawan}', [KaryawanController::class, 'edit'])->name('karyawan.edit');
        Route::put('/karyawan/{karyawan}', [KaryawanController::class, 'update'])->name('karyawan.update');

        Route::get('/absensi', [AbsensiAdminController::class, 'index'])->name('absensi.index');

        Route::get('/cuti', [PersetujuanCutiController::class, 'index'])->name('pengajuan-cuti.index');
        Route::get('/cuti/{leaveRequest}', [PersetujuanCutiController::class, 'show'])->name('pengajuan-cuti.show');
        Route::post('/cuti/{leaveRequest}/approve', [PersetujuanCutiController::class, 'approve'])->name('pengajuan-cuti.approve');
        Route::post('/cuti/{leaveRequest}/reject', [PersetujuanCutiController::class, 'reject'])->name('pengajuan-cuti.reject');

        Route::get('/lembur', [PersetujuanLemburController::class, 'index'])->name('lembur.index');
        Route::post('/lembur/{lembur}/approve', [PersetujuanLemburController::class, 'approve'])->name('lembur.approve');
        Route::post('/lembur/{lembur}/reject', [PersetujuanLemburController::class, 'reject'])->name('lembur.reject');

        Route::get('/koreksi', [PersetujuanKoreksiController::class, 'index'])->name('koreksi.index');
        Route::post('/koreksi/{koreksi}/approve', [PersetujuanKoreksiController::class, 'approve'])->name('koreksi.approve');
        Route::post('/koreksi/{koreksi}/reject', [PersetujuanKoreksiController::class, 'reject'])->name('koreksi.reject');

        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    });
});

Route::get('/sppg-info', fn () => response()->json([
    'name' => Pengaturan::get('sppg_name'),
    'address' => Pengaturan::get('sppg_address'),
    'latitude' => Pengaturan::get('sppg_latitude'),
    'longitude' => Pengaturan::get('sppg_longitude'),
    'radius_meter' => Pengaturan::get('radius_meter'),
]));

require __DIR__.'/auth.php';
