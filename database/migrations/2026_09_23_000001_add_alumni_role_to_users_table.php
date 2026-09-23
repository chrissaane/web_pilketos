<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'guru', 'karyawan', 'siswa', 'alumni') NOT NULL DEFAULT 'siswa'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::table('users')->where('role', 'alumni')->update(['role' => 'siswa', 'is_active' => false]);
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'guru', 'karyawan', 'siswa') NOT NULL DEFAULT 'siswa'");
        }
    }
};
