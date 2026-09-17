<?php

use App\Models\Candidate;
use App\Models\Election;
use App\Models\User;
use App\Models\VotingToken;
use App\Services\SiPintuGatewayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('student can login and access dashboard', function () {
    $user = User::factory()->create([
        'role' => 'siswa',
        'identity_number' => '20260001',
        'password' => '2008-05-12',
        'birth_date' => '2008-05-12',
        'is_active' => true,
    ]);

    $response = $this->post('/login', [
        'identity' => '20260001',
        'password' => '2008-05-12',
    ]);

    $response->assertRedirect(route('siswa.dashboard'));
    $this->assertAuthenticatedAs($user);
});

test('voting token casts foreign keys to integers for strict ownership checks', function () {
    $token = new VotingToken([
        'user_id' => '42',
        'election_id' => '7',
    ]);

    expect($token->user_id)->toBe(42)
        ->and($token->election_id)->toBe(7);
});

test('voter can submit the token assigned to the current election', function () {
    $user = User::factory()->create([
        'role' => 'siswa',
        'class_group' => 'X',
        'is_active' => true,
    ]);
    $election = Election::create([
        'title' => 'Pemilihan Test',
        'year' => '2026',
        'start_time' => now()->subMinute(),
        'end_time' => now()->addMinute(),
        'status' => Election::STATUS_ACTIVE,
    ]);
    $candidate = Candidate::create([
        'election_id' => $election->id,
        'candidate_number' => 1,
        'name' => 'Kandidat Test',
        'class' => 'X',
        'major' => 'PPLG',
        'vision' => 'Visi test',
        'mission' => 'Misi test',
    ]);
    VotingToken::create([
        'user_id' => $user->id,
        'election_id' => $election->id,
        'token' => 'TEST-TOKEN',
    ]);

    $response = $this->actingAs($user)->postJson(route('vote.store'), [
        'election_id' => $election->id,
        'candidate_id' => $candidate->id,
        'token' => ' test-token ',
    ]);

    $response->assertOk()->assertJson(['success' => true]);
    $this->assertDatabaseHas('votes', [
        'election_id' => $election->id,
        'candidate_id' => $candidate->id,
        'user_id' => $user->id,
    ]);
});

