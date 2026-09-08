@extends('layouts.app')

@section('title', 'Historis Pemilihan - PILKETOS')

@section('content')
    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Aktivitas Pemilih</p>
            <h1 class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">Historis Pemilihan</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Daftar pemilihan yang sudah Anda ikuti.</p>
        </div>

        @if ($history->isEmpty())
            <div
                class="border border-dashed border-slate-300 bg-white p-6 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400">
                Belum ada histori pemilihan.
            </div>
        @else
            <div class="space-y-4">
                @foreach ($history as $vote)
                    <article class="border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-lg font-bold text-slate-900 dark:text-white">
                                    {{ $vote->election?->title ?? 'Pemilihan' }}</p>
                                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                    Kandidat pilihan: <span
                                        class="font-semibold text-slate-700 dark:text-slate-200">{{ $vote->candidate?->name ?? 'Kandidat' }}</span>
                                </p>
                            </div>
                            <time class="text-xs text-slate-500 dark:text-slate-400"
                                datetime="{{ $vote->created_at?->toIso8601String() }}">
                                {{ $vote->created_at?->translatedFormat('d M Y, H:i') }}
                            </time>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
@endsection
