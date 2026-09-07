<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('sync fails with informative message when configuration is missing', function () {
    config()->set('services.sipintu.api_url', '');
    config()->set('services.sipintu.client_id', '');
    config()->set('services.sipintu.client_secret', '');

    $admin = User::factory()->create([
        'role' => 'admin',
        'identity_number' => 'ADMIN-SYNC-01',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)
        ->postJson(route('admin.voters.sync'));

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);

    expect($response->json('message'))->toContain('Konfigurasi SiPintu belum lengkap')
        ->and($response->json('message'))->toContain('SIPINTU_API_URL')
        ->and($response->json('message'))->toContain('SIPINTU_CLIENT_ID')
        ->and($response->json('message'))->toContain('SIPINTU_CLIENT_SECRET');
});

test('sync fails and reports 401 unauthorized credentials error', function () {
    config()->set('services.sipintu.api_url', 'http://localhost:8000');
    config()->set('services.sipintu.client_id', 'invalid_id');
    config()->set('services.sipintu.client_secret', 'invalid_secret');

    Http::fake([
        'http://localhost:8000/*' => Http::response([
            'error' => 'unauthorized',
            'message' => 'Missing or invalid credentials.',
        ], 401),
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
        'identity_number' => 'ADMIN-SYNC-02',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)
        ->postJson(route('admin.voters.sync'));

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);

    expect($response->json('message'))->toContain('401 Unauthorized')
        ->and($response->json('message'))->toContain('Missing or invalid credentials');
});

test('sync fails and reports 403 forbidden access error', function () {
    config()->set('services.sipintu.api_url', 'http://localhost:8000');
    config()->set('services.sipintu.client_id', 'app_blocked');
    config()->set('services.sipintu.client_secret', 'sec_blocked');

    Http::fake([
        'http://localhost:8000/*' => Http::response([
            'error' => 'forbidden',
            'message' => 'Access denied for this client.',
        ], 403),
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
        'identity_number' => 'ADMIN-SYNC-03',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)
        ->postJson(route('admin.voters.sync'));

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);

    expect($response->json('message'))->toContain('403 Forbidden')
        ->and($response->json('message'))->toContain('Access denied');
});

test('sync fails and reports 500 internal server error from sipintu', function () {
    config()->set('services.sipintu.api_url', 'http://localhost:8000');
    config()->set('services.sipintu.client_id', 'app_test');
    config()->set('services.sipintu.client_secret', 'sec_test');

    Http::fake([
        'http://localhost:8000/*' => Http::response([
            'message' => 'Database connection crashed on SiPintu.',
        ], 500),
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
        'identity_number' => 'ADMIN-SYNC-04',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)
        ->postJson(route('admin.voters.sync'));

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);

    expect($response->json('message'))->toContain('500 Internal Server Error')
        ->and($response->json('message'))->toContain('Database connection crashed');
});

test('sync fails and reports network connection timeout', function () {
    config()->set('services.sipintu.api_url', 'http://localhost:8000');
    config()->set('services.sipintu.client_id', 'app_test');
    config()->set('services.sipintu.client_secret', 'sec_test');

    Http::fake([
        'http://localhost:8000/*' => function () {
            throw new ConnectionException('cURL error 28: Operation timed out after 60000 milliseconds with 0 bytes received');
        },
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
        'identity_number' => 'ADMIN-SYNC-05',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)
        ->postJson(route('admin.voters.sync'));

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);

    expect($response->json('message'))->toContain('Timeout');
});

test('non ajax sync failure redirects with flash error message', function () {
    config()->set('services.sipintu.api_url', 'http://localhost:8000');
    config()->set('services.sipintu.client_id', 'app_test');
    config()->set('services.sipintu.client_secret', 'sec_test');

    Http::fake([
        'http://localhost:8000/*' => Http::response([
            'error' => 'unauthorized',
            'message' => 'Token expired.',
        ], 401),
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
        'identity_number' => 'ADMIN-SYNC-06',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)
        ->post(route('admin.voters.sync'));

    $response->assertRedirect(route('admin.voters.index', ['filter' => 'semua', 'major' => 'semua']));
    expect(session('error'))->toContain('401 Unauthorized');
});

test('home sipintu sync failure redirects with flash error message', function () {
    config()->set('services.sipintu.api_url', 'http://localhost:8000');
    config()->set('services.sipintu.client_id', 'app_test');
    config()->set('services.sipintu.client_secret', 'sec_test');

    Http::fake([
        'http://localhost:8000/*' => Http::response([
            'error' => 'server_error',
            'message' => 'Service temporarily unavailable.',
        ], 503),
    ]);

    $response = $this->from(route('sipintu.data'))->post(route('sipintu.sync'));

    $response->assertRedirect(route('sipintu.data'));
    expect(session('error'))->toContain('HTTP 503');
});
