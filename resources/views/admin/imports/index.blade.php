@extends('layouts.admin')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-black text-slate-900 dark:text-white">Import Data Pemilih</h2>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Unggah Excel siswa atau guru, lihat preview, lalu
                    konfirmasi impor data ke database.</p>
            </div>
            <a href="{{ route('admin.voters.index') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali ke Data Pemilih
            </a>
        </div>

        @if ($errors->any())
            <div
                class="rounded-3xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200">
                <div class="font-semibold">Terdapat kesalahan pada unggahan:</div>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <form action="{{ route('admin.import.store') }}" method="POST" enctype="multipart/form-data"
                x-data="{ confirmed: false }">
                @csrf
                <div class="grid gap-4 sm:grid-cols-1 items-end">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Pilih File
                            Excel</label>
                        <input type="file" name="file" accept=".xlsx,.xls"
                            class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" />
                    </div>
                </div>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
                    <button type="submit" name="preview" value="1"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                        <i class="fa-solid fa-eye"></i>
                        Lihat Preview
                    </button>
                </div>

                @if (session('preview') || (isset($preview) && $preview))
                    <input type="hidden" name="temp_path" value="{{ $tempPath ?? '' }}" />
                    <input type="hidden" name="confirm_import" value="1" />

                    <div
                        class="mt-8 rounded-3xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-950">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">Preview File</p>
                                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                                    {{ $previewFileName ?? 'Tidak ada file' }}</p>
                            </div>
                            <div class="flex flex-col gap-2 sm:flex-row">
                                <span
                                    class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-700 dark:bg-blue-500/10 dark:text-blue-200">
                                    {{ $summary['total'] ?? 0 }} baris terdeteksi
                                </span>
                                <span
                                    class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-200">
                                    {{ $summary['added'] ?? 0 }} ditambahkan
                                </span>
                                <span
                                    class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-200">
                                    {{ $summary['updated'] ?? 0 }} diperbarui
                                </span>
                            </div>
                        </div>

                        <div
                            class="mt-4 overflow-x-auto rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                            <table class="min-w-full text-sm text-slate-700 dark:text-slate-300">
                                <thead
                                    class="border-b border-slate-200 bg-slate-100 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400">
                                    <tr>
                                        <th class="px-4 py-3">Baris</th>
                                        <th class="px-4 py-3">NIS / NIP</th>
                                        <th class="px-4 py-3">Nama</th>
                                        <th class="px-4 py-3">Tanggal Lahir</th>
                                        <th class="px-4 py-3">Kelas / Jurusan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-700 dark:bg-transparent">
                                    @forelse($previewRows ?? [] as $row)
                                        <tr class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                            <td
                                                class="whitespace-nowrap px-4 py-3 font-medium text-slate-700 dark:text-slate-200">
                                                {{ $loop->iteration }}</td>
                                            <td class="px-4 py-3 text-slate-500 dark:text-slate-400">
                                                {{ $row['identity_number'] }}</td>
                                            <td class="px-4 py-3">{{ $row['name'] }}</td>
                                            <td class="px-4 py-3">{{ $row['birth_date'] }}</td>
                                            <td class="px-4 py-3">
                                                {{ $row['role'] === 'guru' ? 'Guru' : 'Kelas ' . $row['class_group'] . ' / ' . $row['major'] }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5"
                                                class="px-4 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                                Tidak ada data preview untuk ditampilkan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if (!empty($importErrors))
                            <div
                                class="mt-4 rounded-3xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200">
                                <div class="font-semibold">Baris yang gagal:</div>
                                <ul class="mt-2 list-disc space-y-1 pl-5">
                                    @foreach ($importErrors as $error)
                                        <li>{{ is_array($error) ? $error['message'] ?? json_encode($error) : $error }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
                            <button type="button"
                                @click="Swal.fire({
                                title: 'Apakah Anda yakin ingin mengimpor data ini?',
                                html: 'Pastikan jenis data dan file sudah benar sebelum melanjutkan.',
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonText: 'Ya, Import',
                                cancelButtonText: 'Batal',
                                customClass: {
                                    popup: 'rounded-3xl p-6 bg-white dark:bg-slate-900',
                                    confirmButton: 'inline-block w-full rounded-full bg-blue-600 px-6 py-3 text-white text-lg font-bold hover:bg-blue-700',
                                    cancelButton: 'inline-block w-full mt-3 rounded-full border border-slate-300 bg-white px-6 py-3 text-slate-700 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200'
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $el = document.querySelector('form');
                                    if ($el) {
                                        $el.submit();
                                    }
                                }
                            })"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                                <i class="fa-solid fa-file-import"></i>
                                Ya, Import
                            </button>
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>
@endsection
