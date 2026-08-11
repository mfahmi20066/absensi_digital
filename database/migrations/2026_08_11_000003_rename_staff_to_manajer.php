<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')
            ->where('name', 'staff')
            ->update(['name' => 'manajer', 'label' => 'Manajer']);

        DB::table('users')
            ->where('role_id', DB::raw("(SELECT id FROM roles WHERE name = 'manajer')"))
            ->where('email', 'like', 'staff@%')
            ->update(['email' => DB::raw("REPLACE(email, 'staff@', 'manajer@')")]);

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->enum('status', ['pending', 'verified_by_admin', 'approved', 'rejected'])->default('pending')->change();
        });
    }

    public function down(): void
    {
        DB::table('roles')
            ->where('name', 'manajer')
            ->update(['name' => 'staff', 'label' => 'Staff']);

        DB::table('users')
            ->where('role_id', DB::raw("(SELECT id FROM roles WHERE name = 'staff')"))
            ->where('email', 'like', 'manajer@%')
            ->update(['email' => DB::raw("REPLACE(email, 'manajer@', 'staff@')")]);

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->change();
        });
    }
};
