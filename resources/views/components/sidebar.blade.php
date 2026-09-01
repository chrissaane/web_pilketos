<div
    class="admin-sidebar-inner sticky top-0 flex h-screen flex-col bg-white p-5 border-r border-slate-200 shadow-sm transition-colors duration-200 dark:bg-slate-900 dark:border-slate-800">

    <!-- Header / Logo -->
    <div class="mb-8 flex items-center gap-3 px-2">
        @php($logoPath = App\Models\SiteSetting::getValue('logo_path', ''))
        @if ($logoPath)
            <img src="{{ filter_var($logoPath, FILTER_VALIDATE_URL) ? $logoPath : Illuminate\Support\Facades\Storage::url($logoPath) }}"
                alt="Logo" class="h-10 w-10 shrink-0 object-cover rounded-lg">
        @else
            <div
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-600 text-lg font-black text-white shadow-md shadow-blue-600/20">
                P
            </div>
        @endif
        <div>
            <div class="text-sm font-bold tracking-wide text-slate-900 dark:text-white">PILKETOS</div>
            <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Admin Panel</div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto pr-2">
        <ul class="space-y-1.5">
            <li>
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->is('admin/dashboard') ? 'bg-blue-50 text-blue-600 dark:bg-blue-600/10 dark:text-blue-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-white' }}">
                    <i
                        class="fa-solid fa-chart-line w-5 text-center {{ request()->is('admin/dashboard') ? '' : 'opacity-70' }}"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.cards.index') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->is('admin/cards*') ? 'bg-blue-50 text-blue-600 dark:bg-blue-600/10 dark:text-blue-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-white' }}">
                    <i
                        class="fa-solid fa-image w-5 text-center {{ request()->is('admin/cards*') ? '' : 'opacity-70' }}"></i>
                    <span>Card</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.voters.index') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->is('admin/voters*') ? 'bg-blue-50 text-blue-600 dark:bg-blue-600/10 dark:text-blue-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-white' }}">
                    <i
                        class="fa-solid fa-users w-5 text-center {{ request()->is('admin/voters*') ? '' : 'opacity-70' }}"></i>
                    <span>Data Pemilih</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.statistics.index') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->is('admin/statistics*') ? 'bg-blue-50 text-blue-600 dark:bg-blue-600/10 dark:text-blue-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-white' }}">
                    <i
                        class="fa-solid fa-chart-simple w-5 text-center {{ request()->is('admin/statistics*') ? '' : 'opacity-70' }}"></i>
                    <span>Statistik</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.settings.index') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->is('admin/settings*') ? 'bg-blue-50 text-blue-600 dark:bg-blue-600/10 dark:text-blue-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-white' }}">
                    <i
                        class="fa-solid fa-gear w-5 text-center {{ request()->is('admin/settings*') ? '' : 'opacity-70' }}"></i>
                    <span>Pengaturan</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Footer Action -->
    <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800">
        <form action="{{ route('logout') }}" method="POST" class="logout-confirm-form mb-2">
            @csrf
            <button type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-slate-600 transition-colors duration-200 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-white">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar
            </button>
        </form>

        <a href="{{ url('/') }}"
            class="flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-blue-700 hover:shadow group dark:bg-blue-600 dark:hover:bg-blue-500">
            <i
                class="fa-solid fa-arrow-up-right-from-square w-4 transition-transform group-hover:-translate-y-0.5 group-hover:translate-x-0.5"></i>
            <span>Lihat Website</span>
        </a>
    </div>
</div>
