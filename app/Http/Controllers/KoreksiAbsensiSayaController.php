<?php

namespace App\Http\Controllers;

use App\Models\KoreksiAbsensi;
use App\Support\PencatatAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class KoreksiAbsensiSayaController extends Controller
{
    public function index()
    {
        $employee = auth()->user()->employee;

        if (! $employee) {
            return redirect()->route('dasbor')->with('error', 'Data karyawan tidak ditemukan. Hubungi admin.');
        }

        $requests = $employee
            ->attendanceCorrections()
            ->with('approver')
            ->latest()
            ->paginate(15);

        $recentDates = $employee->attendances()
            ->whereDate('date', '<', today())
            ->latest('date')
            ->take(30)
            ->get();

        return view('koreksi.index', compact('requests', 'recentDates'));
    }

    public function store(Request $request)
    {
        $employee = auth()->user()->employee;

        if (! $employee) {
            return redirect()->route('dasbor')->with('error', 'Data karyawan tidak ditemukan. Hubungi admin.');
        }

        $data = $request->validate([
            'date' => ['required', 'date', 'before:today'],
            'time_in' => ['nullable', 'date_format:H:i'],
            'time_out' => ['nullable', 'date_format:H:i'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        if (empty($data['time_in']) && empty($data['time_out'])) {
            return back()->withInput()->with('error', 'Isi minimal salah satu jam (masuk atau pulang).');
        }

        $exists = $employee->attendanceCorrections()
            ->whereDate('date', $data['date'])
            ->whereIn('status', ['pending', 'verified_by_admin'])
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Anda sudah mengajukan koreksi untuk tanggal tersebut.');
        }

        $attendance = $employee->attendances()->whereDate('date', $data['date'])->first();

        $employee->attendanceCorrections()->create([
            'attendance_id' => $attendance?->id,
            'date' => $data['date'],
            'time_in' => $data['time_in'] ? Carbon::parse($data['time_in'])->format('H:i:s') : null,
            'time_out' => $data['time_out'] ? Carbon::parse($data['time_out'])->format('H:i:s') : null,
            'reason' => $data['reason'],
        ]);

        PencatatAudit::log('attendance_correction_created', "Koreksi absensi {$data['date']} oleh {$employee->user->name}");

        return back()->with('success', 'Koreksi absensi berhasil dikirim. Menunggu persetujuan.');
    }

    public function destroy(KoreksiAbsensi $koreksi)
    {
        $employee = auth()->user()->employee;

        abort_unless($employee && $koreksi->employee_id === $employee->id, 403);

        if ($koreksi->status !== 'pending') {
            return back()->with('error', 'Pengajuan yang sudah diproses tidak bisa dihapus.');
        }

        $koreksi->delete();

        return back()->with('success', 'Koreksi absensi dibatalkan.');
    }
}
