<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'login_password')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('login_password')->nullable()->after('password');
            });
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'guru', 'karyawan', 'siswa') NOT NULL DEFAULT 'siswa'");
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'login_password')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('login_password');
            });
        }

        if (DB::getDriverName() === 'mysql') {
            DB::table('users')->where('role', 'karyawan')->update(['role' => 'guru']);
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'guru', 'siswa') NOT NULL DEFAULT 'siswa'");
        }
    }
};
