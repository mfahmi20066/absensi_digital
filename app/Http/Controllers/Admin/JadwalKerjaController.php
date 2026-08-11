<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalKerja;
use App\Support\PencatatAudit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JadwalKerjaController extends Controller
{
    public function index()
    {
        $schedules = JadwalKerja::withCount('employees')->orderBy('time_in')->get();

        return view('admin.jadwal-kerja.index', compact('schedules'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'time_in' => ['required', 'date_format:H:i'],
            'time_out' => ['required', 'date_format:H:i'],
            'tolerance_minutes' => ['required', 'integer', 'min:0', 'max:180'],
        ]);

        JadwalKerja::create($data);

        PencatatAudit::log('schedule_created', "Jadwal kerja {$data['name']} ({$data['time_in']} - {$data['time_out']}) ditambahkan");

        return back()->with('success', 'Jadwal kerja berhasil ditambahkan.');
    }

    public function update(Request $request, JadwalKerja $jadwal_kerja)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'time_in' => ['required', 'date_format:H:i'],
            'time_out' => ['required', 'date_format:H:i'],
            'tolerance_minutes' => ['required', 'integer', 'min:0', 'max:180'],
        ]);

        $jadwal_kerja->update($data);

        PencatatAudit::log('schedule_updated', "Jadwal kerja {$data['name']} diupdate");

        return back()->with('success', 'Jadwal kerja berhasil diperbarui.');
    }

    public function destroy(JadwalKerja $jadwal_kerja)
    {
        PencatatAudit::log('schedule_deleted', "Jadwal kerja {$jadwal_kerja->name} dihapus");

        $jadwal_kerja->delete();

        return back()->with('success', 'Jadwal kerja dihapus.');
    }
}
