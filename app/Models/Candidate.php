<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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

    public function election()
    {
        return $this->belongsTo(Election::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_urls[0] ?? null;
    }

    public function getPhotoUrlsAttribute(): array
    {
        $paths = $this->photo_paths ?: ($this->photo_path ? [$this->photo_path] : []);

        return collect($paths)->filter()->map(fn (string $path) => Storage::disk('public')->url($path))->values()->all();
    }
}