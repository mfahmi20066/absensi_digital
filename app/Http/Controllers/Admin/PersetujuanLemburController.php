<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lembur;
use App\Support\PencatatAudit;
use Illuminate\Http\Request;

class PersetujuanLemburController extends Controller
{
    public function index(Request $request)
    {
        $query = Lembur::with('employee.user', 'approver');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $requests = $query->latest()->paginate(20)->withQueryString();

        return view('admin.lembur.index', compact('requests'));
    }

    public function verify(Lembur $lembur)
    {
        if ($error = $this->statusError($lembur, ['pending'])) {
            return back()->with('error', $error);
        }

        $lembur->update([
            'status' => 'verified_by_admin',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        PencatatAudit::log('overtime_verified', "Lembur {$lembur->date} {$lembur->employee->nip} diverifikasi admin");

        return back()->with('success', 'Pengajuan lembur diverifikasi. Menunggu persetujuan final Manajer.');
    }

    public function approve(Lembur $lembur)
    {
        if ($error = $this->approvableError($lembur)) {
            return back()->with('error', $error);
        }

        $lembur->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_note' => null,
        ]);

        PencatatAudit::log('overtime_approved', "Lembur {$lembur->date} {$lembur->employee->nip} disetujui");

        return back()->with('success', 'Pengajuan lembur disetujui.');
    }

    public function reject(Request $request, Lembur $lembur)
    {
        if ($error = $this->approvableError($lembur)) {
            return back()->with('error', $error);
        }

        $data = $request->validate([
            'rejection_note' => ['required', 'string', 'max:1000'],
        ]);

        $lembur->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_note' => $data['rejection_note'],
        ]);

        PencatatAudit::log('overtime_rejected', "Lembur {$lembur->date} {$lembur->employee->nip} ditolak");

        return back()->with('success', 'Pengajuan lembur ditolak.');
    }

    private function approvableError(Lembur $lembur): ?string
    {
        if (auth()->user()->isAdmin()) {
            return in_array($lembur->status, ['pending', 'verified_by_admin'], true)
                ? null
                : 'Pengajuan ini sudah diproses.';
        }

        return match ($lembur->status) {
            'verified_by_admin' => null,
            'pending' => 'Pengajuan harus diverifikasi Admin terlebih dahulu.',
            default => 'Pengajuan ini sudah diproses.',
        };
    }

    private function statusError(Lembur $lembur, array $statuses): ?string
    {
        return in_array($lembur->status, $statuses, true)
            ? null
            : 'Pengajuan ini sudah diproses.';
    }
}
