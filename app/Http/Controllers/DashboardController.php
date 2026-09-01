<?php

namespace App\Http\Controllers;

use App\Http\Requests\VoteRequest;
use App\Models\ActivityLog;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\SiteSetting;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'guru') {
            return redirect()->route('guru.dashboard');
        }

        return redirect()->route('siswa.dashboard');
    }

    public function siswaDashboard()
    {
        $user = Auth::user();
        $election = Election::query()
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->latest('start_time')
            ->first();
        $hasVoted = $election ? Vote::query()->where('election_id', $election->id)->where('user_id', $user->id)->exists() : false;
        $history = Vote::query()
            ->with(['election', 'candidate'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();
        $stats = ['elections' => $history->count()];
        $totalVoters = User::query()->whereIn('role', ['guru', 'siswa'])->count();
        $participation = $election && $totalVoters > 0 ? round(Vote::query()->where('election_id', $election->id)->count() / $totalVoters * 100) : 0;

        return view('dashboard.siswa', compact('user', 'election', 'hasVoted', 'history', 'stats', 'participation'));
    }

    public function guruDashboard()
    {
        $user = Auth::user();
        $election = Election::query()
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->latest('start_time')
            ->first();
        $history = Vote::query()
            ->with(['election', 'candidate'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();
        $stats = ['elections' => $history->count()];
        $participation = $election ? round(Vote::query()->where('election_id', $election->id)->count() / max(User::query()->whereIn('role', ['guru', 'siswa'])->count(), 1) * 100) : 0;
        $hasVoted = $election ? $history->contains('election_id', $election->id) : false;

        return view('dashboard.guru', compact('user', 'election', 'hasVoted', 'history', 'stats', 'participation'));
    }

    public function adminDashboard()
    {
        $user = Auth::user();

        $activeElection = Election::query()
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->latest('start_time')
            ->first();

        $latestElection = Election::query()->latest('start_time')->first();
        $selectedElection = $activeElection ?? $latestElection;

        $totalElections = Election::query()->count();
        $activeElections = Election::query()->where('status', Election::STATUS_ACTIVE)->count();
        $voterCount = User::query()->whereIn('role', ['guru', 'siswa'])->count();
        $allUsers = User::query()->get();
        $siswaCount = $allUsers->filter(fn ($user) => $this->classifyUserRole($user->identity_number, $user->role) === 'siswa')->count();
        $guruCount = $allUsers->filter(fn ($user) => $this->classifyUserRole($user->identity_number, $user->role) === 'guru')->count();
        $onlineCount = User::query()->where('is_active', true)->count();

        $selectedElectionVotes = $selectedElection ? Vote::query()->where('election_id', $selectedElection->id)->count() : 0;
        $notVotedCount = max($voterCount - $selectedElectionVotes, 0);
        $progressPercent = $voterCount > 0 ? round(($selectedElectionVotes / $voterCount) * 100) : 0;

        $candidates = collect();
        if ($selectedElection) {
            $candidates = $selectedElection->candidates()->withCount('votes')->orderBy('candidate_number')->get();
            $totalVotes = $candidates->sum('votes_count');
            $candidates = $candidates->map(function ($candidate) use ($totalVotes) {
                $percent = $totalVotes > 0 ? number_format(($candidate->votes_count / $totalVotes) * 100, 0) . '%' : '0%';
                return (object) [
                    'id' => $candidate->id,
                    'name' => $candidate->name,
                    'votes_count' => $candidate->votes_count,
                    'percent' => $percent,
                    'votes' => $candidate->votes_count,
                ];
            });
        }

        $activities = \App\Models\ActivityLog::query()
            ->with('user')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($activity) {
                return (object) [
                    'created_at' => $activity->created_at->format('Y-m-d H:i'),
                    'user_type' => $activity->user?->role === 'guru' ? 'Guru' : ($activity->user?->role === 'siswa' ? 'Siswa' : 'Admin'),
                    'message' => $activity->description ?? $activity->action,
                    'class' => $activity->user?->class_group ?? '-',
                ];
            });

        $classTurnout = User::query()
            ->where('role', 'siswa')
            ->select('class_group', DB::raw('count(*) as total'))
            ->groupBy('class_group')
            ->orderByDesc('total')
            ->limit(3)
            ->get();

        $votesByClass = Vote::query()
            ->join('users', 'votes.user_id', '=', 'users.id')
            ->where('users.role', 'siswa')
            ->select('users.class_group', DB::raw('count(votes.id) as voted'))
            ->groupBy('users.class_group')
            ->pluck('voted', 'class_group');

        $turnoutGroups = $classTurnout->map(function ($group) use ($votesByClass) {
            $voted = $votesByClass->get($group->class_group, 0);
            $percent = $group->total > 0 ? round(($voted / $group->total) * 100) : 0;

            return (object) [
                'group' => $group->class_group,
                'percent' => $percent,
                'votes' => $voted,
                'total' => $group->total,
            ];
        });

        $stats = [
            'banners' => $totalElections,
            'active_banners' => $activeElections,
            'voters' => $voterCount,
            'voted' => $selectedElectionVotes,
            'not_voted' => $notVotedCount,
            'progress' => $progressPercent . '%',
            'progress_percent' => $progressPercent,
            'online' => $onlineCount,
            'active_ratio' => $totalElections > 0 ? round(($activeElections / $totalElections) * 100) : 0,
            'voted_ratio' => $voterCount > 0 ? round(($selectedElectionVotes / $voterCount) * 100) : 0,
            'not_voted_ratio' => $voterCount > 0 ? round(($notVotedCount / $voterCount) * 100) : 0,
        ];

        $votingStatus = $selectedElection?->status ?? Election::STATUS_UPCOMING;

        return view('dashboard.admin', compact(
            'user',
            'stats',
            'candidates',
            'activities',
            'turnoutGroups',
            'siswaCount',
            'guruCount',
            'votingStatus'
        ));
    }

    protected function classifyUserRole(?string $identityNumber, ?string $role): string
    {
        $normalizedRole = strtolower((string) ($role ?? ''));

        if (in_array($normalizedRole, ['guru', 'teacher', 'dosen', 'pegawai', 'staff', 'teacher_staff'], true)) {
            return 'guru';
        }

        if (in_array($normalizedRole, ['siswa', 'student', 'murid', 'pelajar', 'alumni', 'alumni_siswa'], true)) {
            return 'siswa';
        }

        $digits = preg_replace('/\D+/', '', (string) $identityNumber);

        if (strlen($digits) === 4) {
            return 'siswa';
        }

        if (strlen($digits) > 4) {
            return 'guru';
        }

        return 'siswa';
    }

    public function adminStatistics(Request $request)
    {
        $elections = Election::query()->orderBy('start_time', 'desc')->with('candidates')->get();
        $selectedElection = null;

        if ($request->filled('election_id')) {
            $selectedElection = $elections->firstWhere('id', $request->input('election_id'));
        }

        if (! $selectedElection) {
            $selectedElection = $elections->first();
        }

        $chartLabels = [];
        $chartData = [];
        $candidateRows = [];

        if ($selectedElection) {
            $candidates = $selectedElection->candidates()->withCount('votes')->orderBy('candidate_number')->get();
            $candidateRows = $candidates;
            $chartLabels = $candidates->pluck('name')->toArray();
            $chartData = $candidates->pluck('votes_count')->toArray();
        }

        $totalVotes = $selectedElection?->votes_count ?? array_sum($chartData);
        $voterCount = User::query()->whereIn('role', ['guru', 'siswa'])->count();
        $participation = $voterCount > 0 ? round(($totalVotes / $voterCount) * 100) : 0;

        return view('admin.statistics.index', compact(
            'elections',
            'selectedElection',
            'chartLabels',
            'chartData',
            'candidateRows',
            'totalVotes',
            'voterCount',
            'participation'
        ));
    }

    public function store(VoteRequest $request)
    {
        $user = Auth::user();
        if (SiteSetting::getValue('election_active', '1') !== '1') {
            return back()->with('error', 'Sistem pemilihan sedang dinonaktifkan oleh admin.');
        }

        $election = Election::findOrFail($request->input('election_id'));
        $candidate = Candidate::findOrFail($request->input('candidate_id'));

        if ($election->current_status !== Election::STATUS_ACTIVE) {
            return back()->with('error', 'Pemilihan ini sudah berakhir atau belum aktif.');
        }

        $alreadyVoted = Vote::query()->where('election_id', $election->id)->where('user_id', $user->id)->exists();
        if ($alreadyVoted) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Anda sudah menggunakan hak pilih pada periode ini.'], 422);
            }

            return back()->with('error', 'Anda sudah menggunakan hak pilih pada periode ini.');
        }

        // Verify voting token (normalize input)
        $tokenInput = $request->input('token');
        $tokenNormalized = strtoupper(trim((string) $tokenInput));
        $votingToken = \App\Models\VotingToken::query()->where('token', $tokenNormalized)->first();

        if (! $votingToken) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Token tidak ditemukan.'], 422);
            }

            return back()->with('error', 'Token tidak ditemukan.');
        }

        if ($votingToken->user_id !== $user->id) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Token tidak sesuai dengan pengguna saat ini.'], 403);
            }

            return back()->with('error', 'Token tidak sesuai dengan pengguna saat ini.');
        }

        if ($votingToken->election_id !== $election->id) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Token tidak berlaku untuk periode pemilihan ini.'], 422);
            }

            return back()->with('error', 'Token tidak berlaku untuk periode pemilihan ini.');
        }

        if ($votingToken->used_at) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Token sudah digunakan.'], 422);
            }

            return back()->with('error', 'Token sudah digunakan.');
        }

        try {
            DB::transaction(function () use ($election, $candidate, $user, $request, $votingToken) {
                Vote::create([
                    'election_id' => $election->id,
                    'candidate_id' => $candidate->id,
                    'user_id' => $user->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                $votingToken->used_at = now();
                $votingToken->save();
            });
        } catch (\Throwable $exception) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal menyimpan suara. Silakan coba lagi.'], 500);
            }

            return back()->with('error', 'Gagal menyimpan suara. Silakan coba lagi.');
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Suara Anda berhasil tersimpan.']);
        }

        return back()->with('success', 'Suara Anda berhasil tersimpan.');
    }
}