<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'role',
        'identity_number',
        'name',
        'email',
        'password',
        'class_group',
        'major',
        'birth_date',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function scopeEligibleVoters(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            $query->where('role', 'guru')
                ->orWhere(function (Builder $studentQuery) {
                    $studentQuery->where('role', 'siswa')
                        ->where('is_active', true)
                        ->whereNotNull('class_group')
                        ->whereRaw("TRIM(class_group) <> ''");
                });
        });
    }
}