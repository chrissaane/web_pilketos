@forelse($accounts as $account)
    <tr class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/50">
        <td class="whitespace-nowrap px-6 py-4 text-slate-500 dark:text-slate-400">
            {{ $loop->iteration }}
        </td>
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
        <td class="whitespace-nowrap px-6 py-4">
            <div class="flex items-center gap-2">
                <form action="{{ route('admin.voters.password.update', $account['credential']) }}" method="POST"
                    class="inline-block" data-password-form data-account-name="{{ $account['name'] }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="password" value="">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1.5 text-xs font-semibold text-blue-700 transition hover:bg-blue-100 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-200"
                        title="Edit password pemilih">
                        <i class="fa-solid fa-pen"></i>
                        Edit
                    </button>
                </form>

                <form action="{{ route('admin.voters.destroy_by_id', $account['user_id']) }}" method="POST"
                    data-confirm="Yakin ingin menonaktifkan data pemilih ini?">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-2.5 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200"
                        title="Nonaktifkan pemilih">
                        <i class="fa-solid fa-trash"></i>
                        Hapus
                    </button>
                </form>
            </div>
        </td>
    </tr>
@empty
    <tr data-empty-state>
        <td colspan="10" class="px-6 py-12 text-center">
            <div class="flex flex-col items-center justify-center gap-2 text-slate-500 dark:text-slate-400">
                <i class="fa-solid fa-inbox text-3xl text-slate-300 dark:text-slate-600 mb-2"></i>
                <p>Tidak ada data untuk filter ini.</p>
            </div>
        </td>
    </tr>
@endforelse
