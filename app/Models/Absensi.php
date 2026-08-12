<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'employee_id', 'work_schedule_id', 'date', 'time_in', 'time_out',
    'method_in', 'method_out', 'photo_in', 'photo_out',
    'latitude_in', 'longitude_in', 'latitude_out', 'longitude_out',
    'is_outside_area_in', 'is_outside_area_out', 'is_anomaly_in', 'is_anomaly_out',
    'status', 'notes',
])]
class Absensi extends Model
{
    protected $table = 'attendances';

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'time_in' => 'datetime',
            'time_out' => 'datetime',
            'is_outside_area_in' => 'boolean',
            'is_outside_area_out' => 'boolean',
            'is_anomaly_in' => 'boolean',
            'is_anomaly_out' => 'boolean',
            'latitude_in' => 'float',
            'longitude_in' => 'float',
            'latitude_out' => 'float',
            'longitude_out' => 'float',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'employee_id');
    }

    public function workSchedule(): BelongsTo
    {
        return $this->belongsTo(JadwalKerja::class, 'work_schedule_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'hadir' => 'Hadir',
            'telat' => 'Telat',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'cuti' => 'Cuti',
            default => 'Alpha',
        };
    }
}
