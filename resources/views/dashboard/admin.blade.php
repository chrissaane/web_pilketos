@extends('layouts.admin')

@section('content')
    <div class="admin-dashboard space-y-6">
        <section class="admin-intro-grid grid gap-4 xl:grid-cols-[1.2fr_0.8fr]">
            <div
                class="admin-hero rounded-[32px] border border-slate-200 bg-white p-6 shadow-2xl shadow-slate-200/40 transition-colors duration-200 dark:border-slate-700 dark:bg-slate-900 dark:shadow-slate-950/40">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-blue-600">Dashboard Admin</p>
                        <h1 class="mt-4 text-3xl font-black text-slate-900 dark:text-slate-100">Monitor Pilketos</h1>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">Lihat ringkasan kegiatan
                            pemilihan, status pemilih, dan progres secara cepat dalam tampilan yang modern.</p>
                    </div>
                    <div x-data="{ now: new Date() }" x-init="setInterval(() => now = new Date(), 1000)"
                        class="admin-clock shrink-0 border border-slate-200 bg-slate-50 p-5 text-center dark:border-slate-700 dark:bg-slate-950">
                        <p class="text-xs uppercase tracking-[0.32em] text-slate-400">Waktu Sekarang</p>
                        <p class="mt-4 text-3xl font-bold text-slate-900 dark:text-white"
                            x-text="now.toLocaleTimeString('id-ID', { hour12: false })"></p>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400"
                            x-text="now.toLocaleDateString('id-ID', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' })">
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                <div
                    class="admin-summary-panel border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Status Pemilihan</p>
                    <p class="mt-4 text-xl font-bold text-slate-900 dark:text-white">{{ $votingStatus ?? 'Belum Aktif' }}
                    </p>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Pantau status periode pemilihan yang sedang
                        dikelola.</p>
                </div>
                <div class="admin-summary-panel border border-slate-200 bg-white p-5 dark:border-slate-900">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Partisipasi</p>
                    <p class="mt-4 text-3xl font-bold text-slate-900 dark:text-white">{{ $stats['progress'] ?? '0%' }}</p>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ $stats['voted'] ?? 0 }} dari
                        {{ $stats['voters'] ?? 0 }} pemilih sudah memilih.</p>
                </div>
            </div>
        </section>

        <section class="grid gap-4 lg:grid-cols-5">
            <div
                class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-950">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-400 dark:text-slate-400">Total Card</p>
                        <p class="mt-3 text-3xl font-black text-slate-900 dark:text-slate-100">{{ $stats['banners'] ?? 0 }}
                        </p>
                    </div>
                    <div class="rounded-3xl bg-blue-100 p-3 text-blue-700 dark:bg-slate-800 dark:text-blue-200">
                        <i class="fa-solid fa-image"></i>
                    </div>
                </div>
                <div class="mt-4 h-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                    <div class="h-1 bg-blue-500" style="width: {{ ($stats['banners'] ?? 0) > 0 ? 100 : 0 }}%"></div>
                </div>
            </div>

            <div
                class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-950">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-400 dark:text-slate-400">Card Aktif</p>
                        <p class="mt-3 text-3xl font-black text-slate-900 dark:text-slate-100">
                            {{ $stats['active_banners'] ?? 0 }}</p>
                    </div>
                    <div class="rounded-3xl bg-amber-100 p-3 text-amber-700 dark:bg-slate-800 dark:text-amber-200">
                        <i class="fa-solid fa-bullhorn"></i>
                    </div>
                </div>
                <div class="mt-4 h-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                    <div class="h-1 bg-amber-500" style="width: {{ $stats['active_ratio'] ?? 0 }}%"></div>
                </div>
            </div>

            <div
                class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-950">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-400 dark:text-slate-400">Pemilih</p>
                        <p class="mt-3 text-3xl font-black text-slate-900 dark:text-slate-100">{{ $stats['voters'] ?? 0 }}
                        </p>
                    </div>
                    <div class="rounded-3xl bg-indigo-100 p-3 text-indigo-700 dark:bg-slate-800 dark:text-indigo-200">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
                <div class="mt-4 h-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                    <div class="h-1 bg-indigo-500" style="width: 100%"></div>
                </div>
            </div>

            <div
                class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-950">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-400 dark:text-slate-400">Sudah Memilih</p>
                        <p class="mt-3 text-3xl font-black text-slate-900 dark:text-slate-100">{{ $stats['voted'] ?? 0 }}
                        </p>
                    </div>
                    <div class="rounded-3xl bg-emerald-100 p-3 text-emerald-700 dark:bg-slate-800 dark:text-emerald-200">
                        <i class="fa-solid fa-check-circle"></i>
                    </div>
                </div>
                <div class="mt-4 h-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                    <div class="h-1 bg-emerald-500" style="width: {{ $stats['voted_ratio'] ?? 0 }}%"></div>
                </div>
            </div>

            <div
                class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-950">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-400 dark:text-slate-400">Belum Memilih</p>
                        <p class="mt-3 text-3xl font-black text-slate-900 dark:text-slate-100">
                            {{ $stats['not_voted'] ?? 0 }}</p>
                    </div>
                    <div class="rounded-3xl bg-rose-100 p-3 text-rose-700 dark:bg-slate-800 dark:text-rose-200">
                        <i class="fa-solid fa-user-clock"></i>
                    </div>
                </div>
                <div class="mt-4 h-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                    <div class="h-1 bg-rose-500" style="width: {{ $stats['not_voted_ratio'] ?? 0 }}%"></div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 xl:grid-cols-[2fr_1fr]">
            <div
                class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-950">
                <div class="flex items-center justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-xl font-black text-slate-900 dark:text-slate-100">Ranking Kandidat</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Persentase suara setiap kandidat saat
                            ini.</p>
                    </div>
                    <span
                        class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.25em] text-slate-600 dark:bg-slate-800 dark:text-slate-300">Realtime</span>
                </div>
                <div class="grid gap-6 lg:grid-cols-[220px_1fr]">
                    <div class="h-56 rounded-[28px] bg-slate-50 p-5 dark:bg-slate-900">
                        <canvas id="donutChart"></canvas>
                    </div>
                    <div class="space-y-4">
                        @foreach ($candidates ?? [] as $cand)
                            <div
                                class="flex items-center justify-between rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-900">
                                <div class="flex items-center gap-3">
                                    <div class="h-3 w-3 rounded-full bg-blue-500"></div>
                                    <span
                                        class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $cand->name ?? 'Paslon' }}</span>
                                </div>
                                <span
                                    class="text-sm text-slate-500 dark:text-slate-400">{{ $cand->percent ?? '0%' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div
                class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-950">
                <h3 class="text-xl font-black text-slate-900 dark:text-slate-100">User Breakdown</h3>
                <div class="mt-5 space-y-4 text-sm text-slate-600 dark:text-slate-400">
                    <div class="rounded-3xl bg-slate-50 p-4 dark:bg-slate-900">
                        <div class="text-sm text-slate-500 dark:text-slate-400">Siswa (Students)</div>
                        <div class="mt-3 text-2xl font-black text-slate-900 dark:text-slate-100">{{ $siswaCount ?? 0 }}
                        </div>
                    </div>
                    <div class="rounded-3xl bg-slate-50 p-4 dark:bg-slate-900">
                        <div class="text-sm text-slate-500 dark:text-slate-400">Guru & Staff</div>
                        <div class="mt-3 text-2xl font-black text-slate-900 dark:text-slate-100">{{ $guruCount ?? 0 }}
                        </div>
                    </div>
                    <div class="rounded-3xl bg-slate-50 p-4 dark:bg-slate-900">
                        <div class="text-sm text-slate-500 dark:text-slate-400">Partisipasi Pemilih</div>
                        <div class="mt-3 text-xl font-semibold text-emerald-600">{{ $stats['progress'] ?? '0%' }}</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4">
            <div
                class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-950">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 dark:text-slate-100">Turnout per Kelas</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Persentase suara berdasarkan kelas.</p>
                    </div>
                </div>
                <div class="mt-6 grid gap-4 sm:grid-cols-3">
                    @forelse($turnoutGroups ?? [] as $group)
                        <div class="rounded-[24px] bg-slate-50 p-5 text-center dark:bg-slate-900">
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $group->group }}</p>
                            <p class="mt-3 text-3xl font-black text-blue-800">{{ $group->percent }}%</p>
                            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">{{ $group->votes }} /
                                {{ $group->total }} Votes</p>
                        </div>
                    @empty
                        <div class="rounded-[24px] bg-slate-50 p-5 text-center dark:bg-slate-900">
                            <p class="text-sm text-slate-500 dark:text-slate-400">Tidak ada data kelas</p>
                            <p class="mt-3 text-3xl font-black text-blue-800">0%</p>
                            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">0 / 0 Votes</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- <div
                class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-950">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-black text-slate-900 dark:text-slate-100">System Logs</h3>
                    <a href="#" class="text-sm font-semibold text-blue-600 dark:text-blue-400">Lihat Semua</a>
                </div>
                <div class="mt-5 overflow-x-auto">
                    <table class="min-w-full text-sm text-slate-600 dark:text-slate-300">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">
                                <th class="py-3">Waktu</th>
                                <th class="py-3">Tipe</th>
                                <th class="py-3">Aksi</th>
                                <th class="py-3">Kelas</th>
                                <th class="py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach ($activities ?? [] as $act)
                                <tr>
                                    <td class="py-3">{{ $act->created_at ?? now() }}</td>
                                    <td class="py-3">{{ $act->user_type ?? 'Siswa' }}</td>
                                    <td class="py-3">{{ $act->message ?? 'Vote Cast Successfully' }}</td>
                                    <td class="py-3">{{ $act->class ?? '-' }}</td>
                                    <td class="py-3"><span
                                            class="inline-flex rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200">Verified</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div> --}}
        </section>

        <section class="grid gap-4 lg:grid-cols-3">
            <div
                class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-lg dark:border-slate-700 dark:bg-slate-950">
                <p class="text-sm font-semibold uppercase tracking-[0.25em] text-slate-400 dark:text-slate-400">Overall
                    Progress</p>
                <p class="mt-4 text-3xl font-black text-slate-900 dark:text-slate-100">{{ $stats['progress'] ?? '78%' }}
                </p>
                <div class="mt-4 h-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                    <div class="h-1" style="width: {{ $stats['progress_percent'] ?? 0 }}%; background-color: #34d399">
                    </div>
                </div>
            </div>

            <div
                class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-lg dark:border-slate-700 dark:bg-slate-950">
                <p class="text-sm font-semibold uppercase tracking-[0.25em] text-slate-400 dark:text-slate-400">Total
                    Online</p>
                <p class="mt-4 text-3xl font-black text-slate-900 dark:text-slate-100">{{ $stats['online'] ?? 0 }}</p>
                <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">Users currently active</p>
            </div>

            <div
                class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-lg dark:border-slate-700 dark:bg-slate-950">
                <p class="text-sm font-semibold uppercase tracking-[0.25em] text-slate-400 dark:text-slate-400">Status
                    Pemilihan</p>
                <p
                    class="mt-4 text-xl font-semibold text-{{ ($votingStatus ?? 'Sedang') == 'Ditutup' ? 'rose' : 'emerald' }}-600 dark:text-slate-100">
                    {{ $votingStatus ?? 'Sedang Berlangsung' }}</p>
            </div>
        </section>
    </div>

    @push('scripts')
        @php
            $chartLabels = collect($candidates ?? [])
                ->pluck('name')
                ->toArray();
            $chartData = collect($candidates ?? [])
                ->pluck('votes')
                ->toArray();
        @endphp
        <script>
            const donutCtx = document.getElementById('donutChart');
            if (donutCtx) {
                new Chart(donutCtx, {
                    type: 'doughnut',
                    data: {
                        labels: @json($chartLabels),
                        datasets: [{
                            data: @json($chartData),
                            backgroundColor: ['#1e40af', '#60a5fa', '#7c3aed', '#34d399', '#f97316'],
                            hoverOffset: 8,
                        }]
                    },
                    options: {
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        responsive: true
                    }
                });
            }
        </script>
    @endpush
@endsection
