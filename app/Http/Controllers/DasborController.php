<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\KoreksiAbsensi;
use App\Models\Lembur;
use App\Models\PengajuanCuti;
use App\Models\Pengaturan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DasborController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin() || $user->isManajer()) {
            return $this->adminDasbor();
        }

        return $this->employeeDasbor();
    }

    private function adminDasbor()
    {
        $today = today();
        $totalKaryawans = Karyawan::count();
        $todayAbsensis = Absensi::whereDate('date', $today)->get();

        $stats = [
            'hadir' => $todayAbsensis->where('status', 'hadir')->count() + $todayAbsensis->where('status', 'telat')->count(),
            'telat' => $todayAbsensis->where('status', 'telat')->count(),
            'belum_absen' => max(0, $totalKaryawans - $todayAbsensis->count()),
            'pending_leave' => PengajuanCuti::where('status', 'pending')->count(),
            'pending_overtime' => Lembur::where('status', 'pending')->count(),
            'pending_correction' => KoreksiAbsensi::where('status', 'pending')->count(),
            'total_employees' => $totalKaryawans,
            'izin' => PengajuanCuti::where('status', 'approved')->whereDate('start_date', '<=', $today)->whereDate('end_date', '>=', $today)->where('type', 'izin')->count(),
            'sakit' => PengajuanCuti::where('status', 'approved')->whereDate('start_date', '<=', $today)->whereDate('end_date', '>=', $today)->where('type', 'sakit')->count(),
            'cuti' => PengajuanCuti::where('status', 'approved')->whereDate('start_date', '<=', $today)->whereDate('end_date', '>=', $today)->where('type', 'cuti')->count(),
        ];

        $monthly = Absensi::selectRaw('DATE_FORMAT(date, "%Y-%m") as bulan, COUNT(*) as total')
            ->where('date', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan');

        $recentAbsensis = Absensi::with('employee.user')
            ->whereDate('date', $today)
            ->latest()
            ->take(10)
            ->get();

        return view('dasbor.admin', compact('stats', 'monthly', 'recentAbsensis', 'today'));
    }

    private function employeeDasbor()
    {
        $employee = auth()->user()->employee;
        $today = today();

        $todayAbsensi = $employee?->attendances()->whereDate('date', $today)->first();

        $monthStart = now()->startOfMonth();
        $monthly = $employee?->attendances()
            ->where('date', '>=', $monthStart)
            ->get()
            ->groupBy('status')
            ->map->count() ?? collect();

        $recent = $employee?->attendances()->latest()->take(10)->get() ?? collect();

        $pendingLeaves = $employee?->leaveRequests()->where('status', 'pending')->count() ?? 0;

        $sisaCuti = $employee?->sisa_cuti ?? 0;

        $pendingOvertime = $employee?->overtimeRequests()->where('status', 'pending')->count() ?? 0;

        $pendingCorrections = $employee?->attendanceCorrections()->where('status', 'pending')->count() ?? 0;

        return view('dasbor.karyawan', compact('todayAbsensi', 'monthly', 'recent', 'pendingLeaves', 'sisaCuti', 'pendingOvertime', 'pendingCorrections'));
    }
}
