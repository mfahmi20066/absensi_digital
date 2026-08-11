<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'employee_id', 'date', 'start_time', 'end_time', 'duration_minutes',
    'reason', 'status', 'approved_by', 'approved_at', 'rejection_note',
])]
class Lembur extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'overtime_requests';

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

    public function getDurationLabelAttribute(): string
    {
        $hours = intdiv($this->duration_minutes, 60);
        $minutes = $this->duration_minutes % 60;

        return $hours > 0 ? "{$hours} jam {$minutes} menit" : "{$minutes} menit";
    }

    public function getStartTimeLabelAttribute(): string
    {
        return substr((string) $this->start_time, 0, 5);
    }

    public function getEndTimeLabelAttribute(): string
    {
        return substr((string) $this->end_time, 0, 5);
    }
}
