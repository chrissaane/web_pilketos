<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    $result = app(\App\Services\SiPintuGatewayService::class)->syncAllUsersFromGateway();

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
