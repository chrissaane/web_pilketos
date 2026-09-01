@extends('layouts.admin')

@section('content')
    <div class="space-y-4">
        <div class="grid gap-4 lg:grid-cols-[280px_1fr]">
            <aside class="space-y-3 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="text-sm font-semibold text-slate-700">Pilih Card</div>
                <div class="space-y-2">
                    @foreach ($banners as $banner)
                        <a href="{{ route('admin.candidates.index', ['election_id' => $banner->id]) }}"
                            class="block rounded-2xl border px-4 py-3 transition {{ optional($selectedBanner)->id === $banner->id ? 'border-blue-500 bg-blue-50 text-slate-900' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300 hover:bg-slate-50' }}">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold">{{ $banner->title }}</div>
                                    <div class="text-xs text-slate-500">{{ $banner->year }}</div>
                                </div>
                                <span
                                    class="inline-flex rounded-full bg-slate-100 px-2 py-1 text-[11px] font-semibold text-slate-600">{{ $banner->candidates_count }}/3</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </aside>

            <div class="space-y-4">
                @if ($selectedBanner)
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <div class="text-xs uppercase tracking-[0.3em] text-slate-400">Card</div>
                                <h2 class="text-2xl font-bold text-slate-900">{{ $selectedBanner->title }}</h2>
                                <div class="text-sm text-slate-500">{{ $selectedBanner->year }}</div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.candidates.create') }}?election_id={{ $selectedBanner->id }}"
                                    class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Tambah
                                    Kandidat</a>
                                <a href="{{ route('admin.cards.edit', $selectedBanner) }}"
                                    class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Edit
                                    Card</a>
                                <form action="{{ route('admin.cards.destroy', $selectedBanner) }}" method="POST"
                                    data-confirm="Hapus card ini? Semua kandidat terkait juga akan dihapus."
                                    class="inline-flex">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center gap-2 rounded-2xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">Hapus
                                        Card</button>
                                </form>
                            </div>
                        </div>

                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            <div class="rounded-3xl border border-slate-100 bg-slate-50 p-4">
                                <div class="text-xs uppercase tracking-[0.3em] text-slate-400">Total Kandidat</div>
                                <div class="mt-3 text-xl font-semibold text-slate-900">
                                    {{ $selectedBanner->candidates_count }}</div>
                            </div>
                            <div class="rounded-3xl border border-slate-100 bg-slate-50 p-4">
                                <div class="text-xs uppercase tracking-[0.3em] text-slate-400">Tipe pemilihan</div>
                                <div class="mt-3 text-xl font-semibold text-slate-900">
                                    {{ $selectedBanner->description ?? 'Standar' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between gap-4 mb-5">
                            <div>
                                <div class="text-xs uppercase tracking-[0.3em] text-slate-400">Kandidat</div>
                                <div class="text-sm text-slate-600">Klik detail untuk melihat kandidat.</div>
                            </div>
                            <span
                                class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">{{ $selectedBanner->candidates_count }}/3</span>
                        </div>

                        <div class="space-y-4">
                            @forelse($selectedBanner->candidates as $candidate)
                                <div
                                    class="grid gap-4 rounded-3xl border border-slate-200 bg-slate-50 p-4 sm:grid-cols-[1fr_auto]">
                                    <div>
                                        <div class="text-base font-semibold text-slate-900">{{ $candidate->name }}</div>
                                        <div class="mt-2 text-sm text-slate-500">#{{ $candidate->candidate_number }} ·
                                            {{ $candidate->class }} · {{ $candidate->major }}</div>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <a href="{{ route('admin.candidates.show', $candidate) }}"
                                            class="rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100">Detail</a>
                                        <a href="{{ route('admin.candidates.edit', $candidate) }}"
                                            class="rounded-full border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100">Edit</a>
                                        <form action="{{ route('admin.candidates.destroy', $candidate) }}" method="POST"
                                            data-confirm="Hapus kandidat ini?" class="inline-flex">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="rounded-full bg-rose-600 px-3 py-2 text-xs font-semibold text-white hover:bg-rose-700">Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div
                                    class="rounded-3xl border border-dashed border-slate-200 bg-white p-8 text-center text-sm text-slate-500">
                                    Belum ada kandidat untuk card ini.</div>
                            @endforelse
                        </div>
                    </div>
                @else
                    <div
                        class="rounded-3xl border border-dashed border-slate-200 bg-white p-8 text-center text-sm text-slate-500">
                        Pilih card di sisi kiri untuk melihat kandidat.</div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const select = document.getElementById('filter_banner');
            const addBtn = document.getElementById('addCandidateBtn');

            if (select) {
                select.addEventListener('change', function() {
                    const val = this.value;
                    const url = new URL(window.location.href);
                    if (val) {
                        url.searchParams.set('election_id', val);
                    } else {
                        url.searchParams.delete('election_id');
                    }
                    window.location.href = url.toString();
                });
            }

            if (addBtn) {
                addBtn.addEventListener('click', function(e) {
                    if (this.disabled) {
                        e.preventDefault();
                        Swal.fire({
                            title: 'Banner belum dipilih',
                            text: 'Pilih Banner terlebih dahulu.',
                            icon: 'info',
                            confirmButtonText: 'Mengerti'
                        });
                        return;
                    }
                    const createUrl = this.getAttribute('data-create-url');
                    const selected = select ? select.value : '';
                    if (selected) {
                        window.location.href = createUrl + '?election_id=' + encodeURIComponent(selected);
                    } else {
                        // fallback
                        window.location.href = createUrl;
                    }
                });
            }
        });
    </script>
@endpush
