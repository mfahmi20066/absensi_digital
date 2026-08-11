<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\PengajuanCuti;
use App\Models\Peran;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TempBugfixTest extends TestCase
{
    use RefreshDatabase;

    private function makePenggunas(): array
    {
        $roleAdmin = Peran::create(['name' => 'admin', 'label' => 'Admin']);
        $roleKaryawan = Peran::create(['name' => 'karyawan', 'label' => 'Karyawan']);

        $admin = Pengguna::create([
            'name' => 'Admin', 'email' => 'a@t.t', 'password' => 'password123',
            'role_id' => $roleAdmin->id, 'status' => 'active',
        ]);

        $u1 = Pengguna::create([
            'name' => 'Andi', 'email' => 'u1@t.t', 'password' => 'password123',
            'role_id' => $roleKaryawan->id, 'status' => 'active',
        ]);
        $u2 = Pengguna::create([
            'name' => 'Budi', 'email' => 'u2@t.t', 'password' => 'password123',
            'role_id' => $roleKaryawan->id, 'status' => 'active',
        ]);

        $e1 = Karyawan::create(['user_id' => $u1->id, 'nip' => 'SPPG-001', 'position' => 'Koki', 'status' => 'aktif']);
        $e2 = Karyawan::create(['user_id' => $u2->id, 'nip' => 'SPPG-002', 'position' => 'Kasir', 'status' => 'aktif']);

        return [$admin, $e1, $e2];
    }

    public function test_search_nip_does_not_leak_across_dates(): void
    {
        [$admin, $e1, $e2] = $this->makePenggunas();

        Absensi::create([
            'employee_id' => $e1->id, 'date' => '2026-08-10', 'time_in' => now(), 'status' => 'hadir',
        ]);
        Absensi::create([
            'employee_id' => $e1->id, 'date' => '2026-08-09', 'time_in' => now(), 'status' => 'hadir',
        ]);
        Absensi::create([
            'employee_id' => $e2->id, 'date' => '2026-08-10', 'time_in' => now(), 'status' => 'hadir',
        ]);

        $response = $this->actingAs($admin)->get('/admin/absensi?date=2026-08-10&search=SPPG-001');
        $response->assertOk();
        $attendances = $response->viewData('attendances');
        $count = $attendances->total();
        $this->assertSame(1, $count);
        $this->assertSame('Andi', $attendances->first()->employee->user->name);
    }

    public function test_approved_leave_does_not_overwrite_existing_attendance(): void
    {
        [$admin, $e1] = $this->makePenggunas();

        $existing = Absensi::create([
            'employee_id' => $e1->id, 'date' => '2026-08-12',
            'time_in' => '2026-08-12 07:30:00', 'time_out' => '2026-08-12 16:00:00',
            'status' => 'hadir', 'photo_in' => 'attendance/x.jpg',
        ]);

        $leave = PengajuanCuti::create([
            'employee_id' => $e1->id, 'type' => 'sakit',
            'start_date' => '2026-08-12', 'end_date' => '2026-08-12',
            'reason' => 'Demam', 'status' => 'pending',
        ]);

        $this->actingAs($admin)->post("/admin/pengajuan-cuti/{$leave->id}/approve")
            ->assertSessionHas('success');

        $fresh = Absensi::find($existing->id);
        $this->assertSame('hadir', $fresh->status);
        $this->assertSame('2026-08-12 07:30:00', $fresh->time_in->format('Y-m-d H:i:s'));
        $this->assertSame('attendance/x.jpg', $fresh->photo_in);
    }
}
