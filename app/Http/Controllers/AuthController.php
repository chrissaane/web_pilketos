<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\SiPintuGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(protected SiPintuGatewayService $sipintuGateway)
    {
    }

    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $identity = trim((string) $request->input('identity'));
        $password = (string) $request->input('password');
        $remember = (bool) $request->boolean('remember');

        $fieldType = filter_var($identity, FILTER_VALIDATE_EMAIL) ? 'email' : 'identity_number';

        if (Auth::attempt([$fieldType => $identity, 'password' => $password, 'is_active' => true], $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            return match ($user->role) {
                'admin' => redirect()->route('admin.dashboard'),
                'guru' => redirect()->route('guru.dashboard'),
                'siswa' => redirect()->route('siswa.dashboard'),
                default => redirect()->route('home'),
            };
        }

        $gatewayData = $this->sipintuGateway->authenticate($identity, $password);

        if ($gatewayData) {
            $user = $this->sipintuGateway->syncUserFromGateway($gatewayData, $password);

            if ($user) {
                $credentialAttempts = [
                    ['identity_number' => $user->identity_number, 'password' => $password, 'is_active' => true],
                ];

                if ($user->email) {
                    $credentialAttempts[] = ['email' => $user->email, 'password' => $password, 'is_active' => true];
                }

                foreach ($credentialAttempts as $credentials) {
                    if (Auth::attempt($credentials, $remember)) {
                        $request->session()->regenerate();

                        return match ($user->role) {
                            'admin' => redirect()->route('admin.dashboard'),
                            'guru' => redirect()->route('guru.dashboard'),
                            'siswa' => redirect()->route('siswa.dashboard'),
                            default => redirect()->route('home'),
                        };
                    }
                }
            }
        }

        return back()->withErrors([
            'identity' => 'Identitas atau password salah, atau akun Anda tidak aktif.',
        ])->onlyInput('identity');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}