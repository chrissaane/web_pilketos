<?php

use App\Exports\VoterCredentialsExport;
use App\Imports\VoterImport;
use App\Models\Election;
use App\Models\User;
use App\Services\SiPintuGatewayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

uses(RefreshDatabase::class);

test('import parser accepts simple excel columns without birth date', function () {
    $import = new VoterImport(true);

    $import->collection(collect([
        [
            'Nama' => 'Budi Santoso',
            'Kelas' => 'XII PPLG 2',
            'NIS / NIP' => '20261234',
            'Email' => 'budi@example.com',
        ],
        [
            'Nama' => 'Ibu Sari',
            'Kelas' => '',
            'NIS / NIP' => '19870001',
            'Email' => 'sari@example.com',
        ],
    ]));

    expect($import->previewRows)->toHaveCount(2)
        ->and($import->previewRows[0]['identity_number'])->toBe('20261234')
        ->and($import->previewRows[0]['role'])->toBe('siswa')
        ->and($import->previewRows[0]['class_group'])->toBe('12')
        ->and($import->previewRows[0]['major'])->toBe('PPLG 2')
        ->and($import->previewRows[1]['role'])->toBe('guru')
        ->and($import->previewRows[1]['identity_number'])->toBe('19870001');
});

test('direct import creates voter records without preview step', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'identity_number' => 'ADMIN-IMPORT',
        'is_active' => true,
    ]);

    $this->actingAs($admin);

    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->fromArray([
        ['Nama', 'Kelas', 'NIS / NIP', 'Email'],
        ['Budi Santoso', 'XII PPLG 2', '20261234', 'budi@example.com'],
        ['Ibu Sari', '', '19870001', 'sari@example.com'],
    ], null, 'A1');

    $writer = new Xlsx($spreadsheet);
    $tempFile = tempnam(sys_get_temp_dir(), 'voter_import_');
    $xlsxPath = $tempFile.'.xlsx';
    unlink($tempFile);
    $writer->save($xlsxPath);

    $file = new UploadedFile(
        $xlsxPath,
        'import_voters.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true
    );

    $response = $this->post(route('admin.import.store'), ['file' => $file, 'direct_import' => '1']);

    $response->assertRedirect(route('admin.voters.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('users', ['identity_number' => '20261234', 'role' => 'siswa']);
    $this->assertDatabaseHas('users', ['identity_number' => '19870001', 'role' => 'guru']);

    @unlink($xlsxPath);
});

test('admin can delete a voter by identity number', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'identity_number' => 'ADMIN-DELETE',
        'is_active' => true,
    ]);

    $voter = User::factory()->create([
        'role' => 'siswa',
        'identity_number' => '2026999',
        'name' => 'Voter Akan Dihapus',
        'class_group' => '12',
        'is_active' => true,
    ]);

    $this->actingAs($admin);

    $response = $this->delete(route('admin.voters.destroy', $voter->identity_number));

    $response->assertRedirect(route('admin.voters.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('users', ['id' => $voter->id]);
});

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

test('admin sees only active voter records in the default semua filter', function () {
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
        ->assertDontSeeText('Siswa Nonaktif');
});

test('admin does not see students without a class in voter data', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'identity_number' => 'ADMIN-003',
        'is_active' => true,
    ]);

    User::factory()->create([
        'role' => 'siswa',
        'identity_number' => '2026003',
        'name' => 'Siswa Tanpa Kelas',
        'class_group' => null,
        'is_active' => true,
    ]);

    User::factory()->create([
        'role' => 'siswa',
        'identity_number' => '2026004',
        'name' => 'Siswa Dengan Kelas',
        'class_group' => '10',
        'is_active' => true,
    ]);

    $this->actingAs($admin);

    $response = $this->get(route('admin.voters.index'));

    $response->assertOk()
        ->assertSeeText('Siswa Dengan Kelas')
        ->assertDontSeeText('Siswa Tanpa Kelas');
});

test('admin can filter voter data by class and major category', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'identity_number' => 'ADMIN-004',
        'is_active' => true,
    ]);

    User::factory()->create([
        'role' => 'siswa',
        'identity_number' => '2026012',
        'name' => 'Siswa XII TO 1',
        'class_group' => '12',
        'major' => 'TO 1',
        'is_active' => true,
    ]);

    User::factory()->create([
        'role' => 'siswa',
        'identity_number' => '2026013',
        'name' => 'Siswa XII TO 2',
        'class_group' => '12',
        'major' => 'TO 2',
        'is_active' => true,
    ]);

    $this->actingAs($admin);

    $response = $this->get(route('admin.voters.index', ['filter' => 'kelas_12_to_1']));

    $response->assertOk()
        ->assertSeeText('Siswa XII TO 1')
        ->assertDontSeeText('Siswa XII TO 2');
});

test('sipintu sync keeps class data when the payload uses an alternate class field name', function () {
    $gateway = app(SiPintuGatewayService::class);

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
    $gateway = app(SiPintuGatewayService::class);

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
    $gateway = app(SiPintuGatewayService::class);
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
    $gateway = app(SiPintuGatewayService::class);
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

    $gateway = app(SiPintuGatewayService::class);
    $result = $gateway->syncAllUsersFromGateway();

    expect($result['total'])->toBe(5)
        ->and($result['students'])->toBe(3)
        ->and($result['teachers'])->toBe(2);
});

test('admin can download voter credentials excel for the selected class and major filter', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'identity_number' => 'ADMIN-001',
        'is_active' => true,
    ]);

    User::factory()->create([
        'role' => 'siswa',
        'identity_number' => '2026001',
        'name' => 'Siswa PPLG 1',
        'class_group' => '12',
        'major' => 'PPLG 1',
        'is_active' => true,
        'birth_date' => '2008-01-01',
    ]);

    User::factory()->create([
        'role' => 'siswa',
        'identity_number' => '2026002',
        'name' => 'Siswa PPLG 2',
        'class_group' => '12',
        'major' => 'PPLG 2',
        'is_active' => true,
        'birth_date' => '2008-02-01',
    ]);

    $election = Election::create([
        'title' => 'Pemilihan Uji Coba',
        'year' => '2026',
        'description' => 'Uji coba export',
        'start_time' => now()->subDay(),
        'end_time' => now()->addDay(),
        'status' => Election::STATUS_ACTIVE,
    ]);

    $export = new VoterCredentialsExport('kelas_12_pplg_1');
    $method = new ReflectionMethod($export, 'buildRows');
    $rows = $method->invoke($export);

    expect($rows)->toHaveCount(2)
        ->and($rows[1][0])->toBe('2026001')
        ->and($rows[1][1])->toBe('2008-01-01');
});
