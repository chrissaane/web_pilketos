@extends('layouts.admin')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold">Detail Banner</h2>
            <p class="text-sm text-slate-500">Informasi lengkap periode pemilihan.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.cards.edit', $banner) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Edit</a>
            <a href="{{ route('admin.cards.index') }}" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg">Kembali</a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
        <div class="grid gap-6 lg:grid-cols-2">
            <div>
                <div class="mb-4">
                    <div class="text-sm text-slate-500">Judul</div>
                    <div class="mt-2 text-xl font-bold text-slate-900">{{ $banner->title }}</div>
                </div>
                <div class="mb-4">
                    <div class="text-sm text-slate-500">Tahun</div>
                    <div class="mt-2 text-base text-slate-800">{{ $banner->year }}</div>
                </div>
                <div class="mb-4">
                    <div class="text-sm text-slate-500">Status</div>
                    <div class="mt-2 inline-flex rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">{{ $banner->current_status }}</div>
                </div>
                <div class="mb-4">
                    <div class="text-sm text-slate-500">Tanggal & Jam</div>
                    <div class="mt-2 text-slate-800">{{ $banner->start_time->format('d M Y H:i') }} – {{ $banner->end_time->format('d M Y H:i') }}</div>
                </div>
            </div>

            <div>
                @if($banner->banner_path)
                    <img src="{{ Storage::disk('public')->url($banner->banner_path) }}" alt="Banner Image" class="w-full rounded-3xl object-cover border border-slate-200">
                @else
                    <div class="flex h-64 items-center justify-center rounded-3xl border border-dashed border-slate-300 text-slate-400">Tidak ada gambar banner</div>
                @endif
            </div>
        </div>

        <div class="mt-6 rounded-3xl bg-slate-50 p-6 text-slate-700">
            <h3 class="font-semibold">Deskripsi</h3>
            <p class="mt-3 text-sm leading-relaxed">{{ $banner->description ?? '-' }}</p>
        </div>
    </div>
</div>
@endsection
