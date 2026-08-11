<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Peran;
use App\Models\Pengguna;
use App\Notifications\KodeOtpVerifikasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PenggunaTerdaftarController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.daftar');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.Pengguna::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = Pengguna::create([
            'role_id' => Peran::firstOrCreate(
                ['name' => 'karyawan'],
                ['label' => 'Karyawan']
            )->id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $kode = $user->generateEmailOtp();
        $user->notify(new KodeOtpVerifikasi($kode));

        Auth::login($user);

        return redirect()->route('verifikasi-otp')->with('status', 'Akun berhasil dibuat! Kode OTP telah dikirim ke email Anda. Silakan periksa kotak masuk atau folder spam.');
    }
}
