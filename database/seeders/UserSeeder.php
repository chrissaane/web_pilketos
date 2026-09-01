<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Admin
        User::updateOrCreate(
            ['identity_number' => 'admin'],
            [
                'role' => 'admin',
                'name' => 'Administrator PILKETOS',
                'email' => 'admin@smkn1bangsri.sch.id',
                'password' => Hash::make('admin123'),
                'is_active' => true,
            ]
        );

        // 2. Akun Guru
        User::updateOrCreate(
            ['identity_number' => '198501012010011001'],
            [
                'role' => 'guru',
                'name' => 'Guru Pembina OSIS, S.Pd',
                'email' => 'guru@smkn1bangsri.sch.id',
                'password' => Hash::make('1985-01-01'),
                'birth_date' => '1985-01-01',
                'phone' => '081234567890',
                'is_active' => true,
            ]
        );

        // 3. Akun Siswa
        User::updateOrCreate(
            ['identity_number' => '20261001'],
            [
                'role' => 'siswa',
                'name' => 'Siswa SMKN 1 Bangsri',
                'email' => 'siswa@smkn1bangsri.sch.id',
                'password' => Hash::make('2008-05-12'),
                'class_group' => 'XI',
                'major' => 'PPLG 1',
                'birth_date' => '2008-05-12',
                'phone' => '089876543210',
                'is_active' => true,
            ]
        );
    }
}