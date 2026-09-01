@extends('layouts.app')

@section('title', 'PILKETOS - SMKN 1 Bangsri')

@section('content')
<div class="min-h-[85vh] flex flex-col items-center justify-center text-center px-4 py-12">
    
    <!-- Badge -->
    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-50 dark:bg-blue-950/50 border border-blue-200 dark:border-blue-800 text-blue-600 dark:text-blue-400 text-xs font-bold mb-6">
        <span class="w-2 h-2 rounded-full bg-blue-600 animate-ping"></span>
        Sistem Pemilihan Ketua OSIS Resmi SMKN 1 Bangsri
    </div>

    <!-- Title -->
    <h1 class="text-4xl sm:text-6xl font-black text-slate-900 dark:text-white tracking-tight leading-tight max-w-4xl">
        Suara Anda Menentukan Masa Depan <span class="text-blue-600">SMKN 1 Bangsri</span>
    </h1>

    <!-- Subtitle -->
    <p class="mt-4 text-slate-600 dark:text-slate-400 text-base sm:text-lg max-w-2xl">
        Gunakan hak pilihmu secara jujur, adil, dan transparan melalui platform e-Voting Pilketos modern.
    </p>

    <!-- Action Buttons -->
    <div class="mt-8 flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
        @auth
            <a href="{{ route('dashboard') }}" 
                class="px-8 py-4 rounded-2xl font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-xl shadow-blue-500/30 transition-all flex items-center justify-center gap-2">
                Masuk ke Dashboard
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        @else
            <a href="{{ route('login') }}" 
                class="px-8 py-4 rounded-2xl font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-xl shadow-blue-500/30 transition-all flex items-center justify-center gap-2">
                Masuk Portal Pemilih
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
            </a>
        @endauth
    </div>

</div>
@endsection