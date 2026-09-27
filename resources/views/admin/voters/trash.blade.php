@extends('layouts.admin')

@section('content')
    <div class="admin-resource-page space-y-6">
        <!-- Header Section -->
        <div class="flex min-w-0 items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-900 dark:text-white">Sampah Pemilih (Soft Deleted)</h2>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Daftar akun pemilih yang telah dihapus secara soft delete. Anda dapat memulihkannya kapan saja.</p>
            </div>
            <div>
                <a href="{{ route('admin.voters.index') }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Kembali ke Data Pemilih</span>
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 dark:border-emerald-800/30 dark:bg-emerald-950/50 dark:text-emerald-300">
                <i class="fa-solid fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
        @endif

        <!-- Table Container -->
        <div class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                    <thead class="border-b border-slate-200/80 bg-slate-50/50 text-xs uppercase text-slate-400 dark:border-slate-800 dark:bg-slate-900/50 dark:text-slate-500">
                        <tr>
                            <th class="px-6 py-4 font-semibold">No</th>
                            <th class="px-6 py-4 font-semibold">Nama</th>
                            <th class="px-6 py-4 font-semibold">Role / Kelas</th>
                            <th class="px-6 py-4 font-semibold">NIS / NIP</th>
                            <th class="px-6 py-4 font-semibold">Email</th>
                            <th class="px-6 py-4 font-semibold">Dihapus Pada</th>
                            <th class="px-6 py-4 text-center font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($trashedUsers as $user)
                            <tr class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="whitespace-nowrap px-6 py-4 text-slate-500 dark:text-slate-400">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 font-semibold text-slate-900 dark:text-white">
                                    {{ $user->name }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    {{ ucfirst($user->role) }} {{ $user->class_group ? '('.$user->class_group.' '.$user->major.')' : '' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-slate-500 dark:text-slate-400">
                                    {{ $user->identity_number ?: '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-slate-500 dark:text-slate-400">
                                    {{ $user->email ?: '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-slate-500 dark:text-slate-400">
                                    {{ $user->deleted_at?->format('d M Y H:i') ?: '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-center">
                                    <form action="{{ route('admin.voters.restore', $user->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
                                            <i class="fa-solid fa-rotate-left"></i>
                                            <span>Pulihkan</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center gap-2 text-slate-500 dark:text-slate-400">
                                        <i class="fa-solid fa-trash-can text-3xl text-slate-300 dark:text-slate-600 mb-2"></i>
                                        <p>Tidak ada data di tong sampah.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
