<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use Illuminate\Http\Request;

class RiwayatController extends Controller
{
    public function index(Request $request)
    {
        $employee = auth()->user()->employee;

        if (! $employee) {
            return redirect()->route('dasbor')->with('error', 'Data karyawan tidak ditemukan. Hubungi admin.');
        }

        $query = $employee->attendances()->with('workSchedule');

        if ($request->filled('month')) {
            $query->whereMonth('date', $request->month);
        }

        if ($request->filled('year')) {
            $query->whereYear('date', $request->year);
        }

        $attendances = $query->latest('date')->paginate(20)->withQueryString();

        return view('riwayat.index', compact('attendances'));
    }

    public function rekap(Request $request)
    {
        $employee = auth()->user()->employee;

        if (! $employee) {
            return redirect()->route('dasbor')->with('error', 'Data karyawan tidak ditemukan. Hubungi admin.');
        }

        $month = $request->filled('month') ? (int) $request->month : now()->month;
        $year = $request->filled('year') ? (int) $request->year : now()->year;

        $records = $employee->attendances()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get();

        $grouped = $records->groupBy('status');
        $summary = [
            'hadir' => ($grouped->get('hadir')?->count() ?? 0) + ($grouped->get('telat')?->count() ?? 0),
            'telat' => $grouped->get('telat')?->count() ?? 0,
            'izin' => $grouped->get('izin')?->count() ?? 0,
            'sakit' => $grouped->get('sakit')?->count() ?? 0,
            'cuti' => $grouped->get('cuti')?->count() ?? 0,
            'alpha' => $grouped->get('alpha')?->count() ?? 0,
        ];

        $workdays = 0;
        $schedule = $employee->workSchedule;
        $workingHours = 0;

        foreach ($records as $record) {
            if ($record->time_in && $record->time_out) {
                $workingHours += $record->time_out->diffInMinutes($record->time_in);
                $workdays++;
            }
        }

        $summary['workdays'] = $workdays;
        $summary['working_hours'] = round($workingHours / 60, 1);

        return view('rekap.index', compact('records', 'summary', 'month', 'year', 'schedule'));
    }
}
