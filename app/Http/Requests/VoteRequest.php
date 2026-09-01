<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
