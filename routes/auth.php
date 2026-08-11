<?php

use App\Http\Controllers\Auth\SesiAutentikasiController;
use App\Http\Controllers\Auth\KonfirmasiSandiController;
use App\Http\Controllers\Auth\NotifikasiVerifikasiEmailController;
use App\Http\Controllers\Auth\PromptVerifikasiEmailController;
use App\Http\Controllers\Auth\SandiController;
use App\Http\Controllers\Auth\SandiOtpController;
use App\Http\Controllers\Auth\TautanResetSandiController;
use App\Http\Controllers\Auth\PenggunaTerdaftarController;
use App\Http\Controllers\Auth\VerifikasiEmailController;
use App\Http\Controllers\Auth\VerifikasiOtpController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [PenggunaTerdaftarController::class, 'create'])
        ->name('register');

    Route::post('register', [PenggunaTerdaftarController::class, 'store']);

    Route::get('login', [SesiAutentikasiController::class, 'create'])
        ->name('login');

    Route::post('login', [SesiAutentikasiController::class, 'store']);

    Route::get('forgot-password', [TautanResetSandiController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [TautanResetSandiController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.email');

    Route::get('forgot-password/verifikasi', [SandiOtpController::class, 'create'])
        ->name('password.otp');

    Route::post('forgot-password/verifikasi/otp', [SandiOtpController::class, 'verifikasi'])
        ->middleware('throttle:6,1')
        ->name('password.otp.verifikasi');

    Route::post('forgot-password/verifikasi', [SandiOtpController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.otp.proses');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', PromptVerifikasiEmailController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifikasiEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [NotifikasiVerifikasiEmailController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('verifikasi-otp', [VerifikasiOtpController::class, 'show'])
        ->name('verifikasi-otp');

    Route::post('verifikasi-otp', [VerifikasiOtpController::class, 'proses'])
        ->middleware('throttle:6,1')
        ->name('verifikasi-otp.proses');

    Route::post('verifikasi-otp/kirim-ulang', [VerifikasiOtpController::class, 'kirimUlang'])
        ->name('verifikasi-otp.kirim-ulang');

    Route::get('confirm-password', [KonfirmasiSandiController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [KonfirmasiSandiController::class, 'store']);

    Route::put('password', [SandiController::class, 'update'])->name('password.update');

    Route::post('logout', [SesiAutentikasiController::class, 'destroy'])
        ->name('logout');
});
