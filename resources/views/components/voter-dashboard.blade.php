@props(['roleLabel' => 'Siswa Aktif', 'summaryTitle' => 'Selamat Datang', 'stats' => [], 'history' => collect()])

@push('styles')
    <style>
        .voter-editorial {
            --voter-ink: #f3f5ff;
            --voter-muted: #9ba3bd;
            --voter-line: rgba(255, 255, 255, .14);
            --voter-cyan: #1ee6e1;
            background: #101116;
            color: var(--voter-ink);
            font-family: 'Trebuchet MS', 'Segoe UI', sans-serif;
        }

        .voter-editorial .voter-panel {
            border: 1px solid var(--voter-line) !important;
            border-radius: 0 !important;
            background: rgba(23, 25, 36, .82) !important;
            box-shadow: none !important;
        }

        .voter-editorial .voter-hero {
            background: radial-gradient(ellipse at 75% 0%, rgba(38, 54, 255, .45), transparent 45%), #111217 !important;
        }

        .voter-editorial .voter-panel h1,
        .voter-editorial .voter-panel h2,
        .voter-editorial .voter-panel h3 {
            color: var(--voter-ink) !important;
        }

        .voter-editorial .voter-panel p {
            color: var(--voter-muted);
        }

        .voter-editorial .voter-panel .text-white,
        .voter-editorial .voter-panel .text-slate-900,
        .voter-editorial .voter-panel .text-slate-800 {
            color: var(--voter-ink) !important;
        }

        .voter-editorial .voter-accent {
            border-top: 1px solid var(--voter-cyan) !important;
        }

        .voter-editorial .voter-link {
            border: 1px solid rgba(30, 230, 225, .5) !important;
            border-radius: 0 !important;
            background: transparent !important;
            color: var(--voter-cyan) !important;
            box-shadow: none !important;
        }

        .voter-editorial .voter-link:hover {
            background: var(--voter-cyan) !important;
            color: #101116 !important;
        }

        .voter-editorial .voter-dark-panel {
            background: #111217 !important;
            border: 1px solid var(--voter-line);
            border-radius: 0 !important;
        }

        .voter-editorial .voter-tile {
            border: 1px solid var(--voter-line) !important;
            border-radius: 0 !important;
            background: rgba(27, 30, 44, .8) !important;
        }

        .voter-editorial .voter-tile:hover {
            border-color: var(--voter-cyan) !important;
        }

        .voter-editorial .voter-icon {
            color: var(--voter-cyan) !important;
        }

        html:not(.dark) .voter-editorial {
            --voter-ink: #151a33;
            --voter-muted: #626b86;
            --voter-line: rgba(25, 34, 70, .14);
            --voter-cyan: #2636ff;
            background: #f5f7fc;
            color: var(--voter-ink);
        }

        html:not(.dark) .voter-editorial .voter-panel {
            border-color: var(--voter-line) !important;
            background: #ffffff !important;
            color: var(--voter-ink) !important;
            box-shadow: 0 12px 28px rgba(25, 34, 70, .06) !important;
        }

        html:not(.dark) .voter-editorial .voter-hero {
            background: radial-gradient(ellipse at 75% 0%, rgba(38, 54, 255, .13), transparent 45%), #ffffff !important;
        }

        html:not(.dark) .voter-editorial .voter-panel h1,
        html:not(.dark) .voter-editorial .voter-panel h2,
        html:not(.dark) .voter-editorial .voter-panel h3,
        html:not(.dark) .voter-editorial .voter-panel .text-white,
        html:not(.dark) .voter-editorial .voter-panel .text-slate-900,
        html:not(.dark) .voter-editorial .voter-panel .text-slate-800 {
            color: var(--voter-ink) !important;
        }

        html:not(.dark) .voter-editorial .voter-panel p {
            color: var(--voter-muted);
        }

        html:not(.dark) .voter-editorial .voter-panel .bg-slate-200 {
            background: #e8ecf6;
            color: #35405f;
        }

        html:not(.dark) .voter-editorial .voter-panel .bg-slate-100 {
            background: #eef1f8;
            color: #35405f;
        }

        html:not(.dark) .voter-editorial .voter-link {
            border-color: #2636ff !important;
            color: #2636ff !important;
        }

        html:not(.dark) .voter-editorial .voter-link:hover {
            background: #2636ff !important;
            color: #ffffff !important;
        }

        html:not(.dark) .voter-editorial .voter-dark-panel {
            border-color: rgba(25, 34, 70, .16) !important;
            background: radial-gradient(ellipse at 75% 0%, rgba(38, 54, 255, .13), transparent 46%), #ffffff !important;
            color: var(--voter-ink) !important;
            box-shadow: 0 12px 28px rgba(25, 34, 70, .06) !important;
        }

        html:not(.dark) .voter-editorial .voter-dark-panel h2,
        html:not(.dark) .voter-editorial .voter-dark-panel .text-white {
            color: var(--voter-ink) !important;
        }

        html:not(.dark) .voter-editorial .voter-dark-panel p {
            color: var(--voter-muted);
        }

        html:not(.dark) .voter-editorial .voter-dark-panel .bg-slate-900\/80 {
            background: #f8f9fd;
            border: 1px solid rgba(25, 34, 70, .1);
        }

        html:not(.dark) .voter-editorial .voter-tile {
            border-color: rgba(25, 34, 70, .14) !important;
            background: #f8f9fd !important;
            color: #35405f !important;
        }

        html:not(.dark) .voter-editorial .voter-tile:hover {
            border-color: #2636ff !important;
            background: #eef1ff !important;
        }
    </style>
