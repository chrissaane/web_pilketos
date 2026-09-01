@extends('layouts.app')

@section('title', 'Data SiPintu - Pilketos')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.25em] text-blue-600 dark:text-blue-400">Integrasi
                    SiPintu</p>
                <h1 class="mt-3 text-4xl font-black text-slate-900 dark:text-white">Data Siswa & Guru SiPintu</h1>
                <p class="mt-3 max-w-3xl text-slate-600 dark:text-slate-300">
                    Menampilkan data siswa dan guru dari gateway SiPintu beserta NIS/NIP, email, kelas, dan status
                    sinkronisasi.
                </p>
            </div>

            <form action="{{ route('sipintu.sync') }}" method="POST">
                @csrf
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-5 py-3 font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700">
                    Sinkronisasi Data SiPintu
                </button>
            </form>
        </div>

        @if (session('success'))
            <div
                class="mb-8 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div
                class="mb-8 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200">
                {{ session('error') }}
            </div>
        @endif

        <div
            class="mb-8 rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Konfigurasi Gateway</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                @foreach ($serviceConfig as $key => $value)
                    <div
                        class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">
                            {{ str_replace('_', ' ', strtoupper($key)) }}</p>
                        <p class="mt-2 break-all text-sm font-medium text-slate-800 dark:text-slate-100">{{ $value ?? '-' }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="space-y-8">
            <div
                class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Data Siswa</h2>
                    <span
                        class="rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-700 dark:bg-blue-950/60 dark:text-blue-300">
                        {{ $students->count() }} siswa
                    </span>
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-800">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 dark:text-slate-400">
                                        Nama</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 dark:text-slate-400">
                                        NIS</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 dark:text-slate-400">
                                        Email</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 dark:text-slate-400">
                                        Kelas</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 dark:text-slate-400">
                                        Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-700 dark:bg-slate-900">
                                @forelse ($students as $student)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-medium text-slate-800 dark:text-slate-100">
                                            {{ $student->name }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                                            {{ $student->identity_number ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                                            {{ $student->email ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                                            {{ $student->class_group ?? '-' }} {{ $student->major ?? '' }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            @if ($student->is_active)
                                                <span
                                                    class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">Aktif</span>
                                            @else
                                                <span
                                                    class="rounded-full bg-rose-100 px-2 py-1 text-xs font-semibold text-rose-700 dark:bg-rose-950/50 dark:text-rose-300">Nonaktif</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5"
                                            class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">Belum
                                            ada data siswa.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div
                class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Data Guru</h2>
                    <span
                        class="rounded-full bg-violet-100 px-3 py-1 text-sm font-semibold text-violet-700 dark:bg-violet-950/60 dark:text-violet-300">
                        {{ $teachers->count() }} guru
                    </span>
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-800">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 dark:text-slate-400">
                                        Nama</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 dark:text-slate-400">
                                        NIP</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 dark:text-slate-400">
                                        Email</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 dark:text-slate-400">
                                        Telepon</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 dark:text-slate-400">
                                        Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-700 dark:bg-slate-900">
                                @forelse ($teachers as $teacher)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-medium text-slate-800 dark:text-slate-100">
                                            {{ $teacher->name }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                                            {{ $teacher->identity_number ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                                            {{ $teacher->email ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                                            {{ $teacher->phone ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            @if ($teacher->is_active)
                                                <span
                                                    class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">Aktif</span>
                                            @else
                                                <span
                                                    class="rounded-full bg-rose-100 px-2 py-1 text-xs font-semibold text-rose-700 dark:bg-rose-950/50 dark:text-rose-300">Nonaktif</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5"
                                            class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">Belum
                                            ada data guru.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
