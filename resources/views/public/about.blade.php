@extends('layouts.app')

@section('title', 'Tentang - ' . App\Models\SiteSetting::getValue('website_name', 'PILKETOS'))

@push('styles')
    <style>
        .public-editorial {
            min-height: 100%;
            background: #101116;
            color: #f3f5ff;
            font-family: 'Trebuchet MS', 'Segoe UI', sans-serif;
        }

        .public-editorial .editorial-shell {
            max-width: 1180px;
            margin: 0 auto;
            padding: 78px 28px;
        }

        .public-editorial .editorial-hero {
            padding: 42px;
            border: 1px solid rgba(255, 255, 255, .16);
            background: radial-gradient(ellipse at 75% 0%, rgba(38, 54, 255, .42), transparent 45%), #111217;
        }

        .public-editorial .editorial-kicker {
            color: #1ee6e1;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .3em;
            text-transform: uppercase;
        }

        .public-editorial .editorial-title {
            margin-top: 14px;
            max-width: 800px;
            font-size: clamp(3rem, 7vw, 6.6rem);
            font-weight: 400;
            letter-spacing: -.08em;
            line-height: .88;
        }

        .public-editorial .editorial-title em {
            font-family: Georgia, 'Times New Roman', serif;
        }

        .public-editorial .editorial-copy {
            max-width: 650px;
            margin-top: 30px;
            color: #9ba3bd;
            font-size: 16px;
            line-height: 1.6;
        }

        .public-editorial .editorial-image {
            width: 100%;
            max-height: 420px;
            margin-top: 14px;
            border: 1px solid rgba(30, 230, 225, .35);
            object-fit: cover;
            opacity: .9;
        }

        .public-editorial .editorial-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
            margin-top: 14px;
        }

        .public-editorial .editorial-card {
            min-height: 220px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, .14);
            border-top: 1px solid #1ee6e1;
            background: rgba(23, 25, 36, .76);
        }

        .public-editorial .editorial-number {
            color: #1ee6e1;
            font-family: Georgia, serif;
            font-size: 25px;
            font-style: italic;
        }

        .public-editorial .editorial-card h2 {
            margin-top: 30px;
            font-size: 24px;
            font-weight: 400;
            letter-spacing: -.04em;
        }

        .public-editorial .editorial-card p {
            margin-top: 14px;
            color: #9ba3bd;
            font-size: 13px;
            line-height: 1.6;
        }

        @media (max-width: 640px) {
            .public-editorial .editorial-shell {
                padding: 45px 15px;
            }

            .public-editorial .editorial-hero {
                padding: 25px 20px;
            }

            .public-editorial .editorial-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $about = [
            'website_description' => App\Models\AboutPage::where('section', 'website_description')->value('content'),
            'school_info' => App\Models\AboutPage::where('section', 'school_info')->value('content'),
            'system_info' => App\Models\AboutPage::where('section', 'system_info')->value('content'),
            'committee_info' => App\Models\AboutPage::where('section', 'committee_info')->value('content'),
            'about_image' => App\Models\AboutPage::where('section', 'about_image')->value('content'),
        ];
        $aboutSections = App\Models\AboutPage::where('section', '!=', 'about_image')
            ->where('section', '!=', 'website_description')
            ->get();
    @endphp

    <div class="public-editorial">
        <div class="editorial-shell">
            <div class="editorial-hero">
                <p class="editorial-kicker">04 / Tentang Kami</p>
                <h1 class="editorial-title">Tentang PILKETOS<br><em>SMKN 1 Bangsri.</em></h1>
                <p class="editorial-copy">
                    {{ $about['website_description'] ?: 'PILKETOS merupakan website pemilihan Ketua OSIS SMKN 1 Bangsri yang dirancang untuk menghadirkan proses demokrasi sekolah secara aman, transparan, dan mudah diikuti.' }}
                </p>
            </div>

            @if ($about['about_image'])
                @php($aboutImageUrl = filter_var($about['about_image'], FILTER_VALIDATE_URL) ? $about['about_image'] : Illuminate\Support\Facades\Storage::url($about['about_image']))
                <img src="{{ $aboutImageUrl }}" alt="Tentang PILKETOS" class="editorial-image aspect-video">
            @endif

            <div class="editorial-grid">
                @foreach ($aboutSections as $index => $section)
                    <section class="editorial-card">
                        <p class="editorial-number">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</p>
                        <h2>{{ Str::headline(Str::after($section->section, 'custom_')) }}</h2>
                        <p>{{ $section->content }}</p>
                    </section>
                @endforeach
            </div>
        </div>
    </div>
@endsection
