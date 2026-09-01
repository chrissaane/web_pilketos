<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BannerRequest;
use App\Models\Election;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        $banners = Election::with(['candidates' => function ($query) {
            $query->orderBy('candidate_number');
        }])->withCount('candidates')->orderBy('start_time', 'desc')->paginate(12);

        $selectedCardId = $request->query('selected');
        $selectedCard = null;

        if ($selectedCardId) {
            $selectedCard = Election::with('candidates')->withCount('candidates')->find($selectedCardId);
        }

        if (! $selectedCard) {
            $selectedCard = $banners->first();
        }

        return view('admin.banners.index', compact('banners', 'selectedCard'));
    }

    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(BannerRequest $request)
    {
        $data = $request->validated();
        $data['start_time'] = $request->input('start_date') . ' ' . $request->input('start_time');
        $data['end_time'] = $request->input('end_date') . ' ' . $request->input('end_time');
        $data['status'] = $this->computeStatus($data['start_time'], $data['end_time']);

        if ($request->hasFile('banner_image')) {
            $data['banner_path'] = $request->file('banner_image')->store('banners', 'public');
        }

        Election::create($data);

        return redirect()->route('admin.cards.index')->with('success', 'Card berhasil ditambahkan.');
    }

    public function show($id)
    {
        $banner = Election::findOrFail($id);

        return view('admin.banners.show', compact('banner'));
    }

    public function edit($id)
    {
        $banner = Election::findOrFail($id);

        return view('admin.banners.edit', compact('banner'));
    }

    public function update(BannerRequest $request, $id)
    {
        $banner = Election::findOrFail($id);
        $data = $request->validated();
        $data['start_time'] = $request->input('start_date') . ' ' . $request->input('start_time');
        $data['end_time'] = $request->input('end_date') . ' ' . $request->input('end_time');
        $data['status'] = $this->computeStatus($data['start_time'], $data['end_time']);

        if ($request->hasFile('banner_image')) {
            if ($banner->banner_path) {
                Storage::disk('public')->delete($banner->banner_path);
            }
            $data['banner_path'] = $request->file('banner_image')->store('banners', 'public');
        }

        $banner->update($data);

        return redirect()->route('admin.cards.index')->with('success', 'Card berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $banner = Election::findOrFail($id);
        if ($banner->banner_path) {
            Storage::disk('public')->delete($banner->banner_path);
        }
        $banner->delete();

        return redirect()->route('admin.cards.index')->with('success', 'Card berhasil dihapus.');
    }

    protected function computeStatus($startTime, $endTime): string
    {
        $start = \Illuminate\Support\Carbon::parse($startTime);
        $end = \Illuminate\Support\Carbon::parse($endTime);

        if (now()->lt($start)) {
            return Election::STATUS_UPCOMING;
        }

        if (now()->between($start, $end)) {
            return Election::STATUS_ACTIVE;
        }

        return Election::STATUS_FINISHED;
    }
}
