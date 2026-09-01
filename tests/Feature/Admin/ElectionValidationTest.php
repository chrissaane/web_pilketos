<?php

use App\Models\Candidate;
use App\Models\Election;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function adminUserForValidation(): User
{
    return User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
    ]);
}

function electionForValidation(array $attributes = []): Election
{
    return Election::create(array_merge([
        'title' => 'Pemilihan Ketua OSIS',
        'year' => '2026',
        'description' => 'Periode pemilihan',
        'start_time' => now()->subDay(),
        'end_time' => now()->addDay(),
        'status' => Election::STATUS_ACTIVE,
    ], $attributes));
}

function candidateForValidation(Election $election, int $number): Candidate
{
    return Candidate::create([
        'election_id' => $election->id,
        'candidate_number' => $number,
        'name' => "Kandidat {$number}",
        'class' => 'XII',
        'major' => 'RPL',
        'vision' => 'Visi kandidat',
        'mission' => 'Misi kandidat',
        'photo_path' => null,
    ]);
}

test('admin cannot create an election with a duplicate year', function () {
    $this->actingAs(adminUserForValidation());
    electionForValidation();

    $response = $this->from(route('admin.cards.create'))->post(route('admin.cards.store'), [
        'title' => 'Pemilihan Kedua',
        'year' => '2026',
        'description' => 'Periode kedua',
        'start_date' => now()->format('Y-m-d'),
        'start_time' => '08:00',
        'end_date' => now()->addDay()->format('Y-m-d'),
        'end_time' => '15:00',
    ]);

    $response->assertRedirect(route('admin.cards.create'));
    $response->assertSessionHasErrors(['year' => 'Tahun pemilihan 2026 sudah terdaftar.']);
});

test('admin can create the next election year', function () {
    $this->actingAs(adminUserForValidation());
    electionForValidation();

    $response = $this->post(route('admin.cards.store'), [
        'title' => 'Pemilihan 2027',
        'year' => '2027',
        'description' => 'Periode baru',
        'start_date' => now()->addYear()->format('Y-m-d'),
        'start_time' => '08:00',
        'end_date' => now()->addYear()->addDay()->format('Y-m-d'),
        'end_time' => '15:00',
    ]);

    $response->assertRedirect(route('admin.cards.index'));
    $this->assertDatabaseHas('elections', ['year' => '2027']);
});

test('admin cannot use candidate number four or duplicate a number in one election', function () {
    Storage::fake('public');
    $this->actingAs(adminUserForValidation());
    $election = electionForValidation();
    candidateForValidation($election, 1);

    $payload = [
        'election_id' => $election->id,
        'name' => 'Kandidat Baru',
        'class' => 'XII',
        'major' => 'RPL',
        'vision' => 'Visi kandidat',
        'mission' => 'Misi kandidat',
        'photo' => UploadedFile::fake()->image('candidate.jpg'),
    ];

    $tooHigh = $this->from(route('admin.candidates.create'))->post(route('admin.candidates.store'), $payload + ['candidate_number' => 4]);
    $tooHigh->assertSessionHasErrors('candidate_number');

    $duplicate = $this->from(route('admin.candidates.create'))->post(route('admin.candidates.store'), $payload + ['candidate_number' => 1]);
    $duplicate->assertSessionHasErrors('candidate_number');
});
