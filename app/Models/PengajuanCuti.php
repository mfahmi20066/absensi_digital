<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['employee_id', 'type', 'start_date', 'end_date', 'reason', 'attachment', 'status', 'approved_by', 'approved_at', 'rejection_note'])]
class PengajuanCuti extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'leave_requests';

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
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

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            default => 'Cuti',
        };
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
}
