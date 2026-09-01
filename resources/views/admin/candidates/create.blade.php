@extends('layouts.admin')

@push('head')
    <style>
        .candidate-form input:not([type="hidden"]),
        .candidate-form textarea,
        .candidate-form select {
            background-color: #ffffff !important;
            color: #151a33 !important;
        }

        html.dark .candidate-form input:not([type="hidden"]),
        html.dark .candidate-form textarea,
        html.dark .candidate-form select {
            background-color: #1b1e2c !important;
            color: #f3f5ff !important;
            color-scheme: dark;
        }
    </style>
@endpush

@section('content')
    <div class="space-y-4">
        <!-- Header Section -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Tambah Kandidat</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Tambahkan kandidat baru untuk periode Pilketos.</p>
            </div>
            <a href="{{ route('admin.cards.index') }}"
                class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 transition-colors">
                Kembali
            </a>
        </div>

        <!-- Form Section -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm dark:bg-slate-900 dark:border-slate-800">
            <form action="{{ route('admin.candidates.store') }}" method="POST" enctype="multipart/form-data"
                class="candidate-form">
                @csrf

                <div class="grid gap-4 lg:grid-cols-2">
                    <!-- Banner Selection -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Banner</label>
                        @if (isset($selectedBanner) && $selectedBanner)
                            <div
                                class="mt-2 rounded-xl border border-slate-200 px-4 py-3 bg-slate-50 text-slate-700 dark:bg-slate-800/50 dark:border-slate-700 dark:text-slate-300">
                                {{ $selectedBanner->title }} ({{ $selectedBanner->year }})
                            </div>
                            <input type="hidden" name="election_id" value="{{ $selectedBanner->id }}">
                        @else
                            <select name="election_id"
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-transparent px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:text-white dark:focus:border-blue-500 dark:bg-slate-900"
                                required>
                                <option value="" class="dark:bg-slate-900">Pilih Banner</option>
                                @foreach ($banners as $banner)
                                    <option value="{{ $banner->id }}" class="dark:bg-slate-900"
                                        {{ old('election_id') == $banner->id ? 'selected' : '' }}>
                                        {{ $banner->title }} ({{ $banner->year }}) - {{ $banner->candidates_count }}
                                        kandidat
                                    </option>
                                @endforeach
                            </select>
                            @error('election_id')
                                <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>

                    <!-- Nomor Urut -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Nomor Urut</label>
                        <input type="number" name="candidate_number" min="1" max="3"
                            value="{{ old('candidate_number') }}"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-transparent px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:text-white dark:focus:border-blue-500"
                            required>
                        @error('candidate_number')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nama -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Nama</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-transparent px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:text-white dark:focus:border-blue-500"
                            required>
                        @error('name')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kelas -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Kelas</label>
                        <input type="text" name="class" value="{{ old('class') }}"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-transparent px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:text-white dark:focus:border-blue-500"
                            required>
                        @error('class')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Jurusan -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Jurusan</label>
                        <input type="text" name="major" value="{{ old('major') }}"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-transparent px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:text-white dark:focus:border-blue-500"
                            required>
                        @error('major')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Foto Kandidat -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Foto Kandidat</label>
                        <input id="photo_input" type="file" name="photo[]" multiple
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-transparent px-4 py-3 text-slate-900 file:mr-4 file:rounded-lg file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-blue-700 hover:file:bg-blue-100 dark:border-slate-700 dark:text-slate-300 dark:file:bg-slate-800 dark:file:text-slate-300 dark:hover:file:bg-slate-700"
                            accept="image/jpeg,image/png,image/webp" required>
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Pilih 1 sampai 5 foto sekaligus. Gunakan
                            Ctrl saat memilih beberapa file.</p>
                        @error('photo')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror

                        <!-- Preview Image -->
                        <div id="photoPreviewWrapper" class="mt-4 hidden grid gap-3 sm:grid-cols-3">
                        </div>
                    </div>

                    <!-- Biodata -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Biodata</label>
                        <textarea name="biodata" rows="3"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-transparent px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:text-white dark:focus:border-blue-500">{{ old('biodata') }}</textarea>
                        @error('biodata')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Visi -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Visi</label>
                        <textarea name="vision" rows="3"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-transparent px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:text-white dark:focus:border-blue-500"
                            required>{{ old('vision') }}</textarea>
                        @error('vision')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Misi -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Misi</label>
                        <textarea name="mission" rows="3"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-transparent px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:text-white dark:focus:border-blue-500"
                            required>{{ old('mission') }}</textarea>
                        @error('mission')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Motto -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Motto</label>
                        <textarea name="motto" rows="2"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-transparent px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:text-white dark:focus:border-blue-500">{{ old('motto') }}</textarea>
                        @error('motto')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Prestasi -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Prestasi</label>
                        <textarea name="achievements" rows="3"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-transparent px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:text-white dark:focus:border-blue-500">{{ old('achievements') }}</textarea>
                        @error('achievements')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Organisasi -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Organisasi</label>
                        <textarea name="organizations" rows="3"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-transparent px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:text-white dark:focus:border-blue-500">{{ old('organizations') }}</textarea>
                        @error('organizations')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-8 flex justify-end gap-3">
                    <a href="{{ route('admin.cards.index') }}"
                        class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                        class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500 transition-colors shadow-sm">
                        Simpan Kandidat
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const fileInput = document.getElementById('photo_input');
                const previewWrapper = document.getElementById('photoPreviewWrapper');
                const pendingFiles = new DataTransfer();

                if (!fileInput) return;

                const renderPreviews = () => {
                    previewWrapper.innerHTML = '';
                    Array.from(pendingFiles.files).forEach((selectedFile, index) => {
                        const previewItem = document.createElement('div');
                        previewItem.className = 'relative block';
                        previewItem.innerHTML = `
                            <img src="${URL.createObjectURL(selectedFile)}" alt="Preview Foto Kandidat" class="h-44 w-full rounded-2xl object-cover border border-slate-200 dark:border-slate-700">
                            <button type="button" data-pending-index="${index}" class="remove-pending-photo absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-full bg-black/70 text-lg leading-none text-white transition hover:bg-rose-600" aria-label="Batalkan foto kandidat" title="Batalkan foto kandidat">
                                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                            </button>`;
                        previewWrapper.appendChild(previewItem);
                    });
                    previewWrapper.classList.toggle('hidden', !pendingFiles.files.length);
                };

                fileInput.addEventListener('change', function() {
                    if (!this.files?.length) {
                        previewWrapper.classList.add('hidden');
                        return;
                    }

                    if (this.files.length > 5) {
                        alert('Maksimal 5 foto kandidat.');
                        this.value = '';
                        previewWrapper.classList.add('hidden');
                        return;
                    }

                    Array.from(this.files).forEach((selectedFile) => pendingFiles.items.add(selectedFile));
                    this.files = pendingFiles.files;
                    renderPreviews();
                });

                previewWrapper.addEventListener('click', async (event) => {
                    const button = event.target.closest('.remove-pending-photo');
                    if (!button) return;

                    const result = await Swal.fire({
                        title: 'Hapus foto dari pilihan?',
                        text: 'Foto ini tidak akan ikut disimpan.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, hapus',
                        cancelButtonText: 'Batal'
                    });

                    if (!result.isConfirmed) return;

                    const remainingFiles = new DataTransfer();
                    Array.from(pendingFiles.files).forEach((file, index) => {
                        if (index !== Number(button.dataset.pendingIndex)) remainingFiles.items.add(
                            file);
                    });
                    pendingFiles.items.clear();
                    Array.from(remainingFiles.files).forEach((file) => pendingFiles.items.add(file));
                    fileInput.files = pendingFiles.files;
                    renderPreviews();
                });
            });
        </script>
    @endpush
@endsection
