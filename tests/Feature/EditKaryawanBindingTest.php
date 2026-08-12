<?php

namespace Tests\Feature;

use App\Models\Jabatan;
use App\Models\JadwalKerja;
use App\Models\Karyawan;
use App\Models\Peran;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditKaryawanBindingTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_edit_karyawan_ter_load(): void
    {
        Peran::create(['name' => 'admin', 'label' => 'Administrator']);
        $admin = Pengguna::factory()->create(['role_id' => Peran::where('name', 'admin')->first()->id]);

        $karyawan = Pengguna::factory()->create();
        $jabatan = Jabatan::create(['name' => 'Staff']);
        $jadwal = JadwalKerja::create(['name' => 'Shift Pagi', 'time_in' => '08:00', 'time_out' => '17:00']);
        $emp = Karyawan::create([
            'user_id' => $karyawan->id,
            'work_schedule_id' => $jadwal->id,
            'nip' => 'NIP001',
            'position' => $jabatan->name,
            'position_id' => $jabatan->id,
            'phone' => '08123',
            'join_date' => '2026-01-01',
            'status' => 'aktif',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.karyawan.edit', $emp))
            ->assertOk()
            ->assertSee('/admin/karyawan/'.$emp->id, false);
    }

    public function test_update_karyawan_berhasil(): void
    {
        Peran::create(['name' => 'admin', 'label' => 'Administrator']);
        $admin = Pengguna::factory()->create(['role_id' => Peran::where('name', 'admin')->first()->id]);

        $karyawan = Pengguna::factory()->create();
        $jabatan = Jabatan::create(['name' => 'Staff']);
        $jadwal = JadwalKerja::create(['name' => 'Shift Pagi', 'time_in' => '08:00', 'time_out' => '17:00']);
        $emp = Karyawan::create([
            'user_id' => $karyawan->id,
            'work_schedule_id' => $jadwal->id,
            'nip' => 'NIP001',
            'position' => $jabatan->name,
            'position_id' => $jabatan->id,
            'join_date' => '2026-01-01',
            'status' => 'aktif',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.karyawan.update', $emp), [
                'name' => 'Nama Baru',
                'email' => $karyawan->email,
                'nip' => 'NIP001',
                'position_id' => $jabatan->id,
                'phone' => '08123',
                'work_schedule_id' => $jadwal->id,
                'join_date' => '2026-01-01',
                'status' => 'aktif',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('Nama Baru', $karyawan->refresh()->name);
    }

    public function test_manajer_tidak_bisa_tambah_edit_hapus_karyawan(): void
    {
        Peran::create(['name' => 'admin', 'label' => 'Administrator']);
        Peran::create(['name' => 'manajer', 'label' => 'Manajer']);
        $manajer = Pengguna::factory()->create(['role_id' => Peran::where('name', 'manajer')->first()->id]);

        // Route manajer untuk tambah/edit/hapus tidak terdaftar
        $this->assertNull(\Illuminate\Support\Facades\Route::getRoutes()->getByName('manajer.karyawan.create'));
        $this->assertNull(\Illuminate\Support\Facades\Route::getRoutes()->getByName('manajer.karyawan.store'));
        $this->assertNull(\Illuminate\Support\Facades\Route::getRoutes()->getByName('manajer.karyawan.edit'));
        $this->assertNull(\Illuminate\Support\Facades\Route::getRoutes()->getByName('manajer.karyawan.update'));
        $this->assertNull(\Illuminate\Support\Facades\Route::getRoutes()->getByName('manajer.karyawan.destroy'));

        // Halaman index masih bisa diakses manajer (hanya lihat data)
        $this->actingAs($manajer)
            ->get('/manajer/karyawan')
            ->assertOk();
    }
}