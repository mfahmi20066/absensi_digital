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
        $this->ensureStatus($lembur, ['pending']);

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
        $this->ensureApprovable($lembur);

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
        $this->ensureApprovable($lembur);

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

    private function ensureApprovable(Lembur $lembur): void
    {
        if (auth()->user()->isAdmin()) {
            abort_if(! in_array($lembur->status, ['pending', 'verified_by_admin'], true), 422, 'Pengajuan ini sudah diproses.');
            return;
        }

        abort_if($lembur->status !== 'verified_by_admin', 422, 'Pengajuan harus diverifikasi Admin terlebih dahulu.');
    }

    private function ensureStatus(Lembur $lembur, array $statuses): void
    {
        abort_if(! in_array($lembur->status, $statuses, true), 422, 'Pengajuan ini sudah diproses.');
    }
}
