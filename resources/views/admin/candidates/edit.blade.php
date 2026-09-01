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
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Edit Kandidat</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Perbarui data kandidat.</p>
            </div>
            <a href="{{ route('admin.cards.index') }}"
                class="px-4 py-2 bg-slate-200 text-slate-700 font-medium rounded-lg hover:bg-slate-300 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 transition-colors">
                Kembali
            </a>
        </div>

        <!-- Form Section -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm dark:bg-slate-900 dark:border-slate-800">
            <form action="{{ route('admin.candidates.update', $candidate) }}" method="POST" enctype="multipart/form-data"
                class="candidate-form">
                @csrf
                @method('PUT')

                <div class="grid gap-4 lg:grid-cols-2">
                    <!-- Banner / Pemilihan -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Banner</label>
                        <select name="election_id"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-transparent px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:focus:border-blue-500"
                            required>
                            <option value="">Pilih Banner</option>
                            @foreach ($banners as $banner)
                                <option value="{{ $banner->id }}"
                                    {{ old('election_id', $candidate->election_id) == $banner->id ? 'selected' : '' }}>
                                    {{ $banner->title }} ({{ $banner->year }}) - {{ $banner->candidates_count }} kandidat
                                </option>
                            @endforeach
                        </select>
                        @error('election_id')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nomor Urut -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Nomor Urut</label>
                        <input type="number" name="candidate_number" min="1" max="3"
                            value="{{ old('candidate_number', $candidate->candidate_number) }}"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-transparent px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:text-white dark:focus:border-blue-500"
                            required>
                        @error('candidate_number')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nama -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Nama</label>
                        <input type="text" name="name" value="{{ old('name', $candidate->name) }}"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-transparent px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:text-white dark:focus:border-blue-500"
                            required>
                        @error('name')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kelas -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Kelas</label>
                        <input type="text" name="class" value="{{ old('class', $candidate->class) }}"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-transparent px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:text-white dark:focus:border-blue-500"
                            required>
                        @error('class')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Jurusan -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Jurusan</label>
                        <input type="text" name="major" value="{{ old('major', $candidate->major) }}"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-transparent px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:text-white dark:focus:border-blue-500"
                            required>
                        @error('major')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Foto Kandidat -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Foto Kandidat</label>

                        <!-- Preview Image -->
                        @php($candidatePhotoPaths = $candidate->photo_paths ?: array_filter([$candidate->photo_path]))
                        <div id="photoPreviewWrapper"
                            class="mt-4 grid gap-3 sm:grid-cols-3 {{ $candidate->photo_url ? '' : 'hidden' }}">
                            @foreach ($candidate->photo_urls as $photoIndex => $photoUrl)
                                <div class="photo-preview-item relative block"
                                    data-photo-path="{{ $candidatePhotoPaths[$photoIndex] }}">
                                    <img src="{{ $photoUrl }}" alt="Preview Foto Kandidat"
                                        class="h-44 w-full rounded-2xl object-cover border border-slate-200 dark:border-slate-700">
                                    <button type="button"
                                        data-delete-url="{{ route('admin.candidates.photos.destroy', $candidate) }}"
                                        data-photo-index="{{ $photoIndex }}"
                                        class="delete-photo-button absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-full bg-black/70 text-lg leading-none text-white transition hover:bg-rose-600"
                                        aria-label="Hapus foto kandidat" title="Hapus foto kandidat">
                                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <input id="photo_input" type="file" name="photo[]" multiple
                            data-existing-count="{{ count($candidate->photo_urls) }}"
                            class="mt-4 w-full rounded-xl border border-slate-200 bg-transparent px-4 py-3 text-slate-900 file:mr-4 file:rounded-lg file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-blue-700 hover:file:bg-blue-100 dark:border-slate-700 dark:text-slate-300 dark:file:bg-slate-800 dark:file:text-slate-300 dark:hover:file:bg-slate-700"
                            accept="image/jpeg,image/png,image/webp"
                            {{ count($candidate->photo_urls) >= 5 ? 'disabled' : '' }}>
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Klik silang untuk menghapus foto, atau
                            tambahkan foto sampai total maksimal 5.</p>
                        @error('photo')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Biodata -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Biodata</label>
                        <textarea name="biodata" rows="3"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-transparent px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:text-white dark:focus:border-blue-500">{{ old('biodata', $candidate->biodata) }}</textarea>
                        @error('biodata')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Visi -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Visi</label>
                        <textarea name="vision" rows="3"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-transparent px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:text-white dark:focus:border-blue-500"
                            required>{{ old('vision', $candidate->vision) }}</textarea>
                        @error('vision')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Misi -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Misi</label>
                        <textarea name="mission" rows="3"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-transparent px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:text-white dark:focus:border-blue-500"
                            required>{{ old('mission', $candidate->mission) }}</textarea>
                        @error('mission')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Motto -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Motto</label>
                        <textarea name="motto" rows="2"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-transparent px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:text-white dark:focus:border-blue-500">{{ old('motto', $candidate->motto) }}</textarea>
                        @error('motto')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Prestasi -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Prestasi</label>
                        <textarea name="achievements" rows="3"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-transparent px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:text-white dark:focus:border-blue-500">{{ old('achievements', $candidate->achievements) }}</textarea>
                        @error('achievements')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Organisasi -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Organisasi</label>
                        <textarea name="organizations" rows="3"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-transparent px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:text-white dark:focus:border-blue-500">{{ old('organizations', $candidate->organizations) }}</textarea>
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
                        Simpan Perubahan
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
                const deleteButtons = document.querySelectorAll('.delete-photo-button');
                const pendingFiles = new DataTransfer();

                if (!fileInput) return;

                const updatePhotoLimit = () => {
                    const remainingSlots = 5 - Number(fileInput.dataset.existingCount || 0);
                    fileInput.disabled = remainingSlots <= 0;
                    fileInput.dataset.remainingSlots = Math.max(0, remainingSlots);
                };

                const syncPhotoIndices = () => {
                    previewWrapper.querySelectorAll('.photo-preview-item').forEach((item, index) => {
                        item.querySelector('.delete-photo-button').dataset.photoIndex = index;
                    });
                };

                const renderPendingPreviews = () => {
                    previewWrapper.querySelectorAll('.pending-photo-preview').forEach((item) => item.remove());
                    Array.from(pendingFiles.files).forEach((selectedFile, index) => {
                        const previewItem = document.createElement('div');
                        previewItem.className = 'pending-photo-preview relative block';
                        previewItem.innerHTML = `
                            <img src="${URL.createObjectURL(selectedFile)}" alt="Preview Foto Kandidat" class="h-44 w-full rounded-2xl object-cover border border-slate-200 dark:border-slate-700">
                            <button type="button" data-pending-index="${index}" class="remove-pending-photo absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-full bg-black/70 text-lg leading-none text-white transition hover:bg-rose-600" aria-label="Batalkan foto kandidat" title="Batalkan foto kandidat">
                                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                            </button>`;
                        previewWrapper.appendChild(previewItem);
                    });
                };

                deleteButtons.forEach((button) => {
                    button.addEventListener('click', async () => {
                        const result = await Swal.fire({
                            title: 'Hapus foto kandidat?',
                            text: 'Foto ini akan langsung dihapus dari kandidat.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Ya, hapus',
                            cancelButtonText: 'Batal'
                        });

                        if (result.isConfirmed) {
                            button.disabled = true;
                            try {
                                const response = await fetch(button.dataset.deleteUrl, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]').content,
                                        'Accept': 'application/json'
                                    },
                                    body: (() => {
                                        const formData = new FormData();
                                        formData.append('photo_index', button
                                            .dataset.photoIndex);
                                        return formData;
                                    })()
                                });

                                if (!response.ok) throw new Error('Delete failed');

                                button.closest('.photo-preview-item').remove();
                                fileInput.dataset.existingCount = Math.max(0,
                                    Number(fileInput.dataset.existingCount || 0) - 1);
                                if (!previewWrapper.querySelector('.photo-preview-item')) {
                                    previewWrapper.classList.add('hidden');
                                }
                                syncPhotoIndices();
                                await Swal.fire({
                                    title: 'Foto dihapus',
                                    text: 'Foto kandidat berhasil dihapus.',
                                    icon: 'success',
                                    timer: 1400,
                                    showConfirmButton: false
                                });
                            } catch (error) {
                                button.disabled = false;
                                await Swal.fire({
                                    title: 'Gagal menghapus foto',
                                    text: 'Silakan coba lagi.',
                                    icon: 'error'
                                });
                            }
                            updatePhotoLimit();
                        }
                    });
                });
                updatePhotoLimit();

                fileInput.addEventListener('change', function() {
                    if (!this.files?.length) {
                        return;
                    }

                    const remainingSlots = Number(this.dataset.remainingSlots || 0);
                    if (pendingFiles.files.length + this.files.length > remainingSlots) {
                        alert(`Foto kandidat maksimal 5. Masih bisa menambah ${remainingSlots} foto.`);
                        this.value = '';
                        return;
                    }

                    Array.from(this.files).forEach((selectedFile) => {
                        pendingFiles.items.add(selectedFile);
                    });
                    this.files = pendingFiles.files;
                    renderPendingPreviews();
                    previewWrapper.classList.remove('hidden');
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
                    renderPendingPreviews();
                    if (!previewWrapper.querySelector('.photo-preview-item, .pending-photo-preview')) {
                        previewWrapper.classList.add('hidden');
                    }
                });
            });
        </script>
    @endpush
@endsection
