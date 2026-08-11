<?php

namespace App\Http\Controllers;

use App\Models\Lembur;
use App\Support\PencatatAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LemburSayaController extends Controller
{
    public function index()
    {
        $employee = auth()->user()->employee;

        if (! $employee) {
            return redirect()->route('dasbor')->with('error', 'Data karyawan tidak ditemukan. Hubungi admin.');
        }

        $requests = $employee
            ->overtimeRequests()
            ->with('approver')
            ->latest()
            ->paginate(15);

        $monthTotal = (int) $employee->overtimeRequests()
            ->where('status', 'approved')
            ->whereYear('date', now()->year)
            ->sum('duration_minutes');

        return view('lembur.index', compact('requests', 'monthTotal'));
    }

    public function store(Request $request)
    {
        $employee = auth()->user()->employee;

        if (! $employee) {
            return redirect()->route('dasbor')->with('error', 'Data karyawan tidak ditemukan. Hubungi admin.');
        }

        $data = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $start = Carbon::parse($data['start_time']);
        $end = Carbon::parse($data['end_time']);

        if (! $end->greaterThan($start)) {
            return back()->withInput()->with('error', 'Jam selesai harus setelah jam mulai.');
        }

        $exists = $employee->overtimeRequests()
            ->whereDate('date', $data['date'])
            ->where('status', '!=', 'rejected')
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Anda sudah mengajukan lembur di tanggal tersebut.');
        }

        $employee->overtimeRequests()->create([
            'date' => $data['date'],
            'start_time' => Carbon::parse($data['start_time'])->format('H:i:s'),
            'end_time' => Carbon::parse($data['end_time'])->format('H:i:s'),
            'duration_minutes' => (int) $start->diffInMinutes($end),
            'reason' => $data['reason'],
        ]);

        PencatatAudit::log('overtime_created', "Pengajuan lembur {$data['date']} oleh {$employee->user->name}");

        return back()->with('success', 'Pengajuan lembur berhasil dikirim. Menunggu persetujuan.');
    }

    public function destroy(Lembur $lembur)
    {
        $employee = auth()->user()->employee;

        abort_unless($employee && $lembur->employee_id === $employee->id, 403);

        if ($lembur->status !== 'pending') {
            return back()->with('error', 'Pengajuan yang sudah diproses tidak bisa dihapus.');
        }

        $lembur->delete();

        return back()->with('success', 'Pengajuan lembur dibatalkan.');
    }
}
