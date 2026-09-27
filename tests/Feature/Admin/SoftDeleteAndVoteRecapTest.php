<?php

use App\Models\Candidate;
use App\Models\Election;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user deletion soft-deletes the record and retains vote tally in recap', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
    ]);

    $student = User::factory()->create([
        'role' => 'siswa',
        'class_group' => 'X',
        'major' => 'PPLG',
        'is_active' => true,
    ]);

    $election = Election::create([
        'title' => 'Pemilihan Ketua OSIS',
        'year' => '2026',
        'start_time' => now()->subHour(),
        'end_time' => now()->addHours(2),
        'status' => Election::STATUS_ACTIVE,
        'is_published' => true,
    ]);

    $candidate = Candidate::create([
        'election_id' => $election->id,
        'candidate_number' => 1,
        'name' => 'Kandidat 1',
        'class' => 'X',
        'major' => 'PPLG',
        'vision' => 'Visi test',
        'mission' => 'Misi test',
    ]);

    // Student votes for candidate
    Vote::create([
        'election_id' => $election->id,
        'candidate_id' => $candidate->id,
        'user_id' => $student->id,
    ]);

    // Admin soft-deletes the student
    $response = $this->actingAs($admin)->delete(route('admin.voters.destroy_by_id', $student->id));
    $response->assertRedirect(route('admin.voters.index'));

    // Check soft delete: student still in database with deleted_at set
    $this->assertSoftDeleted('users', ['id' => $student->id]);

    // Check vote record still exists in database
    $this->assertDatabaseHas('votes', [
        'election_id' => $election->id,
        'candidate_id' => $candidate->id,
        'user_id' => $student->id,
    ]);

    // Check statistics data API still returns 1 vote
    $statsResponse = $this->actingAs($admin)->getJson(route('admin.statistics.data', ['election_id' => $election->id]));
    $statsResponse->assertOk()
        ->assertJson([
            'total_votes' => 1,
            'voted_count' => 1,
        ]);
    expect($statsResponse->json('candidates.0.votes'))->toBe(1);

    // Check public results data API still returns 1 vote
    $publicResponse = $this->getJson(route('results.data'));
    $publicResponse->assertOk()
        ->assertJson([
            'total_votes' => 1,
        ]);
    expect($publicResponse->json('candidates.0.votes'))->toBe(1);
});

test('inactive or alumni user status does not reduce vote tally in recap', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
    ]);

    $student = User::factory()->create([
        'role' => 'siswa',
        'class_group' => 'XII',
        'major' => 'PPLG',
        'is_active' => true,
    ]);

    $election = Election::create([
        'title' => 'Pemilihan Ketua OSIS',
        'year' => '2026',
        'start_time' => now()->subHour(),
        'end_time' => now()->addHours(2),
        'status' => Election::STATUS_ACTIVE,
        'is_published' => true,
    ]);

    $candidate = Candidate::create([
        'election_id' => $election->id,
        'candidate_number' => 1,
        'name' => 'Kandidat 1',
        'class' => 'XII',
        'major' => 'PPLG',
        'vision' => 'Visi test',
        'mission' => 'Misi test',
    ]);

    // Student votes
    Vote::create([
        'election_id' => $election->id,
        'candidate_id' => $candidate->id,
        'user_id' => $student->id,
    ]);

    // Student is marked as alumni / inactive (e.g. after graduation)
    $student->update([
        'role' => 'alumni',
        'is_active' => false,
    ]);

    // Check statistics data API still returns 1 vote
    $statsResponse = $this->actingAs($admin)->getJson(route('admin.statistics.data', ['election_id' => $election->id]));
    $statsResponse->assertOk()
        ->assertJson([
            'total_votes' => 1,
            'voted_count' => 1,
        ]);
    expect($statsResponse->json('candidates.0.votes'))->toBe(1);

    // Check dashboard stats
    $dashboardResponse = $this->actingAs($admin)->get(route('admin.dashboard'));
    $dashboardResponse->assertOk();

    // Check public results
    $publicResponse = $this->getJson(route('results.data'));
    $publicResponse->assertOk()
        ->assertJson([
            'total_votes' => 1,
        ]);
});

test('soft-deleted user can be restored from trash', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
    ]);

    $student = User::factory()->create([
        'role' => 'siswa',
        'class_group' => 'X',
        'is_active' => true,
    ]);

    // Soft delete student
    $student->delete();
    expect($student->trashed())->toBeTrue();

    // Trash page shows the student
    $trashResponse = $this->actingAs($admin)->get(route('admin.voters.trash'));
    $trashResponse->assertOk()
        ->assertSee($student->name);

    // Restore student
    $restoreResponse = $this->actingAs($admin)->post(route('admin.voters.restore', $student->id));
    $restoreResponse->assertRedirect(route('admin.voters.trash'));

    // Check student is no longer trashed
    $student->refresh();
    expect($student->trashed())->toBeFalse();
});
