<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Karyawan;
use App\Support\PencatatAudit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $query = Absensi::with('employee.user', 'workSchedule');

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        } else {
            $query->whereDate('date', today());
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('area') && in_array($request->area, ['dalam', 'luar'], true)) {
            if ($request->area === 'luar') {
                $query->where(function ($q) {
                    $q->where('is_outside_area_in', true)
                        ->orWhere('is_outside_area_out', true);
                });
            } else {
                $query->where(function ($q) {
                    $q->where(fn ($q1) => $q1->whereNotNull('latitude_in')->orWhereNotNull('latitude_out'))
                        ->where('is_outside_area_in', false)
                        ->where('is_outside_area_out', false);
                });
            }
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('employee.user', fn ($u) => $u->where('name', 'like', "%{$request->search}%"))
                    ->orWhereHas('employee', fn ($e) => $e->where('nip', 'like', "%{$request->search}%"));
            });
        }

        $attendances = $query->latest()->paginate(20)->withQueryString();
        $employees = Karyawan::with('user')->where('status', 'aktif')->get();
        $date = $request->date ?? today()->toDateString();

        return view('admin.absensi.index', compact('attendances', 'employees', 'date'));
    }

    public function edit(Absensi $attendance)
    {
        $employees = Karyawan::with('user')->get();

        return view('admin.absensi.edit', compact('attendance', 'employees'));
    }

    public function update(Request $request, Absensi $attendance)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['hadir', 'telat', 'izin', 'sakit', 'alpha', 'cuti'])],
            'notes' => ['nullable', 'string', 'max:1000'],
            'time_in' => ['nullable', 'date_format:H:i'],
            'time_out' => ['nullable', 'date_format:H:i'],
        ]);

        $update = [
            'status' => $data['status'],
            'notes' => $data['notes'],
        ];

        if (! empty($data['time_in'])) {
            $update['time_in'] = $attendance->date->copy()->setTimeFromTimeString($data['time_in']);
        }

        if (! empty($data['time_out'])) {
            $update['time_out'] = $attendance->date->copy()->setTimeFromTimeString($data['time_out']);
        }

        $attendance->update($update);

        PencatatAudit::log('attendance_updated', "Absensi {$attendance->employee->nip} tanggal {$attendance->date} diupdate");

        return redirect()->route('admin.absensi.index', ['date' => $attendance->date->toDateString()])
            ->with('success', 'Data absensi diperbarui.');
    }

    public function destroy(Absensi $attendance)
    {
        PencatatAudit::log('attendance_deleted', "Absensi {$attendance->employee->nip} tanggal {$attendance->date} dihapus");

        $attendance->delete();

        return back()->with('success', 'Data absensi dihapus.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'date' => ['required', 'date'],
            'time_in' => ['nullable', 'date_format:H:i'],
            'time_out' => ['nullable', 'date_format:H:i'],
            'status' => ['required', Rule::in(['hadir', 'telat', 'izin', 'sakit', 'alpha', 'cuti'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $employee = Karyawan::findOrFail($data['employee_id']);

        $attributes = [
            'work_schedule_id' => $employee->work_schedule_id,
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
        ];

        if (! empty($data['time_in'])) {
            $attributes['time_in'] = $data['date'] . ' ' . $data['time_in'];
        }

        if (! empty($data['time_out'])) {
            $attributes['time_out'] = $data['date'] . ' ' . $data['time_out'];
        }

        $attendance = Absensi::where('employee_id', $employee->id)
            ->whereDate('date', $data['date'])
            ->first();

        if ($attendance) {
            $attendance->update($attributes);
        } else {
            $attendance = Absensi::create(array_merge(
                ['employee_id' => $employee->id, 'date' => $data['date']],
                $attributes
            ));
        }

        PencatatAudit::log('attendance_manual', "Absensi manual untuk {$employee->nip} tanggal {$data['date']}");

        return back()->with('success', 'Absensi manual berhasil disimpan.');
    }
}
