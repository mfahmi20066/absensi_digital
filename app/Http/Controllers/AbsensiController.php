<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Barcode;
use App\Models\Pengaturan;
use App\Support\PencatatAudit;
use App\Support\LokasiGeografis;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AbsensiController extends Controller
{
    public function absen()
    {
        $employee = auth()->user()->employee;

        if (! $employee || $employee->status !== 'aktif') {
            return redirect()->route('dasbor')->with('error', 'Data karyawan tidak ditemukan atau status tidak aktif.');
        }

        $todayAbsensi = $employee->attendances()->whereDate('date', today())->first();
        $schedule = $employee->workSchedule;
        $radius = (float) Pengaturan::get('radius_meter', '100');

        return view('absen.index', compact('employee', 'todayAbsensi', 'schedule', 'radius'));
    }

    public function myQr()
    {
        $employee = auth()->user()->employee;
        $barcode = $employee?->activeBarcode;

        if (! $barcode) {
            return redirect()->route('karyawan.absen.index')->with('error', 'Barcode belum dibuat. Hubungi admin.');
        }

        return view('absen.qrcode', compact('employee', 'barcode'));
    }

    public function scanShow(string $code)
    {
        $barcode = Barcode::with('employee.user')->where('code', $code)->where('is_active', true)->first();

        if (! $barcode) {
            return view('absen.invalid')->with('error', 'Barcode tidak valid atau tidak aktif.');
        }

        $employee = $barcode->employee;

        if ($employee->status !== 'aktif') {
            return view('absen.invalid')->with('error', 'Status karyawan tidak aktif.');
        }

        $todayAbsensi = $employee->attendances()->whereDate('date', today())->first();
        $schedule = $employee->workSchedule;
        $radius = (float) Pengaturan::get('radius_meter', '100');

        return view('absen.scan', compact('employee', 'barcode', 'todayAbsensi', 'schedule', 'radius'));
    }

    public function scanBarcode(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'photo' => ['nullable', 'string'],
        ]);

        $barcode = Barcode::with('employee.user')
            ->where('code', $request->code)
            ->where('is_active', true)
            ->first();

        if (! $barcode) {
            return back()->with('error', 'Barcode tidak valid atau tidak aktif.');
        }

        if ($barcode->valid_until && now()->toDateString() > $barcode->valid_until->toDateString()) {
            return back()->with('error', 'Barcode sudah kedaluwarsa.');
        }

        if ($barcode->valid_from && now()->toDateString() < $barcode->valid_from->toDateString()) {
            return back()->with('error', 'Barcode belum berlaku.');
        }

        $employee = $barcode->employee;

        if ($employee->status !== 'aktif') {
            return back()->with('error', 'Status karyawan tidak aktif.');
        }

        $result = $this->doClockInOut($employee, 'barcode', $request->latitude, $request->longitude, $request->photo);

        return back()->with('success', $result['message'])->with('clockResult', $result);
    }

    public function camera(Request $request)
    {
        $request->validate([
            'photo' => ['required', 'string'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ]);

        $employee = auth()->user()->employee;

        if (! $employee || $employee->status !== 'aktif') {
            return back()->with('error', 'Data karyawan tidak ditemukan atau tidak aktif.');
        }

        $result = $this->doClockInOut($employee, 'camera', $request->latitude, $request->longitude, $request->photo);

        return back()->with('success', $result['message'])->with('clockResult', $result);
    }

    private function doClockInOut($employee, string $method, float $latitude, float $longitude, ?string $photoBase64): array
    {
        $today = today();
        $attendance = $employee->attendances()->whereDate('date', $today)->first();
        $schedule = $employee->workSchedule;

        $sppgLat = (float) Pengaturan::get('sppg_latitude', '0');
        $sppgLon = (float) Pengaturan::get('sppg_longitude', '0');
        $radius = (float) Pengaturan::get('radius_meter', '100');

        $distance = LokasiGeografis::distanceInMeters($latitude, $longitude, $sppgLat, $sppgLon);
        $isOutside = $distance > $radius;

        $photoPath = null;
        if ($photoBase64) {
            $photoPath = $this->storeBase64Photo($photoBase64);
        }

        if (! $attendance) {
            $tolerance = $schedule->tolerance_minutes ?? (int) Pengaturan::get('default_tolerance_minutes', '15');
            $timeIn = now();

            $status = 'hadir';
            if ($schedule) {
                $limit = Carbon::parse($schedule->time_in->format('H:i'))->addMinutes($tolerance);
                if ($timeIn->gt($limit)) {
                    $status = 'telat';
                }
            }

            $attendance = $employee->attendances()->create([
                'work_schedule_id' => $schedule?->id,
                'date' => $today,
                'time_in' => $timeIn,
                'method_in' => $method,
                'photo_in' => $photoPath,
                'latitude_in' => $latitude,
                'longitude_in' => $longitude,
                'is_outside_area_in' => $isOutside,
                'status' => $status,
            ]);

            PencatatAudit::log('attendance_checkin', "Absen masuk {$employee->nip} ({$employee->user->name}) via {$method}");

            $message = "Absen masuk berhasil pukul {$timeIn->format('H:i:s')}. Status: " . ($status === 'telat' ? 'TELAT' : 'HADIR');

            return [
                'type' => 'in',
                'status' => $status,
                'time' => $timeIn->format('H:i:s'),
                'outside' => $isOutside,
                'distance' => round($distance),
                'message' => $isOutside ? "{$message}. (Anda berada {$distance} m dari lokasi SPPG)" : $message,
            ];
        }

        if ($attendance->time_out) {
            return [
                'type' => 'duplicate',
                'message' => 'Anda sudah absen masuk dan pulang hari ini.',
            ];
        }

        $attendance->update([
            'time_out' => now(),
            'method_out' => $method,
            'photo_out' => $photoPath,
            'latitude_out' => $latitude,
            'longitude_out' => $longitude,
            'is_outside_area_out' => $isOutside,
        ]);

        PencatatAudit::log('attendance_checkout', "Absen pulang {$employee->nip} ({$employee->user->name}) via {$method}");

        $time = now();
        $message = "Absen pulang berhasil pukul {$time->format('H:i:s')}.";

        return [
            'type' => 'out',
            'time' => $time->format('H:i:s'),
            'outside' => $isOutside,
            'distance' => round($distance),
            'message' => $isOutside ? "{$message} (Anda berada {$distance} m dari lokasi SPPG)" : $message,
        ];
    }

    private function storeBase64Photo(string $base64): string
    {
        $base64 = preg_replace('#^data:image/\w+;base64,#i', '', $base64);

        if ($base64 === '' || strlen($base64) > 5_000_000 || ! base64_decode($base64, true)) {
            abort(422, 'Foto tidak valid atau terlalu besar.');
        }

        $binary = base64_decode($base64);
        $filename = 'attendance/' . now()->format('Y/m/d') . '/' . Str::uuid() . '.jpg';
        Storage::disk('public')->put($filename, $binary);

        return $filename;
    }
}
