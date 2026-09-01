<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class VoterImport implements ToCollection, WithHeadingRow
{
    use Importable;

    public array $previewRows = [];
    public array $errors = [];
    public int $added = 0;
    public int $updated = 0;
    public int $totalRows = 0;

    public function __construct(private bool $preview = false)
    {
        $this->preview = $preview;
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $rowNumber => $row) {
            if ($this->isBlankRow($row)) {
                continue;
            }

            $this->totalRows++;
            $rowIndex = $rowNumber + 2;

            try {
                $parsed = $this->parseRow($row, $rowIndex);
            } catch (\Throwable $exception) {
                $this->errors[] = [
                    'row' => $rowIndex,
                    'message' => $exception->getMessage(),
                ];

                continue;
            }

            if ($this->preview) {
                $this->previewRows[] = $parsed;
                continue;
            }

            $this->importRow($parsed);
        }
    }

    public function headingRow(): int
    {
        return 1;
    }

    private function isBlankRow(Collection $row): bool
    {
        return $row->filter(fn ($value) => ! is_null($value) && trim((string) $value) !== '')->isEmpty();
    }

    private function parseRow(Collection $row, int $rowNumber): array
    {
        $normalized = $row->mapWithKeys(fn ($value, $key) => [Str::of($key)
            ->trim()
            ->lower()
            ->replace([' ', '.', '-'], '_')
            ->toString() => trim((string) $value)]);

        $hasNip = $normalized->has('nip') && $normalized->get('nip') !== '';
        $hasNis = $normalized->has('nis') && $normalized->get('nis') !== '';
        $role = $hasNip && ! $hasNis ? 'guru' : 'siswa';

        $requiredFields = $role === 'guru'
            ? ['nip', 'nama', 'tanggal_lahir']
            : ['nis', 'nama', 'tanggal_lahir', 'kelas', 'jurusan'];

        foreach ($requiredFields as $field) {
            if (! array_key_exists($field, $normalized->toArray()) || $normalized->get($field) === '') {
                throw new \RuntimeException(sprintf('Kolom "%s" wajib diisi pada baris %d.', ucfirst(str_replace('_', ' ', $field)), $rowNumber));
            }
        }

        $birthDate = $this->parseDate($normalized->get('tanggal_lahir'), $rowNumber);

        if ($role === 'guru') {
            return [
                'identity_number' => $normalized->get('nip'),
                'name' => $normalized->get('nama'),
                'birth_date' => $birthDate,
                'password' => $birthDate,
                'role' => 'guru',
                'class_group' => null,
                'major' => null,
            ];
        }

        $classGroup = strtoupper($normalized->get('kelas'));
        $classGroup = match ($classGroup) {
            '10', 'X' => '10',
            '11', 'XI' => '11',
            '12', 'XII' => '12',
            default => null,
        };

        if (! in_array($classGroup, ['10', '11', '12'], true)) {
            throw new \RuntimeException(sprintf('Kolom "Kelas" harus berisi X, XI, XII, 10, 11, atau 12 pada baris %d.', $rowNumber));
        }

        $major = strtoupper($normalized->get('jurusan'));
        if (! in_array($major, $this->allowedMajors(), true)) {
            throw new \RuntimeException(sprintf('Kolom "Jurusan" tidak valid pada baris %d. Gunakan salah satu: %s.', $rowNumber, implode(', ', $this->allowedMajors())));
        }

        return [
            'identity_number' => $normalized->get('nis'),
            'name' => $normalized->get('nama'),
            'birth_date' => $birthDate,
            'password' => $birthDate,
            'role' => 'siswa',
            'class_group' => $classGroup,
            'major' => $major,
        ];
    }

    private function parseDate(string $value, int $rowNumber): string
    {
        if (is_numeric($value)) {
            try {
                $timestamp = ExcelDate::excelToTimestamp((float) $value);
                return Carbon::createFromTimestamp($timestamp)->format('Y-m-d');
            } catch (\Throwable) {
                // fall back to regular parsing below
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            throw new \RuntimeException(sprintf('Format tanggal lahir tidak valid pada baris %d.', $rowNumber));
        }
    }

    private function importRow(array $data): void
    {
        try {
            Log::info('ImportRow start', $data);

            $attributes = [
                'identity_number' => $data['identity_number'],
            ];

            $values = [
                'name' => $data['name'],
                'birth_date' => $data['birth_date'],
                // password default = tanggal lahir (Y-m-d), hashed
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
                'class_group' => $data['class_group'],
                'major' => $data['major'],
                'is_active' => true,
            ];

            $model = User::updateOrCreate($attributes, $values);

            if ($model->wasRecentlyCreated) {
                $this->added++;
                Log::info('ImportRow created', ['identity' => $data['identity_number']]);
            } else {
                $this->updated++;
                Log::info('ImportRow updated (upsert)', ['identity' => $data['identity_number']]);
            }
        } catch (\Throwable $e) {
            $this->errors[] = [
                'row' => $data['identity_number'] ?? 'unknown',
                'message' => $e->getMessage(),
            ];
            Log::error('ImportRow error', ['identity' => $data['identity_number'] ?? null, 'error' => $e->getMessage()]);
        }
    }

    private function allowedMajors(): array
    {
        return [
            'PPLG 1',
            'PPLG 2',
            'PM 1',
            'PM 2',
            'TO 1',
            'TO 2',
            'MPLB 1',
            'MPLB 2',
            'MPLB 3',
            'AKL 1',
            'AKL 2',
        ];
    }
}
