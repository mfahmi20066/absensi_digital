<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\PengajuanCuti;
use App\Support\PencatatAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class PengajuanCutiSayaController extends Controller
{
    public function index()
    {
        $employee = auth()->user()->employee;

        if (! $employee) {
            return redirect()->route('dasbor')->with('error', 'Data karyawan tidak ditemukan. Hubungi admin.');
        }

        $requests = $employee
            ->leaveRequests()
            ->with('approver')
            ->latest()
            ->paginate(15);

        $sisaCuti = $employee->sisa_cuti;

        return view('cuti.index', compact('requests', 'sisaCuti'));
    }

    public function show(PengajuanCuti $leaveRequest)
    {
        $this->authorizeOwnership($leaveRequest);

        $leaveRequest->load('employee.user', 'approver');

        return view('cuti.show', ['req' => $leaveRequest]);
    }

    public function store(Request $request)
    {
        $employee = auth()->user()->employee;

        if (! $employee) {
            return redirect()->route('dasbor')->with('error', 'Data karyawan tidak ditemukan. Hubungi admin.');
        }

        $data = $request->validate([
            'type' => ['required', 'in:izin,sakit,cuti'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:1000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        if ($data['type'] === 'cuti') {
            $days = Carbon::parse($data['start_date'])->diffInDays($data['end_date']) + 1;

            $employee->ensureLeaveQuota();

            if ($days > $employee->leave_balance) {
                return back()->withInput()->with('error', "Sisa kuota cuti Anda tidak mencukupi (sisa: {$employee->leave_balance} hari, diminta: {$days} hari).");
            }
        }

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('leave-attachments', 'public');
        }

        $employee->leaveRequests()->create($data);

        PencatatAudit::log('leave_request_created', "Pengajuan {$data['type']} {$data['start_date']} s/d {$data['end_date']} oleh {$employee->user->name}");

        return back()->with('success', 'Pengajuan berhasil dikirim. Menunggu persetujuan.');
    }

    public function destroy(PengajuanCuti $leaveRequest)
    {
        $this->authorizeOwnership($leaveRequest);

        if ($leaveRequest->status !== 'pending') {
            return back()->with('error', 'Pengajuan yang sudah diproses tidak bisa dihapus.');
        }

        if ($leaveRequest->attachment) {
            Storage::disk('public')->delete($leaveRequest->attachment);
        }

        $leaveRequest->delete();

        return back()->with('success', 'Pengajuan dibatalkan.');
    }

    private function authorizeOwnership(PengajuanCuti $leaveRequest): void
    {
        $employee = auth()->user()->employee;

        abort_unless($employee && $leaveRequest->employee_id === $employee->id, 403);
    }
}
