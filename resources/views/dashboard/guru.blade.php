@extends('layouts.app')

@section('title', 'Dashboard Guru - PILKETOS')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @include('components.voter-dashboard', [
            'user' => $user,
            'roleLabel' => 'Guru Aktif',
            'summaryTitle' => 'Selamat Datang',
            'hasVoted' => $hasVoted,
            'election' => $election,
            'history' => $history,
            'stats' => [
                'elections' => $stats['elections'] ?? 0,
            ],
        ])
    </div>
@endsection
