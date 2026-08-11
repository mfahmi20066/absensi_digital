<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barcode;
use App\Models\Jabatan;
use App\Models\Karyawan;
use App\Models\Pengguna;
use App\Models\JadwalKerja;
use App\Support\PencatatAudit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KaryawanController extends Controller
{
    public function index(Request $request)
    {
        $query = Karyawan::with('user', 'workSchedule');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nip', 'like', "%{$request->search}%")
                    ->orWhere('position', 'like', "%{$request->search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$request->search}%"));
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $employees = $query->latest()->paginate(15)->withQueryString();
        $schedules = JadwalKerja::all();

        return view('admin.karyawan.index', compact('employees', 'schedules'));
    }

    public function create()
    {
        $schedules = JadwalKerja::all();
        $jabatans = Jabatan::orderBy('name')->get();
        $users = Pengguna::whereDoesntHave('employee')
            ->where('role_id', 3)
            ->with('role')
            ->orderBy('name')
            ->get();

        return view('admin.karyawan.create', compact('schedules', 'jabatans', 'users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id', Rule::unique('employees', 'user_id')],
            'nip' => ['nullable', 'string', 'max:255', Rule::unique('employees', 'nip')],
            'position_id' => ['required', 'exists:positions,id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'work_schedule_id' => ['nullable', 'exists:work_schedules,id'],
            'join_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif', 'resign'])],
            'role_id' => ['nullable', Rule::in([3])],
        ]);

        $user = Pengguna::findOrFail($data['user_id']);

        if (! empty($data['role_id']) && (int) $data['role_id'] !== $user->role_id) {
            $user->update(['role_id' => $data['role_id']]);
        }

        $jabatan = Jabatan::findOrFail($data['position_id']);

        $employee = Karyawan::create([
            'user_id' => $user->id,
            'work_schedule_id' => $data['work_schedule_id'] ?? null,
            'nip' => $data['nip'] ?? null,
            'position' => $jabatan->name,
            'position_id' => $jabatan->id,
            'phone' => $data['phone'] ?? null,
            'join_date' => $data['join_date'],
            'status' => $data['status'],
        ]);

        $kodeBarcode = $data['nip'] ?? null ?: 'EMP'.str_pad((string) $user->id, 6, '0', STR_PAD_LEFT);

        Barcode::create([
            'employee_id' => $employee->id,
            'code' => $kodeBarcode,
            'valid_from' => today(),
            'valid_until' => today()->addYear(),
            'is_active' => true,
        ]);

        $labelNip = $employee->nip ?? 'tanpa NIP';
        PencatatAudit::log('employee_created', "Karyawan {$labelNip} - {$user->name} ditambahkan");

        return redirect()->route(auth()->user()->isAdmin() ? 'admin.karyawan.index' : 'manajer.karyawan.index')->with('success', 'Karyawan berhasil ditambahkan beserta barcode.');
    }

    public function edit(Karyawan $employee)
    {
        $schedules = JadwalKerja::all();
        $jabatans = Jabatan::orderBy('name')->get();

        return view('admin.karyawan.edit', compact('employee', 'schedules', 'jabatans'));
    }

    public function update(Request $request, Karyawan $employee)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($employee->user_id)],
            'password' => ['nullable', 'string', 'min:8'],
            'nip' => ['required', 'string', Rule::unique('employees', 'nip')->ignore($employee->id)],
            'position_id' => ['required', 'exists:positions,id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'work_schedule_id' => ['nullable', 'exists:work_schedules,id'],
            'join_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif', 'resign'])],
        ]);

        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        if (! empty($data['password'])) {
            $userData['password'] = $data['password'];
        }

        $jabatan = Jabatan::findOrFail($data['position_id']);

        $employee->user->update($userData);
        $employee->update([
            'work_schedule_id' => $data['work_schedule_id'] ?? null,
            'nip' => $data['nip'],
            'position' => $jabatan->name,
            'position_id' => $jabatan->id,
            'phone' => $data['phone'],
            'join_date' => $data['join_date'],
            'status' => $data['status'],
        ]);

        PencatatAudit::log('employee_updated', "Karyawan {$data['nip']} - {$data['name']} diupdate");

        return redirect()->route(auth()->user()->isAdmin() ? 'admin.karyawan.index' : 'manajer.karyawan.index')->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(Karyawan $employee)
    {
        $nama = $employee->user->name;
        $labelNip = $employee->nip ?? 'tanpa NIP';
        PencatatAudit::log('employee_deleted', "Karyawan {$labelNip} - {$nama} dihapus");

        $employee->delete();

        return redirect()->route('admin.karyawan.index')->with('success', 'Karyawan dihapus (akun user tetap ada).');
    }

    public function toggleStatus(Karyawan $employee)
    {
        $employee->user->update([
            'status' => $employee->user->status === 'active' ? 'inactive' : 'active',
        ]);

        $action = $employee->user->status === 'active' ? 'diaktifkan' : 'dinonaktifkan';
        PencatatAudit::log('employee_status', "Akun {$employee->nip} - {$employee->user->name} {$action}");

        return back()->with('success', "Akun karyawan berhasil {$action}.");
    }

    public function resetPassword(Karyawan $employee)
    {
        $employee->user->update(['password' => 'password123']);

        PencatatAudit::log('password_reset', "Password akun {$employee->nip} - {$employee->user->name} direset");

        return back()->with('success', 'Password direset menjadi: password123');
    }
}
