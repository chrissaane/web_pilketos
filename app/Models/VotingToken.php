<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VotingToken extends Model
{
    protected $fillable = [
        'user_id',
        'election_id',
        'token',
        'used_at',
    ];

    protected $dates = ['used_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }
}
