<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\KoreksiAbsensi;
use App\Support\PencatatAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PersetujuanKoreksiController extends Controller
{
    public function index(Request $request)
    {
        $query = KoreksiAbsensi::with('employee.user', 'approver');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $requests = $query->latest()->paginate(20)->withQueryString();

        return view('admin.koreksi.index', compact('requests'));
    }

    public function verify(KoreksiAbsensi $koreksi)
    {
        if ($error = $this->statusError($koreksi, ['pending'])) {
            return back()->with('error', $error);
        }

        $koreksi->update([
            'status' => 'verified_by_admin',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        PencatatAudit::log('attendance_correction_verified', "Koreksi absensi {$koreksi->date} {$koreksi->employee->nip} diverifikasi admin");

        return back()->with('success', 'Koreksi absensi diverifikasi. Menunggu persetujuan final Manajer.');
    }

    public function approve(KoreksiAbsensi $koreksi)
    {
        if ($error = $this->approvableError($koreksi)) {
            return back()->with('error', $error);
        }

        $koreksi->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_note' => null,
        ]);

        $this->applyCorrection($koreksi);

        PencatatAudit::log('attendance_correction_approved', "Koreksi absensi {$koreksi->date} {$koreksi->employee->nip} disetujui dan diterapkan");

        return back()->with('success', 'Koreksi disetujui dan diterapkan ke data absensi.');
    }

    public function reject(Request $request, KoreksiAbsensi $koreksi)
    {
        if ($error = $this->approvableError($koreksi)) {
            return back()->with('error', $error);
        }

        $data = $request->validate([
            'rejection_note' => ['required', 'string', 'max:1000'],
        ]);

        $koreksi->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_note' => $data['rejection_note'],
        ]);

        PencatatAudit::log('attendance_correction_rejected', "Koreksi absensi {$koreksi->date} {$koreksi->employee->nip} ditolak");

        return back()->with('success', 'Koreksi absensi ditolak.');
    }

    private function applyCorrection(KoreksiAbsensi $koreksi): void
    {
        $employee = $koreksi->employee;
        $date = $koreksi->date->toDateString();

        $existing = Absensi::where('employee_id', $employee->id)->whereDate('date', $date)->first();

        $timeIn = $koreksi->time_in
            ? Carbon::parse($date.' '.$koreksi->time_in)
            : $existing?->time_in;

        $timeOut = $koreksi->time_out
            ? Carbon::parse($date.' '.$koreksi->time_out)
            : $existing?->time_out;

        Absensi::updateOrCreate(
            ['employee_id' => $employee->id, 'date' => $date],
            [
                'work_schedule_id' => $employee->work_schedule_id,
                'time_in' => $timeIn,
                'time_out' => $timeOut,
                'status' => 'hadir',
                'notes' => 'Koreksi disetujui: '.$koreksi->reason,
            ]
        );
    }

    private function approvableError(KoreksiAbsensi $koreksi): ?string
    {
        if (auth()->user()->isAdmin()) {
            return in_array($koreksi->status, ['pending', 'verified_by_admin'], true)
                ? null
                : 'Pengajuan ini sudah diproses.';
        }

        return match ($koreksi->status) {
            'verified_by_admin' => null,
            'pending' => 'Pengajuan harus diverifikasi Admin terlebih dahulu.',
            default => 'Pengajuan ini sudah diproses.',
        };
    }

    private function statusError(KoreksiAbsensi $koreksi, array $statuses): ?string
    {
        return in_array($koreksi->status, $statuses, true)
            ? null
            : 'Pengajuan ini sudah diproses.';
    }
}
