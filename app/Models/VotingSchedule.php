<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VotingSchedule extends Model
{
    protected $fillable = [
        'election_id',
        'class_group',
        'major',
        'start_time',
        'end_time',
    ];

    public function election()
    {
        return $this->belongsTo(Election::class);
    }
}