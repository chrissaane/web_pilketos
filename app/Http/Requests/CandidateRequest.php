<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Candidate;

class CandidateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        $existingPhotoCount = 0;
        if (! $this->isMethod('post')) {
            $candidate = Candidate::find($this->route('candidate'));
            $existingPhotoCount = count($candidate?->photo_urls ?? []);
        }

        $deletePhotoCount = count(array_unique($this->input('delete_photos', [])));

        $photoRule = $this->isMethod('post')
            ? ['required', 'array', 'min:1', 'max:5']
            : ['nullable', 'array', 'max:' . max(0, 5 - max(0, $existingPhotoCount - $deletePhotoCount))];

        return [
            'election_id' => ['required', 'exists:elections,id'],
            'candidate_number' => [
                'required',
                'integer',
                'between:1,3',
                Rule::unique('candidates')->where(function ($query) {
                    return $query->where('election_id', $this->input('election_id'));
                })->ignore($this->route('candidate')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'photo' => $photoRule,
            'photo.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'delete_photos' => ['nullable', 'array'],
            'delete_photos.*' => ['string'],
            'class' => ['required', 'string', 'max:255'],
            'major' => ['required', 'string', 'max:255'],
            'biodata' => ['nullable', 'string'],
            'vision' => ['required', 'string'],
            'mission' => ['required', 'string'],
            'motto' => ['nullable', 'string'],
            'achievements' => ['nullable', 'string'],
            'organizations' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'candidate_number.between' => 'Nomor urut pasangan calon hanya boleh 01, 02, atau 03.',
            'candidate_number.unique' => 'Nomor urut tersebut sudah digunakan pada periode pemilihan ini.',
        ];
    }
}
