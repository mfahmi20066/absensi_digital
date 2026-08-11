<?php

namespace Tests\Feature;

use App\Models\Jabatan;
use App\Models\Karyawan;
use App\Models\PengajuanCuti;
use App\Models\Pengguna;
use App\Models\Peran;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManajerRoleTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $manajer;

    private Karyawan $karyawan;

    protected function setUp(): void
    {
        parent::setUp();

        $roleManajer = Peran::firstOrCreate(['name' => 'manajer'], ['label' => 'Manajer']);
        $roleKaryawan = Peran::firstOrCreate(['name' => 'karyawan'], ['label' => 'Karyawan']);

        $this->manajer = Pengguna::factory()->create(['role_id' => $roleManajer->id, 'status' => 'active']);

        $userKaryawan = Pengguna::factory()->create(['role_id' => $roleKaryawan->id, 'status' => 'active']);

        $jabatan = Jabatan::create(['name' => 'Juru Masak']);

        $this->karyawan = Karyawan::create([
            'user_id' => $userKaryawan->id,
            'nip' => 'SPPG-TEST-01',
            'position' => $jabatan->name,
            'position_id' => $jabatan->id,
            'join_date' => now()->toDateString(),
            'status' => 'aktif',
        ]);
    }

    private function buatPengajuan(string $status): PengajuanCuti
    {
        return PengajuanCuti::create([
            'employee_id' => $this->karyawan->id,
            'type' => 'izin',
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'reason' => 'Keperluan keluarga',
            'status' => $status,
        ]);
    }

    public function test_manajer_bisa_akses_dan_tambah_karyawan(): void
    {
        $this->actingAs($this->manajer)
            ->get('/manajer/karyawan')
            ->assertOk();

        $this->actingAs($this->manajer)
            ->get('/manajer/karyawan/create')
            ->assertOk();

        $userBaru = Pengguna::factory()->create();

        $this->actingAs($this->manajer)
            ->post('/manajer/karyawan', [
                'user_id' => $userBaru->id,
                'position_id' => Jabatan::first()->id,
                'join_date' => now()->toDateString(),
                'status' => 'aktif',
            ])
            ->assertRedirect(route('manajer.karyawan.index'));

        $this->assertDatabaseHas('employees', ['user_id' => $userBaru->id]);
    }

    public function test_manajer_tidak_bisa_akses_halaman_admin(): void
    {
        $this->actingAs($this->manajer)
            ->get('/admin/pengaturan')
            ->assertForbidden();

        $this->actingAs($this->manajer)
            ->get('/admin/barcode')
            ->assertForbidden();

        $this->actingAs($this->manajer)
            ->get('/admin/log-audit')
            ->assertForbidden();
    }

    public function test_manajer_tidak_bisa_approve_cuti_yang_masih_pending(): void
    {
        $pengajuan = $this->buatPengajuan('pending');

        $this->actingAs($this->manajer)
            ->post("/manajer/cuti/{$pengajuan->id}/approve")
            ->assertStatus(422);

        $this->assertDatabaseHas('leave_requests', ['id' => $pengajuan->id, 'status' => 'pending']);
    }

    public function test_manajer_bisa_approve_final_cuti_yang_sudah_diverifikasi_admin(): void
    {
        $pengajuan = $this->buatPengajuan('verified_by_admin');

        $this->actingAs($this->manajer)
            ->post("/manajer/cuti/{$pengajuan->id}/approve")
            ->assertSessionHas('success');

        $this->assertDatabaseHas('leave_requests', ['id' => $pengajuan->id, 'status' => 'approved']);
    }

    public function test_manajer_bisa_tolak_cuti_yang_sudah_diverifikasi_admin(): void
    {
        $pengajuan = $this->buatPengajuan('verified_by_admin');

        $this->actingAs($this->manajer)
            ->post("/manajer/cuti/{$pengajuan->id}/reject", ['rejection_note' => 'Jadwal padat'])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('leave_requests', ['id' => $pengajuan->id, 'status' => 'rejected']);
    }

    public function test_admin_dapat_memverifikasi_cuti(): void
    {
        $pengajuan = $this->buatPengajuan('pending');

        $admin = Pengguna::factory()->create([
            'role_id' => Peran::firstOrCreate(['name' => 'admin'], ['label' => 'Administrator'])->id,
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->post("/admin/pengajuan-cuti/{$pengajuan->id}/verify")
            ->assertSessionHas('success');

        $this->assertDatabaseHas('leave_requests', ['id' => $pengajuan->id, 'status' => 'verified_by_admin']);
    }

    public function test_admin_tetap_bisa_approve_langsung_dari_pending(): void
    {
        $pengajuan = $this->buatPengajuan('pending');

        $admin = Pengguna::factory()->create([
            'role_id' => Peran::firstOrCreate(['name' => 'admin'], ['label' => 'Administrator'])->id,
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->post("/admin/pengajuan-cuti/{$pengajuan->id}/approve")
            ->assertSessionHas('success');

        $this->assertDatabaseHas('leave_requests', ['id' => $pengajuan->id, 'status' => 'approved']);
    }

    public function test_seeder_menyediakan_role_manajer_bukan_staff(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('roles', ['name' => 'manajer']);
        $this->assertDatabaseMissing('roles', ['name' => 'staff']);
        $this->assertDatabaseHas('users', ['email' => 'manajer@absensi.sppg.id']);
    }
}
