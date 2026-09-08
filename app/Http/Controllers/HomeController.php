<?php

namespace App\Http\Controllers;

use App\Models\AboutPage;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\GuideItem;
use App\Models\SiteSetting;
use App\Models\User;

class HomeController extends Controller
{
    public function index()
    {
        $showFinished = SiteSetting::getValue('show_finished', '1') === '1';
        $elections = Election::with(['candidates' => fn ($query) => $query->withCount('votes')])
            ->when(! $showFinished, fn ($query) => $query->where('end_time', '>=', now()))
            ->orderBy('start_time', 'desc')
            ->get();
        $resultElection = $elections->first();
        $resultCandidates = $resultElection?->candidates ?? collect();
        $resultTotalVotes = $resultCandidates->sum('votes_count');
        $resultVoterCount = User::query()->eligibleVoters()->count();
        $resultParticipation = $resultVoterCount > 0
            ? round(($resultTotalVotes / $resultVoterCount) * 100)
            : 0;
        $landingBanner = Election::whereNotNull('banner_path')->orderBy('start_time', 'desc')->first();
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
            'resultParticipation'
        ));
    }

    public function showElection(Election $election)
    {
        $election->load(['candidates', 'votes']);

        $user = auth()->user();
        $hasVoted = false;
        if ($user) {
            $hasVoted = \App\Models\Vote::query()->where('election_id', $election->id)->where('user_id', $user->id)->exists();
        }

        return view('public.election-detail', compact('election', 'hasVoted'));
    }

    public function showCandidate(Candidate $candidate)
    {
        $candidate->load('election');

        $user = auth()->user();
        $hasVoted = false;
        if ($user && $candidate->election) {
            $hasVoted = \App\Models\Vote::query()->where('election_id', $candidate->election->id)->where('user_id', $user->id)->exists();
        }

        return view('public.candidate-detail', compact('candidate', 'hasVoted'));
    }

    public function results()
    {
        $elections = Election::with(['candidates' => fn ($query) => $query->withCount('votes')])
            ->withCount('votes')
            ->orderBy('start_time', 'desc')
            ->get();
        $voterCount = User::query()->eligibleVoters()->count();

        return view('public.results', compact('elections', 'voterCount'));
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
            $result = app(\App\Services\SiPintuGatewayService::class)->syncAllUsersFromGateway();
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