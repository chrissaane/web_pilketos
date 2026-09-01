@extends('layouts.admin')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Dashboard Admin</h1>
                <p class="text-sm text-slate-500">Ringkasan real-time untuk pemilihan</p>
            </div>
            <div class="hidden sm:flex items-center gap-3">
                <div class="text-sm text-slate-500">{{ now()->format('l, d M Y') }}</div>
            </div>
        </div>

        <!-- Stats + Quick Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    @include('components.stat-card', [
                        'title' => 'Total Pemilih',
                        'value' => $stats['voters'] ?? 0,
                        'icon' => 'fa-solid fa-users',
                        'color' => 'bg-blue-500',
                        'percent' => $stats['voters_change'] ?? null,
                        'trend' => $stats['voters_change_trend'] ?? 'up',
                    ])
                    @include('components.stat-card', [
                        'title' => 'Sudah Memilih',
                        'value' => $stats['voted'] ?? 0,
                        'icon' => 'fa-solid fa-check-circle',
                        'color' => 'bg-emerald-500',
                        'percent' => $stats['voted_change'] ?? null,
                        'trend' => $stats['voted_change_trend'] ?? 'up',
                    ])
                    @include('components.stat-card', [
                        'title' => 'Total Kandidat',
                        'value' => $stats['candidates'] ?? 0,
                        'icon' => 'fa-solid fa-user-group',
                        'color' => 'bg-indigo-500',
                        'percent' => null,
                    ])
                    @include('components.stat-card', [
                        'title' => 'User Online',
                        'value' => $stats['online'] ?? 0,
                        'icon' => 'fa-solid fa-signal',
                        'color' => 'bg-amber-500',
                        'percent' => null,
                    ])
                </div>

                <div class="mt-2">
                    @include('components.quick-actions')
                </div>

                <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-bold text-slate-800">Ringkasan Voting</h3>
                        <div class="text-sm text-slate-500">Status: <span
                                class="font-semibold text-{{ ($voting_status ?? 'sedang') == 'ditutup' ? 'rose' : 'emerald' }}-600">{{ $voting_status ?? 'Sedang Berlangsung' }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <div class="lg:col-span-2">
                            <canvas id="votesChart" height="160"></canvas>
                        </div>
                        <div class="space-y-4">
                            <div class="bg-slate-50 rounded-lg p-3 text-sm">
                                <div class="flex justify-between text-slate-500 mb-2">Target Pemilih <span
                                        class="font-semibold text-slate-700">{{ $stats['target'] ?? 1000 }}</span></div>
                                <div class="flex justify-between text-slate-500 mb-2">Sudah Memilih <span
                                        class="font-semibold text-slate-700">{{ $stats['voted'] ?? 0 }}</span></div>
                                @php
                                    $pct = $stats['target']
                                        ? round((($stats['voted'] ?? 0) / ($stats['target'] ?? 1)) * 100)
                                        : 0;
                                @endphp
                                <div class="h-3 bg-slate-100 rounded-full overflow-hidden mt-2">
                                    <div class="h-3 bg-emerald-500 rounded-full" style="width:{{ $pct }}%"></div>
                                </div>
                                <div class="text-xs text-slate-500 mt-2">{{ $pct }}% dari target</div>
                            </div>

                            <div class="bg-white rounded-lg p-3 border text-sm">
                                <div class="font-medium">Countdown Penutupan</div>
                                <div x-data="{ end: new Date('{{ $voting_end ?? now()->addHours(5) }}'), now: new Date(), diff: 0 }" x-init="setInterval(() => { now = new Date();
                                    diff = Math.max(0, end - now); }, 1000)" class="mt-2">
                                    <div class="text-lg font-bold" x-text="new Date(diff).toISOString().substr(11,8)"></div>
                                    <div class="text-xs text-slate-500 mt-1">Berakhir pada:
                                        {{ $voting_end ?? now()->addHours(5) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="space-y-4">
                @include('components.ranking-list')

                <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-sm">
                    <h4 class="font-bold text-slate-800 mb-3">Jadwal Voting Hari Ini</h4>
                    @if (isset($schedules) && count($schedules))
                        <ul class="space-y-2 text-sm text-slate-600">
                            @foreach ($schedules as $s)
                                <li class="flex justify-between">
                                    <div>{{ $s->kelas ?? 'Kelas' }}</div>
                                    <div class="text-slate-500">{{ $s->time ?? '' }}</div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-sm text-slate-500">Tidak ada jadwal untuk hari ini.</div>
                    @endif
                </div>

                @include('components.activity-log')
            </div>
        </div>

        <!-- Candidate / Voting area (kept existing functionality) -->
        <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
            <h3 class="text-lg font-bold text-slate-800 mb-4">Daftar Pasangan Calon</h3>

            @if (session('already_voted') || (Auth::user()->has_voted ?? false))
                <div class="bg-emerald-50 border-2 border-emerald-200 rounded-2xl p-6 text-center shadow-sm">
                    <div class="text-2xl font-bold text-slate-800">Terima Kasih, Anda Sudah Memilih!</div>
                    <p class="text-slate-600">Suara Anda telah tercatat.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($candidates ?? [] as $key => $cand)
                        <div class="bg-slate-50 rounded-2xl overflow-hidden border shadow-sm">
                            <div class="h-44 bg-cover bg-center"
                                style="background-image:url('{{ $cand->photo ?? 'https://i.pravatar.cc/600' }}')"></div>
                            <div class="p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-bold text-slate-800">{{ $cand->name ?? 'Nama Paslon' }}</div>
                                        <div class="text-xs text-slate-500">{{ $cand->position ?? 'Ketua & Wakil' }}</div>
                                    </div>
                                    <div class="text-sm text-slate-500">#{{ str_pad($key + 1, 2, '0', STR_PAD_LEFT) }}</div>
                                </div>

                                <p class="text-sm text-slate-600 mt-3">{{ $cand->vision ?? '' }}</p>

                                <div class="mt-4">
                                    <button
                                        onclick="confirmVote({{ $cand->id ?? $key + 1 }}, '{{ $cand->name ?? 'Paslon' }}')"
                                        class="w-full py-2 rounded-lg bg-blue-600 text-white font-semibold hover:scale-[1.02] transition">Pilih
                                        Paslon</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>

    <form id="voteForm" action="{{ route('vote.store') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="paslon_id" id="selectedPaslonId">
    </form>

    @push('scripts')
        <script>
            // Chart setup
            const ctx = document.getElementById('votesChart');
            if (ctx) {
                const chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($chart['labels'] ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']) !!},
                        datasets: [{
                            label: 'Suara Masuk',
                            data: {!! json_encode($chart['data'] ?? [12, 19, 7, 14, 23, 30]) !!},
                            backgroundColor: 'rgba(59,130,246,0.12)',
                            borderColor: 'rgba(59,130,246,1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.3,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                grid: {
                                    color: '#f1f5f9'
                                }
                            }
                        }
                    }
                });
            }

            function confirmVote(paslonId, paslonName) {
                if (!window.Swal) {
                    return;
                }

                Swal.fire({
                    title: 'Konfirmasi Pilihan',
                    html: `<b>${paslonName}</b><br>Apakah Anda yakin?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Pilih',
                    cancelButtonText: 'Batal'
                }).then((res) => {
                    if (res.isConfirmed) {
                        document.getElementById('selectedPaslonId').value = paslonId;
                        document.getElementById('voteForm').submit();
                    }
                });
            }
        </script>
    @endpush

@endsection
```eof

---

### 🛠️ Langkah Tambahan backend (Laravel Fix):

1. **Tambahkan Controller (`app/Http/Controllers/VotingController.php`)**:
```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vote;
use Illuminate\Support\Facades\Auth;

class VotingController extends Controller
{
public function index()
{
return view('dashboard');
}

public function store(Request $request)
{
$user = Auth::user();

// Cek jika siswa sudah pernah vote
if ($user->has_voted) {
return redirect()->back()->with('already_voted', true);
}

// Simpan suara
Vote::create([
'user_id' => $user->id,
'paslon_id' => $request->paslon_id,
]);

// Tandai siswa sudah memilih
$user->update(['has_voted' => true]);

return redirect()->route('dashboard')->with('already_voted', true);
}
}
