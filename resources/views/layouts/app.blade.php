<!DOCTYPE html>
<html lang="id" x-data="{ theme: localStorage.getItem('theme') || 'light', mobileMenu: false }" x-init="document.documentElement.classList.toggle('dark', theme === 'dark');
$watch('theme', value => {
    localStorage.setItem('theme', value);
    document.documentElement.classList.toggle('dark', value === 'dark');
})">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php($faviconPath = App\Models\SiteSetting::getValue('favicon_path', ''))
    @if ($faviconPath)
        <link rel="icon"
            href="{{ filter_var($faviconPath, FILTER_VALIDATE_URL) ? $faviconPath : Illuminate\Support\Facades\Storage::url($faviconPath) }}">
    @endif
    <title>@yield('title', config('app.name', 'PILKETOS SMKN 1 Bangsri'))</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
    <style>
        html {
            scroll-behavior: smooth;
        }

        section[id] {
            scroll-margin-top: 6rem;
        }

        /* Hide elements marked with x-cloak until Alpine initializes */
        [x-cloak] {
            display: none !important;
        }

        .pilketos-swal-popup {
            position: relative;
            overflow: hidden !important;
            border: 1px solid rgba(30, 230, 225, .38) !important;
            border-top: 3px solid #1ee6e1 !important;
            border-radius: 0 !important;
            background: radial-gradient(circle at 100% 0%, rgba(38, 54, 255, .22), transparent 42%), #111217 !important;
            color: #f3f5ff !important;
            box-shadow: 0 24px 70px rgba(16, 17, 22, .42) !important;
        }

        .swal2-container {
            backdrop-filter: blur(5px);
        }

        .pilketos-swal-popup .swal2-html-container {
            overflow-x: hidden !important;
        }

        .pilketos-swal-popup::after {
            content: '';
            position: absolute;
            right: -34px;
            bottom: -34px;
            width: 110px;
            height: 110px;
            border: 1px solid rgba(30, 230, 225, .2);
            transform: rotate(45deg);
            pointer-events: none;
        }

        .pilketos-swal-popup .swal2-icon {
            margin: 4px auto 14px;
            transform: scale(.82);
            border-width: 1px;
        }

        .pilketos-swal-popup .swal2-icon.swal2-warning {
            border-color: rgba(255, 193, 93, .8);
            color: #ffc15d;
        }

        .pilketos-swal-popup .swal2-icon.swal2-question {
            border-color: rgba(30, 230, 225, .75);
            color: #1ee6e1;
        }

        .pilketos-swal-popup .swal2-icon.swal2-info {
            border-color: rgba(38, 54, 255, .75);
            color: #7180ff;
        }

        .pilketos-swal-popup .swal2-close {
            color: #9ba3bd !important;
        }

        .pilketos-swal-popup .swal2-close:hover {
            color: #1ee6e1 !important;
        }

        .pilketos-swal-title {
            color: #f3f5ff !important;
            font-family: 'Trebuchet MS', 'Segoe UI', sans-serif;
        }

        .pilketos-swal-html {
            color: #9ba3bd !important;
        }

        .pilketos-swal-input-label {
            display: block;
            margin: 8px 0 10px;
            color: #9ba3bd !important;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .18em;
            text-transform: uppercase;
            text-align: left;
        }

        .token-popup-hero {
            position: relative;
            box-sizing: border-box;
            max-width: calc(100% + 48px);
            margin: -24px -24px 24px;
            padding: 24px 24px 22px;
            background: linear-gradient(135deg, #2636ff, #168fd0 72%, #1ee6e1);
            color: #fff;
            text-align: center;
        }

        .token-popup-hero::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: calc(50% - 10px);
            width: 20px;
            height: 20px;
            background: #2636ff;
            transform: rotate(45deg);
        }

        .token-popup-hero i {
            position: relative;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 58px;
            height: 58px;
            border: 1px solid rgba(255, 255, 255, .6);
            background: rgba(16, 17, 22, .12);
            font-size: 24px;
        }

        .token-popup-body {
            text-align: center;
        }

        .token-popup-heading {
            margin: 0;
            color: #f3f5ff;
            font-size: 23px;
            font-weight: 700;
        }

        .token-popup-description {
            margin: 8px 0 0;
            color: #9ba3bd;
            font-size: 13px;
            line-height: 1.5;
        }

        .pilketos-swal-confirm {
            border: 1px solid #1ee6e1 !important;
            border-radius: 0 !important;
            background: #1ee6e1 !important;
            color: #101116 !important;
            min-width: 132px;
        }

        .pilketos-swal-cancel {
            border: 1px solid rgba(255, 255, 255, .22) !important;
            border-radius: 0 !important;
            background: transparent !important;
            color: #9ba3bd !important;
            min-width: 110px;
        }

        .pilketos-swal-confirm:hover {
            background: #2636ff !important;
            color: #fff !important;
        }

        .pilketos-swal-cancel:hover {
            border-color: #1ee6e1 !important;
            color: #1ee6e1 !important;
        }

        .pilketos-swal-input {
            width: 100% !important;
            margin: 0 !important;
            min-height: 54px;
            border: 1px solid rgba(255, 255, 255, .18) !important;
            border-radius: 0 !important;
            background: #171924 !important;
            color: #f3f5ff !important;
        }

        html:not(.dark) .pilketos-swal-popup {
            border-color: rgba(38, 54, 255, .22) !important;
            border-top-color: #2636ff !important;
            background: radial-gradient(circle at 100% 0%, rgba(38, 54, 255, .1), transparent 42%), #f8f9fe !important;
            color: #151a33 !important;
            box-shadow: 0 20px 60px rgba(25, 34, 70, .18) !important;
        }

        html:not(.dark) .pilketos-swal-title {
            color: #151a33 !important;
        }

        html:not(.dark) .pilketos-swal-html {
            color: #626b86 !important;
        }

        html:not(.dark) .pilketos-swal-input-label {
            color: #626b86 !important;
        }

        html:not(.dark) .token-popup-heading {
            color: #151a33;
        }

        html:not(.dark) .token-popup-description {
            color: #626b86;
        }

        html:not(.dark) .pilketos-swal-cancel {
            border-color: rgba(25, 34, 70, .18) !important;
            color: #626b86 !important;
        }

        html:not(.dark) .pilketos-swal-input {
            border-color: rgba(25, 34, 70, .18) !important;
            background: #fff !important;
            color: #151a33 !important;
        }

        html:not(.dark) .pilketos-swal-popup .swal2-close {
            color: #626b86 !important;
        }

        html:not(.dark) .pilketos-swal-popup::after {
            border-color: rgba(38, 54, 255, .14);
        }

        .site-header,
        .site-footer {
            --chrome-ink: #f3f5ff;
            --chrome-muted: #9ba3bd;
            --chrome-line: rgba(255, 255, 255, .16);
            --chrome-cyan: #1ee6e1;
            font-family: 'Trebuchet MS', 'Segoe UI', sans-serif;
        }

        .site-header {
            border-color: var(--chrome-line) !important;
            background: rgba(16, 17, 22, .94) !important;
            color: var(--chrome-ink);
        }

        .site-header .site-brand-mark {
            border: 0;
            border-radius: 0;
            background: transparent;
            box-shadow: none;
        }

        html:not(.dark) .site-header {
            --chrome-ink: #151a33;
            --chrome-muted: #626b86;
            --chrome-line: rgba(25, 34, 70, .16);
            background: rgba(248, 249, 254, .94) !important;
            color: var(--chrome-ink);
        }

        html:not(.dark) .site-header .site-brand-name {
            color: var(--chrome-ink) !important;
        }

        html:not(.dark) .site-header .site-brand-school {
            color: var(--chrome-muted) !important;
        }

        html:not(.dark) .site-header .site-nav a {
            color: var(--chrome-muted) !important;
        }

        html:not(.dark) .site-header .site-nav a:hover,
        html:not(.dark) .site-header .site-nav a.text-blue-600 {
            color: #2636ff !important;
        }

        html:not(.dark) .site-header .site-control,
        html:not(.dark) .site-header .site-mobile-toggle {
            border-color: var(--chrome-line) !important;
            background: rgba(25, 34, 70, .04) !important;
            color: var(--chrome-ink) !important;
        }

        html:not(.dark) .site-header .site-login {
            color: #2636ff;
            border-color: #2636ff;
        }

        html:not(.dark) .site-header .site-login:hover {
            background: #2636ff;
            color: #fff;
        }

        html:not(.dark) .site-header .site-mobile-menu {
            background: rgba(248, 249, 254, .98) !important;
            border-color: var(--chrome-line) !important;
        }

        html:not(.dark) .site-header .site-mobile-menu a {
            color: var(--chrome-muted) !important;
        }

        html:not(.dark) .site-header .site-mobile-menu a:hover,
        html:not(.dark) .site-header .site-mobile-menu a.bg-blue-50 {
            background: rgba(38, 54, 255, .08) !important;
            color: #2636ff !important;
        }

        html:not(.dark) .site-footer {
            --chrome-ink: #151a33;
            --chrome-muted: #626b86;
            --chrome-line: rgba(25, 34, 70, .16);
            background: #f1f3f9 !important;
        }

        html:not(.dark) .site-footer .footer-title,
        html:not(.dark) .site-footer .footer-links a {
            color: #151a33;
        }

        html:not(.dark) .site-footer .footer-label,
        html:not(.dark) .site-footer .footer-links a:hover {
            color: #2636ff;
        }

        .site-header .site-brand-name {
            color: var(--chrome-ink) !important;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .site-header .site-brand-school {
            color: var(--chrome-muted) !important;
        }

        .site-header .site-nav a {
            position: relative;
            color: var(--chrome-muted) !important;
            font-size: 11px;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .site-header .site-nav a:hover,
        .site-header .site-nav a.text-blue-600 {
            color: var(--chrome-cyan) !important;
        }

        .site-header .site-nav a::after {
            content: '';
            position: absolute;
            right: 0;
            bottom: -12px;
            left: 0;
            height: 1px;
            background: var(--chrome-cyan);
            transform: scaleX(0);
            transition: transform .2s ease;
        }

        .site-header .site-nav a:hover::after,
        .site-header .site-nav a.text-blue-600::after {
            transform: scaleX(1);
        }

        .site-header .site-control,
        .site-header .site-mobile-toggle {
            border-color: var(--chrome-line) !important;
            background: rgba(255, 255, 255, .04) !important;
            color: var(--chrome-ink) !important;
            border-radius: 0 !important;
        }

        .site-header .site-control:hover,
        .site-header .site-mobile-toggle:hover {
            border-color: var(--chrome-cyan) !important;
            color: var(--chrome-cyan) !important;
        }

        .site-header .site-login {
            border: 1px solid var(--chrome-cyan);
            border-radius: 0;
            background: transparent;
            color: var(--chrome-cyan);
            box-shadow: none;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .site-header .site-login:hover {
            background: var(--chrome-cyan);
            color: #101116;
        }

        .site-header .site-role {
            border-color: var(--chrome-line);
            border-radius: 0;
            background: rgba(255, 255, 255, .04);
            color: var(--chrome-cyan);
        }

        .site-header .site-logout {
            border-color: rgba(255, 111, 111, .5);
            border-radius: 0;
            background: transparent;
            color: #ff9d9d;
        }

        .site-header .site-mobile-menu {
            border-color: var(--chrome-line) !important;
            background: rgba(16, 17, 22, .98) !important;
        }

        .site-header .site-mobile-menu a {
            border-radius: 0 !important;
            color: var(--chrome-muted) !important;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .site-header .site-mobile-menu a:hover,
        .site-header .site-mobile-menu a.bg-blue-50 {
            background: rgba(30, 230, 225, .08) !important;
            color: var(--chrome-cyan) !important;
        }

        .site-footer {
            border-color: var(--chrome-line) !important;
            background: #101116 !important;
            color: var(--chrome-muted) !important;
            letter-spacing: 0;
            text-transform: none;
        }

        .site-footer .footer-inner {
            max-width: 1180px;
            margin: 0 auto;
            padding: 44px 28px 25px;
        }

        .site-footer .footer-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(180px, .6fr);
            gap: 35px;
            text-align: left;
        }

        .site-footer .footer-title {
            color: var(--chrome-ink);
            font-size: 18px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .site-footer .footer-description {
            max-width: 470px;
            margin-top: 12px;
            color: var(--chrome-muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .site-footer .footer-label {
            color: var(--chrome-cyan);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .2em;
            text-transform: uppercase;
        }

        .site-footer .footer-links {
            display: flex;
            flex-wrap: wrap;
            gap: 12px 22px;
            margin-top: 15px;
        }

        .site-footer .footer-links a {
            color: var(--chrome-ink);
            font-size: 12px;
            transition: color .2s ease;
        }

        .site-footer .footer-links a:hover {
            color: var(--chrome-cyan);
        }

        .site-footer .footer-bottom {
            margin-top: 35px;
            padding-top: 18px;
            border-top: 1px solid var(--chrome-line);
            color: var(--chrome-muted);
            font-size: 11px;
        }

        .voter-sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid transparent;
            padding: 12px 14px;
            color: #626b86;
            font-size: 13px;
            font-weight: 700;
            text-align: left;
            transition: border-color .2s ease, background .2s ease, color .2s ease;
        }

        .voter-sidebar-link:hover,
        .voter-sidebar-link.is-active {
            border-color: rgba(38, 54, 255, .2);
            background: #eef1ff;
            color: #2636ff;
        }

        .voter-sidebar-logout:hover {
            border-color: rgba(225, 29, 72, .2);
            background: #fff1f2;
            color: #e11d48;
        }

        .voter-theme-option {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            border: 1px solid rgba(25, 34, 70, .12);
            padding: 9px 6px;
            color: #626b86;
            font-size: 11px;
            font-weight: 700;
        }

        .voter-theme-option:hover,
        .voter-theme-option.is-active {
            border-color: rgba(38, 54, 255, .25);
            background: #eef1ff;
            color: #2636ff;
        }

        .dark .voter-sidebar-link {
            color: #9ba3bd;
        }

        .dark .voter-sidebar-link:hover,
        .dark .voter-sidebar-link.is-active {
            border-color: rgba(30, 230, 225, .2);
            background: rgba(30, 230, 225, .08);
            color: #1ee6e1;
        }

        .dark .voter-sidebar-logout:hover {
            border-color: rgba(255, 111, 111, .25);
            background: rgba(255, 111, 111, .08);
            color: #ff9d9d;
        }

        .dark .voter-theme-option {
            border-color: rgba(255, 255, 255, .12);
            color: #9ba3bd;
        }

        .dark .voter-theme-option:hover,
        .dark .voter-theme-option.is-active {
            border-color: rgba(30, 230, 225, .25);
            background: rgba(30, 230, 225, .08);
            color: #1ee6e1;
        }

        .voter-sidebar {
            border-color: rgba(25, 34, 70, .14) !important;
            background: #ffffff !important;
        }

        .voter-sidebar .voter-sidebar-link {
            min-height: 42px;
            border-radius: .5rem !important;
            color: #626b86 !important;
            font-size: 14px;
        }

        .voter-sidebar .voter-sidebar-link:hover {
            border-color: transparent !important;
            background: #f1f3f9 !important;
            color: #151a33 !important;
        }

        .voter-sidebar .voter-sidebar-link.is-active {
            border-color: transparent !important;
            background: #eef1ff !important;
            color: #2636ff !important;
        }

        .voter-sidebar .voter-theme-option {
            min-height: 44px;
            border-radius: 0;
        }

        .voter-sidebar .voter-sidebar-logout {
            border-color: transparent !important;
            background: transparent !important;
            color: #626b86 !important;
            justify-content: center;
        }

        .voter-sidebar .voter-sidebar-logout:hover {
            border-color: transparent !important;
            background: #f1f3f9 !important;
            color: #151a33 !important;
        }

        html.dark .voter-sidebar {
            border-color: #1e293b !important;
            background: #0f172a !important;
        }

        html.dark .voter-sidebar .voter-sidebar-link {
            color: #a3aed0 !important;
        }

        html.dark .voter-sidebar .voter-sidebar-link:hover {
            border-color: transparent !important;
            background: rgba(30, 230, 225, .06) !important;
            color: #f3f5ff !important;
        }

        html.dark .voter-sidebar .voter-sidebar-link.is-active {
            border-color: transparent !important;
            background: rgba(30, 230, 225, .08) !important;
            color: #1ee6e1 !important;
        }

        html.dark .voter-sidebar .voter-sidebar-logout {
            border-color: transparent !important;
            background: transparent !important;
            color: #a3aed0 !important;
        }

        html.dark .voter-sidebar .voter-sidebar-logout:hover {
            border-color: transparent !important;
            background: rgba(255, 255, 255, .05) !important;
            color: #f3f5ff !important;
        }

        html:not(.dark) .pilketos-home,
        html:not(.dark) .guide-editorial,
        html:not(.dark) .public-editorial,
        html:not(.dark) .candidate-page,
        html:not(.dark) .candidate-profile,
        html:not(.dark) .results-editorial {
            background: #f5f7fc;
            color: #151a33;
        }

        html:not(.dark) .pilketos-home {
            --ink: #151a33;
            --muted: #626b86;
            --line: rgba(25, 34, 70, .16);
        }

        html:not(.dark) .pilketos-home .pilketos-hero,
        html:not(.dark) .guide-editorial .guide-hero,
        html:not(.dark) .public-editorial .editorial-hero,
        html:not(.dark) .candidate-page .candidate-panel,
        html:not(.dark) .candidate-profile .profile-panel,
        html:not(.dark) .results-editorial .results-panel {
            border-color: rgba(25, 34, 70, .16);
            background: radial-gradient(ellipse at 70% 0%, rgba(38, 54, 255, .12), transparent 45%), #ffffff;
            color: #151a33;
        }

        html:not(.dark) .guide-editorial .guide-panel,
        html:not(.dark) .guide-editorial .guide-extra,
        html:not(.dark) .public-editorial .editorial-card,
        html:not(.dark) .candidate-page .candidate-metric,
        html:not(.dark) .candidate-page .candidate-card,
        html:not(.dark) .candidate-profile .profile-info,
        html:not(.dark) .results-editorial .results-election,
        html:not(.dark) .results-editorial .results-stat {
            border-color: rgba(25, 34, 70, .14);
            background: #ffffff;
        }

        html:not(.dark) .guide-editorial .guide-step h3,
        html:not(.dark) .guide-editorial .guide-extra h2,
        html:not(.dark) .public-editorial .editorial-card h2,
        html:not(.dark) .candidate-page .candidate-name,
        html:not(.dark) .candidate-profile .profile-title,
        html:not(.dark) .results-editorial .results-election h2,
        html:not(.dark) .results-editorial .results-stat p:last-child {
            color: #151a33;
        }

        html:not(.dark) .guide-editorial .guide-copy,
        html:not(.dark) .guide-editorial .guide-step p,
        html:not(.dark) .guide-editorial .guide-extra p,
        html:not(.dark) .public-editorial .editorial-copy,
        html:not(.dark) .public-editorial .editorial-card p,
        html:not(.dark) .candidate-page .candidate-muted,
        html:not(.dark) .candidate-profile .profile-muted,
        html:not(.dark) .candidate-profile .profile-info p,
        html:not(.dark) .results-editorial .results-muted {
            color: #626b86;
        }

        html:not(.dark) .guide-editorial .guide-extra-item,
        html:not(.dark) .candidate-profile .profile-info,
        html:not(.dark) .results-editorial .results-track {
            border-color: rgba(25, 34, 70, .12);
        }

        html:not(.dark) .pilketos-home .eyebrow,
        html:not(.dark) .pilketos-home .status,
        html:not(.dark) .pilketos-home .guide-number,
        html:not(.dark) .guide-editorial .guide-kicker,
        html:not(.dark) .guide-editorial .guide-panel-title,
        html:not(.dark) .guide-editorial .guide-step-number,
        html:not(.dark) .guide-editorial .guide-extra-item h3,
        html:not(.dark) .public-editorial .editorial-kicker,
        html:not(.dark) .candidate-page .candidate-kicker,
        html:not(.dark) .candidate-page .candidate-number,
        html:not(.dark) .candidate-profile .profile-label,
        html:not(.dark) .results-editorial .results-kicker {
            color: #087f83 !important;
        }

        html:not(.dark) .pilketos-home .home-link {
            color: #087f83 !important;
        }

        html:not(.dark) .guide-editorial .guide-step,
        html:not(.dark) .guide-editorial .guide-extra,
        html:not(.dark) .public-editorial .editorial-card,
        html:not(.dark) .candidate-page .candidate-metric,
        html:not(.dark) .candidate-profile .profile-info,
        html:not(.dark) .results-editorial .results-stat {
            border-top-color: #087f83;
        }

        html:not(.dark) .candidate-page .candidate-detail-action,
        html:not(.dark) .candidate-profile .profile-status,
        html:not(.dark) .results-editorial .results-status {
            border-color: rgba(8, 127, 131, .45);
            color: #087f83;
        }

        @media (max-width: 640px) {
            .voter-sidebar {
                min-height: 100vh;
                width: 18rem;
            }

            .site-footer .footer-inner {
                padding: 35px 20px 22px;
            }

            .site-footer .footer-grid {
                grid-template-columns: 1fr;
                gap: 28px;
            }
        }
    </style>
    @stack('styles')
</head>

@php($isVoterDashboard = request()->routeIs('siswa.dashboard', 'guru.dashboard'))

<body
    class="min-h-screen bg-slate-50 text-slate-800 antialiased transition-colors duration-300 dark:bg-slate-950 dark:text-slate-100 {{ $isVoterDashboard ? 'flex flex-col lg:flex-row' : '' }}">
    @if ($isVoterDashboard)
        <div x-cloak x-show="mobileMenu" x-transition.opacity class="fixed inset-0 z-40 bg-slate-950/50 lg:hidden"
            x-on:click="mobileMenu = false" aria-hidden="true"></div>
        @include('components.voter-sidebar', ['user' => Auth::user()])
        <div class="flex min-w-0 flex-grow flex-col">
            @include('components.voter-topnav')
    @endif

    @unless ($isVoterDashboard)
        <header
            class="site-header sticky top-0 z-50 border-b border-slate-200/70 bg-white/80 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/80">
            <div
                class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 md:grid md:grid-cols-[1fr_auto_1fr] lg:px-8">
                <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3">
                    @php($logoPath = App\Models\SiteSetting::getValue('logo_path', ''))
                    @if ($logoPath)
                        <img src="{{ filter_var($logoPath, FILTER_VALIDATE_URL) ? $logoPath : Illuminate\Support\Facades\Storage::url($logoPath) }}"
                            alt="Logo" class="site-brand-mark h-11 w-11 object-cover shadow-lg">
                    @else
                        <div
                            class="site-brand-mark flex h-11 w-11 items-center justify-center text-lg font-black text-white">
                            P</div>
                    @endif
                    <div class="min-w-0">
                        <h1
                            class="site-brand-name truncate text-base font-black leading-none text-slate-900 dark:text-white">
                            {{ App\Models\SiteSetting::getValue('website_name', 'PILKETOS') }}</h1>
                        <span
                            class="site-brand-school truncate text-xs text-slate-500 dark:text-slate-400">{{ App\Models\SiteSetting::getValue('school_name', 'SMKN 1 Bangsri') }}</span>
                    </div>
                </a>

                <nav class="site-nav hidden items-center gap-6 text-sm font-semibold md:flex">
                    <a href="{{ route('home') }}" data-nav-home
                        class="transition {{ request()->routeIs('home') ? 'text-blue-600 dark:text-blue-400' : 'text-slate-600 hover:text-blue-600 dark:text-slate-300 dark:hover:text-blue-400' }}">Beranda</a>
                    <a href="{{ route('home') }}#panduan" data-nav-section="panduan"
                        class="transition text-slate-600 hover:text-blue-600 dark:text-slate-300 dark:hover:text-blue-400">Panduan</a>
                    <a href="{{ route('home') }}#hasil" data-nav-section="hasil"
                        class="transition text-slate-600 hover:text-blue-600 dark:text-slate-300 dark:hover:text-blue-400">Hasil</a>
                    <a href="{{ route('home') }}#tentang" data-nav-section="tentang"
                        class="transition text-slate-600 hover:text-blue-600 dark:text-slate-300 dark:hover:text-blue-400">Tentang</a>
                </nav>

                <div class="flex shrink-0 items-center justify-end gap-2 sm:gap-3">
                    <button type="button" x-on:click="theme = theme === 'dark' ? 'light' : 'dark'"
                        class="site-control flex items-center justify-center rounded-full border border-slate-300 bg-white/80 p-2.5 text-slate-700 shadow-sm transition hover:-translate-y-0.5 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                        aria-label="Ganti tema" title="Ganti tema">
                        <i class="fa-solid" :class="theme === 'dark' ? 'fa-sun' : 'fa-moon'"></i>
                    </button>
                    @auth
                        @if (Auth::user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}"
                                class="site-login rounded-full border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold uppercase text-blue-600 transition hover:border-blue-400 hover:bg-blue-100 dark:border-blue-900 dark:bg-blue-950/40 dark:text-blue-300 dark:hover:bg-blue-950/70">
                                Admin
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}"
                                class="site-role hidden rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold uppercase text-blue-600 transition hover:border-blue-400 hover:bg-blue-100 sm:inline-flex dark:border-blue-900 dark:bg-blue-950/40 dark:text-blue-300 dark:hover:bg-blue-950/70"
                                aria-label="Buka dashboard {{ Auth::user()->role }}">
                                {{ Auth::user()->role }}
                            </a>
                        @endif
                        <form action="{{ route('logout') }}" method="POST" class="logout-confirm-form hidden md:block">
                            @csrf
                            <button type="submit"
                                class="site-logout rounded-full border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-100 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300">
                                Keluar
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}"
                            class="site-login rounded-full bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700">
                            Login
                        </a>
                    @endauth

                    <button type="button"
                        class="site-mobile-toggle rounded-full border border-slate-300 p-2.5 text-slate-700 md:hidden dark:border-slate-700 dark:text-slate-200"
                        x-on:click="mobileMenu = !mobileMenu" aria-label="Buka menu">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>

            <div x-show="mobileMenu" x-transition
                class="site-mobile-menu border-t border-slate-200 bg-white/95 px-4 py-4 md:hidden dark:border-slate-800 dark:bg-slate-900/95">
                <div class="flex flex-col gap-3 text-sm font-semibold">
                    <a href="{{ route('home') }}" data-nav-home
                        class="rounded-2xl px-3 py-2 transition {{ request()->routeIs('home') ? 'bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">Beranda</a>
                    <a href="{{ route('home') }}#panduan" data-nav-section="panduan" x-on:click="mobileMenu = false"
                        class="rounded-2xl px-3 py-2 transition text-slate-600 hover:bg-slate-100 hover:text-blue-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-blue-400">Panduan</a>
                    <a href="{{ route('home') }}#hasil" data-nav-section="hasil" x-on:click="mobileMenu = false"
                        class="rounded-2xl px-3 py-2 transition text-slate-600 hover:bg-slate-100 hover:text-blue-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-blue-400">Hasil</a>
                    <a href="{{ route('home') }}#tentang" data-nav-section="tentang" x-on:click="mobileMenu = false"
                        class="rounded-2xl px-3 py-2 transition text-slate-600 hover:bg-slate-100 hover:text-blue-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-blue-400">Tentang</a>
                    @auth
                        <a href="{{ route('dashboard') }}" x-on:click="mobileMenu = false"
                            class="rounded-2xl bg-blue-50 px-3 py-2 font-semibold text-blue-600 transition hover:bg-blue-100 dark:bg-blue-950/40 dark:text-blue-300 dark:hover:bg-blue-950/70">
                            Dashboard {{ Auth::user()->role }}
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="logout-confirm-form">
                            @csrf
                            <button type="submit"
                                class="site-logout w-full rounded-2xl border border-red-200 bg-red-50 px-3 py-2 text-left font-semibold text-red-600 transition hover:bg-red-100 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300">
                                Keluar
                            </button>
                        </form>
                    @endauth
                </div>
            </div>
        </header>
    @endunless

    <main class="min-w-0 flex-grow">
        @yield('content')
    </main>

    @if ($isVoterDashboard)
        </div>
    @endif

    @unless ($isVoterDashboard)
        <footer class="site-footer border-t border-slate-200 bg-white/80 dark:border-slate-800 dark:bg-slate-900/80">
            <div class="footer-inner">
                <div class="footer-grid">
                    <div>
                        <p class="footer-title">PILKETOS SMKN 1 Bangsri</p>
                        <p class="footer-description">Sistem Pemilihan Ketua OSIS berbasis digital untuk mendukung proses
                            pemilihan yang mudah, tertib, dan transparan.</p>
                    </div>
                    <div>
                        <p class="footer-label">Navigasi</p>
                        <nav class="footer-links" aria-label="Navigasi footer">
                            <a href="{{ route('home') }}">Beranda</a>
                            <a href="{{ route('home') }}#tentang">Tentang</a>
                            <a href="{{ route('home') }}#panduan">Panduan</a>
                        </nav>
                    </div>
                </div>
                <p class="footer-bottom">&copy; 2026 SMK Negeri 1 Bangsri · Dikembangkan oleh Tim ANZ</p>
            </div>
        </footer>
    @endunless

    <!-- SweetAlert2 global defaults for consistent modern modals (light/dark aware) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function() {
            if (typeof Swal === 'undefined') return;

            const swalClasses = {
                popup: 'pilketos-swal-popup p-6 max-w-md w-full',
                title: 'pilketos-swal-title text-lg font-bold',
                htmlContainer: 'pilketos-swal-html mt-3 text-sm',
                actions: 'flex flex-wrap justify-end gap-3 mt-6',
                input: 'pilketos-swal-input mt-4 w-full px-4 py-3 outline-none',
                validationMessage: 'mt-2 text-sm text-red-400',
                confirmButton: 'pilketos-swal-confirm px-6 py-3 font-bold',
                cancelButton: 'pilketos-swal-cancel px-6 py-3 font-bold'
            };

            try {
                const mix = Swal.mixin({
                    customClass: swalClasses,
                    buttonsStyling: false
                });
                // override global Swal with the mixin so all .fire() calls use these defaults
                window.Swal = mix;
            } catch (e) {
                console.error('Failed to initialize Swal defaults', e);
            }
        })();

        document.querySelectorAll('.logout-confirm-form').forEach((form) => {
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                Swal.fire({
                    title: 'Apakah Anda yakin ingin keluar?',
                    text: 'Sesi akun Anda akan diakhiri.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Keluar',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });

        document.querySelectorAll('[data-confirm]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                Swal.fire({
                    title: 'Konfirmasi tindakan',
                    text: form.dataset.confirm,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, lanjutkan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });

        const navSectionLinks = document.querySelectorAll('[data-nav-section]');
        const navHomeLinks = document.querySelectorAll('[data-nav-home]');
        const navSections = [...navSectionLinks]
            .map((link) => document.getElementById(link.dataset.navSection))
            .filter(Boolean);

        if (navSections.length) {
            const setActiveNav = (sectionId) => {
                navSectionLinks.forEach((link) => {
                    const isActive = link.dataset.navSection === sectionId;
                    link.classList.toggle('text-blue-600', isActive);
                    link.classList.toggle('dark:text-blue-400', isActive);
                    link.classList.toggle('bg-blue-50', isActive);
                    link.classList.toggle('dark:bg-blue-950/40', isActive);
                });

                navHomeLinks.forEach((link) => {
                    const isActive = !sectionId;
                    link.classList.toggle('text-blue-600', isActive);
                    link.classList.toggle('dark:text-blue-400', isActive);
                    link.classList.toggle('bg-blue-50', isActive);
                    link.classList.toggle('dark:bg-blue-950/40', isActive);
                });
            };

            const updateActiveNav = () => setActiveNav(window.location.hash.slice(1));
            window.addEventListener('hashchange', updateActiveNav);
            updateActiveNav();

            const observer = new IntersectionObserver((entries) => {
                const visibleSection = entries
                    .filter((entry) => entry.isIntersecting)
                    .sort((first, second) => second.intersectionRatio - first.intersectionRatio)[0];

                if (visibleSection) setActiveNav(visibleSection.target.id);
            }, {
                rootMargin: '-20% 0px -65% 0px',
                threshold: [0, 0.25, 0.5, 1]
            });

            navSections.forEach((section) => observer.observe(section));
        }
    </script>

    @stack('scripts')
</body>

</html>
