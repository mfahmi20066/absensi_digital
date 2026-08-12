<?php

namespace Tests\Feature;

use App\Models\Jabatan;
use App\Models\JadwalKerja;
use App\Models\Karyawan;
use App\Models\Peran;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HalamanErrorTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $admin;

    private Karyawan $karyawan;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Peran::firstOrCreate(['name' => 'admin'], ['label' => 'Administrator']);
        $roleKaryawan = Peran::firstOrCreate(['name' => 'karyawan'], ['label' => 'Karyawan']);

        $this->admin = Pengguna::factory()->create(['role_id' => $roleAdmin->id, 'status' => 'active']);
        $user = Pengguna::factory()->create(['role_id' => $roleKaryawan->id, 'status' => 'active']);

        $jabatan = Jabatan::create(['name' => 'Staff']);
        $jadwal = JadwalKerja::create(['name' => 'Shift Pagi', 'time_in' => '08:00', 'time_out' => '17:00']);

        $this->karyawan = Karyawan::create([
            'user_id' => $user->id,
            'work_schedule_id' => $jadwal->id,
            'nip' => 'SPPG-ERR-01',
            'position' => $jabatan->name,
            'position_id' => $jabatan->id,
            'join_date' => today()->toDateString(),
            'status' => 'aktif',
        ]);
    }

    public function test_admin_karyawan_show_tidak_lagi_memicu_500(): void
    {
        $this->actingAs($this->admin)
            ->get("/admin/karyawan/{$this->karyawan->id}")
            ->assertRedirect(route('admin.karyawan.edit', $this->karyawan));
    }

    public function test_cetak_barcode_tanpa_barcode_aktif_redirect_dengan_error(): void
    {
        $this->actingAs($this->admin)
            ->get("/admin/barcode/{$this->karyawan->id}/print")
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_download_barcode_tanpa_barcode_aktif_redirect_dengan_error(): void
    {
        $this->actingAs($this->admin)
            ->get("/admin/barcode/{$this->karyawan->id}/download")
            ->assertRedirect()
            ->assertSessionHas('error');
    }
}