@forelse($accounts as $account)
    <tr class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/50">
        <td class="whitespace-nowrap px-6 py-4 font-semibold text-slate-900 dark:text-white">
            {{ $account['name'] }}
        </td>
        <td class="whitespace-nowrap px-6 py-4">{{ $account['group'] }}</td>
        <td class="whitespace-nowrap px-6 py-4 text-slate-500 dark:text-slate-400">
            {{ $account['credential'] }}
        </td>
        <td class="whitespace-nowrap px-6 py-4 text-slate-500 dark:text-slate-400">
            {{ $account['email'] }}
        </td>
        <td class="whitespace-nowrap px-6 py-4 text-slate-500 dark:text-slate-400">
            {{ $account['phone'] }}
        </td>
        <td class="whitespace-nowrap px-6 py-4 text-slate-500 dark:text-slate-400">
            {{ $account['status'] }}
        </td>
        <td class="whitespace-nowrap px-6 py-4 text-slate-500 dark:text-slate-400">
            {{ $account['password'] }}
        </td>
        <td class="whitespace-nowrap px-6 py-4 font-medium text-slate-700 dark:text-slate-300">
            {{ $account['token'] }}
        </td>
    </tr>
@empty
    <tr data-empty-state>
        <td colspan="8" class="px-6 py-12 text-center">
            <div class="flex flex-col items-center justify-center gap-2 text-slate-500 dark:text-slate-400">
                <i class="fa-solid fa-inbox text-3xl text-slate-300 dark:text-slate-600 mb-2"></i>
                <p>Tidak ada data untuk filter ini.</p>
            </div>
        </td>
    </tr>
@endforelse
