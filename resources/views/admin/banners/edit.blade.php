@extends('layouts.admin')

@section('content')
<div class="space-y-4">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Edit Banner</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Perbarui informasi periode pemilihan.</p>
        </div>
        <a href="{{ route('admin.cards.index') }}" class="px-4 py-2 bg-slate-200 text-slate-700 font-medium rounded-lg hover:bg-slate-300 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 transition-colors">
            Kembali
        </a>
    </div>

    <!-- Form Section -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm dark:bg-slate-900 dark:border-slate-800">
        <form action="{{ route('admin.cards.update', $banner) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="grid gap-4 lg:grid-cols-2">
                <!-- Judul -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Judul</label>
                    <input type="text" name="title" value="{{ old('title', $banner->title) }}" class="mt-2 w-full rounded-xl border border-slate-200 bg-transparent px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:text-white dark:focus:border-blue-500" required>
                    @error('title')<p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                </div>

                <!-- Tahun -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Tahun</label>
                    <input type="text" name="year" value="{{ old('year', $banner->year) }}" class="mt-2 w-full rounded-xl border border-slate-200 bg-transparent px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:text-white dark:focus:border-blue-500" required>
                    @error('year')<p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                </div>

                <!-- Deskripsi -->
                <div class="lg:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Deskripsi</label>
                    <textarea name="description" rows="4" class="mt-2 w-full rounded-2xl border border-slate-200 bg-transparent px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:text-white dark:focus:border-blue-500">{{ old('description', $banner->description) }}</textarea>
                    @error('description')<p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                </div>

                <!-- Gambar Banner -->
                <div class="lg:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Gambar Banner</label>
                    
                    <!-- Preview Image Existing / New -->
                    <div id="bannerImagePreviewWrapper" class="mt-4">
                        <img id="bannerImagePreview" src="{{ $banner->banner_path ? Storage::disk('public')->url($banner->banner_path) : '#' }}" alt="Banner" class="h-44 w-full rounded-2xl object-cover border border-slate-200 dark:border-slate-700 {{ $banner->banner_path ? '' : 'hidden' }}">
                    </div>
                    
                    <input id="banner_image_input" type="file" name="banner_image" class="mt-4 w-full rounded-xl border border-slate-200 bg-transparent px-4 py-3 text-slate-900 file:mr-4 file:rounded-lg file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-blue-700 hover:file:bg-blue-100 dark:border-slate-700 dark:text-slate-300 dark:file:bg-slate-800 dark:file:text-slate-300 dark:hover:file:bg-slate-700" accept="image/jpeg,image/png,image/webp">
                    @error('banner_image')<p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                </div>

                <!-- Tanggal Mulai -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ old('start_date', $banner->start_time->format('Y-m-d')) }}" class="mt-2 w-full rounded-xl border border-slate-200 bg-transparent px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:text-white dark:focus:border-blue-500" required>
                    @error('start_date')<p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                </div>

                <!-- Jam Mulai -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Jam Mulai</label>
                    <input type="time" name="start_time" value="{{ old('start_time', $banner->start_time->format('H:i')) }}" class="mt-2 w-full rounded-xl border border-slate-200 bg-transparent px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:text-white dark:focus:border-blue-500" required>
                    @error('start_time')<p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                </div>

                <!-- Tanggal Berakhir -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Tanggal Berakhir</label>
                    <input type="date" name="end_date" value="{{ old('end_date', $banner->end_time->format('Y-m-d')) }}" class="mt-2 w-full rounded-xl border border-slate-200 bg-transparent px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:text-white dark:focus:border-blue-500" required>
                    @error('end_date')<p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                </div>

                <!-- Jam Berakhir -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Jam Berakhir</label>
                    <input type="time" name="end_time" value="{{ old('end_time', $banner->end_time->format('H:i')) }}" class="mt-2 w-full rounded-xl border border-slate-200 bg-transparent px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:text-white dark:focus:border-blue-500" required>
                    @error('end_time')<p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 flex justify-end gap-3">
                <a href="{{ route('admin.cards.index') }}" class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 transition-colors">
                    Batal
                </a>
                <button type="submit" class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500 transition-colors shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fileInput = document.getElementById('banner_image_input');
        const previewWrapper = document.getElementById('bannerImagePreviewWrapper');
        const previewImage = document.getElementById('bannerImagePreview');

        if (!fileInput) return;

        fileInput.addEventListener('change', function () {
            const file = this.files?.[0];
            if (!file) {
                // Jangan sembunyikan jika sudah ada gambar asli dari database
                if (previewImage.src && !previewImage.src.endsWith('#')) {
                    return;
                }
                previewWrapper.classList.add('hidden');
                previewImage.classList.add('hidden');
                previewImage.src = '#';
                return;
            }

            previewImage.src = URL.createObjectURL(file);
            previewImage.classList.remove('hidden');
            previewWrapper.classList.remove('hidden');
        });
    });
</script>
@endpush
@endsection