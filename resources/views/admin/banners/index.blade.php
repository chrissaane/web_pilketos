@extends('layouts.admin')

@section('content')
    <div class="admin-resource-page admin-cards-page space-y-4">
        <!-- Header -->
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Card</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Kelola card dan periode pemilihan yang tampil dalam
                    satu sistem.</p>
            </div>
            <a href="{{ route('admin.cards.create') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow transition-all hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-offset-slate-900">
                Tambah Card
            </a>
        </div>

        <!-- Alert Success -->
        @if (session('success'))
            <div
                class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid gap-4 lg:grid-cols-[260px_1fr]">
            <!-- Sidebar: Pilih Card -->
            <aside
                class="space-y-3 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="text-sm font-semibold text-slate-700 dark:text-slate-300">Pilih Card</div>
                <div class="space-y-2">
                    @foreach ($banners as $banner)
                        <a href="{{ route('admin.cards.index', ['selected' => $banner->id]) }}"
                            class="block rounded-2xl border px-4 py-3 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 
                       {{ optional($selectedCard)->id === $banner->id
                           ? 'border-blue-500 bg-blue-50 text-blue-900 dark:border-blue-500 dark:bg-blue-500/10 dark:text-blue-300'
                           : 'border-slate-200 bg-white text-slate-700 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-800 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-500/50 dark:hover:bg-blue-500/10 dark:hover:text-blue-300' }}">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold">{{ $banner->title }}</div>
                                    <div class="text-xs opacity-70">{{ $banner->year }}</div>
                                </div>
                                <span
                                    class="inline-flex rounded-full px-2 py-1 text-[11px] font-semibold 
                                {{ optional($selectedCard)->id === $banner->id
                                    ? 'bg-blue-200/50 text-blue-800 dark:bg-blue-500/20 dark:text-blue-300'
                                    : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                                    {{ $banner->candidates_count }}/3
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </aside>

            <!-- Main Content -->
            <div class="space-y-4">
                @if ($selectedCard)
                    <div
                        class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden dark:border-slate-800 dark:bg-slate-900">
                        <div class="grid gap-6 md:grid-cols-[260px_1fr] p-6">

                            <!-- Left Section: Image & Meta Info -->
                            <div class="space-y-4">
                                <div
                                    class="overflow-hidden rounded-3xl border border-slate-200 bg-slate-100 shadow-sm h-56 dark:border-slate-700/50 dark:bg-slate-800">
                                    @if ($selectedCard->banner_url)
                                        <img src="{{ $selectedCard->banner_url }}" alt="Card {{ $selectedCard->title }}"
                                            class="h-full w-full object-cover">
                                    @else
                                        <div
                                            class="flex h-full items-center justify-center text-slate-500 dark:text-slate-400">
                                            Tidak ada gambar</div>
                                    @endif
                                </div>
                                <div class="grid gap-2">
                                    <div
                                        class="rounded-2xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-700/50 dark:bg-slate-800/50">
                                        <div class="text-xs uppercase tracking-[0.3em] text-slate-400 dark:text-slate-500">
                                            Status</div>
                                        <div class="mt-2 text-sm font-semibold text-slate-900 dark:text-slate-100">
                                            {{ $selectedCard->current_status }}</div>
                                    </div>
                                    <div
                                        class="rounded-2xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-700/50 dark:bg-slate-800/50">
                                        <div class="text-xs uppercase tracking-[0.3em] text-slate-400 dark:text-slate-500">
                                            Periode</div>
                                        <div class="mt-2 text-sm text-slate-700 dark:text-slate-300">
                                            {{ $selectedCard->start_time->format('d M Y H:i') }} –
                                            {{ $selectedCard->end_time->format('d M Y H:i') }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Section: Details & Kandidat -->
                            <div class="space-y-4">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <div class="text-xs uppercase tracking-[0.3em] text-slate-400 dark:text-slate-500">
                                            Card</div>
                                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white">
                                            {{ $selectedCard->title }}</h3>
                                        <div class="text-sm text-slate-500 dark:text-slate-400">{{ $selectedCard->year }}
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        @if ($selectedCard->candidates_count < 3)
                                            <a href="{{ route('admin.candidates.create') }}?election_id={{ $selectedCard->id }}"
                                                class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow transition-all hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600">
                                                Tambah Kandidat
                                            </a>
                                        @else
                                            <button type="button" disabled aria-disabled="true"
                                                class="inline-flex cursor-not-allowed items-center gap-2 rounded-2xl bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-500">
                                                Tambah Kandidat
                                            </button>
                                        @endif
                                        <a href="{{ route('admin.cards.edit', $selectedCard) }}"
                                            class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow transition-all hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600">
                                            Edit Card
                                        </a>
                                        <form action="{{ route('admin.cards.destroy', $selectedCard) }}" method="POST"
                                            data-confirm="Hapus card ini? Semua kandidat terkait juga akan dihapus."
                                            class="inline-flex">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="rounded-2xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow transition-all hover:bg-rose-700 dark:bg-rose-500 dark:hover:bg-rose-600">
                                                Hapus Card
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <div
                                        class="rounded-3xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-700/50 dark:bg-slate-800/50">
                                        <div class="text-xs uppercase tracking-[0.3em] text-slate-400 dark:text-slate-500">
                                            Total kandidat</div>
                                        <div class="mt-3 text-xl font-semibold text-slate-900 dark:text-white">
                                            {{ $selectedCard->candidates_count }}</div>
                                    </div>
                                    <div
                                        class="rounded-3xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-700/50 dark:bg-slate-800/50">
                                        <div class="text-xs uppercase tracking-[0.3em] text-slate-400 dark:text-slate-500">
                                            Tipe pemilihan</div>
                                        <div class="mt-3 text-xl font-semibold text-slate-900 dark:text-white">
                                            {{ $selectedCard->description ?? 'Standar' }}</div>
                                    </div>
                                </div>

                                <!-- List Kandidat -->
                                <div
                                    class="rounded-3xl border border-transparent bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/30">
                                    <div class="mb-4 flex items-center justify-between">
                                        <div>
                                            <div
                                                class="text-xs uppercase tracking-[0.3em] text-slate-400 dark:text-slate-500">
                                                Kandidat</div>
                                            <div class="text-sm text-slate-600 dark:text-slate-400">Kelola kandidat di card
                                                ini.</div>
                                        </div>
                                        <span
                                            class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">
                                            {{ $selectedCard->candidates_count }}/3
                                        </span>
                                    </div>

                                    <div class="space-y-3">
                                        @forelse($selectedCard->candidates as $candidate)
                                            <div
                                                class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-4 transition-all duration-200 hover:border-blue-300 hover:bg-blue-50 sm:grid-cols-[1fr_auto] dark:border-slate-700 dark:bg-slate-900 dark:hover:border-blue-500/50 dark:hover:bg-blue-500/10">
                                                <div>
                                                    <div class="text-sm font-semibold text-slate-900 dark:text-white">
                                                        {{ $candidate->name }}</div>
                                                    <div class="text-xs text-slate-500 dark:text-slate-400">
                                                        {{ $candidate->class }} · {{ $candidate->major }}</div>
                                                </div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <a href="{{ route('admin.candidates.show', $candidate) }}"
                                                        class="rounded-full border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-100 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-800">Detail</a>
                                                    <a href="{{ route('admin.candidates.edit', $candidate) }}"
                                                        class="rounded-full border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 transition-colors hover:bg-blue-100 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-400 dark:hover:bg-blue-500/20">Edit</a>
                                                    <form action="{{ route('admin.candidates.destroy', $candidate) }}"
                                                        method="POST" data-confirm="Hapus kandidat ini?"
                                                        class="inline-flex">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="rounded-full border border-transparent bg-rose-600 px-3 py-2 text-xs font-semibold text-white transition-colors hover:bg-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-400 dark:hover:bg-rose-500/20">Hapus</button>
                                                    </form>
                                                </div>
                                            </div>
                                        @empty
                                            <div
                                                class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/50 p-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-800/20 dark:text-slate-400">
                                                Belum ada kandidat untuk card ini.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Blank State -->
                    <div
                        class="flex flex-col items-center justify-center gap-3 rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-12 text-center text-slate-500 dark:border-slate-700 dark:bg-slate-800/30 dark:text-slate-400">
                        <svg class="h-12 w-12 text-slate-400 dark:text-slate-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                            </path>
                        </svg>
                        <span>Belum ada card. Tambahkan atau pilih card di menu samping untuk memulai.</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-4">{{ $banners->links() }}</div>
    </div>
@endsection
