<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SiPintuGatewayService
{
    public function buildAuthorizationUrl(string $state): ?string
    {
        $baseUrl = rtrim(config('services.sipintu.base_url', env('SIPINTU_BASE_URL', 'http://localhost:8000')), '/');
        $clientId = config('services.sipintu.client_id', env('SIPINTU_CLIENT_ID'));
        $redirectUri = config('services.sipintu.redirect_uri', env('SIPINTU_REDIRECT_URI', 'http://localhost:8001/oauth/callback'));

        if (empty($baseUrl) || empty($clientId) || empty($redirectUri)) {
            return null;
        }

        return $baseUrl.'/oauth/authorize?'.http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'state' => $state,
        ]);
    }

    public function fetchUserFromGateway(string $code): ?array
    {
        $baseUrl = rtrim(config('services.sipintu.base_url', env('SIPINTU_BASE_URL', 'http://localhost:8000')), '/');
        $clientId = config('services.sipintu.client_id', env('SIPINTU_CLIENT_ID'));
        $clientSecret = config('services.sipintu.client_secret', env('SIPINTU_CLIENT_SECRET'));
        $redirectUri = config('services.sipintu.redirect_uri', env('SIPINTU_REDIRECT_URI', 'http://localhost:8001/oauth/callback'));

        if (empty($baseUrl) || empty($clientId) || empty($clientSecret) || empty($redirectUri)) {
            return null;
        }

        $tokenResponse = Http::asForm()
            ->withHeaders(['Accept' => 'application/json'])
            ->post($baseUrl.'/oauth/token', [
                'grant_type' => 'authorization_code',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'code' => $code,
            ]);

        if (! $tokenResponse->successful()) {
            return null;
        }

        $accessToken = $tokenResponse->json('access_token');
        if (empty($accessToken)) {
            return null;
        }

        $userResponse = Http::withToken($accessToken)
            ->withHeaders(['Accept' => 'application/json'])
            ->get($baseUrl.'/api/v1/user/profile');

        if (! $userResponse->successful()) {
            $userResponse = Http::withToken($accessToken)
                ->withHeaders(['Accept' => 'application/json'])
                ->get($baseUrl.'/api/v1/user');
        }

        if (! $userResponse->successful()) {
            return null;
        }

        $data = $userResponse->json();

        if (is_array($data) && isset($data['data']) && is_array($data['data'])) {
            return $data['data'];
        }

        return $data;
    }

    public function authenticate(string $identity, string $password): ?array
    {
        $baseUrl = rtrim(config('services.sipintu.api_url', env('SIPINTU_API_URL', 'http://localhost:8000')), '/');
        $clientId = config('services.sipintu.client_id', env('SIPINTU_CLIENT_ID'));
        $clientSecret = config('services.sipintu.client_secret', env('SIPINTU_CLIENT_SECRET'));

        if (empty($baseUrl) || empty($clientId) || empty($clientSecret)) {
            return null;
        }

        $payload = [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'identity' => $identity,
            'email' => $identity,
            'username' => $identity,
            'password' => $password,
        ];

        $endpoints = [
            '/api/v1/auth/login',
            '/api/v1/auth',
            '/api/v1/login',
        ];

        foreach ($endpoints as $endpoint) {
            $response = Http::asForm()
                ->withHeaders(['Accept' => 'application/json'])
                ->post($baseUrl.$endpoint, $payload);

            if ($response->successful()) {
                $data = $response->json();

                if (is_array($data) && ($data['success'] ?? false) === true) {
                    return $data;
                }

                if (is_array($data) && isset($data['user'])) {
                    return $data;
                }

                if (is_array($data) && isset($data['data'])) {
                    return $data;
                }
            }
        }

        return null;
    }

    public function ping(): ?array
    {
        $baseUrl = rtrim(config('services.sipintu.api_url', env('SIPINTU_API_URL', 'http://localhost:8000')), '/');
        $clientId = config('services.sipintu.client_id', env('SIPINTU_CLIENT_ID'));
        $clientSecret = config('services.sipintu.client_secret', env('SIPINTU_CLIENT_SECRET'));

        if (empty($baseUrl) || empty($clientId)) {
            return null;
        }

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'X-Client-ID' => $clientId,
            'X-Client-Secret' => $clientSecret,
        ])->get($baseUrl.'/api/v1/ping', [
            'client_id' => $clientId,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    public function validateClient(): ?array
    {
        $baseUrl = rtrim(config('services.sipintu.api_url', env('SIPINTU_API_URL', 'http://localhost:8000')), '/');
        $clientId = config('services.sipintu.client_id', env('SIPINTU_CLIENT_ID'));
        $clientSecret = config('services.sipintu.client_secret', env('SIPINTU_CLIENT_SECRET'));

        if (empty($baseUrl) || empty($clientId) || empty($clientSecret)) {
            return null;
        }

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'X-Client-ID' => $clientId,
            'X-Client-Secret' => $clientSecret,
        ])->post($baseUrl.'/api/v1/validate-client', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    public function getStudents(?string $nis = null, ?string $search = null): ?array
    {
        $baseUrl = rtrim(config('services.sipintu.api_url', env('SIPINTU_API_URL', 'http://localhost:8000')), '/');
        $clientId = config('services.sipintu.client_id', env('SIPINTU_CLIENT_ID'));
        $clientSecret = config('services.sipintu.client_secret', env('SIPINTU_CLIENT_SECRET'));

        if (empty($baseUrl) || empty($clientId) || empty($clientSecret)) {
            return null;
        }

        $query = array_filter([
            'nis' => $nis,
            'search' => $search,
        ]);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'X-Client-ID' => $clientId,
            'X-Client-Secret' => $clientSecret,
        ])->get($baseUrl.'/api/v1/sijuna/students', $query);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    public function getTeachers(?string $nip = null, ?string $search = null): ?array
    {
        $baseUrl = rtrim(config('services.sipintu.api_url', env('SIPINTU_API_URL', 'http://localhost:8000')), '/');
        $clientId = config('services.sipintu.client_id', env('SIPINTU_CLIENT_ID'));
        $clientSecret = config('services.sipintu.client_secret', env('SIPINTU_CLIENT_SECRET'));

        if (empty($baseUrl) || empty($clientId) || empty($clientSecret)) {
            return null;
        }

        $query = array_filter([
            'nip' => $nip,
            'search' => $search,
        ]);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'X-Client-ID' => $clientId,
            'X-Client-Secret' => $clientSecret,
        ])->get($baseUrl.'/api/v1/sijuna/teachers', $query);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    public function syncAllUsersFromGateway(): array
    {
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        try {
            $baseUrl = rtrim(config('services.sipintu.api_url', env('SIPINTU_API_URL', 'http://localhost:8000')), '/');
            $clientId = config('services.sipintu.client_id', env('SIPINTU_CLIENT_ID'));
            $clientSecret = config('services.sipintu.client_secret', env('SIPINTU_CLIENT_SECRET'));

            if (empty($baseUrl) || empty($clientId) || empty($clientSecret)) {
                return [
                    'students' => 0,
                    'teachers' => 0,
                    'total' => 0,
                    'message' => 'Konfigurasi gateway SiPintu belum lengkap.',
                ];
            }

            $users = $this->fetchAllUsersFromGateway();

            if (empty($users)) {
                return [
                    'students' => 0,
                    'teachers' => 0,
                    'total' => 0,
                    'message' => 'Tidak ada data SiPintu yang berhasil diambil saat ini.',
                ];
            }

            $studentCount = 0;
            $teacherCount = 0;

            DB::transaction(function () use ($users, &$studentCount, &$teacherCount) {
                foreach ($users as $userData) {
                    $this->syncUserFromGateway($userData, $this->extractPlainPassword($userData));

                    $role = $this->normalizeRole($userData['role'] ?? $userData['user_type'] ?? $userData['type'] ?? null);

                    if ($role === 'guru') {
                        $teacherCount++;
                    } else {
                        $studentCount++;
                    }
                }
            });

            return [
                'students' => $studentCount,
                'teachers' => $teacherCount,
                'total' => $studentCount + $teacherCount,
                'message' => 'Sinkronisasi SiPintu selesai.',
            ];
        } catch (\Throwable $e) {
            return [
                'students' => 0,
                'teachers' => 0,
                'total' => 0,
                'message' => 'Sinkronisasi SiPintu gagal: '.$e->getMessage(),
            ];
        }
    }

    protected function fetchAllUsersFromGateway(): array
    {
        try {
            $mergedUsers = [];
            $seenKeys = [];

            // 1. Fetch Students from SiPintu Gateway
            $studentsRes = $this->getStudents();
            $studentsData = $studentsRes['data'] ?? (is_array($studentsRes) ? $studentsRes : []);

            foreach ($studentsData as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $item['role'] = $item['role'] ?? 'siswa';
                $identityNumber = $this->extractIdentityNumber($item);
                if (blank($identityNumber)) {
                    continue;
                }
                $key = (string) $identityNumber;
                if (! isset($seenKeys[$key])) {
                    $seenKeys[$key] = true;
                    $mergedUsers[] = $item;
                }
            }

            // 2. Fetch Teachers from SiPintu Gateway
            $teachersRes = $this->getTeachers();
            $teachersData = $teachersRes['data'] ?? (is_array($teachersRes) ? $teachersRes : []);

            foreach ($teachersData as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $item['role'] = $item['role'] ?? 'guru';
                $identityNumber = $this->extractIdentityNumber($item);
                if (blank($identityNumber)) {
                    continue;
                }
                $key = (string) $identityNumber;
                if (! isset($seenKeys[$key])) {
                    $seenKeys[$key] = true;
                    $mergedUsers[] = $item;
                }
            }

            return $mergedUsers;
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function syncUserFromGateway(array $payload, string $password): ?User
    {
        if (isset($payload['nis']) || isset($payload['nip']) || isset($payload['nama']) || isset($payload['identity_number']) || isset($payload['no_induk'])) {
            $userData = $payload;
        } else {
            $userData = $payload['data']['user'] ?? $payload['data'] ?? $payload['account'] ?? $payload['user'] ?? $payload;
        }

        if (! is_array($userData)) {
            return null;
        }

        $identityNumber = $this->firstNonEmptyValue($userData, [
            'identity_number',
            'nis',
            'nip',
            'nisn',
            'no_induk',
            'nomor_induk',
            'no_induk_guru',
            'username',
        ]);

        $name = $this->firstNonEmptyValue($userData, [
            'nama',
            'nama_lengkap',
            'full_name',
            'fullName',
            'nama_siswa',
            'nama_guru',
            'name',
            'display_name',
            'user_name',
        ], 'SiPintu User');

        $email = $this->firstNonEmptyValue($userData, ['email', 'email_address', 'alamat_email', 'email_siswa', 'email_guru']);
        $role = $this->normalizeRole(
            $this->firstNonEmptyValue($userData, ['role', 'user_type', 'type', 'status', 'role_name', 'userRole'], null),
            $identityNumber,
        );
        $rawClassValue = $this->normalizeNullableValue($this->firstNonEmptyValue($userData, [
            'classroom.name',
            'classroom_name',
            'class_group',
            'class_group_name',
            'group',
            'group_name',
            'kelas',
            'kelas_siswa',
            'kelas_name',
            'class',
            'class_name',
            'grade',
            'grade_level',
            'student_class',
            'tingkat',
            'level',
        ]));

        $rawMajor = $this->normalizeNullableValue($this->firstNonEmptyValue($userData, ['major', 'jurusan', 'program_studi', 'study_program', 'jurusan_name']));

        $parsedClass = $this->parseClassAndMajor($rawClassValue, $rawMajor);
        $classGroup = $parsedClass['class_group'] ?? $this->normalizeClassGroup($rawClassValue);
        $major = $parsedClass['major'] ?? $rawMajor;

        $birthDate = $this->firstNonEmptyValue($userData, ['birth_date', 'tanggal_lahir', 'date_of_birth', 'dob', 'tgl_lahir']);
        $phone = $this->firstNonEmptyValue($userData, ['phone', 'phone_number', 'telephone', 'no_hp', 'telepon', 'mobile', 'nomor_telepon', 'telp']);
        $isActive = $this->firstNonEmptyValue($userData, ['is_active', 'active', 'status_aktif', 'is_active_user', 'status_user'], true);

        if (blank($identityNumber)) {
            return null;
        }

        $user = User::query()
            ->where('identity_number', $identityNumber)
            ->orWhere(fn ($query) => $email ? $query->where('email', $email) : null)
            ->first();

        $attributes = [
            'role' => $role,
            'identity_number' => $identityNumber,
            'name' => $name,
            'email' => $email,
            'class_group' => $classGroup,
            'major' => $major,
            'birth_date' => $birthDate,
            'phone' => $phone,
            'is_active' => (bool) $isActive,
        ];

        $effectivePassword = ! blank($password) ? $password : $this->extractPlainPassword($userData);
        $fallbackPassword = ! blank($effectivePassword) ? $effectivePassword : (! blank($birthDate) ? $birthDate : $identityNumber);

        if ($user) {
            if (! blank($effectivePassword)) {
                if (empty($user->password) || ! Hash::check($effectivePassword, $user->password)) {
                    $attributes['password'] = Hash::make($effectivePassword);
                }
            }
            $user->fill($attributes);
            $user->save();

            return $user;
        }

        return User::create(array_merge($attributes, [
            'password' => Hash::make($fallbackPassword),
        ]));
    }

    protected function normalizeRole(mixed $role, ?string $identityNumber = null): string
    {
        $value = Str::lower((string) $role);

        if (! blank($value)) {
            return match (true) {
                in_array($value, ['admin', 'administrator', 'superadmin', 'owner'], true) => 'admin',
                in_array($value, ['guru', 'teacher', 'dosen', 'pegawai', 'staff', 'teacher_staff'], true) => 'guru',
                in_array($value, ['student', 'siswa', 'murid', 'pelajar', 'alumni', 'alumni_siswa'], true) => 'siswa',
                str_contains($value, 'guru') => 'guru',
                str_contains($value, 'admin') => 'admin',
                str_contains($value, 'alumni') => 'siswa',
                default => 'siswa',
            };
        }

        $digits = preg_replace('/\D+/', '', (string) $identityNumber);

        if (strlen($digits) === 4) {
            return 'siswa';
        }

        if (strlen($digits) > 4) {
            return 'guru';
        }

        return 'siswa';
    }

    protected function normalizeClassGroup(mixed $value): ?string
    {
        $value = $this->normalizeNullableValue($value);

        if (blank($value)) {
            return null;
        }

        $normalized = trim((string) $value);
        $upper = strtoupper($normalized);

        if (preg_match('/\b(?:KELAS\s+)?(X|XI|XII|XIII|10|11|12)\b/i', $upper, $match)) {
            return match (strtoupper($match[1])) {
                'X', '10' => 'X',
                'XI', '11' => 'XI',
                'XII', '12' => 'XII',
                'XIII', '13' => 'XIII',
                default => $match[1],
            };
        }

        if (preg_match('/\b(\d{1,2})\b/', $normalized, $match)) {
            return match ((int) $match[1]) {
                10 => 'X',
                11 => 'XI',
                12 => 'XII',
                13 => 'XIII',
                default => (string) (int) $match[1],
            };
        }

        return $normalized;
    }

    protected function parseClassAndMajor(mixed $classValue, mixed $majorValue): array
    {
        $classValue = $this->normalizeNullableValue($classValue);
        $majorValue = $this->normalizeNullableValue($majorValue);
        $combined = $classValue ?? $majorValue;

        if (blank($combined)) {
            return [
                'class_group' => $this->normalizeClassGroup($classValue),
                'major' => $majorValue,
            ];
        }

        $normalized = preg_replace('/\s+/', ' ', trim((string) $combined));

        if (preg_match('/^(?:KELAS\s+)?(?:X|XI|XII|XIII|10|11|12)\s+(.+)$/i', $normalized, $match)) {
            $major = trim($match[1]);

            return [
                'class_group' => $this->normalizeClassGroup($normalized),
                'major' => strtoupper($major),
            ];
        }

        return [
            'class_group' => $this->normalizeClassGroup($classValue),
            'major' => $majorValue,
        ];
    }

    protected function normalizeNullableValue(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $trimmed = trim($value);

        if ($trimmed === '' || preg_match('/^(?:n\/a|na|null|none|not\s+available|tidak\s+ada)$/i', $trimmed)) {
            return null;
        }

        return $trimmed;
    }

    protected function firstNonEmptyValue(array $data, array $keys, mixed $default = null): mixed
    {
        // 1. Check direct keys on $data first
        foreach ($keys as $key) {
            if (str_contains($key, '.')) {
                $val = data_get($data, $key);
                if (! blank($val)) {
                    return $val;
                }
            } elseif (array_key_exists($key, $data) && ! blank($data[$key])) {
                return $data[$key];
            }
        }

        // 2. Check nested arrays ($data['user'] or $data['classroom'])
        foreach ($keys as $key) {
            if (isset($data['user']) && is_array($data['user']) && array_key_exists($key, $data['user']) && ! blank($data['user'][$key])) {
                if ($key === 'name' && is_numeric($data['user'][$key])) {
                    continue;
                }

                return $data['user'][$key];
            }
            if (isset($data['classroom']) && is_array($data['classroom']) && array_key_exists($key, $data['classroom']) && ! blank($data['classroom'][$key])) {
                return $data['classroom'][$key];
            }
        }

        return $default;
    }

    protected function extractCollectionFromResponse(mixed $payload, string $role): array
    {
        if (! is_array($payload)) {
            return [];
        }

        $records = [];
        $seenKeys = [];

        $collectRecords = function (mixed $value) use (&$collectRecords, &$records, &$seenKeys): void {
            if (! is_array($value)) {
                return;
            }

            if ($this->looksLikeUserRecord($value)) {
                $identityNumber = $this->extractIdentityNumber($value);
                if (! blank($identityNumber)) {
                    $key = (string) $identityNumber;
                    if (! isset($seenKeys[$key])) {
                        $seenKeys[$key] = true;
                        $records[] = $value;
                    }
                }

                return;
            }

            foreach ($value as $item) {
                $collectRecords($item);
            }
        };

        $collectRecords($payload);

        return $records;
    }

    protected function looksLikeUserRecord(array $value): bool
    {
        foreach (['identity_number', 'nis', 'nisn', 'nip', 'no_induk', 'nomor_induk', 'username', 'user_name', 'id', 'user_id', 'account_id'] as $key) {
            if (array_key_exists($key, $value) && ! blank($value[$key])) {
                return true;
            }
        }

        return false;
    }

    protected function extractIdentityNumber(array $value): mixed
    {
        foreach (['identity_number', 'nis', 'nisn', 'nip', 'no_induk', 'nomor_induk', 'username', 'user_name', 'id', 'user_id', 'account_id'] as $key) {
            if (array_key_exists($key, $value) && ! blank($value[$key])) {
                return $value[$key];
            }
        }

        return null;
    }

    protected function extractPlainPassword(array $data): string
    {
        foreach ([
            'password',
            'plain_password',
            'default_password',
            'user_password',
            'login_password',
            'passwordText',
            'new_password',
            'user.password',
            'user.plain_password',
        ] as $key) {
            $val = str_contains($key, '.') ? data_get($data, $key) : ($data[$key] ?? null);
            if (! empty($val)) {
                return (string) $val;
            }
        }

        return 'password';
    }
}