test('invalid voting token returns JSON instead of an HTML parser error', function () {
    $user = User::factory()->create([
        'role' => 'siswa',
        'class_group' => 'X',
        'is_active' => true,
    ]);
    $election = Election::create([
        'title' => 'Pemilihan Test',
        'year' => '2026',
        'start_time' => now()->subMinute(),
        'end_time' => now()->addMinute(),
        'status' => Election::STATUS_ACTIVE,
    ]);
    $candidate = Candidate::create([
        'election_id' => $election->id,
        'candidate_number' => 1,
        'name' => 'Kandidat Test',
        'class' => 'X',
        'major' => 'PPLG',
        'vision' => 'Visi test',
        'mission' => 'Misi test',
    ]);

    $response = $this->actingAs($user)->postJson(route('vote.store'), [
        'election_id' => $election->id,
        'candidate_id' => $candidate->id,
        'token' => 'WRONG-TOKEN',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Token tidak sesuai dengan pengguna atau periode pemilihan ini.');
});

test('student can login via sipintu sync when local user is not found', function () {
    Http::fake([
        'http://localhost:8000/api/v1/auth/login*' => Http::response([
            'success' => true,
            'user' => [
                'identity_number' => '20260099',
                'name' => 'Ayu SiPintu',
                'email' => 'ayu@sipintu.test',
                'role' => 'siswa',
                'class_group' => 'XI',
                'major' => 'PPLG',
                'birth_date' => '2008-05-12',
                'password' => '2008-05-12',
                'is_active' => true,
            ],
        ], 200),
    ]);

    config()->set('services.sipintu.api_url', 'http://localhost:8000');
    config()->set('services.sipintu.client_id', 'app_test');
    config()->set('services.sipintu.client_secret', 'sec_test');

    $response = $this->post('/login', [
        'identity' => '20260099',
        'password' => '2008-05-12',
    ]);

    $response->assertRedirect(route('siswa.dashboard'));
    $this->assertDatabaseHas('users', [
        'identity_number' => '20260099',
        'email' => 'ayu@sipintu.test',
    ]);
    $this->assertAuthenticated();
});

test('guest can begin sipintu oauth flow', function () {
    config()->set('services.sipintu.base_url', 'http://localhost:8000');
    config()->set('services.sipintu.client_id', 'app_oauth_test');
    config()->set('services.sipintu.redirect_uri', 'http://localhost:8001/oauth/callback');

    $response = $this->withSession([])->get(route('sipintu.oauth.redirect'));

    $response->assertRedirect();
    $this->assertNotNull(session('sipintu_oauth_state'));
});

test('generated local password is unique and secure per voter', function () {
    $password = User::generateLocalPassword('20260099', 'Ayu SiPintu');

    expect($password)
        ->not->toBe('20260099')
        ->toMatch('/^PILKETOS-\d{4}$/')
        ->and(User::generateLocalPassword('20260099', 'Ayu SiPintu'))->not->toBe($password);
});

test('sipintu sync never overwrites local password with sipintu secret data', function () {
    $user = User::factory()->create([
        'role' => 'siswa',
        'identity_number' => '20260555',
        'name' => 'Rina Local',
        'email' => 'rina@local.test',
        'password' => bcrypt('LocalPassword123'),
        'login_password' => 'LocalPassword123',
        'is_active' => true,
    ]);

    $updated = app(SiPintuGatewayService::class)->syncUserFromGateway([
        'identity_number' => '20260555',
        'nama' => 'Rina Local',
        'email' => 'rina@local.test',
        'role' => 'siswa',
        'class_group' => 'XI',
        'major' => 'PPLG',
        'password' => 'super-secret-sipintu-password',
        'is_active' => true,
    ]);

    expect($updated)->not->toBeNull()
        ->and($updated->fresh()->login_password)->toBe('LocalPassword123')
        ->and(Hash::check('super-secret-sipintu-password', $updated->fresh()->password))->toBeFalse();
});

test('admin can update a voter password locally', function () {
    $this->actingAs(User::factory()->create([
        'role' => 'admin',
        'identity_number' => 'admin-01',
        'password' => bcrypt('admin123'),
    ]));

    $user = User::factory()->create([
        'role' => 'siswa',
        'identity_number' => '20260022',
        'password' => bcrypt('oldpass'),
        'login_password' => 'oldpass',
        'is_active' => true,
    ]);

    $response = $this->put(route('admin.voters.password.update', $user->identity_number), [
        'password' => 'NewSecurePass123',
        'password_confirmation' => 'NewSecurePass123',
    ]);

    $response->assertStatus(302);
    $this->assertTrue(Hash::check('NewSecurePass123', $user->fresh()->password));
    $this->assertSame('NewSecurePass123', $user->fresh()->login_password);
});

test('sipintu sync keeps phone when payload uses alternate no hp field names', function () {
    $gateway = app(SiPintuGatewayService::class);

    $user = $gateway->syncUserFromGateway([
        'identity_number' => '20260123',
        'nama' => 'Rina SiPintu',
        'role' => 'siswa',
        'class_group' => 'XI',
        'major' => 'PPLG',
        'nomor_hp' => '081234567890',
        'is_active' => true,
    ]);

    expect($user)->not->toBeNull()
        ->and($user->phone)->toBe('081234567890');
});

test('sipintu oauth callback can authenticate user', function () {
    Http::fake([
        'http://localhost:8000/oauth/token' => Http::response([
            'access_token' => 'token_123',
            'refresh_token' => 'refresh_123',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'id_token' => 'id_token_123',
        ], 200),
        'http://localhost:8000/api/v1/user' => Http::response([
            'success' => true,
            'user' => [
                'identity_number' => '20261101',
                'name' => 'Budi OAuth',
                'email' => 'budi@sipintu.test',
                'role' => 'siswa',
                'class_group' => 'XII',
                'major' => 'TKJ',
                'birth_date' => '2008-01-11',
                'is_active' => true,
            ],
        ], 200),
    ]);

    config()->set('services.sipintu.api_url', 'http://localhost:8000');
    config()->set('services.sipintu.base_url', 'http://localhost:8000');
    config()->set('services.sipintu.client_id', 'app_oauth_test');
    config()->set('services.sipintu.client_secret', 'sec_oauth_test');
    config()->set('services.sipintu.redirect_uri', 'http://localhost:8001/oauth/callback');

    $this->withSession([
        'sipintu_oauth_state' => 'state-abc',
    ])->get('/oauth/callback?code=auth-code-123&state=state-abc');

    $this->assertDatabaseHas('users', [
        'identity_number' => '20261101',
        'email' => 'budi@sipintu.test',
    ]);
    $this->assertAuthenticated();
});

test('full sipintu user sync reads all users from generic api list', function () {
    Http::fake([
        'http://localhost:8000/api/v1/users*' => Http::response([
            'success' => true,
            'data' => [
                [
                    'identity_number' => '20261001',
                    'name' => 'Siswa A',
                    'email' => 'siswaa@sipintu.test',
                    'role' => 'siswa',
                    'class_group' => 'XI',
                    'major' => 'PPLG',
                    'birth_date' => '2008-05-12',
                    'password' => '2008-05-12',
                    'is_active' => true,
                ],
                [
                    'identity_number' => '19650001',
                    'name' => 'Guru B',
                    'email' => 'gurub@sipintu.test',
                    'role' => 'guru',
                    'phone' => '0812333444',
                    'password' => 'guru123',
                    'is_active' => true,
                ],
            ],
        ], 200),
    ]);

    config()->set('services.sipintu.api_url', 'http://localhost:8000');
    config()->set('services.sipintu.client_id', 'app_all_users');
    config()->set('services.sipintu.client_secret', 'sec_all_users');

    $result = app(SiPintuGatewayService::class)->syncAllUsersFromGateway();

    expect($result['total'])->toBe(2)
        ->and(User::where('identity_number', '20261001')->exists())->toBeTrue()
        ->and(User::where('identity_number', '19650001')->exists())->toBeTrue();
});

test('sipintu sync button redirects gracefully when api times out', function () {
    Http::fake([
        'http://localhost:8000/api/v1/users*' => function () {
            throw new RuntimeException('timeout');
        },
    ]);

    config()->set('services.sipintu.api_url', 'http://localhost:8000');
    config()->set('services.sipintu.client_id', 'app_timeout');
    config()->set('services.sipintu.client_secret', 'sec_timeout');

    $response = $this->from(route('sipintu.data'))->post(route('sipintu.sync'));

    $response->assertRedirect(route('sipintu.data'));
    expect(session('error'))->not->toBeNull();
});
