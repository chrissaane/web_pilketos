<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use App\Models\VotingToken;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class Election extends Model
{
    public const STATUS_UPCOMING = 'Akan Datang';
    public const STATUS_ACTIVE = 'Sedang Berlangsung';
    public const STATUS_FINISHED = 'Telah Berakhir';

    protected $fillable = [
        'title',
        'year',
        'description',
        'banner_path',
        'start_time',
        'end_time',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function candidates()
    {
        return $this->hasMany(Candidate::class)->orderBy('candidate_number');
    }

    public function schedules()
    {
        return $this->hasMany(VotingSchedule::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public static function computeStatus($start, $end): string
{
    $now = now();

    if ($now->lt($start)) {
        return self::STATUS_UPCOMING;
    }

    if ($now->between($start, $end)) {
        return self::STATUS_ACTIVE;
    }

    return self::STATUS_FINISHED;
}
    public function getCurrentStatusAttribute(): string
    {
        if (! $this->start_time || ! $this->end_time) {
            return self::STATUS_UPCOMING;
        }

        return self::computeStatus($this->start_time, $this->end_time);
    }

    public function getBannerUrlAttribute(): ?string
    {
        return $this->banner_path ? Storage::disk('public')->url($this->banner_path) : null;
    }

    protected static function booted()
    {
        static::created(function (Election $election) {
            static::generateTokensForElection($election);
        });

        static::updated(function (Election $election) {
            $original = $election->getOriginal('status');
            $current = $election->status;
            if ($current === self::STATUS_ACTIVE && $original !== self::STATUS_ACTIVE) {
                static::generateTokensForElection($election);
            }
        });
    }

    public static function generateTokensForElection(Election $election, bool $force = false): void
    {
        if (! Schema::hasTable('voting_tokens')) {
            Log::warning('voting_tokens table does not exist; skipping token generation for election ' . $election->id);
            return;
        }

        try {
            $voters = User::eligibleVoters()->get();

            DB::transaction(function () use ($voters, $election, $force) {
                if ($force) {
                    VotingToken::query()->where('election_id', $election->id)->delete();
                }

                foreach ($voters as $voter) {
                    $exists = VotingToken::query()->where('user_id', $voter->id)->where('election_id', $election->id)->exists();
                    if ($exists) continue;

                    do {
                        $token = strtoupper(Str::random(8));
                        $conflict = VotingToken::query()->where('election_id', $election->id)->where('token', $token)->exists();
                    } while ($conflict);

                    VotingToken::create([
                        'user_id' => $voter->id,
                        'election_id' => $election->id,
                        'token' => $token,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            Log::error('Failed to auto-generate voting tokens for election ' . $election->id . ': ' . $e->getMessage());
        }
    }
}