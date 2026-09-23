<?php

namespace App\Http\Controllers;

use App\Services\SiPintuGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SiPintuOauthController extends Controller
{
    public function __construct(protected SiPintuGatewayService $sipintuGateway) {}

    public function redirect(Request $request)
    {
        $state = Str::random(40);
        $request->session()->put('sipintu_oauth_state', $state);

        $authorizationUrl = $this->sipintuGateway->buildAuthorizationUrl($state);

        if (! $authorizationUrl) {
            return redirect()->route('login')->withErrors([
                'identity' => 'Konfigurasi OAuth SiPintu belum lengkap.',
            ]);
        }

        return redirect()->away($authorizationUrl);
    }

    public function callback(Request $request)
    {
        $code = $request->query('code');
        $state = $request->query('state');

        if (! $code) {
            return redirect()->route('login')->withErrors([
                'identity' => 'Callback SiPintu tidak membawa authorization code.',
            ]);
        }

        $storedState = $request->session()->get('sipintu_oauth_state');

        // Validasi state jika keduanya (session lokal & callback dari server) ada isinya.
        // Jika user masuk langsung dari portal SiPintu (IdP-Initiated SSO), parameter state memang tidak dikirimkan.
        if ($storedState && $state && ! hash_equals((string) $storedState, (string) $state)) {
            return redirect()->route('login')->withErrors([
                'identity' => 'State OAuth SiPintu tidak valid.',
            ]);
        }

        // Hapus state dari session setelah divalidasi
        $request->session()->forget('sipintu_oauth_state');

        $tokenData = $this->sipintuGateway->exchangeCodeForToken($code);

        if (! $tokenData || empty($tokenData['access_token'])) {
            return redirect()->route('login')->withErrors([
                'identity' => 'Gagal menukarkan code SiPintu menjadi access token.',
            ]);
        }

        $userData = $this->sipintuGateway->fetchUserProfile($tokenData['access_token']);

        if (! $userData) {
            return redirect()->route('login')->withErrors([
                'identity' => 'Gagal mengambil profil user dari SiPintu.',
            ]);
        }

        $password = $tokenData['password']
            ?? $userData['password']
            ?? $userData['login_password']
            ?? $userData['password_hash']
            ?? Str::random(32);

        $user = $this->sipintuGateway->syncUserFromGateway(['user' => $userData], $password);

        if (! $user) {
            return redirect()->route('login')->withErrors([
                'identity' => 'Data user dari SiPintu tidak valid.',
            ]);
        }

        if (! $user->is_active) {
            return redirect()->route('login')->withErrors([
                'identity' => 'Akun Anda saat ini dinonaktifkan. Silakan hubungi administrator.',
            ]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'guru' => redirect()->route('guru.dashboard'),
            'karyawan' => redirect()->route('guru.dashboard'),
            'siswa' => redirect()->route('siswa.dashboard'),
            'alumni' => redirect()->route('home')->with('error', 'Akun alumni tidak memiliki akses ke bilik pemilihan.'),
            default => redirect()->route('home'),
        };
    }

    public function syncUser(Request $request)
    {
        $clientId = (string) $request->header('X-Client-ID');
        $clientSecret = (string) $request->header('X-Client-Secret');

        if (! hash_equals((string) config('services.sipintu.client_id'), $clientId)
            || ! hash_equals((string) config('services.sipintu.client_secret'), $clientSecret)) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $payload = $request->all();
        $userData = $payload['user'] ?? $payload['data']['user'] ?? $payload['data'] ?? $payload;

        if (! is_array($userData)) {
            return response()->json(['message' => 'Payload user tidak valid.'], 422);
        }

        $identity = collect([
            'identity_number',
            'nis',
            'nip',
            'nisn',
            'no_induk',
            'nomor_induk',
            'username',
        ])->first(fn (string $key) => filled($userData[$key] ?? null));

        if (! $identity) {
            return response()->json(['message' => 'Identitas user wajib dikirim.'], 422);
        }

        if (array_key_exists('graduated', $userData) || array_key_exists('is_graduated', $userData)) {
            $isGraduated = filter_var($userData['graduated'] ?? $userData['is_graduated'], FILTER_VALIDATE_BOOLEAN);
            $userData['is_active'] = ! $isGraduated;
            $userData['role'] = $isGraduated ? 'alumni' : 'siswa';
        }

        $user = $this->sipintuGateway->syncUserFromGateway(['user' => $userData]);

        if (! $user) {
            return response()->json(['message' => 'Data user SiPintu tidak dapat disinkronkan.'], 422);
        }

        return response()->json([
            'status' => 'ok',
            'identity_number' => $user->identity_number,
            'is_active' => (bool) $user->is_active,
            'updated' => true,
        ]);
    }
}
