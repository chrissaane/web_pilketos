<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminPassword = env('ADMIN_PASSWORD');

        if (blank($adminPassword)) {
            throw new \RuntimeException('ADMIN_PASSWORD harus diatur sebelum menjalankan UserSeeder.');
        }

        // 1. Akun Admin
        User::updateOrCreate(
            ['identity_number' => 'admin'],
            [
                'role' => 'admin',
                'name' => 'Administrator PILKETOS',
                'email' => 'admin@smkn1bangsri.sch.id',
                'password' => Hash::make($adminPassword),
                'is_active' => true,
            ]
        );

    }
}
