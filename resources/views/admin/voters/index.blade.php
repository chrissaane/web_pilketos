@extends('layouts.admin')

@section('content')
    <div class="admin-resource-page admin-voters-page space-y-6">
        <!-- Header Section -->
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-black text-slate-900 dark:text-white">Data Pemilih</h2>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Kelola data akun pemilih dan token yang aktif
                    mengikuti periode pemilihan yang sedang berlangsung.</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="flex flex-col gap-2 sm:flex-row">
                    <form id="sipintuSyncForm" action="{{ route('admin.voters.sync') }}" method="POST" class="inline-block">
                        @csrf
                        <input type="hidden" name="filter" value="{{ $selectedFilter }}">
                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-700 dark:bg-teal-600 dark:hover:bg-teal-500">
                            <i class="fa-solid fa-rotate"></i>
                            <span>Sinkronisasi SiPintu</span>
                        </button>
                    </form>

                    <form id="generateTokensForm" action="{{ route('admin.voters.generate_tokens') }}" method="POST"
                        style="display:none">
                        @csrf
                        <input type="hidden" name="all" value="1">
                    </form>

                    <form id="regenerateTokensForm" action="{{ route('admin.voters.generate_tokens') }}" method="POST"
                        style="display:none">
                        @csrf
                        <input type="hidden" name="all" value="1">
                        <input type="hidden" name="force" value="1">
                    </form>

                    <button type="button"
                        onclick="event.preventDefault(); Swal.fire({
                        title: 'Buat Token Voting Otomatis',
                        html: '<div class=\'token-popup-copy\'><p>Buat token unik untuk seluruh pemilih.</p><div class=\'token-popup-note\'><i class=\'fa-solid fa-circle-info\'></i><span>Token yang sudah ada tidak akan diduplikasi.</span></div></div>',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Buat',
                        cancelButtonText: 'Batal'
                    }).then((r) => { if (r.isConfirmed) document.getElementById('generateTokensForm').submit(); })"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700 dark:bg-violet-600 dark:hover:bg-violet-500">
                        <i class="fa-solid fa-key"></i>
                        Buat Token Otomatis
                    </button>

                    <button type="button"
                        onclick="event.preventDefault(); Swal.fire({
                        title: 'Regenerasi Token Voting',
                        html: '<div class=\'token-popup-copy\'><p>Ganti seluruh token pemilih untuk periode aktif.</p><div class=\'token-popup-note token-popup-warning\'><i class=\'fa-solid fa-triangle-exclamation\'></i><span>Token lama, termasuk yang sudah digunakan, akan dihapus.</span></div></div>',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Ganti',
                        cancelButtonText: 'Batal'
                    }).then((r) => { if (r.isConfirmed) document.getElementById('regenerateTokensForm').submit(); })"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-700 dark:bg-rose-600 dark:hover:bg-rose-500">
                        <i class="fa-solid fa-rotate"></i>
                        Regenerasi Token
                    </button>
                    <form id="promoteForm" action="{{ route('admin.voters.promote') }}" method="POST" style="display:none">
                        @csrf</form>
                    <form id="archiveForm" action="{{ route('admin.voters.archive_xii') }}" method="POST"
                        style="display:none">@csrf</form>

                    <a href="{{ route('admin.voters.print', ['filter' => $selectedFilter]) }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-green-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 dark:bg-green-600 dark:hover:bg-green-500">
                        <i class="fa-solid fa-print"></i>
                        Cetak Data Pemilih
                    </a>

                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @click.away="open = false" type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500">
                            <i class="fa-solid fa-file-excel"></i>
                            Unduh Excel
                            <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200"
                                :class="open ? 'rotate-180' : ''"></i>
                        </button>

                        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 z-20 mt-2 w-56 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900">
                            <div class="py-1">
                                <a href="{{ route('admin.voters.export', ['filter' => 'semua']) }}"
                                    class="block px-4 py-2.5 text-sm font-medium transition-colors hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300">
                                    Unduh Semua
                                </a>
                                <a href="{{ route('admin.voters.export', ['filter' => 'siswa']) }}"
                                    class="block px-4 py-2.5 text-sm font-medium transition-colors hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300">
                                    Unduh Data Siswa
                                </a>
                                <a href="{{ route('admin.voters.export', ['filter' => 'guru']) }}"
                                    class="block px-4 py-2.5 text-sm font-medium transition-colors hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300">
                                    Unduh Data Guru
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Dropdown -->
                <div class="mt-4 w-full max-w-sm">
                    <label for="filter" class="text-sm font-semibold text-slate-700 dark:text-slate-300">Tingkat</label>
                    <form method="GET" action="{{ route('admin.voters.index') }}" class="mt-2 space-y-3">
                        <select id="filter" name="filter"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:focus:border-blue-400 dark:focus:ring-blue-900">
                            @foreach ($filters as $slug => $label)
                                <option value="{{ $slug }}" {{ $selectedFilter === $slug ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit"
                            class="w-full rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                            Terapkan
                        </button>
                    </form>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div
                class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div
                class="flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200">
                <i class="fa-solid fa-circle-exclamation mt-0.5 text-base text-rose-600 dark:text-rose-400"></i>
                <div>
                    <p class="font-semibold">Sinkronisasi Gagal</p>
                    <p class="mt-0.5">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <!-- Main Content -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">

            <!-- Active Card Banner -->
            <div class="mb-6 rounded-xl border border-blue-100 bg-blue-50 p-4 dark:border-blue-500/20 dark:bg-blue-500/10">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-blue-700 dark:text-blue-400">Card Aktif:
                            {{ $activeCard['title'] }}</p>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ $activeCard['description'] }}</p>
                    </div>
                    <span
                        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold uppercase tracking-wider text-white shadow-sm dark:bg-blue-600">
                        {{ $activeCard['status'] }}
                    </span>
                </div>
            </div>

            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-slate-500 dark:text-slate-400">
                    Cari nama, NIS, NIP, kelas, atau email
                </div>
                <div class="w-full max-w-md">
                    <label for="voterSearch" class="sr-only">Cari pemilih</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input id="voterSearch" type="text" placeholder="Cari data pemilih..."
                            class="w-full rounded-2xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:focus:border-blue-400 dark:focus:ring-blue-900">
                    </div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-slate-700 dark:text-slate-300">
                        <thead
                            class="border-b border-slate-200 bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-400">
                            <tr>
                                <th class="px-6 py-4">Nama</th>
                                <th class="px-6 py-4">Kelas</th>
                                <th class="px-6 py-4">NIS / NIP</th>
                                <th class="px-6 py-4">Email</th>
                                <th class="px-6 py-4">No HP</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Password</th>
                                <th class="px-6 py-4">Token</th>
                            </tr>
                        </thead>
                        <tbody id="votersTableBody"
                            class="divide-y divide-slate-200 bg-white dark:divide-slate-700 dark:bg-transparent">
                            @forelse($accounts as $account)
                                <tr class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                    <td class="whitespace-nowrap px-6 py-4 font-semibold text-slate-900 dark:text-white">
                                        {{ $account['name'] }}</td>
                                    <td class="whitespace-nowrap px-6 py-4">{{ $account['group'] }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-slate-500 dark:text-slate-400">
                                        {{ $account['credential'] }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-slate-500 dark:text-slate-400">
                                        {{ $account['email'] }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-slate-500 dark:text-slate-400">
                                        {{ $account['phone'] }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-slate-500 dark:text-slate-400">
                                        {{ $account['status'] }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-slate-500 dark:text-slate-400">
                                        {{ $account['password'] }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 font-medium text-slate-700 dark:text-slate-300">
                                        {{ $account['token'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center">
                                        <div
                                            class="flex flex-col items-center justify-center gap-2 text-slate-500 dark:text-slate-400">
                                            <i
                                                class="fa-solid fa-inbox text-3xl text-slate-300 dark:text-slate-600 mb-2"></i>
                                            <p>Tidak ada data untuk filter ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('voterSearch');
            const tableBody = document.getElementById('votersTableBody');

            function applyVoterSearch() {
                if (!searchInput || !tableBody) return;

                const query = searchInput.value.trim().toLowerCase();
                const rows = tableBody.querySelectorAll('tr');
                let visibleCount = 0;

                rows.forEach((row) => {
                    if (row.querySelector('td[colspan]')) {
                        row.style.display = '';
                        return;
                    }

                    const rowText = row.textContent.toLowerCase();
                    const match = !query || rowText.includes(query);
                    row.style.display = match ? '' : 'none';

                    if (match) {
                        visibleCount++;
                    }
                });

                const emptyRow = tableBody.querySelector('tr[data-empty-state]');
                if (emptyRow) {
                    emptyRow.style.display = visibleCount > 0 ? 'none' : '';
                }
            }

            if (searchInput) {
                searchInput.addEventListener('input', applyVoterSearch);
                applyVoterSearch();
            }

            const syncForm = document.getElementById('sipintuSyncForm');
            if (!syncForm) return;

            syncForm.addEventListener('submit', function(event) {
                event.preventDefault();

                const button = syncForm.querySelector('button[type="submit"]');
                const originalText = button.innerHTML;
                button.disabled = true;
                button.innerHTML =
                    '<i class="fa-solid fa-spinner fa-spin"></i> <span>Menyinkronkan...</span>';

                const formData = new FormData(syncForm);

                fetch(syncForm.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': formData.get('_token')
                        },
                        body: formData
                    })
                    .then(async (response) => {
                        const data = await response.json().catch(() => ({}));

                        if (!response.ok || data.success === false) {
                            throw new Error((data && data.message) || 'Sinkronisasi gagal dilakukan.');
                        }

                        if (data && data.redirect) {
                            const partialUrl = new URL(data.redirect, window.location.origin);
                            partialUrl.searchParams.set('partial', '1');

                            fetch(partialUrl.toString(), {
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                })
                                .then((res) => res.text())
                                .then((html) => {
                                    const container = document.getElementById(
                                        'votersTableBody');
                                    if (container) {
                                        container.innerHTML = html;
                                    }
                                })
                                .catch(() => {
                                    window.location.href = data.redirect;
                                });
                        }

                        if (window.Swal) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Sinkronisasi Selesai',
                                text: data.message || 'Data SiPintu berhasil diperbarui.',
                                timer: 2500,
                                showConfirmButton: false
                            });
                        } else {
                            alert(data.message || 'Data SiPintu berhasil diperbarui.');
                        }
                    })
                    .catch((error) => {
                        if (window.Swal) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Sinkronisasi Gagal',
                                text: error.message || 'Terjadi kesalahan saat sinkronisasi data SiPintu.',
                                confirmButtonText: 'Tutup',
                                confirmButtonColor: '#e11d48'
                            });
                        } else {
                            alert(error.message || 'Sinkronisasi gagal.');
                        }
                    })
                    .finally(() => {
                        button.disabled = false;
                        button.innerHTML = originalText;
                    });
            });
        });
    </script>
@endsection
