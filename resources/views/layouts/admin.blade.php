<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'PILKETOS') }} - Admin</title>
    @php($faviconPath = App\Models\SiteSetting::getValue('favicon_path', ''))
    @if ($faviconPath)
        <link rel="icon"
            href="{{ filter_var($faviconPath, FILTER_VALIDATE_URL) ? $faviconPath : Illuminate\Support\Facades\Storage::url($faviconPath) }}">
    @endif
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Pemanggilan Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Konfigurasi Tailwind Khusus CDN -->
    <script>
        tailwind.config = {
            darkMode: 'class', // Ini adalah kunci agar tombol dark mode berfungsi
            theme: {
                extend: {}
            }
        }
    </script>
    <style>
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

        .token-popup-copy {
            text-align: left;
            line-height: 1.6;
        }

        .token-popup-copy p {
            margin: 0;
        }

        .token-popup-note {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-top: 16px;
            padding: 12px;
            border-left: 2px solid #1ee6e1;
            background: rgba(30, 230, 225, .08);
            color: #c7d0e8;
            font-size: 12px;
            text-align: left;
        }

        .token-popup-note i {
            margin-top: 4px;
            color: #1ee6e1;
        }

        .token-popup-warning {
            border-left-color: #ffc15d;
            background: rgba(255, 193, 93, .09);
        }

        .token-popup-warning i {
            color: #ffc15d;
        }

        html:not(.dark) .token-popup-note {
            background: rgba(38, 54, 255, .06);
            color: #626b86;
        }

        html:not(.dark) .token-popup-warning {
            background: rgba(255, 193, 93, .12);
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

        body {
            font-family: 'Trebuchet MS', 'Segoe UI', sans-serif;
        }

        .admin-panel {
            --admin-ink: #151a33;
            --admin-muted: #626b86;
            --admin-line: rgba(25, 34, 70, .14);
            --admin-accent: #2636ff;
            --admin-cyan: #1ee6e1;
            background: #f5f7fc;
        }

        .admin-panel .admin-frame {
            background: #f5f7fc;
        }

        .admin-panel .admin-sidebar,
        .admin-panel .admin-topnav,
        .admin-panel .admin-footer {
            border-color: var(--admin-line) !important;
            background: rgba(255, 255, 255, .9) !important;
        }

        .admin-panel .admin-sidebar {
            box-shadow: 10px 0 30px rgba(25, 34, 70, .04);
        }

        .admin-panel .admin-sidebar-inner {
            border-color: var(--admin-line) !important;
            background: #ffffff !important;
        }

        .admin-panel .admin-sidebar-inner a {
            border-radius: 0 !important;
        }

        .admin-panel .admin-sidebar-inner a:not([class*="bg-blue-50"]):hover {
            background: #f1f3f9 !important;
            color: var(--admin-ink) !important;
        }

        .admin-panel .admin-sidebar-inner a[class*="bg-blue-50"] {
            border-radius: 0 !important;
            background: #eef1ff !important;
            color: #2636ff !important;
        }

        .admin-panel .admin-hero {
            background: radial-gradient(ellipse at 75% 0%, rgba(38, 54, 255, .12), transparent 45%), #ffffff !important;
        }

        .admin-panel .admin-topnav {
            color: var(--admin-ink);
        }

        .admin-panel .admin-topnav-header {
            border-color: var(--admin-line) !important;
            background: rgba(255, 255, 255, .9) !important;
        }

        .admin-panel .admin-content {
            background: #f5f7fc;
            padding: 32px !important;
        }

        .admin-panel .admin-dashboard {
            color: var(--admin-ink);
            font-family: 'Trebuchet MS', 'Segoe UI', sans-serif;
        }

        .admin-panel .admin-dashboard>section>div {
            border-color: var(--admin-line) !important;
            border-radius: 0 !important;
            background: #ffffff !important;
            box-shadow: 0 12px 28px rgba(25, 34, 70, .06) !important;
        }

        .admin-panel .admin-summary-panel {
            border-color: var(--admin-line) !important;
            border-top: 1px solid var(--admin-cyan) !important;
            background: #ffffff !important;
            box-shadow: 0 12px 28px rgba(25, 34, 70, .06) !important;
        }

        .admin-panel .admin-dashboard>section:first-child>div.admin-hero {
            background: radial-gradient(ellipse at 75% 0%, rgba(38, 54, 255, .13), transparent 45%), #ffffff !important;
        }

        .admin-panel .admin-dashboard>section>div>div[class*="bg-slate-50"],
        .admin-panel .admin-dashboard>section>div>div[class*="bg-slate-900"] {
            border-radius: 0 !important;
            background: #f8f9fd !important;
        }

        .admin-panel .admin-dashboard>section>div a,
        .admin-panel .admin-dashboard>section>div button {
            border-radius: 0;
        }

        .admin-panel .admin-dashboard .text-slate-900,
        .admin-panel .admin-dashboard .text-slate-800,
        .admin-panel .admin-dashboard .text-slate-700 {
            color: var(--admin-ink) !important;
        }

        .admin-panel .admin-dashboard .text-slate-600,
        .admin-panel .admin-dashboard .text-slate-500,
        .admin-panel .admin-dashboard .text-slate-400 {
            color: var(--admin-muted) !important;
        }

        .admin-panel .settings-form input:not([type="hidden"]),
        .admin-panel .settings-form textarea,
        .admin-panel .settings-form select {
            border-color: var(--admin-line) !important;
            background: #f8f9fd !important;
            color: var(--admin-ink) !important;
            outline: none;
        }

        .admin-panel .settings-form input:not([type="hidden"]):focus,
        .admin-panel .settings-form textarea:focus,
        .admin-panel .settings-form select:focus {
            border-color: var(--admin-accent) !important;
            box-shadow: 0 0 0 2px rgba(38, 54, 255, .12);
        }

        .admin-panel .settings-form input[type="file"] {
            background: #ffffff !important;
        }

        .admin-panel .settings-form input[type="checkbox"] {
            accent-color: var(--admin-accent);
        }

        .admin-panel .settings-form label:has(input[type="checkbox"]) {
            border-color: var(--admin-line) !important;
            background: #f8f9fd;
            color: var(--admin-ink);
        }

        .admin-panel .admin-card {
            border-color: var(--admin-line) !important;
            border-radius: 0 !important;
            box-shadow: 0 12px 28px rgba(25, 34, 70, .06) !important;
        }

        .admin-panel .admin-card-soft {
            border-radius: 0 !important;
            background: #f8f9fd !important;
        }

        .admin-panel .admin-content [class*="border-slate-200"][class*="bg-white"] {
            border-color: var(--admin-line) !important;
            border-radius: 0 !important;
            box-shadow: 0 12px 28px rgba(25, 34, 70, .06) !important;
        }

        .admin-panel .admin-content [class*="border-slate-200"][class*="bg-white"],
        .admin-panel .admin-content [class*="border-slate-200"][class*="bg-slate-900"] {
            background: #ffffff !important;
        }

        .admin-panel .admin-content [class*="rounded-xl"],
        .admin-panel .admin-content [class*="rounded-2xl"],
        .admin-panel .admin-content [class*="rounded-3xl"] {
            border-radius: 0 !important;
        }

        .admin-panel .admin-content input:not([type="hidden"]),
        .admin-panel .admin-content textarea,
        .admin-panel .admin-content select {
            border-color: var(--admin-line) !important;
            background: #f8f9fd !important;
            color: var(--admin-ink) !important;
        }

        .admin-panel .admin-content input:not([type="hidden"]):focus,
        .admin-panel .admin-content textarea:focus,
        .admin-panel .admin-content select:focus {
            border-color: var(--admin-accent) !important;
            box-shadow: 0 0 0 2px rgba(38, 54, 255, .12);
            outline: none;
        }

        .admin-panel .admin-content [class*="bg-slate-50"] {
            border-radius: 0 !important;
        }

        .admin-panel .admin-resource-page {
            color: var(--admin-ink);
            font-family: 'Trebuchet MS', 'Segoe UI', sans-serif;
        }

        .admin-panel .admin-resource-page>div,
        .admin-panel .admin-resource-page>aside,
        .admin-panel .admin-resource-page>form>div {
            border-color: var(--admin-line) !important;
            border-radius: 0 !important;
        }

        .admin-panel .admin-resource-page [class*="bg-white"] {
            border-color: var(--admin-line) !important;
        }

        .admin-panel .admin-resource-page [class*="rounded-2xl"],
        .admin-panel .admin-resource-page [class*="rounded-3xl"] {
            border-radius: 0 !important;
        }

        .admin-panel .admin-resource-page input:not([type="hidden"]),
        .admin-panel .admin-resource-page textarea,
        .admin-panel .admin-resource-page select {
            border-color: var(--admin-line) !important;
            background: #f8f9fd !important;
            color: var(--admin-ink) !important;
        }

        .admin-panel .admin-dashboard>section>div {
            border-top: 1px solid var(--admin-cyan);
        }

        .admin-panel .admin-dashboard>section:first-child {
            border-top: 1px solid var(--admin-cyan);
        }

        .admin-panel .admin-dashboard>section>div {
            border-color: var(--admin-line) !important;
            border-radius: 0 !important;
            background: #ffffff !important;
            box-shadow: 0 12px 28px rgba(25, 34, 70, .06) !important;
        }

        .admin-panel .admin-dashboard>section:first-child>div.admin-hero {
            background: radial-gradient(ellipse at 75% 0%, rgba(38, 54, 255, .13), transparent 45%), #ffffff !important;
        }

        .admin-panel .admin-dashboard>section>div>div[class*="bg-slate-50"],
        .admin-panel .admin-dashboard>section>div>div[class*="bg-slate-900"] {
            border-radius: 0 !important;
            background: #f8f9fd !important;
        }

        .admin-panel .admin-dashboard>section>div a,
        .admin-panel .admin-dashboard>section>div button {
            border-radius: 0;
        }

        html.dark .admin-panel {
            --admin-ink: #f3f5ff;
            --admin-muted: #9ba3bd;
            --admin-line: rgba(255, 255, 255, .14);
            --admin-accent: #2636ff;
            --admin-cyan: #1ee6e1;
            background: #101116;
        }

        html.dark .admin-panel .admin-frame,
        html.dark .admin-panel .admin-content {
            background: #101116;
        }

        /* Final theme layer: keep every admin screen on the users/landing palette. */
        .admin-panel,
        .admin-panel .admin-frame,
        .admin-panel .admin-content {
            background-color: #f5f7fc !important;
            color: #151a33;
        }

        .admin-panel .admin-sidebar,
        .admin-panel .admin-sidebar-inner,
        .admin-panel .admin-topnav-header,
        .admin-panel .admin-footer {
            border-color: rgba(25, 34, 70, .14) !important;
            background: #ffffff !important;
            color: #151a33;
        }

        .admin-panel .admin-content [class*="bg-white"],
        .admin-panel .admin-content [class*="bg-slate-900"],
        .admin-panel .admin-content [class*="bg-slate-950"] {
            background-color: #ffffff !important;
        }

        .admin-panel .admin-content [class*="bg-slate-50"],
        .admin-panel .admin-content [class*="bg-slate-100"] {
            background-color: #f8f9fd !important;
        }

        .admin-panel .admin-content [class*="border-slate-100"],
        .admin-panel .admin-content [class*="border-slate-200"],
        .admin-panel .admin-content [class*="border-slate-300"] {
            border-color: rgba(25, 34, 70, .14) !important;
        }

        html.dark .admin-panel,
        html.dark .admin-panel .admin-frame,
        html.dark .admin-panel .admin-content {
            background-color: #101116 !important;
            color: #f3f5ff;
        }

        html.dark .admin-panel .admin-sidebar,
        html.dark .admin-panel .admin-sidebar-inner,
        html.dark .admin-panel .admin-topnav-header,
        html.dark .admin-panel .admin-footer {
            border-color: rgba(255, 255, 255, .14) !important;
            background: #111217 !important;
            color: #f3f5ff;
        }

        html.dark .admin-panel .admin-content [class*="bg-white"],
        html.dark .admin-panel .admin-content [class*="bg-slate-50"],
        html.dark .admin-panel .admin-content [class*="bg-slate-100"],
        html.dark .admin-panel .admin-content [class*="bg-slate-900"],
        html.dark .admin-panel .admin-content [class*="bg-slate-950"] {
            background-color: #171924 !important;
        }

        html.dark .admin-panel .admin-content [class*="border-slate-100"],
        html.dark .admin-panel .admin-content [class*="border-slate-200"],
        html.dark .admin-panel .admin-content [class*="border-slate-300"] {
            border-color: rgba(255, 255, 255, .14) !important;
        }

        html.dark .admin-panel .admin-content input:not([type="hidden"]),
        html.dark .admin-panel .admin-content textarea,
        html.dark .admin-panel .admin-content select {
            background: #1b1e2c !important;
            border-color: rgba(255, 255, 255, .14) !important;
            color: #f3f5ff !important;
        }

        .admin-panel .admin-sidebar-inner nav a {
            border-radius: 0 !important;
            color: #626b86 !important;
        }

        .admin-panel .admin-sidebar-inner nav a:hover {
            background: #f1f3f9 !important;
            color: #151a33 !important;
        }

        .admin-panel .admin-sidebar-inner nav a[class*="bg-blue-50"] {
            background: #eef1ff !important;
            color: #2636ff !important;
        }

        .admin-theme-option {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border: 1px solid rgba(25, 34, 70, .12);
            padding: 9px 4px;
            color: #626b86;
            font-size: 11px;
            font-weight: 700;
        }

        .admin-theme-option:hover,
        .admin-theme-option.is-active {
            border-color: rgba(38, 54, 255, .25);
            background: #eef1ff;
            color: #2636ff;
        }

        .admin-sidebar-logout {
            border: 1px solid rgba(225, 29, 72, .2);
            background: #fff1f2;
            color: #e11d48;
        }

        .admin-panel .admin-sidebar-inner .border-slate-100 {
            border-color: rgba(25, 34, 70, .14) !important;
        }

        .admin-panel .admin-topnav-header button {
            border-radius: 0 !important;
        }

        html.dark .admin-panel .admin-sidebar-inner nav a {
            color: #9ba3bd !important;
        }

        html.dark .admin-panel .admin-sidebar-inner nav a:hover {
            background: rgba(30, 230, 225, .06) !important;
            color: #f3f5ff !important;
        }

        html.dark .admin-panel .admin-sidebar-inner nav a[class*="bg-blue-50"] {
            background: rgba(30, 230, 225, .08) !important;
            color: #1ee6e1 !important;
        }

        html.dark .admin-theme-option {
            border-color: rgba(255, 255, 255, .12);
            color: #9ba3bd;
        }

        html.dark .admin-theme-option:hover,
        html.dark .admin-theme-option.is-active {
            border-color: rgba(30, 230, 225, .25);
            background: rgba(30, 230, 225, .08);
            color: #1ee6e1;
        }

        html.dark .admin-sidebar-logout {
            border-color: rgba(255, 111, 111, .25);
            background: rgba(255, 111, 111, .08);
            color: #ff9d9d;
        }

        .admin-panel .admin-sidebar-inner,
        .admin-panel .admin-topnav-header {
            background: #ffffff !important;
            border-color: rgba(25, 34, 70, .14) !important;
        }

        .admin-panel .admin-clock {
            border-color: rgba(25, 34, 70, .14) !important;
            background: #f8f9fd !important;
        }

        html.dark .admin-panel .admin-sidebar-inner,
        html.dark .admin-panel .admin-topnav-header {
            background: #0f172a !important;
            border-color: rgba(255, 255, 255, .14) !important;
        }

        html.dark .admin-panel .admin-clock {
            border-color: rgba(255, 255, 255, .14) !important;
            background: #1b1e2c !important;
        }

        html.dark .admin-panel .admin-sidebar-inner .border-slate-100 {
            border-color: rgba(255, 255, 255, .14) !important;
        }

        html.dark .admin-panel .admin-resource-page [class*="bg-white"] {
            background: #171924 !important;
        }

        html.dark .admin-panel .admin-resource-page [class*="bg-slate-50"],
        html.dark .admin-panel .admin-resource-page [class*="bg-slate-100"],
        html.dark .admin-panel .admin-resource-page [class*="bg-slate-900"] {
            background: #1b1e2c !important;
        }

        html.dark .admin-panel .admin-resource-page input:not([type="hidden"]),
        html.dark .admin-panel .admin-resource-page textarea,
        html.dark .admin-panel .admin-resource-page select {
            border-color: var(--admin-line) !important;
            background: #1b1e2c !important;
            color: #f3f5ff !important;
        }

        html.dark .admin-panel .admin-resource-page table tbody {
            background: #171924 !important;
        }

        html.dark .admin-panel .admin-content {
            padding: 32px !important;
        }

        html.dark .admin-panel .admin-dashboard>section>div {
            border-color: var(--admin-line) !important;
            border-radius: 0 !important;
            background: #171924 !important;
            box-shadow: none !important;
        }

        html.dark .admin-panel .admin-summary-panel {
            border-color: var(--admin-line) !important;
            border-top-color: var(--admin-cyan) !important;
            background: #171924 !important;
            box-shadow: none !important;
        }

        html.dark .admin-panel .admin-dashboard>section:first-child>div.admin-hero {
            background: radial-gradient(ellipse at 75% 0%, rgba(38, 54, 255, .45), transparent 45%), #111217 !important;
        }

        html.dark .admin-panel .admin-dashboard>section>div>div[class*="bg-slate-50"],
        html.dark .admin-panel .admin-dashboard>section>div>div[class*="bg-slate-900"] {
            background: #1b1e2c !important;
        }

        html.dark .admin-panel .admin-dashboard .text-slate-900,
        html.dark .admin-panel .admin-dashboard .text-slate-800,
        html.dark .admin-panel .admin-dashboard .text-slate-700 {
            color: #f3f5ff !important;
        }

        .admin-panel .admin-sidebar,
        .admin-panel .admin-sidebar-inner {
            background: #ffffff !important;
            border-color: rgba(25, 34, 70, .14) !important;
        }

        .admin-panel .admin-topnav-header {
            background: rgba(255, 255, 255, .85) !important;
            border-color: rgba(25, 34, 70, .14) !important;
        }

        html.dark .admin-panel .admin-sidebar,
        html.dark .admin-panel .admin-sidebar-inner {
            background: #0f172a !important;
            border-color: #1e293b !important;
        }

        html.dark .admin-panel .admin-topnav-header {
            background: rgba(15, 23, 42, .85) !important;
            border-color: #1e293b !important;
        }

        html.dark .admin-panel .admin-sidebar-inner nav a {
            color: #a3aed0 !important;
        }

        html.dark .admin-panel .admin-sidebar-inner nav a:hover {
            background: rgba(30, 230, 225, .06) !important;
            color: #f3f5ff !important;
        }

        html.dark .admin-panel .admin-sidebar-inner nav a[class*="bg-blue-50"] {
            background: rgba(30, 230, 225, .08) !important;
            color: #1ee6e1 !important;
        }

        html.dark .admin-panel .admin-dashboard .text-slate-600,
        html.dark .admin-panel .admin-dashboard .text-slate-500,
        html.dark .admin-panel .admin-dashboard .text-slate-400 {
            color: #9ba3bd !important;
        }

        html.dark .admin-panel .settings-form input:not([type="hidden"]),
        html.dark .admin-panel .settings-form textarea,
        html.dark .admin-panel .settings-form select {
            border-color: rgba(255, 255, 255, .14) !important;
            background: #1b1e2c !important;
            color: #f3f5ff !important;
            caret-color: #1ee6e1;
        }

        html.dark .admin-panel .settings-form input:not([type="hidden"]):focus,
        html.dark .admin-panel .settings-form textarea:focus,
        html.dark .admin-panel .settings-form select:focus {
            border-color: #1ee6e1 !important;
            box-shadow: 0 0 0 2px rgba(30, 230, 225, .12);
        }

        html.dark .admin-panel .settings-form input[type="file"] {
            background: #171924 !important;
            color: #f3f5ff !important;
        }

        html.dark .admin-panel .settings-form label:has(input[type="checkbox"]) {
            border-color: rgba(255, 255, 255, .14) !important;
            background: #1b1e2c;
            color: #f3f5ff;
        }

        html.dark .admin-panel .admin-sidebar,
        html.dark .admin-panel .admin-topnav,
        html.dark .admin-panel .admin-footer {
            border-color: var(--admin-line) !important;
            background: #111217 !important;
        }

        html.dark .admin-panel .admin-topnav-header {
            border-color: var(--admin-line) !important;
            background: #111217 !important;
        }

        html.dark .admin-panel .admin-sidebar-inner {
            border-color: var(--admin-line) !important;
            background: #111217 !important;
        }

        html.dark .admin-panel .admin-sidebar-inner a:not([class*="bg-blue-50"]):hover {
            background: rgba(30, 230, 225, .06) !important;
            color: #f3f5ff !important;
        }

        html.dark .admin-panel .admin-sidebar-inner a[class*="bg-blue-50"] {
            background: rgba(30, 230, 225, .08) !important;
            color: #1ee6e1 !important;
        }

        html.dark .admin-panel .admin-hero {
            border-color: var(--admin-line) !important;
            background: radial-gradient(ellipse at 75% 0%, rgba(38, 54, 255, .45), transparent 45%), #111217 !important;
        }

        html.dark .admin-panel .admin-card {
            border-color: var(--admin-line) !important;
            background: #171924 !important;
            box-shadow: none !important;
        }

        html.dark .admin-panel .admin-card-soft {
            background: #1b1e2c !important;
        }

        html.dark .admin-panel .admin-content [class*="border-slate-200"][class*="bg-white"] {
            border-color: var(--admin-line) !important;
            background: #171924 !important;
            box-shadow: none !important;
        }

        html.dark .admin-panel .admin-content [class*="border-slate-200"][class*="bg-slate-900"] {
            border-color: var(--admin-line) !important;
            background: #171924 !important;
        }

        html.dark .admin-panel .admin-content input:not([type="hidden"]),
        html.dark .admin-panel .admin-content textarea,
        html.dark .admin-panel .admin-content select {
            border-color: var(--admin-line) !important;
            background: #1b1e2c !important;
            color: #f3f5ff !important;
            caret-color: var(--admin-cyan);
        }

        html.dark .admin-panel .admin-content input:not([type="hidden"]):focus,
        html.dark .admin-panel .admin-content textarea:focus,
        html.dark .admin-panel .admin-content select:focus {
            border-color: var(--admin-cyan) !important;
            box-shadow: 0 0 0 2px rgba(30, 230, 225, .12);
        }

        html.dark .admin-panel .admin-content [class*="bg-slate-50"] {
            background: #1b1e2c !important;
        }

        html.dark .admin-panel .admin-dashboard>section>div {
            border-top-color: var(--admin-cyan);
        }

        html.dark .admin-panel .admin-dashboard>section>div {
            border-color: var(--admin-line) !important;
            background: #171924 !important;
            box-shadow: none !important;
        }

        html.dark .admin-panel .admin-dashboard>section:first-child>div.admin-hero {
            background: radial-gradient(ellipse at 75% 0%, rgba(38, 54, 255, .45), transparent 45%), #111217 !important;
        }

        html.dark .admin-panel .admin-dashboard>section>div>div[class*="bg-slate-50"],
        html.dark .admin-panel .admin-dashboard>section>div>div[class*="bg-slate-900"] {
            background: #1b1e2c !important;
        }

        html.dark .admin-panel .admin-dashboard .bg-blue-100,
        html.dark .admin-panel .admin-dashboard .bg-amber-100,
        html.dark .admin-panel .admin-dashboard .bg-indigo-100,
        html.dark .admin-panel .admin-dashboard .bg-emerald-100,
        html.dark .admin-panel .admin-dashboard .bg-rose-100 {
            background: #1b1e2c !important;
        }

        html.dark .admin-panel .admin-dashboard {
            background: #0b0d15;
        }

        html.dark .admin-panel .admin-dashboard>section>div {
            border-color: #303442 !important;
            background: #171924 !important;
        }

        html.dark .admin-panel .admin-dashboard>section:first-child>div.admin-hero {
            background: radial-gradient(ellipse at 75% 0%, rgba(38, 54, 255, .42), transparent 48%), #0d101c !important;
        }

        html.dark .admin-panel .admin-dashboard .admin-clock,
        html.dark .admin-panel .admin-dashboard>section>div>div[class*="bg-slate-50"],
        html.dark .admin-panel .admin-dashboard>section>div>div[class*="bg-slate-900"] {
            border-color: #303442 !important;
            background: #1b1e2c !important;
        }

        html.dark .admin-panel .admin-dashboard .text-slate-900,
        html.dark .admin-panel .admin-dashboard .text-slate-800,
        html.dark .admin-panel .admin-dashboard .text-slate-700 {
            color: #f3f5ff !important;
        }

        html.dark .admin-panel .admin-dashboard .text-slate-600,
        html.dark .admin-panel .admin-dashboard .text-slate-500,
        html.dark .admin-panel .admin-dashboard .text-slate-400 {
            color: #9ba3bd !important;
        }

        @media (max-width: 640px) {

            .admin-panel .admin-content,
            html.dark .admin-panel .admin-content {
                padding: 18px !important;
            }
        }

        html.dark .admin-panel .text-slate-900,
        html.dark .admin-panel .text-slate-800,
        html.dark .admin-panel .text-slate-700 {
            color: #f3f5ff !important;
        }

        html.dark .admin-panel .text-slate-600,
        html.dark .admin-panel .text-slate-500,
        html.dark .admin-panel .text-slate-400 {
            color: #9ba3bd !important;
        }
    </style>

    <!-- Tag head lainnya milik Anda... -->

    <!-- Script untuk mencegah layar berkedip putih saat dark mode -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia(
                '(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>

    @stack('head')
</head>

<body x-data="layout()" x-init="init()" :class="{ 'dark': dark }"
    class="admin-panel bg-slate-100 min-h-screen text-slate-800 transition-colors duration-200 dark:bg-slate-950 dark:text-slate-100">
    <div class="admin-frame flex min-h-screen bg-slate-100 dark:bg-slate-950">
        <!-- Desktop sidebar -->
        <aside
            class="admin-sidebar hidden lg:flex lg:flex-col w-72 bg-white border-r border-slate-200 shadow-sm dark:bg-slate-900 dark:border-slate-700">
            @include('components.sidebar')
        </aside>

        <!-- Mobile sidebar (slide-over) -->
        <div x-show="openSidebar" x-cloak class="fixed inset-0 z-40 lg:hidden">
            <div @click="openSidebar = false" class="absolute inset-0 bg-black/40"></div>
            <div class="absolute left-0 top-0 bottom-0 w-72 bg-white p-4 shadow-lg overflow-auto dark:bg-slate-900"
                x-transition:enter="transition transform duration-200"
                x-transition:enter-start="-translate-x-6 opacity-0" x-transition:enter-end="translate-x-0 opacity-100"
                x-transition:leave="transition transform duration-150"
                x-transition:leave-start="translate-x-0 opacity-100" x-transition:leave-end="-translate-x-6 opacity-0">
                @include('components.sidebar')
            </div>
        </div>

        <div class="flex-1 flex flex-col" :class="{ 'overflow-hidden': openSidebar }">
            <div class="admin-topnav">
                @include('components.topnav')
            </div>

            <main class="admin-content p-6 overflow-auto">
                <div class="max-w-7xl mx-auto">
                    @yield('content')
                </div>
            </main>

            <footer
                class="admin-footer bg-white border-t border-slate-200 px-6 py-4 text-xs text-slate-500 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-400">
                <div class="max-w-7xl mx-auto flex items-center justify-between">
                    <div>Versi: <span class="font-semibold">1.0.0</span></div>
                    <div>&copy; {{ date('Y') }} PILKETOS SMKN 1 Bangsri</div>
                </div>
            </footer>
        </div>
    </div>

    <script>
        function layout() {
            return {
                dark: localStorage.getItem('theme') === 'dark',
                openSidebar: false,
                time: new Date(),
                init() {
                    this.applyTheme();
                },
                applyTheme() {
                    document.documentElement.classList.toggle('dark', this.dark);
                },
                toggleDark() {
                    this.dark = !this.dark;
                    localStorage.setItem('theme', this.dark ? 'dark' : 'light');
                    this.applyTheme();
                },
                now() {
                    this.time = new Date();
                    setTimeout(() => this.now(), 60000)
                },
            }
        }
        window.layout = layout;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function() {
            if (typeof Swal === 'undefined') return;

            const swalClasses = {
                popup: 'pilketos-swal-popup p-6 max-w-md w-full',
                title: 'pilketos-swal-title text-lg font-bold',
                htmlContainer: 'pilketos-swal-html mt-3 text-sm',
                actions: 'flex flex-wrap justify-end gap-3 mt-6',
                inputLabel: 'pilketos-swal-input-label',
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
                window.Swal = mix;
            } catch (e) {
                console.error('Failed to initialize Swal defaults', e);
            }
        })();
    </script>
    <script>
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
    </script>
    @stack('scripts')
</body>

</html>
