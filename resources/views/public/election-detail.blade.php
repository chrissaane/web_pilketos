@extends('layouts.app')

@section('title', $election->title)

@push('styles')
    <style>
        .candidate-page {
            min-height: 100%;
            background: #101116;
            color: #f3f5ff;
            font-family: 'Trebuchet MS', 'Segoe UI', sans-serif;
        }

        .candidate-page .candidate-shell {
            max-width: 1180px;
            margin: 0 auto;
            padding: 78px 28px;
        }

        .candidate-page .candidate-panel {
            position: relative;
            padding: 42px;
            border: 1px solid rgba(255, 255, 255, .16);
            background: radial-gradient(ellipse at 70% 0%, rgba(32, 45, 255, .35), transparent 42%), #111217;
        }

        .candidate-page .candidate-panel::before {
            content: '';
            position: absolute;
            inset: 0 28px;
            border-left: 1px solid rgba(255, 255, 255, .08);
            border-right: 1px solid rgba(255, 255, 255, .08);
            pointer-events: none;
        }

        .candidate-page .candidate-kicker {
            color: #1ee6e1;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .3em;
            text-transform: uppercase;
        }

        .candidate-page .candidate-title {
            margin-top: 12px;
            font-size: clamp(2.4rem, 5vw, 4.5rem);
            font-weight: 400;
            letter-spacing: -.07em;
            line-height: .95;
        }

        .candidate-page .candidate-status {
            border: 1px solid rgba(30, 230, 225, .5);
            border-radius: 0;
            background: rgba(30, 230, 225, .08);
            color: #1ee6e1;
            font-size: 11px;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .candidate-page .candidate-metric {
            border: 1px solid rgba(255, 255, 255, .12);
            border-top: 1px solid #1ee6e1;
            border-radius: 0;
            background: rgba(27, 30, 44, .8);
        }

        .candidate-page .candidate-metric p:first-child {
            color: #9ba3bd;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .1em;
        }

        .candidate-page .candidate-metric p:last-child {
            color: #f3f5ff;
            font-size: 20px;
        }

        .candidate-page .candidate-card {
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .16);
            border-radius: 0;
            background: #171924;
            transition: transform .25s ease, border-color .25s ease;
        }

        .candidate-page .candidate-card:hover {
            transform: translateY(-6px);
            border-color: #1ee6e1;
        }

        .candidate-page .candidate-photo {
            position: relative;
            aspect-ratio: 1 / 1.08;
            overflow: hidden;
            background: radial-gradient(circle at 70% 20%, rgba(30, 230, 225, .28), transparent 30%), linear-gradient(135deg, #2636ff, #151727);
        }

        .candidate-page .candidate-photo::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, transparent 55%, rgba(16, 17, 22, .8));
            pointer-events: none;
        }

        .candidate-page .candidate-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .4s ease;
        }

        .candidate-page .candidate-card:hover .candidate-photo img {
            transform: scale(1.04);
        }

        .candidate-page .candidate-content {
            display: flex;
            flex: 1;
            flex-direction: column;
            padding: 24px;
        }

        .candidate-page .candidate-number {
            color: #1ee6e1;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .2em;
            text-transform: uppercase;
        }

        .candidate-page .candidate-name {
            margin-top: 10px;
            min-height: 54px;
            color: #f3f5ff;
            font-size: 27px;
            font-weight: 400;
            line-height: 1;
        }

        .candidate-page .candidate-muted {
            color: #9ba3bd;
        }

        .candidate-page .candidate-vision {
            min-height: 72px;
        }

        .candidate-page .candidate-actions {
            margin-top: auto;
            padding-top: 20px;
        }

        .candidate-page .candidate-action {
            border-radius: 0;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            transition: background .2s ease, color .2s ease, border-color .2s ease;
        }

        .candidate-page .candidate-detail-action {
            border: 1px solid rgba(30, 230, 225, .55);
            background: transparent;
            color: #1ee6e1;
        }

        .candidate-page .candidate-detail-action:hover {
            background: #1ee6e1;
            color: #101116;
        }

        .candidate-page .candidate-vote-action {
            background: #2636ff;
            color: white;
            box-shadow: 0 0 20px rgba(38, 54, 255, .28);
        }

        .candidate-page .candidate-vote-action:hover {
            background: #1ee6e1;
            color: #101116;
        }

        .candidate-page .candidate-disabled {
            background: #323747;
            color: #9ba3bd;
        }

        html:not(.dark) .candidate-page {
            background: #f5f7fc;
            color: #151a33;
        }

        html:not(.dark) .candidate-page .candidate-panel {
            border-color: rgba(25, 34, 70, .14);
            background: radial-gradient(ellipse at 70% 0%, rgba(38, 54, 255, .1), transparent 42%), #f8f9fe;
        }

        html:not(.dark) .candidate-page .candidate-panel::before {
            border-color: rgba(25, 34, 70, .08);
        }

        html:not(.dark) .candidate-page .candidate-metric {
            border-color: rgba(25, 34, 70, .14);
            background: rgba(255, 255, 255, .86);
        }

        html:not(.dark) .candidate-page .candidate-metric p:last-child,
        html:not(.dark) .candidate-page .candidate-name {
            color: #151a33;
        }

        html:not(.dark) .candidate-page .candidate-card {
            border-color: rgba(25, 34, 70, .14);
            background: #fff;
        }

        html:not(.dark) .candidate-page .candidate-muted,
        html:not(.dark) .candidate-page .candidate-metric p:first-child {
            color: #626b86;
        }

        html:not(.dark) .candidate-page .candidate-title,
        html:not(.dark) .candidate-page .candidate-card h2,
        html:not(.dark) .candidate-page .candidate-card h3 {
            color: #151a33;
        }

        html:not(.dark) .candidate-page .candidate-status {
            border-color: rgba(38, 54, 255, .35);
            background: rgba(38, 54, 255, .06);
            color: #2636ff;
        }

        html:not(.dark) .candidate-page .candidate-vote-action {
            color: #ffffff;
        }

        @media (max-width: 640px) {
            .candidate-page .candidate-shell {
                padding: 45px 15px;
            }

            .candidate-page .candidate-panel {
                padding: 25px 18px;
            }

            .candidate-page .candidate-panel::before {
                inset: 0 15px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="candidate-page">
        <div class="candidate-shell">
            <div class="candidate-panel">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="candidate-kicker">Detail Pemilihan / {{ $election->year }}</p>
                        <h1 class="candidate-title">{{ $election->title }}</h1>
                    </div>
                    <span
                        class="candidate-status px-3 py-2 {{ $election->current_status === \App\Models\Election::STATUS_ACTIVE ? '' : 'opacity-70' }}">{{ $election->current_status }}</span>
                </div>
                <div class="mt-8 grid gap-6 md:grid-cols-3">
                    <div class="candidate-metric rounded-2xl p-5">
                        <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Tahun</p>
                        <p class="mt-2 text-xl font-black">{{ $election->year }}</p>
                    </div>
                    <div class="candidate-metric rounded-2xl p-5">
                        <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Total Suara</p>
                        <p class="mt-2 text-xl font-black">{{ $election->votes->count() }}</p>
                    </div>
                    <div class="candidate-metric rounded-2xl p-5">
                        <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Mulai - Selesai</p>
                        <p class="mt-2 text-xl font-black">{{ $election->start_time->translatedFormat('d M Y') }} -
                            {{ $election->end_time->translatedFormat('d M Y') }}</p>
                    </div>
                </div>

                <div class="mt-10 grid gap-6 lg:grid-cols-3">
                    @foreach ($election->candidates->take(3) as $candidate)
                        <div class="candidate-card">
                            <div class="candidate-photo">
                                @if ($candidate->photo_url)
                                    <img src="{{ $candidate->photo_url }}" alt="Foto {{ $candidate->name }}" loading="lazy">
                                @else
                                    <div class="flex h-full items-center justify-center px-6 text-center candidate-muted">
                                        Foto kandidat belum tersedia
                                    </div>
                                @endif
                            </div>
                            <div class="candidate-content">
                                <p class="candidate-number">Nomor Urut
                                    {{ $candidate->candidate_number }}</p>
                                <h2 class="candidate-name">{{ $candidate->name }}</h2>
                                <p class="candidate-muted mt-2 text-sm">{{ $candidate->class }} -
                                    {{ $candidate->major }}</p>
                                <p class="candidate-muted candidate-vision mt-4 text-sm">
                                    {{ Str::limit($candidate->vision, 140) }}</p>
                                <div class="candidate-actions flex flex-wrap gap-3">
                                    <a href="{{ route('candidate.show', $candidate) }}"
                                        class="candidate-action candidate-detail-action px-4 py-2">Lihat
                                        Detail</a>

                                    @if ($election->current_status === \App\Models\Election::STATUS_ACTIVE)
                                        @auth
                                            @if (!($hasVoted ?? false))
                                                <button type="button"
                                                    onclick="voteCandidate({{ $candidate->id }}, '{{ addslashes($candidate->name) }}', {{ $election->id }})"
                                                    class="candidate-action candidate-vote-action px-4 py-2">Pilih
                                                    Kandidat</button>
                                            @else
                                                <button type="button" disabled
                                                    class="candidate-action candidate-disabled px-4 py-2">Sudah
                                                    Memilih</button>
                                            @endif
                                        @else
                                            <button type="button"
                                                onclick="Swal.fire({ title: 'Silakan login', html: 'Anda harus login untuk memilih. <br><a href=\'{{ route('login') }}\' class=\'underline text-blue-600\'>Login sekarang</a>', icon: 'info' })"
                                                class="candidate-action candidate-vote-action px-4 py-2">Pilih
                                                Kandidat</button>
                                        @endauth
                                    @else
                                        <button type="button" disabled
                                            class="candidate-action candidate-disabled px-4 py-2">Pemilihan
                                            Berakhir</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

@push('scripts')
    <script>
        async function voteCandidate(candidateId, candidateName, electionId) {
            const confirmed = await Swal.fire({
                title: 'Konfirmasi Pilih Kandidat',
                html: `Apakah Anda yakin memilih kandidat berikut?<br><strong>${candidateName}</strong>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Pilih',
                cancelButtonText: 'Batal'
            });

            if (!confirmed.isConfirmed) return;

            const {
                value: token
            } = await Swal.fire({
                title: '',
                html: '<div class="token-popup-hero"><i class="fa-solid fa-key"></i></div><div class="token-popup-body"><h2 class="token-popup-heading">Masukkan Token Voting</h2><p class="token-popup-description">Gunakan token voting yang sudah diberikan kepada Anda.</p></div>',
                input: 'text',
                inputPlaceholder: 'Masukkan token voting Anda',
                showCancelButton: true,
                confirmButtonText: 'Kirim',
                cancelButtonText: 'Batal',
                inputAttributes: {
                    autocapitalize: 'off'
                }
            });

            if (!token) return;

            const payload = {
                election_id: electionId,
                candidate_id: candidateId,
                token: token
            };

            try {
                const res = await fetch("{{ route('vote.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (!res.ok) {
                    throw new Error(data.message || 'Terjadi kesalahan');
                }

                await Swal.fire({
                    title: 'Berhasil',
                    html: data.message || 'Suara Anda berhasil tersimpan.',
                    icon: 'success'
                });
                location.reload();
            } catch (err) {
                await Swal.fire({
                    title: 'Gagal',
                    html: err.message || 'Gagal mengirim suara.',
                    icon: 'error'
                });
            }
        }
    </script>
@endpush
