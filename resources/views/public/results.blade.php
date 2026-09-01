@extends('layouts.app')

@section('title', 'Hasil Pemilihan')

@push('styles')
    <style>
        .results-editorial {
            min-height: 100%;
            background: #101116;
            color: #f3f5ff;
            font-family: 'Trebuchet MS', 'Segoe UI', sans-serif;
        }

        .results-editorial .results-shell {
            max-width: 1180px;
            margin: 0 auto;
            padding: 78px 28px;
        }

        .results-editorial .results-panel,
        .results-editorial .results-election {
            border: 1px solid rgba(255, 255, 255, .14);
            border-radius: 0;
            background: radial-gradient(ellipse at 75% 0%, rgba(38, 54, 255, .35), transparent 42%), #111217;
        }

        .results-editorial .results-panel {
            padding: 42px;
        }

        .results-editorial .results-kicker {
            color: #1ee6e1;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .3em;
            text-transform: uppercase;
        }

        .results-editorial .results-title {
            margin-top: 14px;
            font-size: clamp(3rem, 7vw, 6rem);
            font-weight: 400;
            letter-spacing: -.08em;
            line-height: .88;
        }

        .results-editorial .results-election {
            margin-top: 32px;
            padding: 25px;
            background: rgba(23, 25, 36, .76);
        }

        .results-editorial .results-election h2 {
            color: #f3f5ff;
            font-size: 25px;
            font-weight: 400;
        }

        .results-editorial .results-muted {
            color: #9ba3bd;
        }

        .results-editorial .results-stat {
            border-top: 1px solid #1ee6e1;
            border-radius: 0;
            background: rgba(27, 30, 44, .8);
        }

        .results-editorial .results-stat p:last-child {
            color: #f3f5ff;
        }

        .results-editorial .results-status {
            border: 1px solid rgba(30, 230, 225, .45);
            border-radius: 0;
            background: transparent;
            color: #1ee6e1;
        }

        .results-editorial .results-track {
            height: 3px;
            border-radius: 0;
            background: rgba(255, 255, 255, .12);
        }

        .results-editorial .results-value {
            background: linear-gradient(90deg, #2636ff, #1ee6e1);
        }

        .results-editorial .results-candidate-photo,
        .results-editorial .results-candidate-fallback {
            width: 48px;
            height: 48px;
            flex: 0 0 48px;
            border: 1px solid rgba(255, 255, 255, .18);
            object-fit: cover;
        }

        .results-editorial .results-candidate-fallback {
            display: grid;
            place-items: center;
            background: #1b1e2c;
            color: #1ee6e1;
            font-size: 18px;
            font-weight: 700;
        }

        html:not(.dark) .results-editorial {
            background: #f5f7fc;
            color: #151a33;
        }

        html:not(.dark) .results-editorial .results-panel,
        html:not(.dark) .results-editorial .results-election {
            border-color: rgba(25, 34, 70, .14);
            background: radial-gradient(ellipse at 75% 0%, rgba(38, 54, 255, .1), transparent 42%), #f8f9fe;
        }

        html:not(.dark) .results-editorial .results-election {
            background: rgba(255, 255, 255, .86);
        }

        html:not(.dark) .results-editorial .results-election h2,
        html:not(.dark) .results-editorial .results-stat p:last-child {
            color: #151a33;
        }

        html:not(.dark) .results-editorial .results-muted {
            color: #626b86;
        }

        html:not(.dark) .results-editorial .results-stat {
            border-top-color: #2636ff;
            background: #eef2ff;
        }

        html:not(.dark) .results-editorial .results-track {
            background: rgba(25, 34, 70, .14);
        }

        html:not(.dark) .results-editorial .results-candidate-fallback {
            border-color: rgba(25, 34, 70, .14);
            background: #eef2ff;
            color: #2636ff;
        }

        @media (max-width: 640px) {
            .results-editorial .results-shell {
                padding: 45px 15px;
            }

            .results-editorial .results-panel {
                padding: 25px 18px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="results-editorial">
        <div class="results-shell">
            <div class="results-panel">
                <p class="results-kicker">03 / Hasil Pemilihan</p>
                <h1 class="results-title">Statistik<br><em style="font-family:Georgia,serif;">suara.</em></h1>

                <div class="mt-10 space-y-8">
                    @forelse($elections as $election)
                        @php
                            $totalVotes = $election->votes_count;
                            $participation = $voterCount > 0 ? round(($totalVotes / $voterCount) * 100) : 0;
                        @endphp
                        <section class="results-election">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                                <div>
                                    <h2>{{ $election->title }}</h2>
                                    <p class="results-muted mt-2 text-sm">Tahun {{ $election->year }}</p>
                                </div>
                                <span class="results-status w-fit px-3 py-2 text-xs font-semibold uppercase">
                                    {{ $election->current_status }}
                                </span>
                            </div>

                            <div class="mt-6 grid gap-4 sm:grid-cols-3">
                                <div class="results-stat rounded-2xl p-4">
                                    <p class="results-muted text-xs font-semibold uppercase tracking-[0.2em]">
                                        Total Suara</p>
                                    <p class="mt-2 text-3xl font-black">{{ $totalVotes }}</p>
                                </div>
                                <div class="results-stat rounded-2xl p-4">
                                    <p class="results-muted text-xs font-semibold uppercase tracking-[0.2em]">
                                        Pemilih</p>
                                    <p class="mt-2 text-3xl font-black">{{ $voterCount }}</p>
                                </div>
                                <div class="results-stat rounded-2xl p-4">
                                    <p class="results-muted text-xs font-semibold uppercase tracking-[0.2em]">
                                        Partisipasi</p>
                                    <p class="mt-2 text-3xl font-black">{{ $participation }}%</p>
                                </div>
                            </div>

                            <div class="mt-7 space-y-5">
                                @forelse($election->candidates as $candidate)
                                    @php($candidatePercent = $totalVotes > 0 ? round(($candidate->votes_count / $totalVotes) * 100) : 0)
                                    <div class="flex items-center gap-3">
                                        @if ($candidate->photo_url)
                                            <img src="{{ $candidate->photo_url }}" alt="Foto {{ $candidate->name }}"
                                                class="results-candidate-photo" loading="lazy">
                                        @else
                                            <span class="results-candidate-fallback"
                                                aria-hidden="true">{{ str($candidate->name)->substr(0, 1)->upper() }}</span>
                                        @endif
                                        <div class="min-w-0 flex-1">
                                            <div class="mb-2 flex items-center justify-between gap-4 text-sm">
                                                <span class="font-semibold">{{ $candidate->name }}</span>
                                                <span class="results-muted whitespace-nowrap">{{ $candidate->votes_count }}
                                                    suara ({{ $candidatePercent }}%)</span>
                                            </div>
                                            <div class="results-track overflow-hidden">
                                                <div class="results-value h-full" style="width: {{ $candidatePercent }}%">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p
                                        class="rounded-2xl bg-slate-50 p-5 text-sm text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                        Belum ada data kandidat.</p>
                                @endforelse
                            </div>
                        </section>
                    @empty
                        <p class="rounded-2xl bg-slate-50 p-5 text-sm text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                            Belum ada data pemilihan.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
