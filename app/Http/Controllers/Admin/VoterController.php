<?php

namespace App\Http\Controllers\Admin;

use App\Exports\VoterCredentialsExport;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Election;
use App\Models\VotingToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VoterController extends Controller
{
    public function syncFromSiPintu(Request $request)
    {
        try {
            $result = app(\App\Services\SiPintuGatewayService::class)->syncAllUsersFromGateway();
            $count = (int) ($result['total'] ?? 0);
            $message = $result['message'] ?? 'Sinkronisasi SiPintu selesai.';

            $redirectTo = route('admin.voters.index', [
                'filter' => $request->query('filter', 'semua'),
                'major' => $request->query('major', 'semua'),
            ]);

            if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                $payload = [
                    'success' => $count > 0 || str_contains(strtolower($message), 'selesai') || ! str_contains(strtolower($message), 'gagal'),
                    'message' => $count > 0 ? $message.' Ditemukan '.$count.' data baru.' : $message,
                    'count' => $count,
                    'redirect' => $redirectTo,
                ];

                return response()->json($payload);
            }

            if ($count > 0) {
                return redirect($redirectTo)->with('success', $message.' Ditemukan '.$count.' data baru.');
            }

            return redirect($redirectTo)->with('error', $message);
        } catch (\Throwable $e) {
            $redirectTo = route('admin.voters.index', [
                'filter' => $request->query('filter', 'semua'),
                'major' => $request->query('major', 'semua'),
            ]);

            if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'Sinkronisasi SiPintu gagal: '.$e->getMessage(),
                    'redirect' => $redirectTo,
                ], 500);
            }

            return redirect($redirectTo)->with('error', 'Sinkronisasi SiPintu gagal: '.$e->getMessage());
        }
    }

    public function index(Request $request)
    {
        $filters = [
            'semua' => 'Semua',
            'x' => 'X',
            'xi' => 'XI',
            'xii' => 'XII',
            'alumni' => 'Alumni',
            'guru' => 'Guru',
        ];

        $selectedFilter = strtolower($request->query('filter', 'semua'));
        $selectedMajor = 'semua';

        // Get active election (time-based)
        $activeElection = Election::where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->latest('start_time')
            ->first();

        $voters = User::query()
            ->when($selectedFilter === 'alumni', fn($q) => $q->where('role', 'siswa')->where('is_active', false))
            ->when($selectedFilter === 'guru', fn($q) => $q->where('role', 'guru'))
            ->when($selectedFilter === 'semua', fn($q) => $q->whereIn('role', ['siswa', 'guru']))
            ->when(in_array($selectedFilter, ['x','xi','xii'], true), function ($q) use ($selectedFilter) {
                $q->where('role', 'siswa')
                    ->where('class_group', $this->resolveClassGroup($selectedFilter));
            })
            ->orderByRaw("CASE WHEN role = 'guru' THEN 1 ELSE 0 END")
            ->orderBy('class_group')
            ->orderBy('name')
            ->get();

        $accounts = $voters->map(function ($voter) use ($activeElection) {
            $token = $this->resolveToken($voter->id, $activeElection);

            return [
                'name' => $voter->name,
                'group' => $voter->role === 'guru'
                    ? 'Guru'
                    : $this->formatClassDisplay($voter->class_group, $voter->major),
                'credential' => $voter->identity_number ?: 'N/A',
                'email' => $voter->email ?: 'N/A',
                'major' => $voter->major ?: 'N/A',
                'phone' => $voter->phone ?: 'N/A',
                'status' => $voter->is_active ? 'Aktif' : 'Alumni',
                'password' => $voter->password ? 'Terdaftar' : 'N/A',
                'token' => $token ?? 'N/A',
                'user_id' => $voter->id,
            ];
        })->toArray();

        $activeCard = [
            'title' => $activeElection?->title ?? 'Tidak ada pemilihan aktif',
            'status' => $activeElection?->current_status ?? 'N/A',
            'description' => $activeElection
                ? 'Token hanya berlaku di card aktif saat ini. Akun hanya bisa memilih 1 kandidat sekali.'
                : 'Tidak ada periode pemilihan yang sedang berlangsung.',
        ];

        if ($request->boolean('partial')) {
            return view('admin.voters.partials.table', compact('accounts'));
        }

        return view('admin.voters.index', compact('filters', 'selectedFilter', 'selectedMajor', 'accounts', 'activeCard', 'activeElection'));
    }

    /**
     * Print preview for voter credentials
     */
    public function print(Request $request)
    {
        $filters = [
            'semua' => 'Semua',
            'x' => 'X',
            'xi' => 'XI',
            'xii' => 'XII',
            'guru' => 'Guru',
        ];

        $selectedFilter = strtolower($request->query('filter', 'semua'));
        $selectedMajor = 'semua';

        // Get active election (time-based)
        $activeElection = Election::where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->latest('start_time')
            ->first();

        $voters = User::where('is_active', true)
            ->when($selectedFilter !== 'semua', function ($query) use ($selectedFilter) {
                if ($selectedFilter === 'guru') {
                    $query->where('role', 'guru');
                } else {
                    $query->where('role', 'siswa')
                        ->where('class_group', $this->resolveClassGroup($selectedFilter));
                }
            })
            ->get();

        $accounts = $voters->map(function ($voter) use ($activeElection) {
            $token = $this->resolveToken($voter->id, $activeElection);

            return [
                'name' => $voter->name,
                'group' => $voter->role === 'guru'
                    ? 'Guru'
                    : $this->formatClassDisplay($voter->class_group, $voter->major),
                'credential' => $voter->identity_number,
                'password' => $voter->birth_date ? $voter->birth_date->format('Y-m-d') : 'N/A',
                'token' => $token ?? 'N/A',
                'user_id' => $voter->id,
            ];
        })->toArray();

        return view('admin.voters.print', compact('filters', 'selectedFilter', 'selectedMajor', 'accounts', 'activeElection'));
    }

    private function resolveToken(int $userId, ?Election $activeElection): ?string
    {
        if (! Schema::hasTable('voting_tokens')) {
            return null;
        }

        $query = VotingToken::where('user_id', $userId);

        if ($activeElection) {
            $query->where('election_id', $activeElection->id);
        }

        return $query->value('token');
    }

    private function resolveClassGroup(string $filter): string
    {
        return match ($filter) {
            'x' => '10',
            'xi' => '11',
            'xii' => '12',
            default => '10',
        };
    }

    private function formatClassDisplay(?string $classGroup, ?string $major = null): string
    {
        $normalizedClass = blank($classGroup)
            ? null
            : match ($classGroup) {
                '10', 'X' => 'X',
                '11', 'XI' => 'XI',
                '12', 'XII' => 'XII',
                default => trim((string) $classGroup),
            };

        $label = blank($normalizedClass) ? 'Kelas' : 'Kelas ' . $normalizedClass;

        if (blank($major)) {
            return $normalizedClass ? $label : 'N/A';
        }

        return trim($label . ' ' . $major);
    }

    public function export(Request $request)
    {
        $filter = strtolower($request->query('filter', 'semua'));
        $major = $request->query('major', 'semua');

        return (new VoterCredentialsExport($filter, $major))->download();
    }

    public function promote(Request $request)
    {
        $request->validate([]);

        DB::beginTransaction();
        try {
            $promoted10 = User::where('role', 'siswa')->where('is_active', true)->where('class_group', '10')->update(['class_group' => '11']);
            $promoted11 = User::where('role', 'siswa')->where('is_active', true)->where('class_group', '11')->update(['class_group' => '12']);
            $graduated = User::where('role', 'siswa')->where('is_active', true)->where('class_group', '12')->update(['is_active' => false]);

            DB::commit();

            return redirect()->route('admin.voters.index')->with('success', sprintf('Proses kenaikan kelas selesai. Naik: %d (X→XI), %d (XI→XII). Alumni diproses: %d', $promoted10, $promoted11, $graduated));
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal memproses kenaikan kelas: ' . $e->getMessage()]);
        }
    }

    public function archiveXii(Request $request)
    {
        DB::beginTransaction();
        try {
            $archived = User::where('role', 'siswa')->where('class_group', '12')->where('is_active', true)->update(['is_active' => false]);
            DB::commit();

            return redirect()->route('admin.voters.index')->with('success', sprintf('Arsip Kelas XII selesai. %d siswa dipindahkan ke Alumni (nonaktif).', $archived));
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal mengarsipkan kelas XII: ' . $e->getMessage()]);
        }
    }

    public function generateTokens(Request $request)
    {
        $all = $request->boolean('all');
        $force = $request->boolean('force');
        $createdTotal = 0;

        if ($all) {
            $elections = Election::where('end_time', '>=', now())->get();
            if ($elections->isEmpty()) {
                return back()->withErrors(['error' => 'Tidak ada card pemilihan yang belum berakhir.']);
            }

            foreach ($elections as $election) {
                \App\Models\Election::generateTokensForElection($election, $force);
            }

            return redirect()->route('admin.voters.index')->with('success', 'Token berhasil dibuat untuk semua card yang belum berakhir.');
        }

        $electionId = $request->input('election_id');

        $election = null;
        if ($electionId) {
            $election = \App\Models\Election::find($electionId);
        }

        if (! $election) {
            // prefer active election, fall back to latest
            $election = \App\Models\Election::where('start_time', '<=', now())->where('end_time', '>=', now())->latest('start_time')->first()
                ?? \App\Models\Election::latest('start_time')->first();
        }

        if (! $election) {
            return back()->withErrors(['error' => 'Tidak ada periode pemilihan yang ditemukan.']);
        }

        \App\Models\Election::generateTokensForElection($election, $force);

        return redirect()->route('admin.voters.index')->with('success', "Token berhasil dibuat untuk periode {$election->title}.");
    }
}
