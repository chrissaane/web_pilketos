@extends('layouts.app')

@section('title', 'Login Portal - PILKETOS SMKN 1 Bangsri')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl p-8 border border-slate-200 dark:border-slate-800 shadow-xl space-y-6">
        
        <div class="text-center space-y-2">
            <div class="inline-flex w-14 h-14 rounded-2xl bg-blue-600 items-center justify-center text-white font-black text-2xl shadow-lg shadow-blue-500/30 mb-2">
                PIL
            </div>
            <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Portal Masuk</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Gunakan NIS (Siswa) atau Email (Guru/Admin)</p>
        </div>

        @if($errors->any())
            <div class="p-4 rounded-xl bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" class="space-y-4" x-data="{ showPassword: false }">
            @csrf

            <!-- Identitas -->
            <div>
                <label for="login" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">NIS / Email / Username</label>
                <input type="text" name="login" id="login" value="{{ old('login') }}" required placeholder="Masukkan NIS atau Email" 
                       class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-sm">
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Password</label>
                <div class="relative">
                    <input :type="showPassword ? 'text' : 'password'" name="password" id="password" required placeholder="Masukkan Tanggal Lahir (YYYY-MM-DD)" 
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-sm pr-10">
                    <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-3.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.018 10.018 0 012.122-.063c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m-1.558 1.558A9.969 9.969 0 0112 19c-1.39 0-2.733-.28-3.958-.788M3 3l18 18"/></svg>
                    </button>
                </div>
                <span class="text-[11px] text-slate-400 mt-1 block">*Untuk Siswa/Guru, password default adalah Tanggal Lahir (Format: YYYY-MM-DD). Contoh: 2008-05-12</span>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300 dark:border-slate-700 dark:bg-slate-800">
                    <span class="text-xs text-slate-600 dark:text-slate-400 font-medium">Ingat Saya</span>
                </label>
            </div>

            <button type="submit" class="w-full py-3.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-lg shadow-blue-500/30 transition-all">
                Masuk Sekarang
            </button>
        </form>

    </div>
</div>
@endsection