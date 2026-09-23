<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role !== 'alumni' && (bool) auth()->user()->is_active;
    }

    public function rules(): array
    {
        return [
            'election_id' => ['required', 'exists:elections,id'],
            'candidate_id' => ['required', 'exists:candidates,id'],
            'token' => ['required', 'string'],
        ];
    }
}
