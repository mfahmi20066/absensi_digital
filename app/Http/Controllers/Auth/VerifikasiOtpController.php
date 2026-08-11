<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Notifications\KodeOtpVerifikasi;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class VerifikasiOtpController extends Controller
{
    public function show(): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dasbor');
        }

        return view('auth.verifikasi-otp');
    }

    public function proses(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dasbor');
        }

        $request->validate([
            'kode' => ['required', 'string', 'digits:6'],
        ]);

        if (! $user->verifyEmailOtp($request->kode)) {
            throw ValidationException::withMessages([
                'kode' => 'Kode OTP salah atau sudah kedaluwarsa.',
            ]);
        }

        event(new Verified($user));

        return redirect()->route('dasbor')->with('status', 'Email berhasil diverifikasi.');
    }

    public function kirimUlang(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dasbor');
        }

        $kunci = 'kirim-otp:'.$user->id;

        if (RateLimiter::tooManyAttempts($kunci, 1)) {
            $detik = RateLimiter::availableIn($kunci);

            return back()->withErrors([
                'kode' => "Silakan tunggu {$detik} detik sebelum mengirim ulang kode.",
            ]);
        }

        RateLimiter::hit($kunci, 60);

        $kode = $user->generateEmailOtp();
        $user->notify(new KodeOtpVerifikasi($kode));

        return back()->with('status', 'Kode OTP baru telah dikirim ke email Anda.');
    }
}
