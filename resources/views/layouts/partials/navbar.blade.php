<nav x-data="{ mobileMenuOpen: false }"
    class="sticky top-0 z-50 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            <!-- Logo & Title -->
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white font-bold text-xl shadow-md">
                    PIL
                </div>
                <div>
                    <span class="font-extrabold text-lg tracking-tight text-blue-600 dark:text-blue-400">
                        PILKETOS
                    </span>
                    <span class="block text-[10px] font-semibold text-slate-500 uppercase">
                        SMK Negeri 1 Bangsri
                    </span>
                </div>
            </a>

            <!-- Right Menu & Login Button -->
            <div class="flex items-center gap-3">
                <!-- Toggle Dark Mode -->
                <button
                    onclick="(function(){ const isNowDark = document.documentElement.classList.toggle('dark'); localStorage.setItem('theme', isNowDark ? 'dark' : 'light'); })()"
                    type="button"
                    class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                    <!-- Moon shown in light mode, hidden in dark -->
                    <svg class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <!-- Sun shown in dark mode, hidden in light -->
                    <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </button>

                <!-- Tombol Login -->
                <a href="{{ route('login') }}"
                    class="px-5 py-2 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md transition-all">
                    Masuk Portal
                </a>
            </div>

        </div>
    </div>
</nav>
