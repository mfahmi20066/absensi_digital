<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use App\Models\Peran;
use App\Support\PencatatAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PenggunaController extends Controller
{
    public function index(Request $request)
    {
        $query = Pengguna::with('role', 'employee');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('role_id', $request->role);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        $roles = Peran::orderBy('id')->get();

        return view('admin.pengguna.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Peran::orderBy('id')->get();

        return view('admin.pengguna.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
            'role_id' => ['required', 'exists:roles,id'],
            'status' => ['required', Rule::in(['pending', 'active', 'inactive'])],
        ]);

        $user = Pengguna::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $data['role_id'],
            'status' => $data['status'],
        ]);

        PencatatAudit::log('user_created', "Pengguna {$user->name} ({$user->email}) dibuat");

        return redirect()->route('admin.pengguna.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(Pengguna $pengguna)
    {
        $roles = Peran::orderBy('id')->get();

        return view('admin.pengguna.edit', compact('pengguna', 'roles'));
    }

    public function update(Request $request, Pengguna $pengguna)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($pengguna->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role_id' => ['required', 'exists:roles,id'],
            'status' => ['required', Rule::in(['pending', 'active', 'inactive'])],
        ]);

        $errors = [];

        if ((int) $data['role_id'] !== $pengguna->role_id && $pengguna->id === auth()->id()) {
            $errors['role_id'] = 'Anda tidak dapat mengubah peran akun sendiri.';
        }

        if ($data['status'] !== $pengguna->status && $pengguna->id === auth()->id() && $data['status'] === 'inactive') {
            $errors['status'] = 'Anda tidak dapat menonaktifkan akun sendiri.';
        }

        if ($errors !== []) {
            return back()->withErrors($errors)->withInput();
        }

        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role_id' => $data['role_id'],
            'status' => $data['status'],
        ];

        if (! empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $pengguna->update($updateData);

        PencatatAudit::log('user_updated', "Pengguna {$pengguna->name} ({$pengguna->email}) diupdate");

        return redirect()->route('admin.pengguna.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(Pengguna $pengguna)
    {
        if ($pengguna->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        if ($pengguna->employee) {
            return back()->with('error', 'Pengguna terhubung dengan data karyawan. Hapus lewat menu Data Karyawan terlebih dahulu.');
        }

        PencatatAudit::log('user_deleted', "Pengguna {$pengguna->name} ({$pengguna->email}) dihapus");

        $pengguna->delete();

        return redirect()->route('admin.pengguna.index')->with('success', 'Pengguna berhasil dihapus.');
    }

    public function toggleStatus(Pengguna $pengguna)
    {
        if ($pengguna->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $pengguna->update([
            'status' => $pengguna->status === 'active' ? 'inactive' : 'active',
        ]);

        $action = $pengguna->status === 'active' ? 'diaktifkan' : 'dinonaktifkan';
        PencatatAudit::log('user_status', "Akun {$pengguna->email} {$action}");

        return back()->with('success', "Akun pengguna berhasil {$action}.");
    }

    public function resetPassword(Pengguna $pengguna)
    {
        $password = Str::password(12, symbols: false);

        $pengguna->update(['password' => Hash::make($password)]);

        PencatatAudit::log('password_reset', "Password akun {$pengguna->email} direset");

        return back()->with('success', "Password akun berhasil direset. Password baru (ditampilkan sekali): {$password}");
    }
}
