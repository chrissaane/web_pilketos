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

    public function exchangeCodeForToken(string $code): ?array
    {
        $baseUrl = rtrim(config('services.sipintu.base_url', env('SIPINTU_BASE_URL', 'http://localhost:8000')), '/');
        $clientId = config('services.sipintu.client_id', env('SIPINTU_CLIENT_ID'));
        $clientSecret = config('services.sipintu.client_secret', env('SIPINTU_CLIENT_SECRET'));
        $redirectUri = config('services.sipintu.redirect_uri', env('SIPINTU_REDIRECT_URI', 'http://localhost:8001/oauth/callback'));

        if (empty($baseUrl) || empty($clientId) || empty($clientSecret) || empty($redirectUri)) {
            return null;
        }

        $response = Http::asForm()
            ->withHeaders(['Accept' => 'application/json'])
            ->post($baseUrl.'/oauth/token', [
                'grant_type' => 'authorization_code',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'code' => $code,
            ]);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        if (! is_array($data) || empty($data['access_token'])) {
            return null;
        }

        return $data;
    }

    public function fetchUserProfile(string $accessToken): ?array
    {
        $baseUrl = rtrim(config('services.sipintu.api_url', env('SIPINTU_API_URL', 'http://localhost:8000')), '/');

        if (empty($baseUrl)) {
            return null;
        }

        $response = Http::withToken($accessToken)
            ->withHeaders(['Accept' => 'application/json'])
            ->get($baseUrl.'/api/v1/user/profile');

        $data = $response->successful() ? $response->json() : null;

        if (empty($data) || (! isset($data['data']) && ! isset($data['user']))) {
            $fallbackResponse = Http::withToken($accessToken)
                ->withHeaders(['Accept' => 'application/json'])
                ->get($baseUrl.'/api/v1/user');

            if ($fallbackResponse->successful()) {
                $fallbackData = $fallbackResponse->json();
                if (! empty($fallbackData)) {
                    $data = $fallbackData;
                }
            }
        }

        if (! is_array($data)) {
            return null;
        }

        if (isset($data['user']) && is_array($data['user'])) {
            return $data['user'];
        }

        if (isset($data['data']) && is_array($data['data'])) {
            return $data['data'];
        }

        return $data;
    }

    public function fetchUserFromGateway(string $code): ?array
    {
        $tokenData = $this->exchangeCodeForToken($code);
        if (! $tokenData || empty($tokenData['access_token'])) {
            return null;
        }

        return $this->fetchUserProfile($tokenData['access_token']);
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
            try {
                $response = Http::timeout(5)->asForm()
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
            } catch (\Throwable $e) {
                continue;
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

    public function getStudents(?string $nis = null, ?string $search = null, ?int $page = null, ?int $perPage = null): ?array
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
            'page' => $page,
            'per_page' => $perPage,
        ]);

        $response = Http::timeout(60)->withHeaders([
            'Accept' => 'application/json',
            'X-Client-ID' => $clientId,
            'X-Client-Secret' => $clientSecret,
        ])->get($baseUrl.'/api/v1/sijuna/students', $query);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    public function getTeachers(?string $nip = null, ?string $search = null, ?int $page = null, ?int $perPage = null): ?array
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
            'page' => $page,
            'per_page' => $perPage,
        ]);

        $response = Http::timeout(60)->withHeaders([
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

        $baseUrl = rtrim(config('services.sipintu.api_url', env('SIPINTU_API_URL', 'http://localhost:8000')), '/');
        $clientId = config('services.sipintu.client_id', env('SIPINTU_CLIENT_ID'));
        $clientSecret = config('services.sipintu.client_secret', env('SIPINTU_CLIENT_SECRET'));

        $missing = [];
        if (empty($baseUrl)) {
            $missing[] = 'SIPINTU_API_URL';
        }
        if (empty($clientId)) {
            $missing[] = 'SIPINTU_CLIENT_ID';
        }
        if (empty($clientSecret)) {
            $missing[] = 'SIPINTU_CLIENT_SECRET';
        }

        if (! empty($missing)) {
            return [
                'success' => false,
                'students' => 0,
                'teachers' => 0,
                'total' => 0,
                'message' => 'Konfigurasi SiPintu belum lengkap: parameter '.implode(', ', $missing).' belum diatur pada environment (.env).',
            ];
        }

        try {
            $fetchError = null;
            $users = $this->fetchAllUsersFromGateway($fetchError);

            if (empty($users)) {
                $cause = $fetchError ?: 'Tidak ada data pengguna siswa maupun guru yang berhasil diambil dari server SiPintu.';

                return [
                    'success' => false,
                    'students' => 0,
                    'teachers' => 0,
                    'total' => 0,
                    'message' => 'Sinkronisasi SiPintu gagal: '.$cause,
                ];
            }

            // 1. Kumpulkan semua identitas dan email untuk bulk lookup (mengeliminasi ribuan query SELECT)
            $identities = [];
            $emails = [];
            foreach ($users as $u) {
                if (! is_array($u)) {
                    continue;
                }
                $id = $this->extractIdentityNumber($u);
                if (! blank($id)) {
                    $identities[] = (string) $id;
                }
                $em = $this->firstNonEmptyValue($u, ['email', 'email_address', 'alamat_email', 'email_siswa', 'email_guru']);
                if (! blank($em)) {
                    $emails[] = (string) $em;
                }
            }

            $existingByIdentity = [];
            $existingByEmail = [];

            $identityChunks = array_chunk(array_values(array_unique($identities)), 1000);
            foreach ($identityChunks as $chunk) {
                User::query()
                    ->where('role', '!=', 'admin')
                    ->whereIn('identity_number', $chunk)
                    ->get()
                    ->each(function ($user) use (&$existingByIdentity, &$existingByEmail) {
                        $existingByIdentity[(string) $user->identity_number] = $user;
                        if (! blank($user->email)) {
                            $existingByEmail[(string) $user->email] = $user;
                        }
                    });
            }

            if (! empty($emails)) {
                $emailChunks = array_chunk(array_values(array_unique($emails)), 1000);
                foreach ($emailChunks as $chunk) {
                    User::query()
                        ->where('role', '!=', 'admin')
                        ->whereIn('email', $chunk)
                        ->get()
                        ->each(function ($user) use (&$existingByIdentity, &$existingByEmail) {
                            $existingByIdentity[(string) $user->identity_number] = $user;
                            $existingByEmail[(string) $user->email] = $user;
                        });
                }
            }

            // 2. Pre-hash password default dan siapkan memoization cache (mengeliminasi bcrypt bottleneck)
            $defaultPasswordHash = Hash::make('password');
            $hashCache = [
                'password' => $defaultPasswordHash,
            ];

            $studentCount = 0;
            $teacherCount = 0;

            // 3. Proses dalam batch untuk menjaga transaksi dan waktu eksekusi tetap aman
            $chunks = array_chunk($users, 250);

            foreach ($chunks as $chunk) {
                @set_time_limit(180);

                DB::transaction(function () use (
                    $chunk,
                    &$studentCount,
                    &$teacherCount,
                    &$existingByIdentity,
                    &$existingByEmail,
                    &$hashCache
                ) {
                    foreach ($chunk as $userData) {
                        if (! is_array($userData)) {
                            continue;
                        }

                        $identityNumber = $this->extractIdentityNumber($userData);
                        if (blank($identityNumber)) {
                            continue;
                        }

                        $email = $this->firstNonEmptyValue($userData, ['email', 'email_address', 'alamat_email', 'email_siswa', 'email_guru']);

                        $existingUser = $existingByIdentity[(string) $identityNumber]
                            ?? ($email ? ($existingByEmail[(string) $email] ?? null) : null);

                        // Cek apakah payload memiliki password eksplisit
                        $explicitPassword = $this->extractPlainPasswordOrNull($userData);
                        $birthDate = $this->firstNonEmptyValue($userData, ['birth_date', 'tanggal_lahir', 'date_of_birth', 'dob', 'tgl_lahir']);

                        $plainFallback = ! blank($explicitPassword)
                            ? $explicitPassword
                            : (! blank($birthDate) ? $birthDate : 'password');

                        $prehashed = null;
                        if (! $existingUser || empty($existingUser->password)) {
                            $prehashed = $hashCache[$plainFallback] ??= Hash::make($plainFallback);
                        }

                        $syncedUser = $this->syncUserFromGateway(
                            $userData,
                            $explicitPassword ?? '',
                            $prehashed,
                            $existingUser
                        );

                        if ($syncedUser) {
                            $existingByIdentity[(string) $syncedUser->identity_number] = $syncedUser;
                            if (! blank($syncedUser->email)) {
                                $existingByEmail[(string) $syncedUser->email] = $syncedUser;
                            }

                            if ($syncedUser->role === 'guru') {
                                $teacherCount++;
                            } else {
                                $studentCount++;
                            }
                        }
                    }
                });
            }

            $total = $studentCount + $teacherCount;

            return [
                'success' => true,
                'students' => $studentCount,
                'teachers' => $teacherCount,
                'total' => $total,
                'message' => "Sinkronisasi SiPintu selesai. Berhasil menyinkronkan {$total} pengguna ({$studentCount} siswa, {$teacherCount} guru).",
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'students' => 0,
                'teachers' => 0,
                'total' => 0,
                'message' => 'Sinkronisasi SiPintu gagal: '.$this->humanizeException($e),
            ];
        }
    }

    protected function fetchAllUsersFromGateway(?string &$errorReason = null): array
    {
        $baseUrl = rtrim(config('services.sipintu.api_url', env('SIPINTU_API_URL', 'http://localhost:8000')), '/');
        $clientId = config('services.sipintu.client_id', env('SIPINTU_CLIENT_ID'));
        $clientSecret = config('services.sipintu.client_secret', env('SIPINTU_CLIENT_SECRET'));

        $mergedUsers = [];
        $seenKeys = [];
        $errors = [];

        // 1. Fetch Students (HANYA siswa yang classroom-nya memiliki isi dan BUKAN null)
        $studentsErr = null;
        $studentsData = $this->fetchEndpointUsers($baseUrl, $clientId, $clientSecret, '/api/v1/sijuna/students', $studentsErr);
        if ($studentsErr) {
            $errors[] = $studentsErr;
        }

        if (! empty($studentsData)) {
            foreach ($studentsData as $item) {
                if (! is_array($item)) {
                    continue;
                }

                // Filter siswa: jika classroom == null atau kosong, JANGAN diambil!
                if (! $this->hasValidClassroom($item)) {
                    continue;
                }

                $item['role'] = 'siswa';
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
        }

        // 2. Fetch Teachers
        $teachersErr = null;
        $teachersData = $this->fetchEndpointUsers($baseUrl, $clientId, $clientSecret, '/api/v1/sijuna/teachers', $teachersErr);
        if ($teachersErr) {
            $errors[] = $teachersErr;
        }

        if (! empty($teachersData)) {
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
        }

        // 3. Fallback to generic endpoints if no users found from sijuna endpoints
        if (empty($mergedUsers)) {
            $fallbackEndpoints = [
                '/api/v1/users',
                '/api/v1/users/all',
                '/api/v1/sijuna/users',
                '/api/v1/students',
                '/api/v1/teachers',
            ];

            foreach ($fallbackEndpoints as $endpoint) {
                $fallbackErr = null;
                $fallbackData = $this->fetchEndpointUsers($baseUrl, $clientId, $clientSecret, $endpoint, $fallbackErr);
                if ($fallbackErr) {
                    $errors[] = $fallbackErr;
                }

                if (! empty($fallbackData)) {
                    foreach ($fallbackData as $item) {
                        if (! is_array($item)) {
                            continue;
                        }
                        $identityNumber = $this->extractIdentityNumber($item);
                        if (blank($identityNumber)) {
                            continue;
                        }

                        $role = $this->normalizeRole($item['role'] ?? $item['user_type'] ?? $item['type'] ?? null, $identityNumber);
                        // Filter: jika siswa dan classroom tidak valid, jangan diambil!
                        if ($role === 'siswa' && ! $this->hasValidClassroom($item)) {
                            continue;
                        }

                        $item['role'] = $role;
                        $key = (string) $identityNumber;
                        if (! isset($seenKeys[$key])) {
                            $seenKeys[$key] = true;
                            $mergedUsers[] = $item;
                        }
                    }

                    if (! empty($mergedUsers)) {
                        break;
                    }
                }
            }
        }

        if (empty($mergedUsers) && ! empty($errors)) {
            $errorReason = $this->consolidateErrors($errors, $baseUrl);
        }

        return $mergedUsers;
    }

    protected function fetchEndpointUsers(string $baseUrl, string $clientId, string $clientSecret, string $endpoint, ?string &$error = null): array
    {
        $page = 1;
        $perPage = 200;
        $allItems = [];

        do {
            try {
                $response = Http::timeout(60)->withHeaders([
                    'Accept' => 'application/json',
                    'X-Client-ID' => $clientId,
                    'X-Client-Secret' => $clientSecret,
                ])->get($baseUrl.$endpoint, [
                    'page' => $page,
                    'per_page' => $perPage,
                ]);
            } catch (\Throwable $e) {
                $error = $this->humanizeException($e, $baseUrl.$endpoint);
                break;
            }

            if (! $response->successful()) {
                $error = $this->humanizeResponseError($response, $baseUrl.$endpoint);
                break;
            }

            $payload = $response->json();
            if (! is_array($payload)) {
                break;
            }

            if (isset($payload['error']) || (isset($payload['success']) && $payload['success'] === false)) {
                $error = $payload['message'] ?? $payload['error'] ?? "Server SiPintu mengembalikan status gagal pada {$endpoint}.";
                break;
            }

            $items = $this->extractItemsFromPayload($payload);
            if (empty($items)) {
                break;
            }

            $allItems = array_merge($allItems, $items);

            $lastPage = $payload['last_page'] ?? $payload['lastPage'] ?? null;
            $total = $payload['total'] ?? $payload['count'] ?? null;

            if (is_numeric($lastPage) && (int) $lastPage > 1) {
                if ($page >= (int) $lastPage) {
                    break;
                }
            } elseif (! isset($payload['last_page']) && ! isset($payload['current_page'])) {
                break;
            }

            if (is_numeric($total) && count($allItems) >= (int) $total) {
                break;
            }

            $page++;
        } while ($page <= 100);

        return $allItems;
    }

    protected function extractItemsFromPayload(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        if (isset($payload['data']) && is_array($payload['data'])) {
            if (array_is_list($payload['data']) && ! empty($payload['data']) && is_array($payload['data'][0])) {
                return $payload['data'];
            }
            $nested = $this->extractCollectionFromResponse($payload['data'], 'users');
            if (! empty($nested)) {
                return $nested;
            }
        }

        if (array_is_list($payload) && ! empty($payload) && is_array($payload[0])) {
            return $payload;
        }

        return $this->extractCollectionFromResponse($payload, 'users');
    }

    protected function humanizeException(\Throwable $e, ?string $target = null): string
    {
        $msg = $e->getMessage();
        $lower = strtolower($msg);

        if (str_contains($lower, 'curl error 28') || str_contains($lower, 'timed out') || str_contains($lower, 'timeout')) {
            return 'Waktu koneksi ke server SiPintu habis (Connection Timeout). Periksa koneksi internet atau beban server SiPintu.';
        }

        if (str_contains($lower, 'connection refused') || str_contains($lower, 'curl error 7')) {
            return 'Koneksi ditolak oleh server SiPintu (Connection Refused). Pastikan server target aktif dan URL benar.';
        }

        if (str_contains($lower, 'could not resolve host') || str_contains($lower, 'curl error 6')) {
            return 'Domain server SiPintu tidak dapat dijangkau (DNS/Host tidak ditemukan). Periksa SIPINTU_API_URL pada .env.';
        }

        if (str_contains($lower, 'ssl') || str_contains($lower, 'curl error 60')) {
            return 'Terjadi masalah verifikasi sertifikat SSL pada koneksi SiPintu.';
        }

        return $msg ?: 'Terjadi kesalahan pada koneksi jaringan ke server SiPintu.';
    }

    protected function humanizeResponseError(\Illuminate\Http\Client\Response $response, string $url): string
    {
        $status = $response->status();
        $body = $response->json();
        $apiMessage = is_array($body) ? ($body['message'] ?? $body['error'] ?? null) : null;

        return match ($status) {
            401 => 'Autentikasi ke SiPintu gagal (401 Unauthorized): Client ID atau Client Secret tidak valid atau salah.'.($apiMessage ? " Pesan SiPintu: {$apiMessage}" : ''),
            403 => 'Akses ke SiPintu ditolak (403 Forbidden): Akun klien tidak memiliki izin akses.'.($apiMessage ? " Pesan SiPintu: {$apiMessage}" : ''),
            404 => "Endpoint API tidak ditemukan (404 Not Found) di {$url}.",
            500 => 'Server SiPintu mengalami kesalahan internal (500 Internal Server Error).'.($apiMessage ? " Pesan SiPintu: {$apiMessage}" : ''),
            502, 503, 504 => "Server SiPintu tidak dapat merespons atau sedang down (HTTP {$status}).",
            default => "Server SiPintu mengembalikan respons error (HTTP {$status}).".($apiMessage ? " Pesan: {$apiMessage}" : ''),
        };
    }

    protected function consolidateErrors(array $errors, string $baseUrl): string
    {
        foreach ($errors as $err) {
            if (str_contains($err, '401 Unauthorized') || str_contains($err, 'Autentikasi')) {
                return $err;
            }
        }

        foreach ($errors as $err) {
            if (str_contains($err, 'Timeout') || str_contains($err, 'Koneksi ditolak') || str_contains($err, 'DNS/Host')) {
                return $err;
            }
        }

        foreach ($errors as $err) {
            if (str_contains($err, '403 Forbidden') || str_contains($err, '500 Internal') || str_contains($err, 'down')) {
                return $err;
            }
        }

        $all404 = true;
        foreach ($errors as $err) {
            if (! str_contains($err, '404 Not Found')) {
                $all404 = false;
                break;
            }
        }
        if ($all404) {
            return "Endpoint API SiPintu tidak ditemukan (404 Not Found) pada server {$baseUrl}. Pastikan rute API SiPintu tersedia.";
        }

        return $errors[0] ?? 'Gagal mengambil data dari server SiPintu.';
    }

    public function syncUserFromGateway(
        array $payload,
        string $password = '',
        ?string $prehashedPassword = null,
        ?User $existingUser = null
    ): ?User {
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
            'classroom.nama',
            'classroom',
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
            'rombel',
            'nama_rombel',
        ]));

        $rawMajor = $this->normalizeNullableValue($this->firstNonEmptyValue($userData, ['major', 'jurusan', 'program_studi', 'study_program', 'jurusan_name']));

        $parsedClass = $this->parseClassAndMajor($rawClassValue, $rawMajor);
        $classGroup = $parsedClass['class_group'] ?? $this->normalizeClassGroup($rawClassValue);
        $major = $parsedClass['major'] ?? $rawMajor;

        $birthDate = $this->firstNonEmptyValue($userData, ['birth_date', 'tanggal_lahir', 'date_of_birth', 'dob', 'tgl_lahir']);
        $phone = $this->firstNonEmptyValue($userData, ['phone', 'phone_number', 'telephone', 'no_hp', 'telepon', 'mobile', 'nomor_telepon', 'telp']);
        $isClassEmpty = blank($classGroup) && blank($rawClassValue);
        $defaultActive = ! ($role === 'siswa' && $isClassEmpty);
        $isActive = (bool) $this->firstNonEmptyValue($userData, ['is_active', 'active', 'status_aktif', 'is_active_user', 'status_user'], $defaultActive);
        if ($role === 'siswa' && $isClassEmpty) {
            $isActive = false;
        }

        if (blank($identityNumber)) {
            return null;
        }

        $user = $existingUser ?? User::query()
            ->where('role', '!=', 'admin')
            ->where(function ($q) use ($identityNumber, $email) {
                $q->where('identity_number', (string) $identityNumber);
                if ($email) {
                    $q->orWhere('email', (string) $email);
                }
            })
            ->first();

        if ($user && $user->role === 'admin') {
            return $user;
        }

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

        if ($user) {
            // Jika user sudah ada dan sudah memiliki password:
            // Hanya update password bila pemanggil secara eksplisit memberikan input password baru
            if (! blank($password)) {
                if (empty($user->password) || ! Hash::check($password, $user->password)) {
                    $attributes['password'] = Hash::make($password);
                }
            } elseif (empty($user->password)) {
                if (! blank($prehashedPassword)) {
                    $attributes['password'] = $prehashedPassword;
                } else {
                    $effective = $this->extractPlainPassword($userData);
                    $fallback = ! blank($effective) ? $effective : (! blank($birthDate) ? $birthDate : $identityNumber);
                    $attributes['password'] = Hash::make($fallback);
                }
            }

            $user->fill($attributes);
            if ($user->isDirty()) {
                $user->save();
            }

            return $user;
        }

        // Akun baru (New User)
        if (! blank($prehashedPassword)) {
            $hashedPassword = $prehashedPassword;
        } else {
            $effectivePassword = ! blank($password) ? $password : $this->extractPlainPassword($userData);
            $fallbackPassword = ! blank($effectivePassword) ? $effectivePassword : (! blank($birthDate) ? $birthDate : $identityNumber);
            $hashedPassword = Hash::make($fallbackPassword);
        }

        return User::create(array_merge($attributes, [
            'password' => $hashedPassword,
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
                'X', '10' => '10',
                'XI', '11' => '11',
                'XII', '12' => '12',
                'XIII', '13' => '13',
                default => $match[1],
            };
        }

        if (preg_match('/\b(\d{1,2})\b/', $normalized, $match)) {
            return (string) (int) $match[1];
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
        if (is_array($value)) {
            $value = $value['name'] ?? $value['nama'] ?? $value['title'] ?? null;
        }

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

    public function hasValidClassroom(array $item): bool
    {
        if (array_key_exists('classroom', $item)) {
            $classroom = $item['classroom'];
            if (is_array($classroom)) {
                $name = $classroom['name'] ?? $classroom['nama'] ?? $classroom['title'] ?? null;
                if (! blank($name) && ! in_array(strtolower(trim((string) $name)), ['null', 'none', '-', 'n/a', 'na'], true)) {
                    return true;
                }
                $id = $classroom['id'] ?? null;
                if (! blank($id) && (string) $id !== '0') {
                    return true;
                }

                return false;
            }

            if (is_string($classroom) && ! blank($classroom)) {
                $val = strtolower(trim($classroom));
                if (! in_array($val, ['null', 'none', '-', 'n/a', 'na', '0'], true)) {
                    return true;
                }

                return false;
            }

            if (is_numeric($classroom) && (int) $classroom > 0) {
                return true;
            }

            if ($classroom === null || blank($classroom)) {
                return false;
            }
        }

        foreach ([
            'classroom_id',
            'classroom_name',
            'kelas',
            'kelas_siswa',
            'kelas_name',
            'class',
            'class_name',
            'rombel',
            'nama_rombel',
            'group',
            'group_name',
            'class_group',
            'class_group_name',
        ] as $key) {
            if (array_key_exists($key, $item) && ! blank($item[$key])) {
                $val = $item[$key];
                if (is_array($val)) {
                    $name = $val['name'] ?? $val['nama'] ?? null;
                    if (! blank($name) && ! in_array(strtolower(trim((string) $name)), ['null', 'none', '-', 'n/a', 'na'], true)) {
                        return true;
                    }
                } elseif (is_string($val)) {
                    $str = strtolower(trim($val));
                    if (! in_array($str, ['null', 'none', '-', 'n/a', 'na', '0'], true)) {
                        return true;
                    }
                } elseif (is_numeric($val) && (int) $val > 0) {
                    return true;
                }
            }
        }

        return false;
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

    protected function extractIdentityNumber(array $value): ?string
    {
        foreach (['identity_number', 'nis', 'nisn', 'nip', 'no_induk', 'nomor_induk', 'username', 'user_name'] as $key) {
            if (array_key_exists($key, $value) && ! blank($value[$key])) {
                $val = trim((string) $value[$key]);
                if ($val !== '' && $val !== '0' && strtolower($val) !== 'null' && strtolower($val) !== 'none') {
                    return $val;
                }
            }
        }

        // Fallback khusus guru tanpa NIP (misal nip = 0 / kosong): gunakan kode guru atau id
        if (! empty($value['kode'])) {
            return 'GURU-' . trim((string) $value['kode']);
        }

        if (! empty($value['id']) && (string) $value['id'] !== '0') {
            return 'GURU-' . trim((string) $value['id']);
        }

        return null;
    }

    protected function extractPlainPasswordOrNull(array $data): ?string
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

        return null;
    }

    protected function extractPlainPassword(array $data): string
    {
        return $this->extractPlainPasswordOrNull($data) ?? 'password';
    }
}
