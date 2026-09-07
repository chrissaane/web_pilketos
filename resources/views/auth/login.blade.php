@extends('layouts.app')

@section('title', 'Login - PILKETOS SMKN 1 Bangsri')

@push('styles')
    <style>
        .login-editorial {
            min-height: 80vh;
            padding: 78px 28px;
            background: #f5f7fc;
            color: #151a33;
            font-family: 'Trebuchet MS', 'Segoe UI', sans-serif;
        }

        .login-editorial .login-panel {
            position: relative;
            max-width: 520px;
            margin: 0 auto;
            padding: 42px;
            border: 1px solid rgba(25, 34, 70, .14);
            border-top: 1px solid #2636ff;
            background: radial-gradient(ellipse at 75% 0%, rgba(38, 54, 255, .12), transparent 46%), #ffffff;
            box-shadow: 0 18px 40px rgba(25, 34, 70, .08);
        }

        .login-editorial .login-title {
            margin-top: 22px;
            color: #151a33;
            font-size: 36px;
            font-weight: 400;
            letter-spacing: -.05em;
        }

        .login-editorial .login-copy,
        .login-editorial .login-note {
            color: #626b86;
        }

        .login-editorial .login-label {
            display: block;
            margin-bottom: 8px;
            color: #626b86;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .login-editorial .login-input {
            width: 100%;
            border: 1px solid rgba(25, 34, 70, .18);
            border-radius: 0;
            background: #f8f9fd;
            padding: 13px 15px;
            color: #151a33;
            outline: none;
        }

        .login-editorial .login-input:focus {
            border-color: #2636ff;
            box-shadow: 0 0 0 2px rgba(38, 54, 255, .12);
        }

        .login-editorial .login-submit {
            width: 100%;
            border: 1px solid #2636ff;
            border-radius: 0;
            background: #2636ff;
            padding: 14px;
            color: #fff;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            transition: background .2s ease, border-color .2s ease;
        }

        .login-editorial .login-submit:hover {
            border-color: #1ee6e1;
            background: #1ee6e1;
            color: #101116;
        }

        .login-editorial .login-error {
            border: 1px solid rgba(225, 29, 72, .2);
            background: #fff1f2;
            padding: 13px;
            color: #be123c;
            font-size: 13px;
        }

        .login-editorial .login-success {
            border: 1px solid rgba(16, 185, 129, .25);
            background: #ecfdf5;
            padding: 13px;
            color: #065f46;
            font-size: 13px;
        }

        html.dark .login-editorial {
            background: #101116;
            color: #f3f5ff;
        }

        html.dark .login-editorial .login-panel {
            border-color: rgba(255, 255, 255, .14);
            border-top-color: #1ee6e1;
            background: radial-gradient(ellipse at 75% 0%, rgba(38, 54, 255, .42), transparent 46%), #111217;
            box-shadow: none;
        }

        html.dark .login-editorial .login-title {
            color: #f3f5ff;
        }

        html.dark .login-editorial .login-copy,
        html.dark .login-editorial .login-note,
        html.dark .login-editorial .login-label {
            color: #9ba3bd;
        }

        html.dark .login-editorial .login-input {
            border-color: rgba(255, 255, 255, .14);
            background: #1b1e2c;
            color: #f3f5ff;
        }

        html.dark .login-editorial .login-input:focus {
            border-color: #1ee6e1;
            box-shadow: 0 0 0 2px rgba(30, 230, 225, .12);
        }

        html.dark .login-editorial .login-error {
            border-color: rgba(255, 111, 111, .25);
            background: rgba(255, 111, 111, .08);
            color: #ff9d9d;
        }

        html.dark .login-editorial .login-success {
            border-color: rgba(52, 211, 153, .25);
            background: rgba(16, 185, 129, .08);
            color: #6ee7b7;
        }

        @media (max-width: 640px) {
            .login-editorial {
                padding: 45px 15px;
            }

            .login-editorial .login-panel {
                padding: 28px 20px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="login-editorial flex items-center justify-center">
        <div class="login-panel w-full space-y-6">

            <div class="space-y-2">
                <h2 class="login-title">Portal Masuk</h2>
            </div>

            @if ($errors->any())
                <div class="login-error">
                    {{ $errors->first() }}
                </div>
            @endif

            @if (session('error'))
                <div class="login-error">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('success') || session('status'))
                <div class="login-success">
                    {{ session('success') ?? session('status') }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-5" x-data="{ showPassword: false }">
                @csrf

                <!-- Input NIS / Email -->
                <div>
                    <label class="login-label">
                        NIS / Email / Username
                    </label>
                    <input type="text" name="identity" value="{{ old('identity') }}" required autofocus
                        placeholder="NIS / NIP / Gmail" class="login-input">
                </div>

                <!-- Input Password / Tanggal Lahir -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="login-label mb-0">
                            Password / Tanggal Lahir
                        </label>
                        <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">Contoh: 12/05/2008 atau 2008-05-12</span>
                    </div>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" name="password" required placeholder="00/00/0000 atau password Anda"
                            class="login-input pr-20">

                        <button type="button" @click="showPassword = !showPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-500 hover:text-blue-600 dark:hover:text-cyan-300">
                            <span x-text="showPassword ? 'Sembunyikan' : 'Lihat'"></span>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between text-sm">
                    <label class="login-copy flex cursor-pointer items-center gap-2">
                        <input type="checkbox" name="remember"
                            class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span>Ingat Saya</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="login-submit">
                    Masuk Sekarang
                </button>
            </form>

        </div>
    </div>
@endsection
