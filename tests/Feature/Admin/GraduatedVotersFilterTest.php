<?php

use App\Models\User;
use App\Services\SiPintuGatewayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('isGraduated correctly detects graduated student flags', function () {
    $service = app(SiPintuGatewayService::class);

    expect($service->isGraduated(['graduated' => true]))->toBeTrue();
    expect($service->isGraduated(['graduated' => 'true']))->toBeTrue();
    expect($service->isGraduated(['graduated' => 1]))->toBeTrue();
    expect($service->isGraduated(['is_graduated' => true]))->toBeTrue();
    expect($service->isGraduated(['status' => 'graduated']))->toBeTrue();
    expect($service->isGraduated(['status' => 'lulus']))->toBeTrue();
    expect($service->isGraduated(['role' => 'alumni']))->toBeTrue();
    expect($service->isGraduated(['user' => ['graduated' => true]]))->toBeTrue();

    expect($service->isGraduated(['graduated' => false]))->toBeFalse();
    expect($service->isGraduated(['graduated' => 'false']))->toBeFalse();
    expect($service->isGraduated(['graduated' => 0]))->toBeFalse();
    expect($service->isGraduated(['status' => 'active']))->toBeFalse();
});

test('sync skips graduated students and only includes active ungraduated students in voters list', function () {
    config()->set('services.sipintu.api_url', 'https://sipintu.smkn1bangsri.sch.id');
    config()->set('services.sipintu.client_id', 'app_test');
    config()->set('services.sipintu.client_secret', 'sec_test');

    $mockStudents = [
        [
            'id' => 1,
            'nis' => 1001,
            'nama' => 'Siswa Aktif Kelas 10',
            'graduated' => false,
            'classroom' => ['name' => 'X PPLG 1'],
            'user' => ['email' => 'aktif10@smkn1bangsri.sch.id'],
        ],
        [
            'id' => 2,
            'nis' => 1002,
            'nama' => 'Siswa Aktif Kelas 11',
            'graduated' => false,
            'classroom' => ['name' => 'XI TKJ 2'],
            'user' => ['email' => 'aktif11@smkn1bangsri.sch.id'],
        ],
        [
            'id' => 3,
            'nis' => 1003,
            'nama' => 'Siswa Sudah Lulus',
            'graduated' => true,
            'classroom' => ['name' => 'XII TO 1'],
            'user' => ['email' => 'lulus@smkn1bangsri.sch.id'],
        ],
        [
            'id' => 4,
            'nis' => 1004,
            'nama' => 'Siswa Tanpa Kelas',
            'graduated' => false,
            'classroom' => null,
            'user' => ['email' => 'tanpakelas@smkn1bangsri.sch.id'],
        ],
    ];

    $mockTeachers = [
        [
            'id' => 10,
            'nip' => '199001012020011001',
            'nama' => 'Guru Pengajar',
            'user' => ['email' => 'guru@smkn1bangsri.sch.id'],
        ],
    ];

    Http::fake([
        'https://sipintu.smkn1bangsri.sch.id/api/v1/sijuna/students*' => Http::response([
            'status' => 'success',
            'data' => $mockStudents,
        ], 200),
        'https://sipintu.smkn1bangsri.sch.id/api/v1/sijuna/teachers*' => Http::response([
            'status' => 'success',
            'data' => $mockTeachers,
        ], 200),
    ]);

    $service = app(SiPintuGatewayService::class);
    $result = $service->syncAllUsersFromGateway();

    expect($result['success'])->toBeTrue();
    expect($result['students'])->toBe(2); // Only NIS 1001 and 1002
    expect($result['teachers'])->toBe(1);

    // Verify eligible voters list only contains active ungraduated students + teachers
    $eligible = User::eligibleVoters()->get();
    expect($eligible)->toHaveCount(3);
    expect($eligible->pluck('identity_number')->all())->toEqualCanonicalizing([
        '1001',
        '1002',
        '199001012020011001',
    ]);

    // Graduated and null-class students should NOT be in eligible voters
    expect(User::where('identity_number', '1003')->first())->toBeNull();
    expect(User::where('identity_number', '1004')->first())->toBeNull();
});
