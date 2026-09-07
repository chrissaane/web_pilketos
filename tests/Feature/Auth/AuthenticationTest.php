<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('login screen can be rendered', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
    $response->assertSee('Portal Masuk');
});

test('admin can authenticate and is redirected to admin dashboard', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'identity_number' => 'admin-auth-test',
        'password' => Hash::make('secretadmin'),
        'is_active' => true,
    ]);

    $response = $this->post(route('login.post'), [
        'identity' => 'admin-auth-test',
        'password' => 'secretadmin',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($admin);
});

test('teacher can authenticate and is redirected to guru dashboard', function () {
    $teacher = User::factory()->create([
        'role' => 'guru',
        'identity_number' => '19850101001',
        'password' => Hash::make('1985-01-01'),
        'is_active' => true,
    ]);

    $response = $this->post(route('login.post'), [
        'identity' => '19850101001',
        'password' => '1985-01-01',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('guru.dashboard'));

    $this->assertAuthenticatedAs($teacher);
});

test('student can authenticate using birth date format DD/MM/YYYY and is redirected to siswa dashboard', function () {
    $student = User::factory()->create([
        'role' => 'siswa',
        'identity_number' => '20269999',
        'birth_date' => '2008-05-12',
        'password' => Hash::make('2008-05-12'),
        'is_active' => true,
    ]);

    // Test with Indonesian date format (DD/MM/YYYY)
    $response = $this->post(route('login.post'), [
        'identity' => '20269999',
        'password' => '12/05/2008',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('siswa.dashboard'));

    $this->assertAuthenticatedAs($student);
});

test('user can authenticate using email', function () {
    $user = User::factory()->create([
        'role' => 'siswa',
        'identity_number' => '20268888',
        'email' => 'student.email@smkn1bangsri.sch.id',
        'password' => Hash::make('mypassword'),
        'is_active' => true,
    ]);

    $response = $this->post(route('login.post'), [
        'identity' => 'student.email@smkn1bangsri.sch.id',
        'password' => 'mypassword',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('siswa.dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create([
        'identity_number' => '20267777',
        'password' => Hash::make('correct-password'),
        'is_active' => true,
    ]);

    $response = $this->post(route('login.post'), [
        'identity' => '20267777',
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors(['identity']);
    expect(session('errors')->first('identity'))->toContain('Password yang Anda masukkan salah');

    $this->assertGuest();
});

test('inactive user receives clear account disabled error message', function () {
    User::factory()->create([
        'identity_number' => '20266666',
        'password' => Hash::make('valid-pass'),
        'is_active' => false,
    ]);

    $response = $this->post(route('login.post'), [
        'identity' => '20266666',
        'password' => 'valid-pass',
    ]);

    $response->assertSessionHasErrors(['identity']);
    expect(session('errors')->first('identity'))->toContain('dinonaktifkan');

    $this->assertGuest();
});

test('users can logout and are redirected to login', function () {
    $user = User::factory()->create([
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->post(route('logout'));

    $response->assertRedirect(route('login'));
    $this->assertGuest();
});

test('authenticated user visiting login is redirected to dashboard', function () {
    $user = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->get(route('login'));

    $response->assertRedirect(route('dashboard'));
});