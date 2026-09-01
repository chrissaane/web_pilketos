@extends('layouts.admin')

@section('content')
    <div class="space-y-4">
        <!-- Header Section -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Detail Kandidat</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Informasi lengkap kandidat.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.candidates.edit', $candidate) }}"
                    class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500 transition-colors shadow-sm">
                    Edit
                </a>
                <a href="{{ route('admin.cards.index') }}"
                    class="px-4 py-2 bg-slate-200 text-slate-700 font-medium rounded-lg hover:bg-slate-300 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 transition-colors">
                    Kembali
                </a>
            </div>
        </div>

        <!-- Main Content Card -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm dark:bg-slate-900 dark:border-slate-800">
            <div class="grid gap-6 lg:grid-cols-[280px_1fr]">
                <!-- Photo Section -->
                <div x-data="{
                    active: 0,
                    photos: @js($candidate->photo_urls),
                    startX: null,
                    startDrag(event) {
                        if (event.target.closest('button')) return;
                        this.startX = event.clientX;
                        event.currentTarget.setPointerCapture(event.pointerId);
                    },
                    endDrag(event) {
                        if (this.startX === null) return;
                        const distance = event.clientX - this.startX;
                        if (Math.abs(distance) > 40) this.active = distance < 0 ? (this.active + 1) % this.photos.length : (this.active - 1 + this.photos.length) % this.photos.length;
                        this.startX = null;
                        if (event.currentTarget.hasPointerCapture(event.pointerId)) event.currentTarget.releasePointerCapture(event.pointerId);
                    }
                }">
                    @if ($candidate->photo_urls)
                        <div class="relative cursor-grab select-none active:cursor-grabbing" style="touch-action: pan-y"
                            @pointerdown="startDrag($event)" @pointerup="endDrag($event)" @pointercancel="startX = null">
                            <template x-for="(photo, index) in photos" :key="photo">
                                <img x-show="active === index" :src="photo" alt="Foto {{ $candidate->name }}"
                                    class="h-80 w-full rounded-3xl object-cover border border-slate-200 dark:border-slate-700 shadow-sm"
                                    draggable="false">
                            </template>
                            <div x-show="photos.length > 1"
                                class="pointer-events-none absolute inset-x-0 bottom-3 flex justify-center gap-1.5">
                                <template x-for="(photo, index) in photos" :key="`dot-${index}`">
                                    <span :class="active === index ? 'bg-white' : 'bg-white/50'"
                                        class="h-2 w-2 rounded-full shadow"></span>
                                </template>
                            </div>
                        </div>
                    @else
                        <div
                            class="flex h-80 items-center justify-center rounded-3xl border border-slate-200 bg-slate-50 text-slate-500 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-400">
                            Tidak ada foto
                        </div>
                    @endif
                </div>

                <!-- Details Section -->
                <div class="space-y-4">
                    <!-- Header Info -->
                    <div class="border-b border-slate-100 pb-4 dark:border-slate-800">
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white">{{ $candidate->name }}</h3>
                        <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">
                            <span class="text-blue-600 dark:text-blue-400">#{{ $candidate->candidate_number }}</span>
                            &middot; {{ $candidate->class }} &middot; {{ $candidate->major }}
                        </p>
                    </div>

                    <!-- Grid Info 1 -->
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div
                            class="rounded-3xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/50">
                            <p class="text-xs font-bold uppercase tracking-[0.3em] text-slate-500 dark:text-slate-400">
                                Banner</p>
                            <p class="mt-2 font-semibold text-slate-900 dark:text-white">
                                {{ $candidate->election->title ?? '-' }}</p>
                        </div>
                        <div
                            class="rounded-3xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/50">
                            <p class="text-xs font-bold uppercase tracking-[0.3em] text-slate-500 dark:text-slate-400">
                                Prestasi</p>
                            <p class="mt-2 text-slate-700 dark:text-slate-300 leading-relaxed">
                                {{ $candidate->achievements ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Visi -->
                    <div
                        class="rounded-3xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/50">
                        <h4 class="text-xs font-bold uppercase tracking-[0.3em] text-slate-500 dark:text-slate-400">Visi
                        </h4>
                        <p class="mt-2 text-slate-700 dark:text-slate-300 leading-relaxed">{{ $candidate->vision }}</p>
                    </div>

                    <!-- Misi -->
                    <div
                        class="rounded-3xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/50">
                        <h4 class="text-xs font-bold uppercase tracking-[0.3em] text-slate-500 dark:text-slate-400">Misi
                        </h4>
                        <p class="mt-2 text-slate-700 dark:text-slate-300 leading-relaxed">{{ $candidate->mission }}</p>
                    </div>

                    <!-- Grid Info 2 -->
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div
                            class="rounded-3xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/50">
                            <h4 class="text-xs font-bold uppercase tracking-[0.3em] text-slate-500 dark:text-slate-400">
                                Motto</h4>
                            <p class="mt-2 text-slate-700 dark:text-slate-300 leading-relaxed">
                                {{ $candidate->motto ?? '-' }}</p>
                        </div>
                        <div
                            class="rounded-3xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/50">
                            <h4 class="text-xs font-bold uppercase tracking-[0.3em] text-slate-500 dark:text-slate-400">
                                Organisasi</h4>
                            <p class="mt-2 text-slate-700 dark:text-slate-300 leading-relaxed">
                                {{ $candidate->organizations ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Biodata -->
                    <div
                        class="rounded-3xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/50">
                        <h4 class="text-xs font-bold uppercase tracking-[0.3em] text-slate-500 dark:text-slate-400">Biodata
                        </h4>
                        <p class="mt-2 text-slate-700 dark:text-slate-300 leading-relaxed">{{ $candidate->biodata ?? '-' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
