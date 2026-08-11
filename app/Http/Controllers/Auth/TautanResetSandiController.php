<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use App\Notifications\KodeOtpVerifikasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TautanResetSandiController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.lupa-sandi');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = Pengguna::where('email', $request->email)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => 'Email tidak terdaftar.',
            ]);
        }

        session(['reset_email' => $user->email]);
        session()->forget('reset_otp_terverifikasi');

        $kode = $user->generateEmailOtp();
        $user->notify(new KodeOtpVerifikasi($kode));

        return redirect()->route('password.otp')->with('status', 'Kode OTP telah dikirim ke email Anda.');
    }
}
