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
        'login_password',
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
        return $query
            ->where('is_active', true)
            ->where(function (Builder $query) {
                $query->whereIn('role', ['guru', 'karyawan'])
                    ->orWhere(function (Builder $studentQuery) {
                        $studentQuery->where('role', 'siswa')
                            ->whereNotNull('class_group')
                            ->whereRaw("TRIM(class_group) <> ''");
                    });
            });
    }

    public static function generateLocalPassword(?string $identityNumber = null, ?string $name = null, ?array $usedPasswords = null): string
    {
        $usedPasswords ??= self::query()->pluck('login_password')->filter()->all();
        $maxAttempts = 25;

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $randomDigits = random_int(1000, 9999);
            $candidate = sprintf('PILKETOS-%d', $randomDigits);

            if (! in_array($candidate, $usedPasswords, true)) {
                $usedPasswords[] = $candidate;

                return $candidate;
            }
        }

        $fallback = sprintf('PILKETOS-%d', random_int(1000, 9999));

        if (in_array($fallback, $usedPasswords, true)) {
            $fallback = sprintf('PILKETOS-%s', strtoupper(bin2hex(random_bytes(2))));
        }

        return $fallback;
    }
}