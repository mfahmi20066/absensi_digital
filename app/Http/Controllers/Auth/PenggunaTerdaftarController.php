<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use App\Models\Peran;
use App\Support\PencatatAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
     * Akun baru dibuat dengan status 'pending' dan menunggu persetujuan
     * administrator sebelum bisa login.
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
            'status' => 'pending',
        ]);

        PencatatAudit::log('user_registered', "Akun baru {$user->name} ({$user->email}) mendaftar, menunggu persetujuan admin");

        return redirect()->route('login')->with('status', 'Akun berhasil dibuat dan menunggu persetujuan administrator. Anda dapat login setelah akun diaktifkan.');
    }
}
