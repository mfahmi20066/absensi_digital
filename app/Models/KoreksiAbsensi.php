<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'employee_id', 'attendance_id', 'date', 'time_in', 'time_out',
    'reason', 'status', 'approved_by', 'approved_at', 'rejection_note',
])]
class KoreksiAbsensi extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'attendance_corrections';

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'employee_id');
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Absensi::class, 'attendance_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'approved_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'verified_by_admin' => 'Diverifikasi Admin',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => 'Pending',
        };
    }

    public function getTimeInLabelAttribute(): ?string
    {
        return $this->time_in ? substr((string) $this->time_in, 0, 5) : null;
    }

    public function getTimeOutLabelAttribute(): ?string
    {
        return $this->time_out ? substr((string) $this->time_out, 0, 5) : null;
    }
}
