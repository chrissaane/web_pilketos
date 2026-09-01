@extends('layouts.app')

@section('title', 'Dashboard Siswa - PILKETOS')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8 space-y-6">
    <!-- Banner Selamat Datang -->
    <div class="p-8 rounded-3xl bg-gradient-to-r from-blue-600 to-indigo-700 text-white shadow-xl">
        <h1 class="text-3xl font-black">Selamat Datang, {{ $user->name }}! 👋</h1>
        <p class="text-blue-100 mt-2">NIS: {{ $user->identity_number }} | Kelas: {{ $user->class_group }} {{ $user->major }}</p>
    </div>

    <!-- Status Voting -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase">Status Hak Pilih</span>
            <h3 class="text-xl font-bold text-emerald-600 mt-1">Siswa Aktif</h3>
        </div>
        <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase">Status Suara</span>
            <h3 class="text-xl font-bold text-amber-500 mt-1">Belum Memilih</h3>
        </div>
        <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase">Jumlah Paslon</span>
            <h3 class="text-xl font-bold text-blue-600 mt-1">3 Pasangan Calon</h3>
        </div>
    </div>

    <!-- Tombol Logout -->
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-sm shadow-md transition-all">
            Keluar (Logout)
        </button>
    </form>
</div>
@endsection