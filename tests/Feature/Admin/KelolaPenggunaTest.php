<?php

namespace Tests\Feature\Admin;

use App\Models\Pengguna;
use App\Models\Peran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KelolaPenggunaTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Pengguna
    {
        return Pengguna::factory()->create([
            'role_id' => Peran::firstOrCreate(['name' => 'admin'], ['label' => 'Administrator'])->id,
        ]);
    }

    private function karyawan(): Pengguna
    {
        return Pengguna::factory()->create([
            'role_id' => Peran::firstOrCreate(['name' => 'karyawan'], ['label' => 'Karyawan'])->id,
        ]);
    }

    public function test_index_menampilkan_daftar_pengguna(): void
    {
        $admin = $this->admin();
        $user = $this->karyawan();

        $response = $this->actingAs($admin)->get('/admin/pengguna');

        $response->assertOk()
            ->assertSee($user->name)
            ->assertSee($user->email);
    }

    public function test_manajer_tidak_bisa_mengakses_kelola_pengguna(): void
    {
        $manajer = Pengguna::factory()->create([
            'role_id' => Peran::firstOrCreate(['name' => 'manajer'], ['label' => 'Manajer'])->id,
        ]);

        $this->actingAs($manajer)->get('/admin/pengguna')->assertForbidden();
    }

    public function test_admin_bisa_membuat_pengguna_baru(): void
    {
        $admin = $this->admin();
        $role = Peran::firstOrCreate(['name' => 'karyawan'], ['label' => 'Karyawan']);

        $response = $this->actingAs($admin)->post('/admin/pengguna', [
            'name' => 'Budi Baru',
            'email' => 'budi@example.com',
            'password' => 'rahasia123',
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('admin.pengguna.index'));

        $this->assertDatabaseHas('users', [
            'name' => 'Budi Baru',
            'email' => 'budi@example.com',
            'role_id' => $role->id,
        ]);
    }

    public function test_admin_bisa_mengedit_pengguna(): void
    {
        $admin = $this->admin();
        $user = $this->karyawan();

        $this->actingAs($admin)->put("/admin/pengguna/{$user->id}", [
            'name' => 'Nama Baru',
            'email' => 'baru@example.com',
            'role_id' => $user->role_id,
            'status' => 'inactive',
        ])->assertRedirect(route('admin.pengguna.index'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nama Baru',
            'email' => 'baru@example.com',
            'status' => 'inactive',
        ]);
    }

    public function test_admin_tidak_bisa_mengubah_peran_atau_status_akun_sendiri(): void
    {
        $admin = $this->admin();
        $roleAdmin = $admin->role_id;
        $roleManajer = Peran::firstOrCreate(['name' => 'manajer'], ['label' => 'Manajer'])->id;

        $this->actingAs($admin)->put("/admin/pengguna/{$admin->id}", [
            'name' => $admin->name,
            'email' => $admin->email,
            'role_id' => $roleManajer,
            'status' => 'inactive',
        ])->assertSessionHasErrors(['role_id', 'status']);

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'role_id' => $roleAdmin,
            'status' => 'active',
        ]);
    }

    public function test_admin_tidak_bisa_menghapus_akun_sendiri(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->delete("/admin/pengguna/{$admin->id}")
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_bisa_menghapus_pengguna(): void
    {
        $admin = $this->admin();
        $user = $this->karyawan();

        $this->actingAs($admin)->delete("/admin/pengguna/{$user->id}")
            ->assertRedirect(route('admin.pengguna.index'));

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_bisa_menonaktifkan_dan_mengaktifkan_akun(): void
    {
        $admin = $this->admin();
        $user = $this->karyawan();

        $this->actingAs($admin)->post("/admin/pengguna/{$user->id}/toggle-status");
        $this->assertDatabaseHas('users', ['id' => $user->id, 'status' => 'inactive']);

        $this->actingAs($admin)->post("/admin/pengguna/{$user->id}/toggle-status");
        $this->assertDatabaseHas('users', ['id' => $user->id, 'status' => 'active']);
    }

    public function test_admin_bisa_reset_password_pengguna(): void
    {
        $admin = $this->admin();
        $user = $this->karyawan();

        $this->actingAs($admin)->post("/admin/pengguna/{$user->id}/reset-password");

        $this->assertTrue(Hash::check('password123', $user->fresh()->password));
    }
}
