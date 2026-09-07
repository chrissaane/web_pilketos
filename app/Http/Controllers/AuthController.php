<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Services\SiPintuGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(protected SiPintuGatewayService $sipintuGateway)
    {
    }

    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->intended($this->redirectPathForUser(Auth::user()));
        }

        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $identity = trim((string) $request->input('identity'));
        $password = (string) $request->input('password');
        $remember = (bool) $request->boolean('remember');

        // 1. Cek kredensial di database lokal (berdasarkan identity_number atau email)
        $user = User::query()
            ->where(function ($query) use ($identity) {
                $query->where('identity_number', $identity)
                    ->orWhere('email', $identity);
            })
            ->first();

        if ($user) {
            $matched = false;
            foreach ($this->getPasswordCandidates($password) as $candidate) {
                if (Hash::check($candidate, $user->password) || $candidate === $user->password) {
                    $matched = true;
                    if ($candidate === $user->password) {
                        $user->password = Hash::make($candidate);
                        $user->save();
                    }
                    break;
                }
            }

            if ($matched) {
                if (! $user->is_active) {
                    return back()->withErrors([
                        'identity' => 'Akun Anda saat ini dinonaktifkan. Silakan hubungi administrator.',
                    ])->onlyInput('identity');
                }

                Auth::login($user, $remember);
                $request->session()->regenerate();

                return redirect()->intended($this->redirectPathForUser($user));
            }
        }

        // 2. Fallback autentikasi langsung ke gateway SiPintu jika data lokal belum ada / belum cocok
        $gatewayData = $this->sipintuGateway->authenticate($identity, $password);

        if ($gatewayData) {
            $user = $this->sipintuGateway->syncUserFromGateway($gatewayData, $password);

            if ($user) {
                if (! $user->is_active) {
                    return back()->withErrors([
                        'identity' => 'Akun Anda saat ini dinonaktifkan. Silakan hubungi administrator.',
                    ])->onlyInput('identity');
                }

                Auth::login($user, $remember);
                $request->session()->regenerate();

                return redirect()->intended($this->redirectPathForUser($user));
            }
        }

        // 3. Pesan error spesifik jika user lokal ditemukan namun password salah
        if ($user) {
            return back()->withErrors([
                'identity' => 'Password yang Anda masukkan salah.',
            ])->onlyInput('identity');
        }

        return back()->withErrors([
            'identity' => 'Identitas atau password salah, atau akun Anda belum terdaftar.',
        ])->onlyInput('identity');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    protected function redirectPathForUser(User $user): string
    {
        return match ($user->role) {
            'admin' => route('admin.dashboard'),
            'guru' => route('guru.dashboard'),
            'siswa' => route('siswa.dashboard'),
            default => route('home'),
        };
    }

    /**
     * Mendukung variasi format tanggal lahir untuk password default siswa/guru
     * Contoh: DD/MM/YYYY, DD-MM-YYYY, YYYY-MM-DD, DDMMYYYY, YYYYMMDD
     */
    protected function getPasswordCandidates(string $password): array
    {
        $candidates = [$password];
        $clean = trim($password);

        if (preg_match('/^(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{4})$/', $clean, $m)) {
            $day = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            $year = $m[3];

            if (checkdate((int) $month, (int) $day, (int) $year)) {
                $candidates[] = "{$year}-{$month}-{$day}";
                $candidates[] = "{$day}-{$month}-{$year}";
                $candidates[] = "{$day}/{$month}/{$year}";
                $candidates[] = "{$year}{$month}{$day}";
                $candidates[] = "{$day}{$month}{$year}";
            }
        } elseif (preg_match('/^(\d{4})[\/\-\.](\d{1,2})[\/\-\.](\d{1,2})$/', $clean, $m)) {
            $year = $m[1];
            $month = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            $day = str_pad($m[3], 2, '0', STR_PAD_LEFT);

            if (checkdate((int) $month, (int) $day, (int) $year)) {
                $candidates[] = "{$year}-{$month}-{$day}";
                $candidates[] = "{$day}-{$month}-{$year}";
                $candidates[] = "{$day}/{$month}/{$year}";
                $candidates[] = "{$year}{$month}{$day}";
                $candidates[] = "{$day}{$month}{$year}";
            }
        } elseif (preg_match('/^(19\d{2}|20\d{2})(\d{2})(\d{2})$/', $clean, $m)) {
            // YYYYMMDD
            $year = $m[1];
            $month = $m[2];
            $day = $m[3];

            if (checkdate((int) $month, (int) $day, (int) $year)) {
                $candidates[] = "{$year}-{$month}-{$day}";
                $candidates[] = "{$day}-{$month}-{$year}";
                $candidates[] = "{$day}/{$month}/{$year}";
                $candidates[] = "{$year}{$month}{$day}";
                $candidates[] = "{$day}{$month}{$year}";
            }
        } elseif (preg_match('/^(\d{2})(\d{2})(19\d{2}|20\d{2})$/', $clean, $m)) {
            // DDMMYYYY
            $day = $m[1];
            $month = $m[2];
            $year = $m[3];

            if (checkdate((int) $month, (int) $day, (int) $year)) {
                $candidates[] = "{$year}-{$month}-{$day}";
                $candidates[] = "{$day}-{$month}-{$year}";
                $candidates[] = "{$day}/{$month}/{$year}";
                $candidates[] = "{$year}{$month}{$day}";
                $candidates[] = "{$day}{$month}{$year}";
            }
        }

        return array_values(array_unique($candidates));
    }
}