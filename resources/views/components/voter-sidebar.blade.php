<aside
    class="voter-sidebar fixed inset-y-0 left-0 z-50 flex min-h-0 w-72 shrink-0 -translate-x-full flex-col border-r border-slate-200 bg-white p-5 shadow-xl transition-transform duration-200 dark:border-slate-800 dark:bg-slate-900 lg:sticky lg:top-0 lg:h-screen lg:min-h-screen lg:translate-x-0 lg:overflow-y-auto lg:shadow-none"
    x-bind:class="mobileMenu ? 'translate-x-0' : ''">
    <a href="{{ route('home') }}" class="mb-8 flex items-center gap-3 border-0 bg-transparent px-2 shadow-none ring-0">
        @php($logoPath = App\Models\SiteSetting::getValue('logo_path', ''))
        @if ($logoPath)
            <img src="{{ filter_var($logoPath, FILTER_VALIDATE_URL) ? $logoPath : Illuminate\Support\Facades\Storage::url($logoPath) }}"
                alt="Logo"
                class="h-10 w-10 shrink-0 rounded-none border-0 bg-transparent object-cover outline-none ring-0 shadow-none">
        @else
            <span
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-none border-0 bg-blue-600 text-lg font-black text-white">P</span>
        @endif
        <span class="min-w-0">
            <strong
                class="block truncate text-sm font-bold tracking-wide text-slate-900 dark:text-white">PILKETOS</strong>
            <span
                class="block truncate text-xs font-bold uppercase text-slate-500 dark:text-slate-400">{{ App\Models\SiteSetting::getValue('school_name', 'SMKN 1 Bangsri') }}</span>
        </span>
    </a>

    <button type="button" x-on:click="mobileMenu = false"
        class="absolute right-4 top-4 flex h-9 w-9 items-center justify-center border border-slate-200 text-slate-500 transition-colors hover:bg-slate-50 hover:text-slate-900 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white lg:hidden"
        aria-label="Tutup menu" title="Tutup menu">
        <i class="fa-solid fa-xmark"></i>
    </button>

    <nav class="flex-1 overflow-y-auto pr-2" aria-label="Navigasi dashboard">
        <ul class="space-y-1.5">
            <li>
                <a href="{{ route('dashboard') }}" x-on:click="mobileMenu = false"
                    class="voter-sidebar-link {{ request()->routeIs('siswa.dashboard', 'guru.dashboard') ? 'is-active' : '' }}">
                    <i
                        class="fa-solid fa-chart-line w-5 text-center {{ request()->routeIs('siswa.dashboard', 'guru.dashboard') ? '' : 'opacity-70' }}"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('history') }}" x-on:click="mobileMenu = false"
                    class="voter-sidebar-link {{ request()->routeIs('history') ? 'is-active' : '' }}">
                    <i
                        class="fa-solid fa-clock-rotate-left w-5 text-center {{ request()->routeIs('history') ? '' : 'opacity-70' }}"></i>
                    <span>History</span>
                </a>
            </li>
            <li>
                <a href="{{ route('home') }}" x-on:click="mobileMenu = false" class="voter-sidebar-link">
                    <i class="fa-solid fa-house w-5 text-center opacity-70"></i>
                    <span>Beranda</span>
                </a>
            </li>
            <li>
                <a href="{{ route('home') }}#pemilihan" x-on:click="mobileMenu = false" class="voter-sidebar-link">
                    <i class="fa-solid fa-image w-5 text-center opacity-70"></i>
                    <span>Pemilihan</span>
                </a>
            </li>
            <li>
                <a href="{{ route('results') }}" x-on:click="mobileMenu = false" class="voter-sidebar-link">
                    <i class="fa-solid fa-chart-simple w-5 text-center opacity-70"></i>
                    <span>Hasil</span>
                </a>
            </li>
            <li>
                <a href="{{ route('home') }}#tentang" x-on:click="mobileMenu = false" class="voter-sidebar-link">
                    <i class="fa-solid fa-circle-info w-5 text-center opacity-70"></i>
                    <span>Tentang</span>
                </a>
            </li>
        </ul>
    </nav>

    <div class="mt-auto space-y-2 border-t border-slate-200 pt-5 dark:border-slate-800">
        <form action="{{ route('logout') }}" method="POST" class="logout-confirm-form">
            @csrf
            <button type="submit" class="voter-sidebar-link voter-sidebar-logout w-full">
                <i class="fa-solid fa-arrow-right-from-bracket w-5 text-center"></i> Keluar
            </button>
        </form>
    </div>
</aside>
