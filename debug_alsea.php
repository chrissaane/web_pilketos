<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';

$app = app();

// Check user
$user = \App\Models\User::where('email', 'alsea.dummy@smkn1bangsri.sch.id')->first();
echo "=== USER ===\n";
if ($user) {
    echo "Name: " . $user->name . "\n";
    echo "NIS: " . $user->identity_number . "\n";
    echo "Role: " . $user->role . "\n";
    echo "Class: " . $user->class_group . "\n";
    echo "Major: " . $user->major . "\n";
    echo "Active: " . ($user->is_active ? "YES" : "NO") . "\n";
    echo "ID: " . $user->id . "\n";
} else {
    echo "NOT FOUND\n";
}

// Check election
$election = \App\Models\Election::where('status', 'Sedang Berlangsung')->first();
echo "\n=== ELECTION ===\n";
if ($election) {
    echo "Title: " . $election->title . "\n";
    echo "Status: " . $election->status . "\n";
    echo "ID: " . $election->id . "\n";
} else {
    echo "NOT FOUND\n";
}

// Check token
if ($user && $election) {
    $token = \App\Models\VotingToken::where('user_id', $user->id)
        ->where('election_id', $election->id)
        ->first();
    echo "\n=== TOKEN ===\n";
    if ($token) {
        echo "Token: " . $token->token . "\n";
        echo "Used: " . ($token->used_at ? $token->used_at : "NO") . "\n";
    } else {
        echo "NOT FOUND - creating new token...\n";
        // Generate a fresh token for this user/election
        do {
            $newToken = strtoupper(\Illuminate\Support\Str::random(8));
            $conflict = \App\Models\VotingToken::where('election_id', $election->id)
                ->whereRaw('UPPER(token) = ?', [$newToken])
                ->exists();
        } while ($conflict);

        $created = \App\Models\VotingToken::create([
            'user_id' => $user->id,
            'election_id' => $election->id,
            'token' => $newToken,
        ]);

        echo "Created Token: " . $created->token . "\n";
    }
}
