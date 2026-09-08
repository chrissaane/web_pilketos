<header
    class="voter-topnav flex min-h-20 items-center justify-between border-b border-slate-200 bg-white/85 px-5 py-4 backdrop-blur-md dark:border-slate-800 dark:bg-slate-900/85 sm:px-8">
    <div class="flex items-center gap-4">
        <button type="button" x-on:click="mobileMenu = true"
            class="flex h-10 w-10 items-center justify-center border border-slate-200 bg-slate-50 text-slate-600 transition-colors hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 lg:hidden"
            aria-label="Buka menu" title="Buka menu">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Portal Pemilih</p>
            <h1 class="mt-1 text-lg font-bold text-slate-900 dark:text-white">
                {{ request()->routeIs('history') ? 'History Pemilihan' : (Auth::user()->role === 'guru' ? 'Dashboard Guru' : 'Dashboard Siswa') }}
            </h1>
        </div>
    </div>

    <div class="flex items-center gap-3 text-right">
        <time class="hidden text-xs text-slate-500 dark:text-slate-400 sm:block"
            x-text="new Date().toLocaleString('id-ID', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' })"></time>
        <div class="hidden h-8 w-px bg-slate-200 dark:bg-slate-800 sm:block"></div>
        <button type="button" x-on:click="theme = theme === 'dark' ? 'light' : 'dark'"
            class="flex h-10 w-10 items-center justify-center border border-slate-200 bg-slate-50 text-slate-600 transition-colors hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
            aria-label="Ganti tema" title="Ganti tema">
            <i class="fa-solid" :class="theme === 'dark' ? 'fa-sun' : 'fa-moon'"></i>
        </button>
        <div class="hidden h-10 w-10 items-center justify-center bg-blue-600 text-sm font-black text-white sm:flex">
            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
        </div>
    </div>
</header>
