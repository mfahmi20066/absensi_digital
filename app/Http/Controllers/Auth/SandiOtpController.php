<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SandiOtpController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (! session('reset_email')) {
            return redirect()->route('password.request');
        }

        return view('auth.atur-ulang-sandi-otp', [
            'email' => session('reset_email'),
            'otpTerverifikasi' => session('reset_otp_terverifikasi', false),
        ]);
    }

    public function verifikasi(Request $request): RedirectResponse
    {
        $request->validate([
            'kode' => ['required', 'string', 'digits:6'],
        ]);

        $email = session('reset_email');

        if (! $email) {
            return redirect()->route('password.request');
        }

        $user = Pengguna::where('email', $email)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'kode' => 'Email tidak ditemukan.',
            ]);
        }

        if (! $user->verifyEmailOtp($request->kode)) {
            throw ValidationException::withMessages([
                'kode' => 'Kode OTP salah atau sudah kedaluwarsa.',
            ]);
        }

        session(['reset_otp_terverifikasi' => true]);

        return redirect()->route('password.otp')->with('status', 'Kode OTP valid. Silakan atur sandi baru Anda.');
    }

    public function store(Request $request): RedirectResponse
    {
        if (! session('reset_otp_terverifikasi') || ! session('reset_email')) {
            return redirect()->route('password.otp');
        }

        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $email = session('reset_email');

        $user = Pengguna::where('email', $email)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'password' => 'Email tidak ditemukan.',
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        session()->forget(['reset_email', 'reset_otp_terverifikasi']);

        return redirect()->route('login')->with('status', 'Password berhasil direset. Silakan masuk.');
    }
}
