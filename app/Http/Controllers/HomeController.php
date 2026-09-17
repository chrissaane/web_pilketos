<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\SiteSetting;
use App\Models\User;
use App\Models\Vote;
use App\Services\SiPintuGatewayService;
use Illuminate\Support\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        $showFinished = SiteSetting::getValue('show_finished', '1') === '1';
        $eligibleVoterIds = User::query()->eligibleVoters()->pluck('id');
        $elections = Election::with(['candidates' => fn ($query) => $query->withCount(['votes' => fn ($voteQuery) => $voteQuery->whereIn('user_id', $eligibleVoterIds)])])
            ->where('is_published', true)
            ->orderBy('start_time', 'desc')
            ->get();
        $resultElection = $elections->first();
        $showVoteCounts = $resultElection?->publicResultsVisible() ?? false;
        $resultsPublishAt = $resultElection?->results_publish_at;
        $resultCandidates = $resultElection?->candidates ?? collect();
        $resultTotalVotes = $resultCandidates->sum('votes_count');
        $resultVoterCount = User::query()->eligibleVoters()->count();
        $resultParticipation = $resultVoterCount > 0
            ? round(($resultTotalVotes / $resultVoterCount) * 100)
            : 0;
        $landingBanner = Election::where('is_published', true)->whereNotNull('banner_path')->orderBy('start_time', 'desc')->first();
        $settings = [
            'website_name' => SiteSetting::getValue('website_name', 'PILKETOS'),
            'school_name' => SiteSetting::getValue('school_name', 'SMKN 1 Bangsri'),
            'email' => SiteSetting::getValue('email', ''),
            'whatsapp' => SiteSetting::getValue('whatsapp', ''),
            'address' => SiteSetting::getValue('address', ''),
            'footer_text' => SiteSetting::getValue('footer_text', 'PILKETOS'),
            'copyright_text' => SiteSetting::getValue('copyright_text', 'All rights reserved.'),
            'footer_year' => SiteSetting::getValue('footer_year', date('Y')),
            'election_active' => SiteSetting::getValue('election_active', '1'),
            'show_statistics' => SiteSetting::getValue('show_statistics', '1'),
            'show_finished' => SiteSetting::getValue('show_finished', '1'),
        ];

        return view('home', compact(
            'elections',
            'landingBanner',
            'settings',
            'resultElection',
            'resultCandidates',
            'resultTotalVotes',
            'resultVoterCount',
            'resultParticipation',
            'showVoteCounts',
            'resultsPublishAt'
        ));
    }

    public function showElection(Election $election)
    {
        abort_unless($election->is_published, 404);
        $election->load(['candidates', 'votes']);

        $user = auth()->user();
        $hasVoted = false;
        if ($user) {
            $hasVoted = Vote::query()->where('election_id', $election->id)->where('user_id', $user->id)->exists();
        }

        return view('public.election-detail', compact('election', 'hasVoted'));
    }

    public function showCandidate(Candidate $candidate)
    {
        $candidate->load('election');
        abort_unless($candidate->election?->is_published, 404);

        $user = auth()->user();
        $hasVoted = false;
        if ($user && $candidate->election) {
            $hasVoted = Vote::query()->where('election_id', $candidate->election->id)->where('user_id', $user->id)->exists();
        }

        return view('public.candidate-detail', compact('candidate', 'hasVoted'));
    }

    public function results()
    {
        $eligibleVoterIds = User::query()->eligibleVoters()->pluck('id');
        $elections = Election::with(['candidates' => fn ($query) => $query->withCount(['votes' => fn ($voteQuery) => $voteQuery->whereIn('user_id', $eligibleVoterIds)])])
            ->where('is_published', true)
            ->withCount(['votes' => fn ($query) => $query->whereIn('user_id', $eligibleVoterIds)])
            ->orderBy('start_time', 'desc')
            ->get();
        $voterCount = User::query()->eligibleVoters()->count();
        $showVoteCounts = $elections->first()?->publicResultsVisible() ?? false;
        $resultsPublishAt = $elections->first()?->results_publish_at;

        return view('public.results', compact('elections', 'voterCount', 'showVoteCounts', 'resultsPublishAt'));
    }

    public function publicResultsData()
    {
        $eligibleVoterIds = User::query()->eligibleVoters()->pluck('id');
        $election = Election::query()
            ->where('is_published', true)
            ->with(['candidates' => fn ($query) => $query->withCount(['votes' => fn ($voteQuery) => $voteQuery->whereIn('user_id', $eligibleVoterIds)])])
            ->orderBy('start_time', 'desc')
            ->first();
        $totalVotes = $election?->candidates->sum('votes_count') ?? 0;
        $voterCount = $eligibleVoterIds->count();
        $visible = $election?->publicResultsVisible() ?? false;

        return response()->json([
            'visible' => $visible,
            'total_votes' => $visible ? $totalVotes : 0,
            'voter_count' => $voterCount,
            'participation' => $visible && $voterCount > 0 ? round(($totalVotes / $voterCount) * 100) : 0,
            'candidates' => $election?->candidates->map(fn ($candidate) => [
                'id' => $candidate->id,
                'votes' => $visible ? $candidate->votes_count : 0,
                'percent' => $visible && $totalVotes > 0 ? round(($candidate->votes_count / $totalVotes) * 100) : 0,
            ])->values() ?? [],
        ]);
    }

    private function showVoteCounts(): bool
    {
        $publishAt = SiteSetting::getValue('results_publish_at', '');

        if (SiteSetting::getValue('show_vote_counts_public', '1') !== '1') {
            return false;
        }

        return blank($publishAt) || now()->greaterThanOrEqualTo(Carbon::parse($publishAt));
    }

    public function sipintuData()
    {
        $students = User::query()->where('role', 'siswa')->orderBy('name')->get();
        $teachers = User::query()->where('role', 'guru')->orderBy('name')->get();

        $serviceConfig = [
            'api_url' => config('services.sipintu.api_url', env('SIPINTU_API_URL')),
            'base_url' => config('services.sipintu.base_url', env('SIPINTU_BASE_URL')),
            'redirect_uri' => config('services.sipintu.redirect_uri', env('SIPINTU_REDIRECT_URI')),
            'client_id' => config('services.sipintu.client_id', env('SIPINTU_CLIENT_ID')),
            'client_secret' => config('services.sipintu.client_secret', env('SIPINTU_CLIENT_SECRET')) ? '********' : null,
        ];

        return view('sipintu-data', compact('students', 'teachers', 'serviceConfig'));
    }

    public function syncSipintuData()
    {
        try {
            $result = app(SiPintuGatewayService::class)->syncAllUsersFromGateway();
            $isSuccess = (bool) ($result['success'] ?? false);
            $message = $result['message'] ?? ($isSuccess ? 'Sinkronisasi selesai.' : 'Sinkronisasi gagal.');

            if ($isSuccess) {
                return redirect()->route('sipintu.data')->with('success', $message);
            }

            return redirect()->route('sipintu.data')->with('error', $message);
        } catch (\Throwable $e) {
            return redirect()->route('sipintu.data')->with('error', 'Sinkronisasi SiPintu gagal: '.$e->getMessage());
        }
    }
}
