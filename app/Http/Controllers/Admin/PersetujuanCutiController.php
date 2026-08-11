<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\PengajuanCuti;
use App\Support\PencatatAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PersetujuanCutiController extends Controller
{
    public function index(Request $request)
    {
        $query = PengajuanCuti::with('employee.user', 'approver');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        $requests = $query->latest()->paginate(20)->withQueryString();

        return view('admin.pengajuan-cuti.index', compact('requests'));
    }

    public function show(PengajuanCuti $leaveRequest)
    {
        $leaveRequest->load('employee.user', 'employee.jabatan', 'approver');

        return view('admin.pengajuan-cuti.show', ['req' => $leaveRequest]);
    }

    public function verify(PengajuanCuti $leaveRequest)
    {
        $this->ensureStatus($leaveRequest, ['pending']);

        $leaveRequest->update([
            'status' => 'verified_by_admin',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        PencatatAudit::log('leave_verified', "Pengajuan {$leaveRequest->type} {$leaveRequest->employee->nip} diverifikasi admin");

        return back()->with('success', 'Pengajuan diverifikasi. Menunggu persetujuan final Manajer.');
    }

    public function approve(PengajuanCuti $leaveRequest)
    {
        $this->ensureApprovable($leaveRequest);

        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_note' => null,
        ]);

        $this->markLeaveDays($leaveRequest, 'approved');

        if ($leaveRequest->type === 'cuti') {
            $this->deductLeaveQuota($leaveRequest);
        }

        PencatatAudit::log('leave_approved', "Pengajuan {$leaveRequest->type} {$leaveRequest->employee->nip} disetujui");

        return back()->with('success', 'Pengajuan disetujui. Hari tersebut otomatis tercatat di absensi.');
    }

    public function reject(Request $request, PengajuanCuti $leaveRequest)
    {
        $this->ensureApprovable($leaveRequest);

        $data = $request->validate([
            'rejection_note' => ['required', 'string', 'max:1000'],
        ]);

        $leaveRequest->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_note' => $data['rejection_note'],
        ]);

        PencatatAudit::log('leave_rejected', "Pengajuan {$leaveRequest->type} {$leaveRequest->employee->nip} ditolak");

        return back()->with('success', 'Pengajuan ditolak.');
    }

    private function ensureApprovable(PengajuanCuti $leaveRequest): void
    {
        if (auth()->user()->isAdmin()) {
            abort_if(! in_array($leaveRequest->status, ['pending', 'verified_by_admin'], true), 422, 'Pengajuan ini sudah diproses.');
            return;
        }

        abort_if($leaveRequest->status !== 'verified_by_admin', 422, 'Pengajuan harus diverifikasi Admin terlebih dahulu.');
    }

    private function ensureStatus(PengajuanCuti $leaveRequest, array $statuses): void
    {
        abort_if(! in_array($leaveRequest->status, $statuses, true), 422, 'Pengajuan ini sudah diproses.');
    }

    private function deductLeaveQuota(PengajuanCuti $leaveRequest): void
    {
        $employee = $leaveRequest->employee;
        $employee->ensureLeaveQuota();

        $days = Carbon::parse($leaveRequest->start_date)->diffInDays($leaveRequest->end_date) + 1;

        $employee->update([
            'leave_balance' => max(0, $employee->leave_balance - $days),
        ]);
    }

    private function markLeaveDays(PengajuanCuti $leaveRequest, string $status): void
    {
        $statusMap = [
            'izin' => 'izin',
            'sakit' => 'sakit',
            'cuti' => 'cuti',
        ];

        $attendanceStatus = $statusMap[$leaveRequest->type] ?? 'izin';

        $days = Carbon::parse($leaveRequest->start_date)->daysUntil($leaveRequest->end_date);

        foreach ($days as $day) {
            $date = $day->toDateString();

            $alreadyRecorded = Absensi::where('employee_id', $leaveRequest->employee_id)
                ->whereDate('date', $date)
                ->exists();

            if (! $alreadyRecorded) {
                Absensi::create([
                    'employee_id' => $leaveRequest->employee_id,
                    'work_schedule_id' => $leaveRequest->employee->work_schedule_id,
                    'date' => $date,
                    'status' => $attendanceStatus,
                    'notes' => 'Cuti/Izin disetujui: ' . $leaveRequest->reason,
                ]);
            }
        }
    }
}
