<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Candidate extends Model
{
    protected $fillable = [
        'election_id',
        'candidate_number',
        'name',
        'photo_path',
        'photo_paths',
        'class',
        'major',
        'biodata',
        'vision',
        'mission',
        'motto',
        'achievements',
        'organizations',
    ];

    protected $casts = [
        'photo_paths' => 'array',
    ];

    /** @return BelongsTo<Election, $this> */
    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    /** @return HasMany<Vote, $this> */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_urls[0] ?? null;
    }

    /** @return array<int, string> */
    public function getPhotoUrlsAttribute(): array
    {
        $paths = $this->photo_paths ?: ($this->photo_path ? [$this->photo_path] : []);

        return collect($paths)->filter()->map(fn (string $path) => Storage::disk('public')->url($path))->values()->all();
    }
}
