<?php

namespace App\Http\Controllers;

use App\Services\SiPintuGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SiPintuOauthController extends Controller
{
    public function __construct(protected SiPintuGatewayService $sipintuGateway)
    {
    }

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

        if ($storedState && $state !== $storedState) {
            return redirect()->route('login')->withErrors([
                'identity' => 'State OAuth SiPintu tidak valid.',
            ]);
        }

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

        Auth::login($user, true);
        $request->session()->forget('sipintu_oauth_state');

        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'guru' => redirect()->route('guru.dashboard'),
            'siswa' => redirect()->route('siswa.dashboard'),
            default => redirect()->route('home'),
        };
    }
}
