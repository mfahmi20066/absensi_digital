<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'period' => ['nullable', 'in:daily,monthly,yearly'],
            'date' => ['nullable', 'date'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ]);

        $period = $request->period ?? 'daily';
        $date = $request->date ?? today()->toDateString();
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $query = Absensi::with('employee.user', 'workSchedule');

        $title = match ($period) {
            'monthly' => 'Rekap Bulanan - ' . Carbon::create($year, $month, 1)->translatedFormat('F Y'),
            'yearly' => 'Rekap Tahunan - ' . $year,
            default => 'Rekap Harian - ' . Carbon::parse($date)->translatedFormat('d F Y'),
        };

        $header = match ($period) {
            'monthly' => 'Bulan ' . Carbon::create($year, $month, 1)->translatedFormat('F Y'),
            'yearly' => 'Tahun ' . $year,
            default => Carbon::parse($date)->translatedFormat('d F Y'),
        };

        if ($period === 'monthly') {
            $query->whereYear('date', $year)->whereMonth('date', $month);
        } elseif ($period === 'yearly') {
            $query->whereYear('date', $year);
        } else {
            $query->whereDate('date', $date);
        }

        $attendances = $query->orderBy('date')->orderBy('employee_id')->get();

        $summary = $attendances->groupBy('status')->map->count();

        if ($request->filled('export') && in_array($request->export, ['xlsx', 'csv', 'print', 'pdf'])) {
            return $this->export($period, $title, $header, $attendances, $summary, $request->export);
        }

        return view('admin.laporan.index', compact('period', 'date', 'month', 'year', 'title', 'header', 'attendances', 'summary'));
    }

    private function export(string $period, string $title, string $header, $attendances, $summary, string $type)
    {
        if ($type === 'pdf' || $type === 'print') {
            return $this->surat($period, $title, $header, $attendances, $summary, $type);
        }

        return (new \App\Exports\LaporanAbsensiExport($period, $title, $attendances, $summary))->download();
    }

    private function surat(string $period, string $title, string $header, $attendances, $summary, string $type)
    {
        $romawi = [1 => 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $urutan = (int) \App\Models\Pengaturan::get('surat_counter', 0) + 1;
        \App\Models\Pengaturan::set('surat_counter', (string) $urutan);

        $namaInstansi = \App\Models\Pengaturan::get('sppg_name', 'SPPG');
        $kataInstansi = explode(' ', trim($namaInstansi));
        $kota = $kataInstansi[1] ?? 'Palopo';

        $data = [
            'title' => $title,
            'header' => $header,
            'attendances' => $attendances,
            'summary' => $summary,
            'nomorSurat' => sprintf('%03d', $urutan) . '/SPPG/ABS/' . $romawi[now()->month] . '/' . now()->year,
            'tanggalSurat' => $kota . ', ' . now()->translatedFormat('d F Y'),
            'kepalaNama' => \App\Models\Pengaturan::get('kepala_nama'),
            'kepalaNip' => \App\Models\Pengaturan::get('kepala_nip'),
        ];

        if ($type === 'print') {
            return view('admin.laporan.pdf', $data + ['printMode' => true]);
        }

        $filename = 'laporan-absensi-' . $period . '-' . now()->format('Ymd-His') . '.pdf';

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.laporan.pdf', $data)
            ->setPaper('a4', 'portrait')
            ->stream($filename);
    }
}
