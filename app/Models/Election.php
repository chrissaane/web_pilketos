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
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'is_published',
        'results_publish_at',
        'show_vote_counts_public',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_published' => 'boolean',
        'results_publish_at' => 'datetime',
        'show_vote_counts_public' => 'boolean',
    ];

    /** @return HasMany<Candidate, $this> */
    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class)->orderBy('candidate_number');
    }

    /** @return HasMany<VotingSchedule, $this> */
    public function schedules(): HasMany
    {
        return $this->hasMany(VotingSchedule::class);
    }

    /** @return HasMany<Vote, $this> */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function publicResultsVisible(): bool
    {
        return $this->show_vote_counts_public
            && (! $this->results_publish_at || now()->greaterThanOrEqualTo($this->results_publish_at));
    }

    public static function computeStatus(Carbon|string $start, Carbon|string $end): string
    {
        $start = $start instanceof Carbon ? $start : Carbon::parse($start);
        $end = $end instanceof Carbon ? $end : Carbon::parse($end);
        $now = Carbon::now(config('app.timezone'));

        if ($now->isBefore($start)) {
            return self::STATUS_UPCOMING;
        }

        if ($now->lessThanOrEqualTo($end)) {
            return self::STATUS_ACTIVE;
        }

        return self::STATUS_FINISHED;
    }
    public function getCurrentStatusAttribute(): string
    {
        if (blank($this->start_time) || blank($this->end_time)) {
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

                $existingUserIds = VotingToken::query()
                    ->where('election_id', $election->id)
                    ->pluck('user_id')
                    ->flip()
                    ->keys()
                    ->all();

                $existingTokens = VotingToken::query()
                    ->where('election_id', $election->id)
                    ->pluck('token')
                    ->flip()
                    ->keys()
                    ->all();

                $rowsToInsert = [];
                $maxAttempts = 1000;

                foreach ($voters as $voter) {
                    if (in_array((int) $voter->id, $existingUserIds, true)) {
                        continue;
                    }

                    $token = null;
                    for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
                        $candidate = strtoupper(Str::random(8));

                        if (! in_array($candidate, $existingTokens, true)) {
                            $token = $candidate;
                            $existingTokens[] = $candidate;
                            break;
                        }
                    }

                    if ($token === null) {
                        $token = strtoupper(Str::random(8));
                    }

                    $rowsToInsert[] = [
                        'user_id' => $voter->id,
                        'election_id' => $election->id,
                        'token' => $token,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if ($rowsToInsert !== []) {
                    VotingToken::query()->insert($rowsToInsert);
                }
            });
        } catch (\Throwable $e) {
            Log::error('Failed to auto-generate voting tokens for election ' . $election->id . ': ' . $e->getMessage());
        }
    }
}