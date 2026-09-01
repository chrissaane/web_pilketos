@extends('layouts.admin')

@section('content')
    <div class="admin-resource-page admin-settings-page space-y-6">
        <div>
            <h2 class="text-2xl font-black text-slate-900 dark:text-white">Pengaturan Admin</h2>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Kelola identitas website dan halaman Tentang.</p>
        </div>

        @if (session('success'))
            <div
                class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div
                class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-300">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.settings.store') }}" enctype="multipart/form-data"
            class="settings-form space-y-6">
            @csrf
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">1. Identitas Website</h3>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div><label class="text-sm font-semibold">Nama Website</label><input name="website_name"
                            value="{{ old('website_name', $settings['website_name']) }}"
                            class="mt-2 w-full rounded-xl border px-3 py-2"></div>
                    <div><label class="text-sm font-semibold">Nama Sekolah</label><input name="school_name"
                            value="{{ old('school_name', $settings['school_name']) }}"
                            class="mt-2 w-full rounded-xl border px-3 py-2"></div>
                    <div>
                        <label class="text-sm font-semibold">Logo</label>
                        <input name="logo_file" type="file" accept=".jpg,.jpeg,.png,.webp,.svg"
                            class="mt-2 w-full rounded-xl border px-3 py-2">
                        <p class="mt-1 text-xs text-slate-500">JPG, PNG, WEBP, atau SVG. Maksimal 5 MB.</p>
                        @error('logo_file')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-sm font-semibold">Favicon</label>
                        <input name="favicon_file" type="file" accept=".ico,.png,.jpg,.jpeg,.webp,.svg"
                            class="mt-2 w-full rounded-xl border px-3 py-2">
                        <p class="mt-1 text-xs text-slate-500">ICO, PNG, JPG, WEBP, atau SVG. Maksimal 2 MB.</p>
                        @error('favicon_file')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button class="rounded-xl bg-blue-600 px-5 py-2.5 font-semibold text-white">Simpan Semua</button>
            </div>
        </form>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">2. Tentang</h3>
            <form id="about-settings-form" method="POST" action="{{ route('admin.settings.about.store') }}"
                enctype="multipart/form-data" class="settings-form mt-4 space-y-4">
                @csrf
                <div>
                    <label class="text-sm font-semibold">Upload Gambar Tentang</label>
                    <input name="about_image_file" type="file" accept=".jpg,.jpeg,.png,.webp"
                        class="mt-2 w-full rounded-xl border px-3 py-2">
                    <p class="mt-1 text-xs text-slate-500">Format JPG, PNG, atau WEBP. Maksimal 5 MB.</p>
                    @error('about_image_file')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                    @if (!empty($aboutSections['about_image']))
                        <p class="mt-2 text-xs text-emerald-600">Gambar saat ini sudah tersimpan. Upload file baru untuk
                            menggantinya.</p>
                    @endif
                </div>
                @php
                    $aboutContentSections = $aboutSections->except(['about_image']);
                @endphp
                <div id="custom-about-sections" class="space-y-4">
                    @foreach ($aboutContentSections as $section => $content)
                        <div
                            class="custom-about-section grid gap-4 rounded-xl border border-slate-200 p-4 dark:border-slate-700 md:grid-cols-[1fr_1fr_auto]">
                            <input type="hidden" name="custom_keys[]" value="{{ $section }}">
                            <div>
                                <label class="text-sm font-semibold">Judul Bagian</label>
                                <input name="custom_titles[]" value="{{ Str::headline(Str::after($section, 'custom_')) }}"
                                    class="mt-2 w-full rounded-xl border px-3 py-2">
                            </div>
                            <div>
                                <label class="text-sm font-semibold">Isi Bagian</label>
                                <textarea name="content[{{ $section }}]" rows="4" class="mt-2 w-full rounded-xl border px-3 py-2">{{ old('content.' . $section, $content) }}</textarea>
                            </div>
                            <button type="button"
                                class="remove-about-section self-end rounded-xl border border-rose-300 px-3 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50 dark:border-rose-800 dark:hover:bg-rose-900/20"
                                title="Hapus bagian">Hapus</button>
                        </div>
                    @endforeach
                </div>
                <button type="button" id="add-about-section"
                    class="rounded-xl border border-dashed border-emerald-500 px-4 py-2.5 font-semibold text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20">
                    + Tambah Bagian Tentang
                </button>
                <div class="flex justify-end"><button
                        class="rounded-xl bg-emerald-600 px-4 py-2.5 font-semibold text-white">Simpan Tentang</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.getElementById('add-about-section')?.addEventListener('click', () => {
            const index = document.querySelectorAll('.custom-about-section').length + 1;
            const key = `custom_new_section_${Date.now()}`;
            const section = document.createElement('div');
            section.className =
                'custom-about-section grid gap-4 rounded-xl border border-slate-200 p-4 dark:border-slate-700 md:grid-cols-[1fr_1fr_auto]';
            section.innerHTML =
                `
                <input type="hidden" name="custom_keys[]" value="${key}">
                <div>
                    <label class="text-sm font-semibold">Judul Bagian</label>
                    <input name="custom_titles[]" value="Bagian ${index}" class="mt-2 w-full rounded-xl border px-3 py-2">
                </div>
                <div>
                    <label class="text-sm font-semibold">Isi Bagian</label>
                    <textarea name="content[${key}]" rows="4" class="mt-2 w-full rounded-xl border px-3 py-2"></textarea>
                </div>
                <button type="button" class="remove-about-section self-end rounded-xl border border-rose-300 px-3 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50 dark:border-rose-800 dark:hover:bg-rose-900/20" title="Hapus bagian">Hapus</button>`;
            document.getElementById('custom-about-sections').appendChild(section);
        });

        document.getElementById('custom-about-sections')?.addEventListener('click', (event) => {
            const button = event.target.closest('.remove-about-section');
            if (!button) return;

            const section = button.closest('.custom-about-section');
            const key = section.querySelector('input[name="custom_keys[]"]')?.value;
            Swal.fire({
                title: 'Konfirmasi tindakan',
                text: 'Hapus bagian Tentang? Perubahan akan diterapkan setelah menekan Simpan Tentang.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, lanjutkan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) return;

                if (key && !key.startsWith('custom_new_section_')) {
                    const deleted = document.createElement('input');
                    deleted.type = 'hidden';
                    deleted.name = 'deleted_custom_keys[]';
                    deleted.value = key;
                    document.getElementById('about-settings-form')?.appendChild(deleted);
                }
                section.remove();
            });
        });
    </script>
@endsection
