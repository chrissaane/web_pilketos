<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'year' => [
                'required',
                'digits:4',
                Rule::unique('elections', 'year')->ignore($this->route('card')),
            ],
            'description' => ['nullable', 'string'],
            'banner_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'start_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'end_time' => ['required', 'date_format:H:i'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $start = trim((string) $this->input('start_date')).' '.trim((string) $this->input('start_time'));
        $end = trim((string) $this->input('end_date')).' '.trim((string) $this->input('end_time'));

        if ($this->filled('start_date') && $this->filled('start_time') && $this->filled('end_date') && $this->filled('end_time')) {
            $this->merge([
                'end_time' => $this->input('end_time'),
                'schedule_start' => $start,
                'schedule_end' => $end,
            ]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->filled('schedule_start') || ! $this->filled('schedule_end')) {
                return;
            }

            if (strtotime($this->input('schedule_end')) <= strtotime($this->input('schedule_start'))) {
                $validator->errors()->add('end_time', 'Waktu berakhir harus setelah waktu mulai.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'year.unique' => 'Tahun pemilihan :input sudah terdaftar.',
        ];
    }
}