@endpush

<div class="voter-editorial" x-data="{ now: new Date(), end: new Date('{{ $election?->end_time?->toIsoString() ?? now()->toIsoString() }}'), remaining: '{{ $election ? '00j 00m 00d' : '-' }}' }" x-init="setInterval(() => {
    now = new Date();
    if (end) {
        const diff = Math.max(0, end - now);
        const hours = String(Math.floor(diff / 3600000)).padStart(2, '0');
        const minutes = String(Math.floor(diff / 60000) % 60).padStart(2, '0');
        const seconds = String(Math.floor(diff / 1000) % 60).padStart(2, '0');
        remaining = `${hours}j ${minutes}m ${seconds}d`;
    }
}, 1000)" class="space-y-6">
    <div class="grid gap-4 xl:grid-cols-[1.2fr_0.8fr]">
        <div
            class="voter-panel voter-hero rounded-[2rem] bg-slate-100 p-8 text-slate-900 shadow-sm shadow-slate-200/40 dark:bg-slate-900 dark:text-slate-100 dark:shadow-slate-950/20">
            <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                <div class="space-y-5">
                    <p class="text-sm font-semibold uppercase tracking-[0.32em] text-slate-500 dark:text-slate-400">
                        Dashboard Pemilih</p>
                    <h1 class="text-3xl font-bold">{{ $summaryTitle }}, {{ $user->name }}!</h1>
                    <p class="max-w-2xl text-sm text-slate-600 dark:text-slate-400">Akses dashboard pemilihan Ketua OSIS
                        SMKN 1 Bangsri. Suara Anda menentukan masa depan sekolah kita.</p>
                    <div class="flex flex-wrap gap-3">
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm dark:bg-slate-800 dark:text-slate-200">
                            <i class="fa-solid fa-school"></i>
                            {{ $user->class_group ? $user->class_group . ' ' . $user->major : 'Guru Aktif' }}
                        </span>
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-2 text-xs font-semibold text-emerald-700 shadow-sm">
                            <i class="fa-solid fa-user-check"></i>
                            {{ $roleLabel }}
                        </span>
                    </div>
                </div>

                <div
                    class="voter-panel voter-accent rounded-3xl border border-slate-200 bg-white p-5 text-center shadow-sm dark:border-slate-700 dark:bg-slate-950">
                    <p class="text-xs uppercase tracking-[0.32em] text-slate-400">Waktu Sekarang</p>
                    <p class="mt-4 text-3xl font-bold text-slate-900 dark:text-white"
                        x-text="now.toLocaleTimeString('id-ID', { hour12: false })"></p>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400"
                        x-text="now.toLocaleDateString('id-ID', { weekday:'long', day:'2-digit', month:'long', year:'numeric' })">
                    </p>
                </div>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
            <div
                class="voter-panel voter-accent rounded-3xl bg-white p-5 shadow-sm border border-slate-200 dark:bg-slate-950 dark:border-slate-800">
                <p class="text-sm text-slate-500">Status Suara</p>
                <p class="mt-4 text-xl font-bold text-slate-900 dark:text-white">
                    {{ isset($election) ? ($hasVoted ? 'Sudah Memilih' : 'Belum Memilih') : 'Tidak Ada Pemilihan Aktif' }}
                </p>
                <p class="mt-2 text-sm text-slate-500">
                    {{ isset($election) ? 'Pastikan Anda memberikan suara sebelum pemilihan berakhir hari ini.' : 'Tidak ada pemilihan aktif saat ini. Pantau halaman pemilihan untuk informasi terbaru.' }}
                </p>
                @if ($election)
                    <a href="{{ route('election.show', $election) }}"
                        class="mt-5 inline-flex items-center gap-2 rounded-full bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-600 transition">Mulai
                        Sekarang <i class="fa-solid fa-arrow-right-long"></i></a>
                @else
                    <span
                        class="mt-5 inline-flex items-center gap-2 rounded-full bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">Tidak
                        Tersedia</span>
                @endif
            </div>

            <div
                class="voter-panel voter-accent rounded-3xl bg-white p-5 shadow-sm border border-slate-200 dark:bg-slate-950 dark:border-slate-800 sm:col-span-2 xl:col-span-1">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm text-slate-500">Total Partisipasi</p>
                        <p class="mt-3 text-2xl font-bold text-slate-900 dark:text-white">{{ $stats['elections'] ?? 0 }}
                            Pemilihan</p>
                    </div>
                    <div
                        class="shrink-0 rounded-3xl bg-slate-100 p-3 text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        <i class="fa-solid fa-check-double"></i>
                    </div>
                </div>
                <div class="mt-4 space-y-2 text-sm text-slate-600 dark:text-slate-400">
                    <div class="flex items-center justify-between">
                        <span>Pilkets 2023</span>
                        <span class="text-emerald-600">✔</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Pemilihan Majelis</span>
                        <span class="text-emerald-600">✔</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-4">
        <div
            class="voter-panel voter-dark-panel rounded-[2rem] bg-slate-950 p-6 text-white shadow-xl shadow-slate-900/20">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.32em] text-slate-400">Live Election</p>
                    <h2 class="mt-4 text-3xl font-bold">
                        {{ $election?->title ?? 'Pemilihan Ketua OSIS Masa Bakti 2024/2025' }}</h2>
                    <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-300">
                        {{ $election ? 'Gunakan hak suara Anda secara bijak. Kenali visi dan misi setiap kandidat sebelum menentukan pilihan terbaik untuk kemajuan organisasi intra sekolah.' : 'Belum ada pemilihan aktif saat ini. Pantau halaman pemilihan untuk informasi terbaru.' }}
                    </p>
                </div>
                @if ($election)
                    <img src="{{ $election->banner_url ?? 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=500&q=80' }}"
                        alt="Election" class="hidden h-40 w-40 rounded-3xl object-cover md:block" />
                @endif
            </div>

            <div class="mt-8 grid gap-3 sm:grid-cols-3">
                <div class="rounded-3xl bg-slate-900/80 p-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Berakhir Dalam</p>
                    <div class="mt-3 text-xl font-bold text-white" x-text="remaining"></div>
                </div>
                <div class="rounded-3xl bg-slate-900/80 p-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Tingkat Partisipasi</p>
                    <div class="mt-3 text-xl font-bold text-white">{{ $election ? ($participation ?? 0) . '%' : '-' }}
                    </div>
                </div>
                <div class="rounded-3xl bg-slate-900/80 p-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Status</p>
                    <div
                        class="mt-3 inline-flex items-center gap-2 rounded-full bg-emerald-500/20 px-3 py-2 text-sm font-semibold text-emerald-200">
                        {{ $election ? $election->current_status : 'Belum Aktif' }}</div>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                @if ($election)
                    <a href="{{ route('election.show', $election) }}"
                        class="inline-flex items-center gap-2 rounded-full bg-blue-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 hover:bg-blue-400 transition">Pilih
                        Sekarang <i class="fa-solid fa-arrow-right-long"></i></a>
                @else
                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-slate-800/80 px-5 py-3 text-sm font-semibold text-slate-200">Tidak
                        tersedia</span>
                @endif
            </div>
        </div>

        <section class="voter-panel voter-accent rounded-[2rem] bg-white p-6 shadow-sm dark:bg-slate-950">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Riwayat</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">Histori Pemilihan</h2>
                </div>
                <span class="text-sm text-slate-500 dark:text-slate-400">{{ $history->count() }} pemilihan
                    diikuti</span>
            </div>

            @if ($history->isEmpty())
                <div
                    class="mt-5 border border-dashed border-slate-200 bg-slate-50 p-5 text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400">
                    Belum ada histori pemilihan. Informasi akan muncul setelah Anda memberikan suara.
                </div>
            @else
                <div
                    class="mt-5 divide-y divide-slate-200 border border-slate-200 dark:divide-slate-800 dark:border-slate-800">
                    @foreach ($history as $vote)
                        <div class="flex flex-col gap-2 p-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-bold text-slate-900 dark:text-white">
                                    {{ $vote->election?->title ?? 'Pemilihan' }}</p>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Pilihan:
                                    {{ $vote->candidate?->name ?? 'Kandidat' }}</p>
                            </div>
                            <time class="text-xs text-slate-500 dark:text-slate-400"
                                datetime="{{ $vote->created_at?->toIso8601String() }}">
                                {{ $vote->created_at?->translatedFormat('d M Y, H:i') }}
                            </time>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</div>
