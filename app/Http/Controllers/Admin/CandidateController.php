<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CandidateRequest;
use App\Models\Candidate;
use App\Models\Election;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CandidateController extends Controller
{
    public function index(Request $request)
    {
        try {
            $electionId = $request->query('election_id');

            $banners = Election::withCount('candidates')->orderBy('title')->get();

            $query = Candidate::with('election')->orderBy('candidate_number');
            if ($electionId) {
                $query->where('election_id', $electionId);
            }

            $candidates = $query->paginate(12)->withQueryString();

            $selectedBanner = $electionId ? Election::find($electionId) : null;
        } catch (\Exception $e) {
            // If DB is down, avoid throwing and show an empty list with a warning
            logger()->error('Candidate index failed: ' . $e->getMessage());
            $banners = collect([]);
            $selectedBanner = null;

            $paginator = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12, 1, [
                'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
            ]);

            return view('admin.candidates.index', ['candidates' => $paginator, 'banners' => $banners, 'selectedBanner' => $selectedBanner])
                ->with('error', 'Koneksi database gagal. Beberapa data mungkin tidak tersedia.');
        }

        return view('admin.candidates.index', compact('candidates', 'banners', 'selectedBanner'));
    }

    public function create(Request $request)
    {
        try {
            $banners = Election::withCount('candidates')->orderBy('title')->get();
            $selectedBanner = $request->query('election_id') ? Election::find($request->query('election_id')) : null;
        } catch (\Exception $e) {
            logger()->error('Candidate create failed: ' . $e->getMessage());
            $banners = collect([]);
            $selectedBanner = null;
            return redirect()->route('admin.candidates.index')->with('error', 'Koneksi database gagal. Coba lagi nanti.');
        }

        return view('admin.candidates.create', compact('banners', 'selectedBanner'));
    }

    public function store(CandidateRequest $request)
    {
        $data = $request->validated();

        $candidateCount = Candidate::where('election_id', $data['election_id'])->count();
        if ($candidateCount >= 3) {
            return back()->withInput()->withErrors(['election_id' => 'Banner ini sudah memiliki 3 kandidat.']);
        }

        $photoPaths = collect($request->file('photo', []))
            ->map(fn ($photo) => $photo->store('candidates', 'public'))
            ->all();
        $data['photo_path'] = $photoPaths[0] ?? null;
        $data['photo_paths'] = $photoPaths;

        Candidate::create($data);

        return redirect()->route('admin.cards.index', ['selected' => $data['election_id']])
            ->with('success', 'Kandidat berhasil ditambahkan.');
    }

    public function show($id)
    {
        try {
            $candidate = Candidate::with('election')->findOrFail($id);
        } catch (\Exception $e) {
            logger()->error('Candidate show failed: ' . $e->getMessage());
            return redirect()->route('admin.candidates.index')->with('error', 'Gagal mengambil data kandidat.');
        }

        return view('admin.candidates.show', compact('candidate'));
    }

    public function edit($id)
    {
        try {
            $candidate = Candidate::findOrFail($id);
            $banners = Election::withCount('candidates')->orderBy('title')->get();
        } catch (\Exception $e) {
            logger()->error('Candidate edit failed: ' . $e->getMessage());
            return redirect()->route('admin.cards.index')->with('error', 'Gagal mengambil data kandidat.');
        }

        return view('admin.candidates.edit', compact('candidate', 'banners'));
    }

    public function update(CandidateRequest $request, $id)
    {
        $candidate = Candidate::findOrFail($id);
        $data = $request->validated();

        if ((int) $data['election_id'] !== (int) $candidate->election_id) {
            $candidateCount = Candidate::where('election_id', $data['election_id'])->count();
            if ($candidateCount >= 3) {
                return back()->withInput()->withErrors(['election_id' => 'Banner tujuan sudah memiliki 3 kandidat.']);
            }
        }

        $currentPhotoPaths = $candidate->photo_paths ?: array_filter([$candidate->photo_path]);
        $deletePhotoPaths = array_values(array_intersect(
            $request->input('delete_photos', []),
            $currentPhotoPaths
        ));
        if ($deletePhotoPaths) {
            Storage::disk('public')->delete($deletePhotoPaths);
            $currentPhotoPaths = array_values(array_diff($currentPhotoPaths, $deletePhotoPaths));
        }

        if ($request->hasFile('photo')) {
            $newPhotoPaths = collect($request->file('photo', []))
                ->map(fn ($photo) => $photo->store('candidates', 'public'))
                ->all();
            $currentPhotoPaths = array_values(array_merge($currentPhotoPaths, $newPhotoPaths));
        }

        if ($deletePhotoPaths || $request->hasFile('photo')) {
            $data['photo_path'] = $currentPhotoPaths[0] ?? null;
            $data['photo_paths'] = $currentPhotoPaths;
        }

        $candidate->update($data);

        return redirect()->route('admin.cards.index', ['selected' => $candidate->election_id])
            ->with('success', 'Kandidat berhasil diperbarui.');
    }

    public function destroyPhoto(Request $request, Candidate $candidate)
    {
        $photoPaths = collect($candidate->photo_paths ?: [$candidate->photo_path])
            ->filter()
            ->values()
            ->all();
        $photoIndex = $request->integer('photo_index', -1);
        $photoPath = $photoPaths[$photoIndex] ?? null;

        abort_unless($photoPath, 404);

        $remainingPhotoPaths = array_values(array_diff($photoPaths, [$photoPath]));
        Storage::disk('public')->delete($photoPath);
        $candidate->update([
            'photo_path' => $remainingPhotoPaths[0] ?? null,
            'photo_paths' => $remainingPhotoPaths,
        ]);

        return response()->json(['message' => 'Foto kandidat berhasil dihapus.']);
    }

    public function destroy($id)
    {
        $candidate = Candidate::findOrFail($id);
        $electionId = $candidate->election_id;

        Storage::disk('public')->delete($candidate->photo_paths ?: array_filter([$candidate->photo_path]));

        $candidate->delete();

        return redirect()->route('admin.cards.index', ['selected' => $electionId])
            ->with('success', 'Kandidat berhasil dihapus.');
    }
}
