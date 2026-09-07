<?php

use App\Models\Election;
use App\Models\User;
use App\Models\VotingToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('admin can open voter index even when voting tokens table is missing', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'identity_number' => 'ADMIN-002',
        'is_active' => true,
    ]);

    Schema::dropIfExists('voting_tokens');

    $this->actingAs($admin);

    $response = $this->get(route('admin.voters.index'));

    $response->assertOk();
});

test('admin can see all voter records in the default semua filter', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'identity_number' => 'ADMIN-001',
        'is_active' => true,
    ]);

    User::factory()->create([
        'role' => 'siswa',
        'identity_number' => '2026001',
        'name' => 'Siswa Aktif',
        'class_group' => '12',
        'is_active' => true,
    ]);

    User::factory()->create([
        'role' => 'siswa',
        'identity_number' => '2026002',
        'name' => 'Siswa Nonaktif',
        'class_group' => '11',
        'is_active' => false,
    ]);

    $this->actingAs($admin);

    $response = $this->get(route('admin.voters.index'));

    $response->assertOk()
        ->assertSeeText('Siswa Aktif')
        ->assertSeeText('Siswa Nonaktif')
        ->assertSeeText('12')
        ->assertSeeText('11');
});

test('sipintu sync keeps class data when the payload uses an alternate class field name', function () {
    $gateway = app(\App\Services\SiPintuGatewayService::class);

    $student = $gateway->syncUserFromGateway([
        'user' => [
            'identity_number' => '2026009',
            'name' => 'Siswa Kelas XII',
            'role' => 'siswa',
            'class_name' => 'XII',
            'email' => 'kelas12@example.com',
        ],
    ], 'secret123');

    $groupStudent = $gateway->syncUserFromGateway([
        'user' => [
            'identity_number' => '2026010',
            'name' => 'Siswa dari Group',
            'role' => 'siswa',
            'group' => 'Kelas XI',
            'email' => 'kelas11-group@example.com',
        ],
    ], 'secret123');

    $combined = $gateway->syncUserFromGateway([
        'user' => [
            'identity_number' => '2026011',
            'name' => 'Helmy Yunan Nasution',
            'role' => 'siswa',
            'class_group' => 'Kelas XII PPLG 2',
            'email' => 'helmy@example.com',
        ],
    ], 'secret123');

    $teacher = $gateway->syncUserFromGateway([
        'user' => [
            'identity_number' => '1987098765',
            'name' => 'Guru SiPintu',
            'email' => 'guru@example.com',
        ],
    ], 'secret123');

    expect($student)->not->toBeNull()
        ->and($student->class_group)->toBe('12')
        ->and($groupStudent)->not->toBeNull()
        ->and($groupStudent->class_group)->toBe('11')
        ->and($combined)->not->toBeNull()
        ->and($combined->class_group)->toBe('12')
        ->and($combined->major)->toBe('PPLG 2')
        ->and($teacher)->not->toBeNull()
        ->and($teacher->role)->toBe('guru');
});

test('sipintu sync ignores placeholder values like N/A when populating class and major', function () {
    $gateway = app(\App\Services\SiPintuGatewayService::class);

    $student = $gateway->syncUserFromGateway([
        'user' => [
            'identity_number' => '2026012',
            'name' => 'Siswa Placeholder',
            'role' => 'siswa',
            'class_group' => 'N/A',
            'major' => 'N/A',
            'email' => 'placeholder@example.com',
        ],
    ], 'secret123');

    expect($student)->not->toBeNull()
        ->and($student->class_group)->toBeNull()
        ->and($student->major)->toBeNull();
});

test('gateway flattens nested student and teacher payloads for full sync coverage', function () {
    $gateway = app(\App\Services\SiPintuGatewayService::class);
    $method = new ReflectionMethod($gateway, 'extractCollectionFromResponse');
    $method->setAccessible(true);

    $payload = [
        'data' => [
            'students' => [
                ['identity_number' => '2026001', 'name' => 'Siswa 1', 'role' => 'siswa'],
                ['identity_number' => '2026002', 'name' => 'Siswa 2', 'role' => 'siswa'],
            ],
            'teachers' => [
                ['identity_number' => '1987001', 'name' => 'Guru 1', 'role' => 'guru'],
            ],
        ],
    ];

    $result = $method->invoke($gateway, $payload, 'users');

    expect($result)->toHaveCount(3)
        ->and($result[0]['identity_number'])->toBe('2026001')
        ->and($result[2]['identity_number'])->toBe('1987001');
});

