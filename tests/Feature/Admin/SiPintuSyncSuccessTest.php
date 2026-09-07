<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('sync succeeds and processes large batch of students and teachers quickly', function () {
    config()->set('services.sipintu.api_url', 'http://localhost:8000');
    config()->set('services.sipintu.client_id', 'app_test');
    config()->set('services.sipintu.client_secret', 'sec_test');

    $mockStudents = [];
    for ($i = 1; $i <= 50; $i++) {
        $mockStudents[] = [
            'id' => $i,
            'nis' => 4000 + $i,
            'nama' => "Siswa {$i}",
            'classroom' => $i <= 30 ? ['name' => 'XI PPLG 1'] : null,
            'user' => [
                'id' => 100 + $i,
                'email' => "siswa{$i}@smkn1bangsri.sch.id",
            ],
        ];
    }

    $mockTeachers = [];
    for ($i = 1; $i <= 10; $i++) {
        $mockTeachers[] = [
            'id' => $i,
            'nip' => '19850101' . str_pad($i, 10, '0', STR_PAD_LEFT),
            'nama' => "Guru {$i}, S.Pd",
            'user' => [
                'id' => 200 + $i,
                'email' => "guru{$i}@smkn1bangsri.sch.id",
            ],
        ];
    }

    Http::fake([
        'http://localhost:8000/api/v1/sijuna/students*' => Http::response([
            'status' => 'success',
            'count' => 50,
            'data' => $mockStudents,
        ], 200),
        'http://localhost:8000/api/v1/sijuna/teachers*' => Http::response([
            'status' => 'success',
            'count' => 10,
            'data' => $mockTeachers,
        ], 200),
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
        'identity_number' => 'ADMIN-TEST-SYNC',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)
        ->postJson(route('admin.voters.sync'));

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'count' => 40,
        ]);

    expect($response->json('message'))->toContain('Berhasil menyinkronkan 40 pengguna (30 siswa, 10 guru)');

    // Verify student records with valid classroom are synced
    $activeStudent = User::where('identity_number', '4001')->first();
    expect($activeStudent)->not->toBeNull()
        ->and($activeStudent->role)->toBe('siswa')
        ->and($activeStudent->is_active)->toBeTrue()
        ->and($activeStudent->class_group)->toBe('11')
        ->and(Hash::check('password', $activeStudent->password))->toBeTrue();

    // Verify students with null classroom are skipped and NOT created
    $graduatedStudent = User::where('identity_number', '4050')->first();
    expect($graduatedStudent)->toBeNull();

    // Verify teacher
    $teacher = User::where('identity_number', '198501010000000001')->first();
    expect($teacher)->not->toBeNull()
        ->and($teacher->role)->toBe('guru')
        ->and($teacher->is_active)->toBeTrue();
});

test('subsequent sync preserves existing user password and does not overwrite it', function () {
    config()->set('services.sipintu.api_url', 'http://localhost:8000');
    config()->set('services.sipintu.client_id', 'app_test');
    config()->set('services.sipintu.client_secret', 'sec_test');

    $existingStudent = User::factory()->create([
        'role' => 'siswa',
        'identity_number' => '5001',
        'email' => 'existing@smkn1bangsri.sch.id',
        'password' => Hash::make('custom-secret-password'),
        'is_active' => true,
    ]);

    Http::fake([
        'http://localhost:8000/api/v1/sijuna/students*' => Http::response([
            'status' => 'success',
            'count' => 1,
            'data' => [
                [
                    'nis' => 5001,
                    'nama' => 'Existing Student Renamed',
                    'classroom' => ['name' => 'XII PPLG 2'],
                    'user' => ['email' => 'existing@smkn1bangsri.sch.id'],
                ],
            ],
        ], 200),
        'http://localhost:8000/api/v1/sijuna/teachers*' => Http::response([
            'status' => 'success',
            'count' => 0,
            'data' => [],
        ], 200),
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
        'identity_number' => 'ADMIN-TEST-SYNC-2',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)
        ->postJson(route('admin.voters.sync'));

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'count' => 1,
        ]);

    $refreshed = User::where('identity_number', '5001')->first();
    expect($refreshed->name)->toBe('Existing Student Renamed')
        ->and($refreshed->class_group)->toBe('12')
        ->and(Hash::check('custom-secret-password', $refreshed->password))->toBeTrue();
});
