<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Jabatan;
use App\Models\Karyawan;
use App\Models\KoreksiAbsensi;
use App\Models\Lembur;
use App\Models\PengajuanCuti;
use App\Models\Pengguna;
use App\Models\Peran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FiturBaruTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $karyawanUser;

    private Karyawan $karyawan;

    private Pengguna $admin;

    private Pengguna $manajer;

    protected function setUp(): void
    {
        parent::setUp();

        $roleKaryawan = Peran::firstOrCreate(['name' => 'karyawan'], ['label' => 'Karyawan']);
        $roleAdmin = Peran::firstOrCreate(['name' => 'admin'], ['label' => 'Administrator']);
        $roleManajer = Peran::firstOrCreate(['name' => 'manajer'], ['label' => 'Manajer']);

        $this->karyawanUser = Pengguna::factory()->create(['role_id' => $roleKaryawan->id, 'status' => 'active']);
        $this->admin = Pengguna::factory()->create(['role_id' => $roleAdmin->id, 'status' => 'active']);
        $this->manajer = Pengguna::factory()->create(['role_id' => $roleManajer->id, 'status' => 'active']);

        $jabatan = Jabatan::create(['name' => 'Juru Masak']);

        $this->karyawan = Karyawan::create([
            'user_id' => $this->karyawanUser->id,
            'nip' => 'SPPG-NEW-01',
            'position' => $jabatan->name,
            'position_id' => $jabatan->id,
            'join_date' => now()->toDateString(),
            'status' => 'aktif',
        ]);
    }

    // ===== LEMBUR =====

    public function test_karyawan_bisa_mengajukan_lembur(): void
    {
        $this->actingAs($this->karyawanUser)
            ->post('/karyawan/lembur', [
                'date' => today()->toDateString(),
                'start_time' => '17:00',
                'end_time' => '20:30',
                'reason' => 'Penyelesaian laporan stok',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('overtime_requests', [
            'employee_id' => $this->karyawan->id,
            'duration_minutes' => 210,
        ]);
    }

    public function test_karyawan_tidak_bisa_mengajukan_lembur_duplikat(): void
    {
        Lembur::create([
            'employee_id' => $this->karyawan->id,
            'date' => today()->toDateString(),
            'start_time' => '17:00',
            'end_time' => '18:00',
            'duration_minutes' => 60,
            'reason' => 'Tugas tambahan',
        ]);

        $this->actingAs($this->karyawanUser)
            ->post('/karyawan/lembur', [
                'date' => today()->toDateString(),
                'start_time' => '18:00',
                'end_time' => '19:00',
                'reason' => 'Tugas lain',
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('overtime_requests', 1);
    }

    public function test_alur_persetujuan_lembur_admin_lalu_manajer(): void
    {
        $lembur = Lembur::create([
            'employee_id' => $this->karyawan->id,
            'date' => today()->toDateString(),
            'start_time' => '17:00',
            'end_time' => '18:00',
            'duration_minutes' => 60,
            'reason' => 'Tugas tambahan',
        ]);

        $this->actingAs($this->manajer)
            ->post("/manajer/lembur/{$lembur->id}/approve")
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->actingAs($this->admin)
            ->post("/admin/lembur/{$lembur->id}/verify")
            ->assertSessionHas('success');

        $this->assertDatabaseHas('overtime_requests', ['id' => $lembur->id, 'status' => 'verified_by_admin']);

        $this->actingAs($this->manajer)
            ->post("/manajer/lembur/{$lembur->id}/approve")
            ->assertSessionHas('success');

        $this->assertDatabaseHas('overtime_requests', ['id' => $lembur->id, 'status' => 'approved']);
    }

    public function test_manajer_bisa_menolak_lembur_yang_sudah_diverifikasi(): void
    {
        $lembur = Lembur::create([
            'employee_id' => $this->karyawan->id,
            'date' => today()->toDateString(),
            'start_time' => '17:00',
            'end_time' => '18:00',
            'duration_minutes' => 60,
            'reason' => 'Tugas tambahan',
            'status' => 'verified_by_admin',
        ]);

        $this->actingAs($this->manajer)
            ->post("/manajer/lembur/{$lembur->id}/reject", ['rejection_note' => 'Tidak ada izin lembur malam ini'])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('overtime_requests', ['id' => $lembur->id, 'status' => 'rejected']);
    }

    // ===== KOREKSI ABSENSI =====

    public function test_karyawan_bisa_mengajukan_koreksi_absensi(): void
    {
        $this->actingAs($this->karyawanUser)
            ->post('/karyawan/koreksi', [
                'date' => today()->subDay()->toDateString(),
                'time_in' => '08:05',
                'time_out' => '16:45',
                'reason' => 'Lupa absen karena rapat',
            ])
            ->assertSessionHas('success');

        $this->assertTrue(
            KoreksiAbsensi::where('employee_id', $this->karyawan->id)
                ->whereDate('date', today()->subDay())
                ->where('time_in', '08:05:00')
                ->exists()
        );
    }

    public function test_karyawan_tidak_bisa_mengajukan_koreksi_untuk_hari_ini(): void
    {
        $this->actingAs($this->karyawanUser)
            ->post('/karyawan/koreksi', [
                'date' => today()->toDateString(),
                'time_in' => '08:05',
                'reason' => 'Coba-coba',
            ])
            ->assertSessionHasErrors('date');

        $this->assertDatabaseCount('attendance_corrections', 0);
    }

    public function test_admin_approve_koreksi_menerapkan_perubahan_ke_absensi(): void
    {
        $koreksi = KoreksiAbsensi::create([
            'employee_id' => $this->karyawan->id,
            'date' => today()->subDay()->toDateString(),
            'time_in' => '08:05:00',
            'reason' => 'Lupa absen masuk',
        ]);

        $this->actingAs($this->admin)
            ->post("/admin/koreksi/{$koreksi->id}/approve")
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_corrections', ['id' => $koreksi->id, 'status' => 'approved']);

        $this->assertTrue(
            Absensi::where('employee_id', $this->karyawan->id)
                ->whereDate('date', today()->subDay())
                ->where('status', 'hadir')
                ->exists()
        );
    }

    // ===== KUOTA CUTI =====

    public function test_pengajuan_cuti_melebihi_sisa_kuota_ditolak(): void
    {
        $this->karyawan->update(['leave_balance' => 2, 'leave_balance_year' => now()->year]);

        $this->actingAs($this->karyawanUser)
            ->post('/karyawan/cuti', [
                'type' => 'cuti',
                'start_date' => today()->toDateString(),
                'end_date' => today()->addDays(4)->toDateString(),
                'reason' => 'Liburan keluarga',
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('leave_requests', 0);
    }

    public function test_cuti_disetujui_memotong_sisa_kuota(): void
    {
        $this->karyawan->update(['leave_balance' => 12, 'leave_balance_year' => now()->year]);

        $pengajuan = PengajuanCuti::create([
            'employee_id' => $this->karyawan->id,
            'type' => 'cuti',
            'start_date' => today()->toDateString(),
            'end_date' => today()->addDays(2)->toDateString(),
            'reason' => 'Cuti tahunan',
        ]);

        $this->actingAs($this->admin)
            ->post("/admin/pengajuan-cuti/{$pengajuan->id}/approve")
            ->assertSessionHas('success');

        $this->assertDatabaseHas('employees', [
            'id' => $this->karyawan->id,
            'leave_balance' => 9,
        ]);
    }

    public function test_izin_tidak_memotong_kuota_cuti(): void
    {
        $this->karyawan->update(['leave_balance' => 12, 'leave_balance_year' => now()->year]);

        $pengajuan = PengajuanCuti::create([
            'employee_id' => $this->karyawan->id,
            'type' => 'izin',
            'start_date' => today()->toDateString(),
            'end_date' => today()->toDateString(),
            'reason' => 'Keperluan pribadi',
        ]);

        $this->actingAs($this->admin)
            ->post("/admin/pengajuan-cuti/{$pengajuan->id}/approve")
            ->assertSessionHas('success');

        $this->assertDatabaseHas('employees', [
            'id' => $this->karyawan->id,
            'leave_balance' => 12,
        ]);
    }

    public function test_kuota_cuti_direset_saat_pergantian_tahun(): void
    {
        $this->karyawan->update(['leave_balance' => 3, 'leave_balance_year' => now()->year - 1]);

        $this->assertSame(12, $this->karyawan->sisa_cuti);

        $this->karyawan->refresh();

        $this->assertSame((int) now()->year, (int) $this->karyawan->leave_balance_year);
        $this->assertSame(12, (int) $this->karyawan->leave_balance);
    }

    public function test_halaman_karyawan_menampilkan_fitur_baru(): void
    {
        $this->actingAs($this->karyawanUser)
            ->get('/karyawan/lembur')
            ->assertOk();

        $this->actingAs($this->karyawanUser)
            ->get('/karyawan/koreksi')
            ->assertOk();
    }
}