test('gateway accepts alternative siPintu identity keys when building the full user list', function () {
    $gateway = app(\App\Services\SiPintuGatewayService::class);
    $method = new ReflectionMethod($gateway, 'extractCollectionFromResponse');
    $method->setAccessible(true);

    $payload = [
        'data' => [
            'students' => [
                ['no_induk' => '2026001', 'name' => 'Siswa Alternatif', 'role' => 'siswa'],
            ],
            'teachers' => [
                ['nip' => '1987001', 'name' => 'Guru Alternatif', 'role' => 'guru'],
            ],
        ],
    ];

    $result = $method->invoke($gateway, $payload, 'users');

    expect($result)->toHaveCount(2)
        ->and($result[0]['no_induk'])->toBe('2026001')
        ->and($result[1]['nip'])->toBe('1987001');
});

test('gateway imports all paginated siPintu students and teachers', function () {
    config()->set('services.sipintu.api_url', 'https://sipintu.smkn1bangsri.sch.id');
    config()->set('services.sipintu.client_id', 'app_test');
    config()->set('services.sipintu.client_secret', 'secret_test');

    Http::fake([
        'https://sipintu.smkn1bangsri.sch.id/api/v1/sijuna/students?page=1&per_page=200' => Http::response([
            'data' => [
                ['nis' => '1001', 'name' => 'Siswa 1', 'role' => 'siswa', 'classroom' => ['name' => 'X PPLG 1']],
            ],
            'current_page' => 1,
            'last_page' => 2,
            'total' => 3,
        ], 200),
        'https://sipintu.smkn1bangsri.sch.id/api/v1/sijuna/students?page=2&per_page=200' => Http::response([
            'data' => [
                ['nis' => '1002', 'name' => 'Siswa 2', 'role' => 'siswa', 'classroom' => ['name' => 'X PPLG 1']],
                ['nis' => '1003', 'name' => 'Siswa 3', 'role' => 'siswa', 'classroom' => ['name' => 'X PPLG 2']],
            ],
            'current_page' => 2,
            'last_page' => 2,
            'total' => 3,
        ], 200),
        'https://sipintu.smkn1bangsri.sch.id/api/v1/sijuna/teachers?page=1&per_page=200' => Http::response([
            'data' => [
                ['nip' => '2001', 'name' => 'Guru 1', 'role' => 'guru'],
                ['nip' => '2002', 'name' => 'Guru 2', 'role' => 'guru'],
            ],
            'current_page' => 1,
            'last_page' => 1,
            'total' => 2,
        ], 200),
    ]);

    $gateway = app(\App\Services\SiPintuGatewayService::class);
    $result = $gateway->syncAllUsersFromGateway();

    expect($result['total'])->toBe(5)
        ->and($result['students'])->toBe(3)
        ->and($result['teachers'])->toBe(2);
});

test('admin can download voter credentials excel for the selected filter', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'identity_number' => 'ADMIN-001',
        'is_active' => true,
    ]);

    $student = User::factory()->create([
        'role' => 'siswa',
        'identity_number' => '2026001',
        'name' => 'Siswa Test',
        'is_active' => true,
        'birth_date' => '2008-01-01',
    ]);

    $teacher = User::factory()->create([
        'role' => 'guru',
        'identity_number' => '1987001',
        'name' => 'Guru Test',
        'is_active' => true,
        'birth_date' => '1975-01-01',
    ]);

    $election = Election::create([
        'title' => 'Pemilihan Uji Coba',
        'year' => '2026',
        'description' => 'Uji coba export',
        'start_time' => now()->subDay(),
        'end_time' => now()->addDay(),
        'status' => Election::STATUS_ACTIVE,
    ]);

    VotingToken::create([
        'user_id' => $student->id,
        'election_id' => $election->id,
        'token' => 'STUDENT-TOKEN-001',
    ]);

    VotingToken::create([
        'user_id' => $teacher->id,
        'election_id' => $election->id,
        'token' => 'TEACHER-TOKEN-001',
    ]);

    $this->actingAs($admin);

    $response = $this->get(route('admin.voters.export', ['filter' => 'siswa']));

    $response->assertOk()
        ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});
