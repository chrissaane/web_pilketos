@extends('layouts.app')

@section('title', 'Panduan - ' . App\Models\SiteSetting::getValue('website_name', 'PILKETOS'))

@push('styles')
    <style>
        .guide-editorial {
            min-height: 100%;
            background: #101116;
            color: #f3f5ff;
            font-family: 'Trebuchet MS', 'Segoe UI', sans-serif;
        }

        .guide-editorial .guide-shell {
            max-width: 1180px;
            margin: 0 auto;
            padding: 78px 28px;
        }

        .guide-editorial .guide-hero {
            padding: 42px;
            border: 1px solid rgba(255, 255, 255, .16);
            background: radial-gradient(ellipse at 70% 0%, rgba(38, 54, 255, .42), transparent 45%), #111217;
        }

        .guide-editorial .guide-kicker {
            color: #1ee6e1;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .3em;
            text-transform: uppercase;
        }

        .guide-editorial .guide-title {
            margin-top: 14px;
            font-size: clamp(3rem, 7vw, 6.6rem);
            font-weight: 400;
            letter-spacing: -.08em;
            line-height: .88;
        }

        .guide-editorial .guide-title em {
            font-family: Georgia, 'Times New Roman', serif;
        }

        .guide-editorial .guide-copy {
            max-width: 650px;
            margin-top: 30px;
            color: #9ba3bd;
            font-size: 16px;
            line-height: 1.6;
        }

        .guide-editorial .guide-panel {
            margin-top: 14px;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, .14);
            border-top: 1px solid #1ee6e1;
            background: rgba(23, 25, 36, .76);
        }

        .guide-editorial .guide-panel-title {
            color: #1ee6e1;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .25em;
            text-transform: uppercase;
        }

        .guide-editorial .guide-panel h2 {
            margin-top: 12px;
            font-size: 28px;
            font-weight: 400;
            letter-spacing: -.04em;
        }

        .guide-editorial .guide-list {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
            margin-top: 32px;
        }

        .guide-editorial .guide-step {
            display: flex;
            gap: 16px;
        }

        .guide-editorial .guide-step-number {
            flex: 0 0 36px;
            color: #1ee6e1;
            font-family: Georgia, serif;
            font-size: 26px;
            font-style: italic;
        }

        .guide-editorial .guide-step h3 {
            color: #f3f5ff;
            font-size: 16px;
            font-weight: 500;
        }

        .guide-editorial .guide-step p,
        .guide-editorial .guide-extra p {
            margin-top: 7px;
            color: #9ba3bd;
            font-size: 13px;
            line-height: 1.55;
        }

        .guide-editorial .guide-extra-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
            margin-top: 14px;
        }

        .guide-editorial .guide-extra {
            padding: 24px;
            border: 1px solid rgba(255, 255, 255, .14);
            background: rgba(23, 25, 36, .6);
        }

        .guide-editorial .guide-extra h2 {
            color: #f3f5ff;
            font-size: 22px;
            font-weight: 400;
        }

        .guide-editorial .guide-extra-item {
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid rgba(255, 255, 255, .1);
        }

        .guide-editorial .guide-extra-item h3 {
            color: #1ee6e1;
            font-size: 14px;
            font-weight: 500;
        }

        @media (max-width: 640px) {
            .guide-editorial .guide-shell {
                padding: 45px 15px;
            }

            .guide-editorial .guide-hero,
            .guide-editorial .guide-panel {
                padding: 25px 20px;
            }

            .guide-editorial .guide-list,
            .guide-editorial .guide-extra-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    <div class="guide-editorial">
        <div class="guide-shell">
            <div class="guide-hero">
                <p class="guide-kicker">02 / Panduan Pemilihan</p>
                <h1 class="guide-title">Cara menggunakan<br><em>PILKETOS.</em></h1>
                <p class="guide-copy">Ikuti langkah sesuai peranmu agar proses pemilihan Ketua OSIS berjalan lancar, aman,
                    dan tercatat dengan baik.</p>
            </div>

            <section class="guide-panel">
                <p class="guide-panel-title">Panduan Bersama</p>
                <h2>Lima langkah, satu suara.</h2>
                <ol class="guide-list">
                    <li class="guide-step"><span class="guide-step-number">01</span>
                        <div>
                            <h3>Login sesuai akun</h3>
                            <p>Siswa masuk menggunakan
                                <strong>NIS</strong>. Guru dan Admin masuk menggunakan <strong>NIP atau email</strong> yang
                                terdaftar, lalu masukkan password.
                            </p>
                        </div>
                    </li>
                    <li class="guide-step"><span class="guide-step-number">02</span>
                        <div>
                            <h3>Buka dashboard</h3>
                            <p>Setelah login, sistem
                                akan mengarahkan pengguna ke dashboard sesuai perannya.</p>
                        </div>
                    </li>
                    <li class="guide-step"><span class="guide-step-number">03</span>
                        <div>
                            <h3>Lihat kandidat dan periode</h3>
                            <p>Baca profil, visi, misi,
                                jadwal, dan status periode sebelum menentukan tindakan.</p>
                        </div>
                    </li>
                    <li class="guide-step"><span class="guide-step-number">04</span>
                        <div>
                            <h3>Kelola atau berikan suara</h3>
                            <p>Siswa memasukkan token
                                pemilihan yang valid, lalu memilih kandidat. Guru dan Admin dapat mengelola data serta
                                memantau
                                proses sesuai kewenangan.</p>
                        </div>
                    </li>
                    <li class="guide-step"><span class="guide-step-number">05</span>
                        <div>
                            <h3>Lihat hasil</h3>
                            <p>Buka menu Hasil untuk
                                melihat statistik pemilihan dan rekap suara yang tersedia.</p>
                        </div>
                    </li>
                </ol>
            </section>
            @foreach (['siswa' => 'Panduan Tambahan Siswa', 'guru' => 'Panduan Tambahan Guru'] as $target => $label)
                @php($items = App\Models\GuideItem::targeted($target)->get())
                @if ($items->isNotEmpty())
                    <section class="guide-extra-grid">
                        <div class="guide-extra">
                            <h2>{{ $label }}</h2>
                            @foreach ($items as $item)
                                <div class="guide-extra-item">
                                    <h3>{{ $item->title }}</h3>
                                    <p>
                                        {{ $item->content }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            @endforeach
        </div>
    </div>
@endsection
