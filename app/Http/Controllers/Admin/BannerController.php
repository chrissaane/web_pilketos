<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BannerRequest;
use App\Models\Election;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index(Request $request): View
    {
        $banners = Election::with(['candidates' => function ($query) {
            $query->orderBy('candidate_number');
        }])->withCount('candidates')->orderBy('start_time', 'desc')->paginate(12);

        $selectedCardId = $request->query('selected', session('setup_statistics_card_id'));
        $selectedCard = null;

        if ($selectedCardId) {
            $selectedCard = Election::with('candidates')->withCount('candidates')->find($selectedCardId);
        }

        if (! $selectedCard) {
            $selectedCard = $banners->first();
        }

        return view('admin.banners.index', compact('banners', 'selectedCard'));
    }

    public function create(): View
    {
        return view('admin.banners.create');
    }

    public function store(BannerRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['start_time'] = $request->input('start_date').' '.$request->input('start_time');
        $data['end_time'] = $request->input('end_date').' '.$request->input('end_time');
        $data['status'] = $this->computeStatus($data['start_time'], $data['end_time']);
        $data['is_published'] = $request->boolean('publish');

        if ($request->hasFile('banner_image')) {
            $data['banner_path'] = $request->file('banner_image')->store('banners', 'public');
        }

        $card = Election::create($data);

        return redirect()->route('admin.cards.index')
            ->with('success', 'Card berhasil ditambahkan.')
            ->with('setup_statistics_card_id', $card->id);
    }

    public function show(Election $card): View
    {
        $banner = $card;

        return view('admin.banners.show', compact('banner'));
    }

    public function edit(Election $card): View
    {
        $banner = $card;

        return view('admin.banners.edit', compact('banner'));
    }

    public function update(BannerRequest $request, Election $card): RedirectResponse
    {
        $banner = $card;
        $data = $request->validated();
        $data['start_time'] = $request->input('start_date').' '.$request->input('start_time');
        $data['end_time'] = $request->input('end_date').' '.$request->input('end_time');
        $data['status'] = $this->computeStatus($data['start_time'], $data['end_time']);
        $data['is_published'] = $request->boolean('publish');

        if ($request->hasFile('banner_image')) {
            if ($banner->banner_path) {
                Storage::disk('public')->delete($banner->banner_path);
            }
            $data['banner_path'] = $request->file('banner_image')->store('banners', 'public');
        }

        $banner->update($data);

        return redirect()->route('admin.cards.index')->with('success', 'Card berhasil diperbarui.');
    }

    public function destroy(Election $card): RedirectResponse
    {
        $banner = $card;
        if ($banner->banner_path) {
            Storage::disk('public')->delete($banner->banner_path);
        }
        $banner->delete();

        return redirect()->route('admin.cards.index')->with('success', 'Card berhasil dihapus.');
    }

    public function publish(Election $card): RedirectResponse
    {
        $banner = $card;
        $banner->update(['is_published' => true]);

        return redirect()->route('admin.cards.index', ['selected' => $banner->id])
            ->with('success', 'Card berhasil dipublish.');
    }

    protected function computeStatus(string $startTime, string $endTime): string
    {
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);

        if (now()->lt($start)) {
            return Election::STATUS_UPCOMING;
        }

        if (now()->between($start, $end)) {
            return Election::STATUS_ACTIVE;
        }

        return Election::STATUS_FINISHED;
    }
}
