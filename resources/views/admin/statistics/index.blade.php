@extends('layouts.admin')

@push('head')
    <style>
        .admin-statistics {
            --stats-ink: #151a33;
            --stats-muted: #626b86;
            --stats-line: rgba(25, 34, 70, .14);
            --stats-accent: #2636ff;
            --stats-cyan: #1ee6e1;
        }

        .admin-statistics .stats-heading {
            color: var(--stats-ink);
            font-family: 'Trebuchet MS', 'Segoe UI', sans-serif;
        }

        .admin-statistics .stats-kicker {
            color: var(--stats-accent);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .3em;
            text-transform: uppercase;
        }

        .admin-statistics .stats-panel,
        .admin-statistics .stats-metric {
            border: 1px solid var(--stats-line) !important;
            border-radius: 0 !important;
            background: #ffffff !important;
            box-shadow: 0 12px 28px rgba(25, 34, 70, .06) !important;
        }

        .admin-statistics .stats-panel {
            border-top-color: var(--stats-cyan) !important;
            background: radial-gradient(ellipse at 75% 0%, rgba(38, 54, 255, .12), transparent 42%), #ffffff !important;
        }

        .admin-statistics .stats-metric {
            border-top-color: var(--stats-cyan) !important;
        }

        .admin-statistics .stats-track {
            height: 3px;
            border-radius: 0;
            background: rgba(25, 34, 70, .1) !important;
        }

        .admin-statistics .stats-value {
            height: 100%;
            border-radius: 0;
            background: linear-gradient(90deg, #2636ff, #1ee6e1) !important;
        }

        .admin-statistics .stats-select {
            border-color: var(--stats-line) !important;
            border-radius: 0 !important;
            background: #f8f9fd !important;
            color: var(--stats-ink) !important;
        }

        .admin-statistics .stats-candidate-photo,
        .admin-statistics .stats-candidate-fallback {
            width: 48px;
            height: 48px;
            flex: 0 0 48px;
            border: 1px solid var(--stats-line);
            object-fit: cover;
        }

        .admin-statistics .stats-candidate-fallback {
            display: grid;
            place-items: center;
            background: #f1f3f9;
            color: var(--stats-accent);
            font-size: 18px;
            font-weight: 700;
        }

        html.dark .admin-statistics {
            --stats-ink: #f3f5ff;
            --stats-muted: #9ba3bd;
            --stats-line: rgba(255, 255, 255, .14);
            --stats-accent: #1ee6e1;
        }

        html.dark .admin-statistics .stats-heading,
        html.dark .admin-statistics .text-slate-900,
        html.dark .admin-statistics .text-slate-800 {
            color: var(--stats-ink) !important;
        }

        html.dark .admin-statistics .text-slate-500,
        html.dark .admin-statistics .text-slate-400 {
            color: var(--stats-muted) !important;
        }

        html.dark .admin-statistics .stats-panel,
        html.dark .admin-statistics .stats-metric {
            border-color: var(--stats-line) !important;
            background: #171924 !important;
            box-shadow: none !important;
        }

        html.dark .admin-statistics .stats-panel {
            border-top-color: var(--stats-cyan) !important;
            background: radial-gradient(ellipse at 75% 0%, rgba(38, 54, 255, .35), transparent 42%), #111217 !important;
        }

        html.dark .admin-statistics .stats-track {
            background: rgba(255, 255, 255, .12) !important;
        }

        html.dark .admin-statistics .stats-select {
            border-color: var(--stats-line) !important;
            background: #1b1e2c !important;
            color: var(--stats-ink) !important;
        }
    </style>
@endpush

@section('content')
    <div class="admin-statistics space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="stats-kicker">03 / Statistik</p>
                <h2 class="stats-heading mt-3 text-3xl font-black">Statistik Card</h2>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Pilih card untuk melihat perbandingan suara setiap
                    kandidat.</p>
            </div>
        </div>

        <div
            class="stats-panel rounded-[28px] border border-slate-200 bg-white p-7 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-10">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.25em] text-blue-600 dark:text-blue-400">Hasil
                        Pemilihan</p>
                    <h3 class="mt-3 text-3xl font-black text-slate-900 dark:text-white">Statistik Suara</h3>
                    <p class="mt-2 text-base text-slate-500 dark:text-slate-400">
                        {{ optional($selectedElection)->title ?? 'Belum ada card' }}</p>
                </div>
                <form method="GET" class="w-full sm:w-64">
                    <label for="election_id"
                        class="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Pilih
                        Card</label>
                    <select id="election_id" name="election_id" onchange="this.form.submit()"
                        class="stats-select w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200">
                        @foreach ($elections as $election)
                            <option value="{{ $election->id }}" @selected(optional($selectedElection)->id === $election->id)>{{ $election->title }}
                                ({{ $election->year }})
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="stats-metric rounded-2xl bg-blue-50 p-5 dark:bg-blue-950/30">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-600 dark:text-blue-400">Total Suara
                    </p>
                    <p data-stat-total class="mt-3 text-4xl font-black text-slate-900 dark:text-white">{{ $totalVotes }}
                    </p>
                </div>
                <div class="stats-metric rounded-2xl bg-rose-50 p-5 dark:bg-rose-950/30">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-600 dark:text-rose-400">Belum
                        Memilih
                    </p>
                    <p data-stat-not-voted class="mt-3 text-4xl font-black text-slate-900 dark:text-white">
                        {{ $notVotedCount }}</p>
                </div>
                <div class="stats-metric rounded-2xl bg-emerald-50 p-5 dark:bg-emerald-950/30">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-600 dark:text-emgit commit -m "Remove
                        debug scripts blocking CI"erald-400">
                        Pemilih</p>
                    <p data-stat-voters class="mt-3 text-4xl font-black text-slate-900 dark:text-white">{{ $voterCount }}
                    </p>
                </div>
                <div class="stats-metric rounded-2xl bg-amber-50 p-5 dark:bg-amber-950/30">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-600 dark:text-amber-400">
                        Partisipasi</p>
                    <p data-stat-participation class="mt-3 text-4xl font-black text-slate-900 dark:text-white">
                        {{ rtrim(rtrim(number_format($participation, 2, ',', '.'), '0'), ',') }}%</p>
                </div>
            </div>

            <div class="mt-8 border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-900">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">Tampilkan total suara ke public</p>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Total dan jumlah suara kandidat akan
                            terlihat mulai waktu yang ditentukan.</p>
                    </div>
                    <form method="POST" action="{{ route('admin.statistics.results_visibility') }}"
                        class="flex w-full flex-col gap-3 sm:flex-row sm:items-end lg:w-auto">
                        @csrf
                        <input type="hidden" name="election_id" value="{{ optional($selectedElection)->id }}">
                        <div>
                            <label for="results_publish_at"
                                class="mb-1 block text-xs font-bold uppercase tracking-[0.15em] text-slate-500 dark:text-slate-400">Tanggal
                                dan jam tampil</label>
                            <input id="results_publish_at" name="results_publish_at" type="datetime-local"
                                value="{{ old('results_publish_at', $resultsPublishAt ? \Illuminate\Support\Carbon::parse($resultsPublishAt)->format('Y-m-d\\TH:i') : '') }}"
                                class="stats-select w-full border px-3 py-2 text-sm sm:w-64">
                        </div>
                        <label
                            class="flex items-center gap-2 pb-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                            <input type="checkbox" name="show_vote_counts_public" value="1"
                                @checked($showVoteCountsPublic)>
                            Aktifkan
                        </label>
                        <button type="submit"
                            class="bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Simpan
                            Jadwal</button>
                    </form>
                </div>
                @if (!$showVoteCountsPublic)
                    <p class="mt-3 text-xs text-amber-600">Tampilan total suara untuk card ini sedang dinonaktifkan.</p>
                @elseif ($resultsPublishAt)
                    <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">Jadwal aktif:
                        {{ \Illuminate\Support\Carbon::parse($resultsPublishAt)->translatedFormat('d F Y, H:i') }}</p>
                @else
                    <p class="mt-3 text-xs text-emerald-600">Total suara card ini langsung terlihat di public.</p>
                @endif
            </div>

            <div class="mt-8 space-y-5">
                @forelse($candidateRows as $candidate)
                    @php($candidatePercent = $totalVotes > 0 ? round(($candidate->votes_count / $totalVotes) * 100) : 0)
                    <div class="flex items-center gap-3" data-stat-candidate="{{ $candidate->id }}">
                        @if ($candidate->photo_url)
                            <img src="{{ $candidate->photo_url }}" alt="Foto {{ $candidate->name }}"
                                class="stats-candidate-photo" loading="lazy">
                        @else
                            <span class="stats-candidate-fallback"
                                aria-hidden="true">{{ str($candidate->name)->substr(0, 1)->upper() }}</span>
                        @endif
                        <div class="min-w-0 flex-1">
                            <div class="mb-2 flex items-center justify-between gap-4 text-sm">
                                <span class="font-semibold">{{ $candidate->name }}</span>
                                <span data-stat-candidate-label
                                    class="stats-muted whitespace-nowrap">{{ $candidate->votes_count }} suara
                                    ({{ $candidatePercent }}%)
                                </span>
                            </div>
                            <div class="stats-track overflow-hidden">
                                <div data-stat-candidate-bar class="stats-value" style="width: {{ $candidatePercent }}%">
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="stats-muted rounded-2xl bg-slate-50 p-5 text-sm dark:bg-slate-800">Belum ada data
                        kandidat.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const electionId = @json(optional($selectedElection)->id);
            const dataUrl = @json(route('admin.statistics.data'));

            if (!electionId) return;

            const refreshStatistics = async () => {
                try {
                    const response = await fetch(`${dataUrl}?election_id=${electionId}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    if (!response.ok) return;

                    const data = await response.json();
                    document.querySelector('[data-stat-total]').textContent = data.total_votes;
                    document.querySelector('[data-stat-not-voted]').textContent = data.not_voted_count;
                    document.querySelector('[data-stat-voters]').textContent = data.voter_count;
                    document.querySelector('[data-stat-participation]').textContent =
                        `${Number(data.participation).toLocaleString('id-ID')}%`;

                    data.candidates.forEach((candidate) => {
                        const row = document.querySelector(`[data-stat-candidate="${candidate.id}"]`);
                        if (!row) return;
                        row.querySelector('[data-stat-candidate-label]').textContent =
                            `${candidate.votes} suara (${candidate.percent}%)`;
                        row.querySelector('[data-stat-candidate-bar]').style.width =
                            `${candidate.percent}%`;
                    });
                } catch (error) {}
            };

            window.setInterval(refreshStatistics, 5000);
        })();
    </script>
@endpush
