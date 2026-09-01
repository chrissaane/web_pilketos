@extends('layouts.app')

@section('title', $candidate->name)

@push('styles')
    <style>
        .candidate-profile {
            min-height: 100%;
            background: #101116;
            color: #f3f5ff;
            font-family: 'Trebuchet MS', 'Segoe UI', sans-serif;
        }

        .candidate-profile .profile-shell {
            max-width: 1080px;
            margin: 0 auto;
            padding: 78px 28px;
        }

        .candidate-profile .profile-panel {
            padding: 36px;
            border: 1px solid rgba(255, 255, 255, .16);
            background: radial-gradient(ellipse at 80% 0%, rgba(32, 45, 255, .35), transparent 40%), #111217;
        }

        .candidate-profile .profile-photo {
            overflow: hidden;
            border: 1px solid rgba(30, 230, 225, .35);
            border-radius: 0;
            background: linear-gradient(135deg, #2636ff, #151727);
        }

        .candidate-profile .profile-photo img {
            height: 100%;
            min-height: 320px;
            object-fit: cover;
        }

        .candidate-profile .profile-label {
            color: #1ee6e1;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .25em;
            text-transform: uppercase;
        }

        .candidate-profile .profile-title {
            margin-top: 12px;
            color: #f3f5ff;
            font-size: clamp(2.4rem, 5vw, 4.8rem);
            font-weight: 400;
            letter-spacing: -.07em;
            line-height: .9;
        }

        .candidate-profile .profile-muted {
            color: #9ba3bd;
        }

        .candidate-profile .profile-info {
            border: 1px solid rgba(255, 255, 255, .12);
            border-top: 1px solid #1ee6e1;
            border-radius: 0;
            background: rgba(27, 30, 44, .8);
        }

        .candidate-profile .profile-info h2 {
            color: #1ee6e1;
            font-size: 11px;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .candidate-profile .profile-info p {
            margin-top: 12px;
            color: #dfe3f4;
            font-size: 14px;
            line-height: 1.55;
        }

        .candidate-profile .profile-vote {
            border-radius: 0;
            background: #2636ff;
            color: white;
            box-shadow: 0 0 20px rgba(38, 54, 255, .3);
        }

        @media (max-width: 640px) {
            .candidate-profile .profile-shell {
                padding: 45px 15px;
            }

            .candidate-profile .profile-panel {
                padding: 20px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="candidate-profile">
        <div class="profile-shell">
            <div class="profile-panel">
                <div class="grid gap-6 md:grid-cols-[320px_1fr] items-start">
                    <div class="profile-photo" x-data="{
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
                                        class="w-full object-cover" draggable="false">
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
                            <div class="flex h-80 items-center justify-center px-5 text-center profile-muted">
                                Foto kandidat belum tersedia
                            </div>
                        @endif
                    </div>
                    <div>
                        <p class="profile-label">Kandidat {{ $candidate->candidate_number }}</p>
                        <h1 class="profile-title">{{ $candidate->name }}</h1>
                        <p class="profile-muted mt-3">{{ $candidate->class }} - {{ $candidate->major }}</p>
                        <div class="mt-8 grid gap-6 md:grid-cols-2">
                            <div class="profile-info p-6">
                                <h2 class="text-xl font-bold">Biodata</h2>
                                <p>{{ $candidate->biodata }}</p>
                            </div>
                            <div class="profile-info p-6">
                                <h2 class="text-xl font-bold">Visi</h2>
                                <p>{{ $candidate->vision }}</p>
                            </div>
                        </div>
                        <div class="mt-6 grid gap-6 md:grid-cols-2">
                            <div class="profile-info p-6">
                                <h2 class="text-xl font-bold">Motto</h2>
                                <p>{{ $candidate->motto }}</p>
                            </div>
                            <div class="profile-info p-6">
                                <h2 class="text-xl font-bold">Prestasi</h2>
                                <p>{{ $candidate->achievements }}</p>
                            </div>
                        </div>
                        <div class="profile-info mt-6 p-6">
                            <h2 class="text-xl font-bold">Misi</h2>
                            <p>{{ $candidate->mission }}</p>
                        </div>
                        @auth
                            @if ($candidate->election && $candidate->election->current_status === \App\Models\Election::STATUS_ACTIVE && !$hasVoted)
                                <div class="mt-8">
                                    <button id="voteBtn"
                                        class="profile-vote w-full rounded-2xl px-6 py-3 text-lg font-bold">Pilih
                                        Kandidat</button>
                                </div>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('voteBtn');
            if (!btn) return;

            btn.addEventListener('click', async function() {
                const candidateName = "{{ addslashes($candidate->name) }}";
                const candidateNumber = "{{ $candidate->candidate_number }}";

                const first = await Swal.fire({
                    showCloseButton: false,
                    showCancelButton: true,
                    reverseButtons: false,
                    allowOutsideClick: false,
                    title: 'Konfirmasi Pilihan Anda',
                    html: `
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4 text-center dark:bg-slate-800 dark:border-slate-700">
                            <div class="text-xs font-semibold text-slate-500">PASANGAN CALON TERPILIH</div>
                            <div class="mt-2 text-sm font-bold text-slate-700 dark:text-slate-100">Paslon ${candidateNumber}: ${candidateName}</div>
                        </div>
                        <div class="mt-4 rounded-lg border border-red-100 bg-red-50 p-3 text-sm text-red-700 dark:bg-red-900/30 dark:border-red-900/30">
                            <strong>Perhatian:</strong> Pilihan Anda tidak dapat diubah setelah dikirimkan. Pastikan pilihan Anda sudah benar.
                        </div>
                    `,
                    confirmButtonText: 'Ya, Pilih Sekarang',
                    cancelButtonText: 'Batal',
                    didOpen: () => {
                        // stack buttons vertically
                        const actions = document.querySelector('.swal2-actions');
                        if (actions) actions.classList.add('grid', 'gap-3');
                    }
                });

                if (!first.isConfirmed) return;

                const tokenResult = await Swal.fire({
                    title: '',
                    html: '<div class="token-popup-hero"><i class="fa-solid fa-key"></i></div><div class="token-popup-body"><h2 class="token-popup-heading">Masukkan Token Voting</h2><p class="token-popup-description">Gunakan token voting yang sudah diberikan kepada Anda.</p></div>',
                    input: 'text',
                    inputPlaceholder: 'Masukkan token voting Anda',
                    inputAttributes: {
                        autocapitalize: 'off'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Kirim',
                    cancelButtonText: 'Batal',
                    preConfirm: (value) => {
                        if (!value) {
                            Swal.showValidationMessage('Token wajib diisi');
                        }
                        return value;
                    },
                });

                if (!tokenResult.isConfirmed) return;
                const token = tokenResult.value;

                try {
                    const res = await fetch("{{ route('vote.store') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            election_id: {{ $candidate->election->id ?? 'null' }},
                            candidate_id: {{ $candidate->id }},
                            token: token
                        })
                    });

                    const data = await res.json();
                    if (res.ok && data.success) {
                        await Swal.fire({
                            title: 'Berhasil',
                            html: `<div class="text-sm">${data.message || 'Suara Anda berhasil tersimpan.'}</div>`,
                            icon: 'success',
                            confirmButtonText: 'Tutup'
                        });
                        btn.disabled = true;
                    } else {
                        await Swal.fire({
                            title: 'Gagal',
                            html: `<div class="text-sm">${data.message || 'Token salah atau sudah digunakan.'}</div>`,
                            icon: 'error',
                            confirmButtonText: 'Tutup'
                        });
                    }
                } catch (err) {
                    await Swal.fire({
                        title: 'Gagal',
                        html: `<div class="text-sm">Terjadi kesalahan. Silakan coba lagi.</div>`,
                        icon: 'error',
                        confirmButtonText: 'Tutup'
                    });
                }
            });
        });
    </script>
@endpush
