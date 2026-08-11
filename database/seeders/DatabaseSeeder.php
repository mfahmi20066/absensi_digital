<?php

namespace Database\Seeders;

use App\Models\Barcode;
use App\Models\Jabatan;
use App\Models\Karyawan;
use App\Models\Pengguna;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use \Illuminate\Database\Console\Seeds\WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PeranSeeder::class,
            PengaturanSeeder::class,
            JadwalKerjaSeeder::class,
            JabatanSeeder::class,
        ]);

        $admin = Pengguna::firstOrCreate(
            ['email' => 'admin@absensi.sppg.id'],
            [
                'name' => 'Admin SPPG',
                'password' => 'password123',
                'role_id' => 1,
                'status' => 'active',
            ]
        );

        $manajer = Pengguna::firstOrCreate(
            ['email' => 'manajer@absensi.sppg.id'],
            [
                'name' => 'Manajer SPPG',
                'password' => 'password123',
                'role_id' => 2,
                'status' => 'active',
            ]
        );

        $employeePengguna = Pengguna::firstOrCreate(
            ['email' => 'karyawan@absensi.sppg.id'],
            [
                'name' => 'Andi Karyawan',
                'password' => 'password123',
                'role_id' => 3,
                'status' => 'active',
            ]
        );

        $karyawan = Karyawan::firstOrCreate(
            ['user_id' => $employeePengguna->id],
            [
                'work_schedule_id' => 1,
                'nip' => 'SPPG-001',
                'position' => 'Juru Masak',
                'position_id' => Jabatan::where('name', 'Juru Masak')->value('id'),
                'phone' => '081234567890',
                'join_date' => now()->subMonths(6)->toDateString(),
                'status' => 'aktif',
            ]
        );

        if ($karyawan->position_id === null) {
            $karyawan->update(['position_id' => Jabatan::where('name', 'Juru Masak')->value('id')]);
        }

        Barcode::firstOrCreate(
            ['code' => $karyawan->nip],
            [
                'employee_id' => $karyawan->id,
                'valid_from' => now()->toDateString(),
                'valid_until' => now()->addYear()->toDateString(),
                'is_active' => true,
            ]
        );
    }
}
