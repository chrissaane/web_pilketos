@extends('layouts.app')

@section('title', 'Beranda - Sistem Pemilihan Ketua OSIS SMKN 1 Bangsri')

@push('styles')
    <style>
        .pilketos-home {
            --ink: #f3f5ff;
            --muted: #9ba3bd;
            --line: rgba(255, 255, 255, .16);
            --blue: #2636ff;
            --cyan: #1ee6e1;
            background: #101116;
            color: var(--ink);
            font-family: 'Trebuchet MS', 'Segoe UI', sans-serif;
            overflow: hidden;
        }

        .pilketos-home h1,
        .pilketos-home h2,
        .pilketos-home h3,
        .pilketos-home p {
            margin: 0;
        }

        .pilketos-home .display-serif {
            font-family: Georgia, 'Times New Roman', serif;
            font-style: italic;
        }

        .pilketos-shell {
            position: relative;
            max-width: 1180px;
            margin: 0 auto;
            padding: 0 28px;
        }

        .pilketos-shell:before {
            content: '';
            position: absolute;
            inset: 0 28px;
            border-left: 1px solid var(--line);
            border-right: 1px solid var(--line);
            pointer-events: none;
        }

        .pilketos-hero {
            position: relative;
            min-height: 840px;
            padding: 92px 12% 76px;
            border: 1px solid var(--line);
            background: radial-gradient(ellipse at 57% 7%, rgba(31, 40, 255, .94) 0, rgba(15, 18, 112, .78) 22%, transparent 47%), radial-gradient(ellipse at 43% 58%, rgba(23, 48, 255, .7), transparent 34%), #111217;
        }

        .pilketos-hero:after {
            content: '';
            position: absolute;
            width: 520px;
            height: 520px;
            right: -240px;
            top: -100px;
            background: #2636ff;
            filter: blur(110px);
            opacity: .55;
            border-radius: 50%;
            pointer-events: none;
        }

        .eyebrow {
            color: var(--cyan);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .28em;
            text-transform: uppercase;
        }

        .hero-kicker {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #d9ddf2;
            font-size: 12px;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .hero-kicker:before {
            content: '';
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--cyan);
            box-shadow: 0 0 18px #1ee6e1;
        }

        .hero-title {
            position: relative;
            z-index: 1;
            max-width: 730px;
            margin-top: 40px !important;
            font-size: clamp(3.7rem, 8vw, 7.8rem);
            font-weight: 400;
            letter-spacing: -.08em;
            line-height: .86;
        }

        .hero-title span {
            display: block;
            text-decoration: underline;
            text-decoration-thickness: 2px;
            text-underline-offset: 10px;
        }

        .hero-copy {
            max-width: 550px;
            margin: 62px 0 0 auto !important;
            color: #e7e9f6;
            font-size: 18px;
            line-height: 1.35;
        }

        .hero-copy strong {
            color: #fff;
            font-weight: 500;
        }

        .hero-copy em {
            color: var(--muted);
            font-style: normal;
        }

        .stats-grid,
        .election-grid,
        .guide-grid,
        .result-strip {
            display: grid;
            gap: 14px;
        }

        .stats-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 28px;
            margin-top: 50px;
        }

        .stat,
        .result-stat {
            border-top: 1px solid var(--cyan);
            padding-top: 17px;
        }

        .stat-number,
        .result-stat b {
            font-family: Georgia, serif;
            font-style: italic;
            line-height: .95;
        }

        .stat-number {
            font-size: clamp(2.8rem, 5vw, 4.7rem);
        }

        .stat-label {
            margin-top: 12px !important;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .stat-note {
            max-width: 160px;
            margin-top: 6px !important;
            color: var(--muted);
            font-size: 10px;
            line-height: 1.35;
        }

        .hero-card {
            position: absolute;
            z-index: 2;
            top: -22px;
            right: 28px;
            width: 190px;
            padding: 19px;
            border: 1px solid var(--line);
            background: rgba(24, 26, 39, .72);
            backdrop-filter: blur(12px);
        }

        .hero-card h2 {
            margin-top: 17px;
            font-size: 19px;
            font-weight: 400;
            line-height: .96;
        }

        .hero-card p {
            margin-top: 18px !important;
            color: var(--muted);
            font-size: 10px;
            line-height: 1.3;
        }

        .hero-card strong {
            display: block;
            margin-top: 19px;
            padding-top: 12px;
            border-top: 1px solid var(--cyan);
            font-family: Georgia, serif;
            font-size: 32px;
            font-style: italic;
        }

        .hero-card small {
            color: var(--muted);
            font-size: 8px;
            text-transform: uppercase;
        }

        .home-section {
            position: relative;
            padding: 78px 12%;
            border: 1px solid var(--line);
            border-top: 0;
            background: radial-gradient(ellipse at 52% 5%, rgba(33, 50, 255, .48), transparent 42%), #111217;
        }

        .section-heading {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 34px;
        }

        .section-heading h2 {
            margin-top: 10px;
            font-size: clamp(2.3rem, 5vw, 4.6rem);
            font-weight: 400;
            letter-spacing: -.07em;
            line-height: .9;
        }

        .section-heading p {
            max-width: 280px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.5;
        }

        .election-grid {
            grid-template-columns: repeat(3, 1fr);
        }

        .election-visual {
            position: relative;
            height: 142px;
            margin: -22px -22px 22px;
            overflow: hidden;
            border-bottom: 1px solid var(--line);
            background: radial-gradient(circle at 70% 15%, rgba(30, 230, 225, .42), transparent 28%), linear-gradient(135deg, #2735e8, #111326 72%);
        }

        .election-visual::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, transparent 35%, rgba(15, 16, 20, .75));
            pointer-events: none;
        }

        .election-visual img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
            opacity: .9;
            transition: transform .4s ease, opacity .4s ease;
        }

        .election-card:hover .election-visual img {
            transform: scale(1.06);
            opacity: 1;
        }

        .election-card {
            position: relative;
            min-height: 260px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 22px;
            border: 1px solid var(--line);
            background: rgba(23, 25, 36, .76);
            transition: transform .25s, border-color .25s, background .25s;
        }

        .election-card:hover {
            transform: translateY(-6px);
            border-color: var(--cyan);
            background: rgba(36, 39, 62, .9);
        }

        .election-card h3 {
            margin-top: 25px;
            font-size: 25px;
            font-weight: 400;
            line-height: 1;
        }

        .election-card p {
            margin-top: 12px !important;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.4;
        }

        .card-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 16px;
            border-top: 1px solid var(--line);
            color: #d8ddf4;
            font-size: 11px;
        }

        .status {
            position: absolute;
            top: 14px;
            right: 14px;
            z-index: 2;
            padding: 7px 10px;
            border: 1px solid rgba(30, 230, 225, .45);
            background: rgba(16, 17, 22, .72);
            color: var(--cyan);
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .12em;
        }

        .election-card-status {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 3;
            display: block;
            padding: 4px 7px;
            border: 1px solid rgba(30, 230, 225, .45);
            background: rgba(16, 17, 22, .72);
            color: var(--cyan);
            font-size: 8px;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .guide-grid {
            grid-template-columns: repeat(4, 1fr);
        }

        .guide-item {
            min-height: 150px;
            padding: 18px;
            border: 1px solid var(--line);
            background: rgba(23, 25, 36, .6);
        }

        .guide-number {
            color: var(--cyan);
            font-family: Georgia, serif;
            font-size: 25px;
            font-style: italic;
        }

        .guide-item h3 {
            margin-top: 25px;
            font-size: 16px;
            font-weight: 500;
        }

        .guide-item p {
            margin-top: 8px !important;
            color: var(--muted);
            font-size: 11px;
            line-height: 1.4;
        }

        .result-strip {
            grid-template-columns: repeat(3, 1fr);
            margin: 25px 0 35px;
        }

        .result-stat {
            padding: 20px;
            background: rgba(23, 25, 36, .6);
        }

        .result-stat b {
            display: block;
            font-size: 40px;
        }

        .result-stat span {
            color: var(--muted);
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .12em;
        }

        .progress-row {
            display: grid;
            grid-template-columns: 40px minmax(0, 1fr);
            align-items: center;
            column-gap: 10px;
            margin-top: 17px;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            color: #e4e6f3;
            font-size: 12px;
        }

        .progress-candidate {
            display: flex;
            min-width: 0;
            align-items: center;
        }

        .progress-candidate-photo,
        .progress-candidate-fallback {
            width: 40px;
            height: 40px;
            flex: 0 0 40px;
            border-radius: 0;
        }

        .progress-candidate-photo {
            object-fit: cover;
            border: 1px solid var(--cyan);
        }

        .progress-candidate-fallback {
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--blue);
            color: #fff;
            font-weight: 700;
        }

        .progress-details {
            min-width: 0;
        }

        .progress-track {
            height: 3px;
            margin-top: 8px;
            background: rgba(255, 255, 255, .12);
        }

        .progress-value {
            height: 100%;
            background: linear-gradient(90deg, var(--blue), var(--cyan));
        }

        .home-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--cyan);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .home-link:after {
            content: '↗';
            font-size: 18px;
        }

        html:not(.dark) .pilketos-home {
            --ink: #151a33;
            --muted: #626b86;
            --line: rgba(25, 34, 70, .16);
            background: #f5f7fc;
            color: var(--ink);
        }

        html:not(.dark) .pilketos-hero {
            background: radial-gradient(ellipse at 57% 7%, rgba(70, 84, 255, .3) 0, rgba(137, 145, 255, .2) 22%, transparent 47%), radial-gradient(ellipse at 43% 58%, rgba(74, 92, 255, .18), transparent 34%), #f8f9fe;
        }

        html:not(.dark) .pilketos-hero::after {
            opacity: .12;
        }

        html:not(.dark) .hero-kicker {
            color: #39415e;
        }

        html:not(.dark) .eyebrow,
        html:not(.dark) .status,
        html:not(.dark) .home-link {
            color: var(--cyan);
        }

        html:not(.dark) .hero-copy {
            color: #303852;
        }

        html:not(.dark) .hero-copy strong {
            color: #151a33;
        }

        html:not(.dark) .stat-label {
            color: #151a33;
        }

        html:not(.dark) .hero-card {
            background: rgba(255, 255, 255, .78);
        }

        html:not(.dark) .home-section {
            background: radial-gradient(ellipse at 52% 5%, rgba(61, 77, 255, .14), transparent 42%), #f5f7fc;
        }

        html:not(.dark) .home-section[style*="#15161c"],
        html:not(.dark) .home-section[style*="#0f1014"] {
            background: #eef1f8 !important;
        }

        html:not(.dark) .election-card,
        html:not(.dark) .guide-item,
        html:not(.dark) .result-stat {
            background: rgba(255, 255, 255, .8);
        }

        html:not(.dark) .election-card:hover {
            background: #fff;
        }

        html:not(.dark) .election-visual::after {
            background: linear-gradient(180deg, transparent 35%, rgba(22, 29, 65, .35));
        }

        html:not(.dark) .card-meta,
        html:not(.dark) .progress-label {
            color: #303852;
        }

        html:not(.dark) .result-stat b {
            color: #151a33;
        }

        html:not(.dark) .progress-track {
            background: rgba(25, 34, 70, .14);
        }

        @media (max-width:760px) {
            .pilketos-shell {
                padding: 0 15px;
            }

            .pilketos-shell:before {
                inset: 0 15px;
            }

            .pilketos-hero,
            .home-section {
                padding: 65px 24px;
            }

            .pilketos-hero {
                min-height: 790px;
            }

            .hero-card {
                position: relative;
                top: auto;
                right: auto;
                width: auto;
                margin: 48px 0 0;
            }

            .hero-title {
                margin-top: 28px !important;
                font-size: clamp(3.3rem, 16vw, 5.3rem);
            }

            .hero-copy {
                margin-top: 52px !important;
                font-size: 16px;
            }

            .stats-grid,
            .election-grid,
            .guide-grid,
            .result-strip {
                grid-template-columns: 1fr;
                gap: 18px;
            }

            .section-heading {
                display: block;
            }

            .section-heading p {
                margin-top: 22px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="pilketos-home">
        <div class="pilketos-shell">
            <section class="pilketos-hero">
                <div class="hero-card"><span class="hero-kicker">PILKETOS</span>
                    <h2>Suara yang membentuk masa depan sekolah.</h2>
                    <p>Platform pemilihan Ketua OSIS yang dirancang untuk proses demokrasi yang jujur, aman, dan mudah
                        diikuti.</p><strong>{{ $resultParticipation }}%</strong><small>partisipasi terdata</small>
                </div>
                <p class="hero-kicker">E-Voting resmi {{ $settings['school_name'] }}</p>
                <h1 class="hero-title">Pilih pemimpin<br><span class="display-serif">masa depan.</span></h1>
                <p class="hero-copy"><strong>Setiap suara punya arti.</strong> Kenali kandidat, pahami visi mereka, lalu
                    gunakan hak pilihmu secara <em>jujur, adil, dan transparan.</em></p>
                <div class="stats-grid">
                    <div class="stat">
                        <p class="stat-number">{{ $elections->count() }}</p>
                        <p class="stat-label">Periode pemilihan</p>
                        <p class="stat-note">Seluruh agenda pemilihan yang tersedia.</p>
                    </div>
                    <div class="stat">
                        <p class="stat-number">{{ $resultCandidates->count() }}</p>
                        <p class="stat-label">Kandidat pilihan</p>
                        <p class="stat-note">Calon pemimpin dengan visi untuk sekolah.</p>
                    </div>
                    <div class="stat">
                        <p class="stat-number">{{ $resultTotalVotes }}</p>
                        <p class="stat-label">Suara tercatat</p>
                        <p class="stat-note">Data suara tersimpan secara aman.</p>
                    </div>
                </div>
            </section>
            <section id="pemilihan" class="home-section">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">01 / Pemilihan</p>
                        <h2>Temukan<br><span class="display-serif">pilihanmu.</span></h2>
                    </div>
                    <p>Telusuri periode yang sedang berlangsung dan kenali kandidat terbaik untuk mewakili suara siswa.</p>
                </div>
                <div class="election-grid">
                    @forelse ($elections as $election)
                        <a href="{{ route('election.show', $election) }}" class="election-card">
                            <span class="election-card-status">{{ $election->current_status }}</span>
                            <div class="election-visual">
                                @if ($election->banner_url)
                                    <img src="{{ $election->banner_url }}" alt="Gambar {{ $election->title }}"
                                        loading="lazy">
                                @endif
                            </div>
                            <div>
                                <div><span class="eyebrow">{{ $election->year }}</span></div>
                                <h3>{{ $election->title }}</h3>
                                <p>{{ Str::limit($election->description, 100) }}</p>
                            </div>
                            <div class="card-meta"><span>{{ $election->candidates->count() }} kandidat</span><span
                                    class="home-link">Lihat detail</span></div>
                    </a>@empty<p style="color:var(--muted);font-size:12px;">Belum ada periode pemilihan yang tersedia.
                        </p>
                    @endforelse
                </div>
            </section>
            <section id="panduan" class="home-section" style="background:#15161c;">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">02 / Panduan</p>
                        <h2>Empat langkah<br><span class="display-serif">satu suara.</span></h2>
                    </div><a href="{{ route('guide') }}" class="home-link">Panduan lengkap</a>
                </div>
                <div class="guide-grid">
                    @foreach ([['01', 'Login', 'Masuk menggunakan akun yang terdaftar.'], ['02', 'Kenali kandidat', 'Baca visi, misi, dan profil kandidat.'], ['03', 'Masukkan token', 'Gunakan token pemilihan yang valid.'], ['04', 'Kirim suara', 'Periksa pilihan lalu konfirmasi.']] as $step)
                        <div class="guide-item"><span class="guide-number">{{ $step[0] }}</span>
                            <h3>{{ $step[1] }}</h3>
                            <p>{{ $step[2] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
            @if (($settings['show_statistics'] ?? '1') === '1')
                <section id="hasil" class="home-section">
                    <div class="section-heading">
                        <div>
                            <p class="eyebrow">03 / Hasil</p>
                            <h2>Suara yang<br><span class="display-serif">terlihat.</span></h2>
                        </div><a href="{{ route('results') }}" class="home-link">Semua hasil</a>
                    </div>
                    <div class="result-strip">
                        <div class="result-stat"><b>{{ $resultTotalVotes }}</b><span>Total suara</span></div>
                        <div class="result-stat"><b>{{ $resultVoterCount }}</b><span>Pemilih terdaftar</span></div>
                        <div class="result-stat"><b>{{ $resultParticipation }}%</b><span>Tingkat partisipasi</span></div>
                    </div>
                    @forelse ($resultCandidates as $candidate)
                        @php($candidatePercent = $resultTotalVotes > 0 ? round(($candidate->votes_count / $resultTotalVotes) * 100) : 0)
                        <div class="progress-row">
                            @if ($candidate->photo_url)
                                <img src="{{ $candidate->photo_url }}" alt="Foto {{ $candidate->name }}"
                                    class="progress-candidate-photo" loading="lazy">
                            @else
                                <span class="progress-candidate-fallback" aria-hidden="true">
                                    {{ str($candidate->name)->substr(0, 1)->upper() }}
                                </span>
                            @endif
                            <div class="progress-details">
                                <div class="progress-label">
                                    <span class="progress-candidate">{{ $candidate->name }}</span>
                                    <span>{{ $candidatePercent }}% / {{ $candidate->votes_count }} suara</span>
                                </div>
                                <div class="progress-track">
                                    <div class="progress-value" style="width:{{ $candidatePercent }}%"></div>
                                </div>
                            </div>
                        </div>
                    @empty<p style="color:var(--muted);font-size:12px;">Belum ada data suara.</p>
                    @endforelse
                </section>
            @endif
            <section id="tentang" class="home-section" style="background:#0f1014;">
                <p class="eyebrow">04 / Tentang PILKETOS</p>
                <h2
                    style="margin-top:15px;font-size:clamp(2.8rem,6vw,6rem);font-weight:400;letter-spacing:-.07em;line-height:.9;">
                    Demokrasi sekolah,<br><span class="display-serif">dimulai dari sini.</span></h2>
                <p style="max-width:600px;margin-top:25px;color:var(--muted);font-size:15px;line-height:1.6;">
                    {{ App\Models\AboutPage::where('section', 'website_description')->value('content') ?: 'PILKETOS adalah sistem pemilihan Ketua OSIS secara digital yang membantu siswa memberikan suara dengan aman, transparan, dan mudah.' }}
                </p><a href="{{ route('about') }}" class="home-link" style="margin-top:28px;">Kenali PILKETOS</a>
            </section>
        </div>
    </div>
    <div class="hidden">
        <div class="relative overflow-hidden">
            <section class="relative">
                <div
                    class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.15),_transparent_45%)]">
                </div>
                <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
                    <div class="grid items-center gap-10 lg:grid-cols-[1.1fr_0.9fr]">
                        <div class="max-w-2xl">
                            <span
                                class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3.5 py-2 text-sm font-semibold text-blue-700 shadow-sm dark:border-blue-900 dark:bg-blue-950/40 dark:text-blue-300">
                                <span class="h-2.5 w-2.5 rounded-full bg-blue-600"></span>
                                E-Voting Resmi {{ App\Models\SiteSetting::getValue('school_name', 'SMKN 1 Bangsri') }}
                            </span>
                            <h1
                                class="mt-6 text-4xl font-black leading-tight tracking-tight text-slate-900 dark:text-white sm:text-5xl lg:text-6xl">
                                Sistem Pemilihan Ketua OSIS
                                <span
                                    class="block bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent">
                                    {{ App\Models\SiteSetting::getValue('school_name', 'SMKN 1 Bangsri') }}
                                </span>
                            </h1>
                            <p class="mt-6 text-lg leading-relaxed text-slate-600 dark:text-slate-300">
                                {{ App\Models\SiteSetting::getValue('website_name', 'PILKETOS') }} membantu proses
                                pemungutan
                                suara secara aman, transparan, dan modern.
                            </p>
                            <div class="mt-8 flex flex-wrap gap-3 sm:gap-4">
                                <a href="#pemilihan"
                                    class="rounded-2xl bg-blue-600 px-6 py-3.5 font-semibold text-white shadow-lg shadow-blue-600/20 transition duration-300 hover:-translate-y-0.5 hover:bg-blue-700">
                                    Lihat Pemilihan
                                </a>
                                <a href="#panduan"
                                    class="rounded-2xl border border-slate-300 bg-white px-6 py-3.5 font-semibold text-slate-700 transition duration-300 hover:-translate-y-0.5 hover:border-blue-500 hover:text-blue-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-blue-400 dark:hover:text-blue-400">
                                    Panduan Voting
                                </a>
                            </div>
                        </div>

                        <div
                            class="rounded-[28px] border border-slate-200 bg-white/80 p-5 shadow-2xl shadow-slate-200/70 backdrop-blur dark:border-slate-800 dark:bg-slate-900/80 dark:shadow-black/20">
                            <div
                                class="rounded-[24px] bg-gradient-to-br from-blue-600 via-indigo-700 to-purple-700 p-6 text-white">
                                <div class="flex items-center justify-between">
                                    <span
                                        class="rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.25em]">Pemilihan
                                        Aktif</span>
                                    <span class="text-sm font-semibold">2026</span>
                                </div>
                                <h2 class="mt-8 text-2xl font-black sm:text-3xl">Pemilihan Ketua OSIS</h2>
                                <p class="mt-3 text-sm leading-relaxed text-blue-50">Pilih calon terbaik untuk mewakili
                                    suara
                                    siswa dan membawa perubahan positif bagi sekolah.</p>
                                <div class="mt-8 grid gap-3 sm:grid-cols-2">
                                    <div class="rounded-2xl border border-white/20 bg-white/10 p-4">
                                        <p class="text-xs uppercase tracking-[0.25em] text-blue-100">Kandidat</p>
                                        <p class="mt-2 text-2xl font-black">3</p>
                                    </div>
                                    <div class="rounded-2xl border border-white/20 bg-white/10 p-4">
                                        <p class="text-xs uppercase tracking-[0.25em] text-blue-100">Status</p>
                                        <p class="mt-2 text-xl font-black">Aktif</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="panduan" class="mx-auto max-w-7xl px-4 pb-8 sm:px-6 lg:px-8">
                <div
                    class="rounded-[28px] border border-blue-100 bg-blue-50/80 p-6 shadow-sm dark:border-blue-900/50 dark:bg-blue-950/30 sm:p-8">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.25em] text-blue-600 dark:text-blue-400">
                                Panduan Pemilihan</p>
                            <h2 class="mt-2 text-3xl font-black text-slate-900 dark:text-white">Cara Menggunakan PILKETOS
                            </h2>
                        </div>
                        <a href="{{ route('guide') }}"
                            class="inline-flex w-fit items-center justify-center rounded-2xl bg-blue-600 px-5 py-3 font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700">
                            Lihat Selengkapnya
                        </a>
                    </div>
                    <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ([['number' => '1', 'title' => 'Login', 'description' => 'Masuk menggunakan akun yang terdaftar.'], ['number' => '2', 'title' => 'Lihat Kandidat', 'description' => 'Kenali visi, misi, dan profil kandidat.'], ['number' => '3', 'title' => 'Masukkan Token', 'description' => 'Gunakan token pemilihan yang valid.'], ['number' => '4', 'title' => 'Pilih Kandidat', 'description' => 'Periksa pilihan lalu konfirmasi suara.']] as $step)
                            <div
                                class="rounded-2xl border border-blue-100 bg-white/80 p-4 dark:border-blue-900/60 dark:bg-slate-900/50">
                                <span
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-sm font-black text-white">
                                    {{ $step['number'] }}
                                </span>
                                <h3 class="mt-3 font-bold text-slate-900 dark:text-white">{{ $step['title'] }}</h3>
                                <p class="mt-1 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                                    {{ $step['description'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="pemilihan" class="mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8">
                <div class="mb-10 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-blue-600">Daftar Pemilihan</p>
                        <h2 class="text-3xl font-black text-slate-900 dark:text-white">Periode Pilketos</h2>
                    </div>
                    <p class="max-w-xl text-sm text-slate-500 dark:text-slate-400">Pilih periode pemilihan yang sedang
                        berlangsung atau lihat arsip periode sebelumnya dengan tampilan yang lebih rapi.</p>
                </div>

                <div class="grid gap-8 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($elections as $election)
                        <a href="{{ route('election.show', $election) }}"
                            class="group relative flex flex-col overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                            <span
                                class="election-card-status {{ $election->current_status === \App\Models\Election::STATUS_ACTIVE ? 'bg-emerald-500 text-white' : 'bg-slate-700/80 text-slate-100' }}">
                                {{ $election->current_status }}
                            </span>
                            <div class="relative h-48 overflow-hidden rounded-t-[28px] p-6"
                                style="{{ $election->banner_path ? "background-image:url('{$election->banner_url}'); background-size:cover; background-position:center;" : '' }}">
                                <div class="absolute inset-0 bg-slate-950/40"></div>
                                <div
                                    class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(255,255,255,0.18),_transparent_35%)]">
                                </div>
                                <div class="relative z-10 mt-auto flex h-full flex-col justify-end">
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-blue-100">Periode</p>
                                    <h3 class="mt-2 text-2xl font-black text-white">{{ $election->title }}</h3>
                                </div>
                            </div>
                            <div class="flex flex-grow flex-col justify-between p-6">
                                <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                                    {{ Str::limit($election->description, 120) }}</p>
                                <div
                                    class="mt-6 flex items-center justify-between border-t border-slate-200 pt-4 text-sm text-slate-600 dark:border-slate-800 dark:text-slate-300">
                                    <span>Tahun {{ $election->year }}</span>
                                    <span
                                        class="font-semibold text-blue-600 transition group-hover:text-blue-700 dark:text-blue-400">Lihat
                                        detail →</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>

            @if (($settings['show_statistics'] ?? '1') === '1')
                <section id="hasil" class="mx-auto max-w-7xl px-4 pb-12 sm:px-6 lg:px-8">
                    <div
                        class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <p
                                    class="text-sm font-semibold uppercase tracking-[0.25em] text-blue-600 dark:text-blue-400">
                                    Hasil
                                    Pemilihan</p>
                                <h2 class="mt-2 text-3xl font-black text-slate-900 dark:text-white">Statistik Suara</h2>
                                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                    {{ $resultElection?->title ?? 'Belum ada data pemilihan' }}
                                </p>
                            </div>
                            <a href="{{ route('results') }}"
                                class="inline-flex w-fit items-center justify-center rounded-2xl border border-blue-200 px-5 py-3 font-semibold text-blue-700 transition hover:bg-blue-50 dark:border-blue-900 dark:text-blue-300 dark:hover:bg-blue-950/40">
                                Lihat Semua Hasil
                            </a>
                        </div>

                        <div class="mt-6 grid gap-4 sm:grid-cols-3">
                            <div class="rounded-2xl bg-blue-50 p-4 dark:bg-blue-950/30">
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-600 dark:text-blue-400">
                                    Total
                                    Suara</p>
                                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $resultTotalVotes }}
                                </p>
                            </div>
                            <div class="rounded-2xl bg-emerald-50 p-4 dark:bg-emerald-950/30">
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-600 dark:text-emerald-400">
                                    Pemilih</p>
                                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $resultVoterCount }}
                                </p>
                            </div>
                            <div class="rounded-2xl bg-amber-50 p-4 dark:bg-amber-950/30">
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-600 dark:text-amber-400">
                                    Partisipasi</p>
                                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">
                                    {{ $resultParticipation }}%
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 space-y-4">
                            @forelse ($resultCandidates as $candidate)
                                @php($candidatePercent = $resultTotalVotes > 0 ? round(($candidate->votes_count / $resultTotalVotes) * 100) : 0)
                                <div>
                                    <div class="mb-2 flex items-center justify-between gap-4 text-sm">
                                        <span
                                            class="font-semibold text-slate-900 dark:text-white">{{ $candidate->name }}</span>
                                        <span class="text-slate-500 dark:text-slate-400">{{ $candidate->votes_count }}
                                            suara
                                            ({{ $candidatePercent }}%)
                                        </span>
                                    </div>
                                    <div class="h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                        <div class="h-full rounded-full bg-gradient-to-r from-blue-600 to-indigo-500"
                                            style="width: {{ $candidatePercent }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <p
                                    class="rounded-2xl bg-slate-50 p-5 text-sm text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                    Belum ada data suara.</p>
                            @endforelse
                        </div>
                    </div>
                </section>
            @endif

            <section id="tentang" class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden rounded-[28px] border border-blue-100 bg-blue-50/80 shadow-sm dark:border-blue-900/50 dark:bg-blue-950/30">
                    <div class="grid items-center gap-8 p-7 sm:p-10 lg:grid-cols-[1fr_0.8fr]">
                        <div class="space-y-4">
                            @php($aboutImage = App\Models\AboutPage::where('section', 'about_image')->value('content'))
                            @if ($aboutImage)
                                @php($aboutImageUrl = filter_var($aboutImage, FILTER_VALIDATE_URL) ? $aboutImage : Illuminate\Support\Facades\Storage::url($aboutImage))
                                <img src="{{ $aboutImageUrl }}" alt="Tentang PILKETOS"
                                    class="aspect-video w-full rounded-2xl object-cover shadow-lg">
                            @endif
                        </div>
                        <div class="max-w-3xl">
                            <p class="text-sm font-semibold uppercase tracking-[0.25em] text-blue-600 dark:text-blue-400">
                                Tentang PILKETOS</p>
                            <h2 class="mt-3 text-3xl font-black text-slate-900 dark:text-white">Tentang PILKETOS SMKN 1
                                Bangsri
                            </h2>
                            <p class="mt-4 leading-relaxed text-slate-600 dark:text-slate-300">
                                {{ App\Models\AboutPage::where('section', 'website_description')->value('content') ?: 'PILKETOS adalah sistem pemilihan Ketua OSIS secara digital yang membantu siswa memberikan suara dengan aman, transparan, dan mudah.' }}
                            </p>
                            <a href="{{ route('about') }}"
                                class="mt-4 inline-flex items-center justify-center rounded-2xl bg-blue-600 px-5 py-3 font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700">
                                Lihat Selengkapnya
                            </a>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </div>
@endsection
