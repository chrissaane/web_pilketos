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
            if (! $row instanceof Collection) {
                $row = collect($row);
            }

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
        $normalized = $row->mapWithKeys(fn ($value, $key) => [$this->normalizeColumnKey((string) $key) => trim((string) $value)]);

        $identityNumber = $this->firstNonEmpty($normalized, [
            'nis_nip',
            'nisnip',
            'nis_nip_',
            'nomor_nis_nip',
            'nip',
            'nis',
            'identity_number',
        ]);
        $displayIdentityNumber = $identityNumber;
        $name = $this->firstNonEmpty($normalized, ['nama', 'name']);
        $email = $this->firstNonEmpty($normalized, ['email', 'email_address']);
        $phone = $this->firstNonEmpty($normalized, ['no_hp', 'nomor_hp', 'phone', 'telephone', 'telepon']);
        $passwordValue = $this->firstNonEmpty($normalized, ['password', 'kata_sandi']);
        $classValue = $this->firstNonEmpty($normalized, ['tingkat', 'kelas', 'class_group', 'class', 'class_name']);
        $birthDateValue = $this->firstNonEmpty($normalized, ['tanggal_lahir', 'birth_date', 'dob', 'date_of_birth']);

        if ($name === '' || $classValue === '' || $email === '') {
            throw new \RuntimeException(sprintf('Kolom "Nama", "Tingkat", dan "Email" wajib diisi pada baris %d.', $rowNumber));
        }

        $hasLegacyRequiredFields = $this->hasLegacyFormat($normalized);
        $isSimpleImport = ! $hasLegacyRequiredFields;

        if ($isSimpleImport) {
            $isStaff = preg_match('/^(guru|teacher|karyawan|pegawai|staff)$/i', trim($classValue)) === 1;
            $classData = $isStaff
                ? ['class_group' => null, 'major' => null]
                : $this->parseClassValue($classValue, $rowNumber);
            $role = $this->resolveRoleFromSimpleImport($identityNumber, $classData['class_group'], $classValue);
            $identityNumber = $identityNumber !== '' ? $identityNumber : $this->generateImportIdentity();
            $generatedPassword = User::generateLocalPassword($identityNumber, $name);

            return [
                'identity_number' => $identityNumber,
                'display_identity_number' => $displayIdentityNumber !== '' ? $displayIdentityNumber : null,
                'name' => $name,
                'birth_date' => $birthDateValue !== '' ? $this->parseDate($birthDateValue, $rowNumber) : null,
                'password' => $passwordValue !== '' ? $passwordValue : $generatedPassword,
                'role' => $role,
                'class_group' => $role === 'siswa' ? $classData['class_group'] : null,
                'major' => $role === 'siswa' ? $classData['major'] : null,
                'email' => $email !== '' ? $email : null,
                'phone' => $phone !== '' ? $phone : null,
            ];
        }

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

        $generatedPassword = User::generateLocalPassword($identityNumber, $name);
        $birthDate = $this->parseDate($normalized->get('tanggal_lahir'), $rowNumber);

        if ($role === 'guru') {
            return [
                'identity_number' => $normalized->get('nip'),
                'display_identity_number' => $normalized->get('nip'),
                'name' => $normalized->get('nama'),
                'birth_date' => $birthDate,
                'password' => $passwordValue !== '' ? $passwordValue : $generatedPassword,
                'role' => 'guru',
                'class_group' => null,
                'major' => null,
                'email' => $normalized->get('email', ''),
                'phone' => $phone !== '' ? $phone : null,
            ];
        }

        $classData = $this->parseClassValue($normalized->get('kelas'), $rowNumber);

        return [
            'identity_number' => $normalized->get('nis'),
            'display_identity_number' => $normalized->get('nis'),
            'name' => $normalized->get('nama'),
            'birth_date' => $birthDate,
            'password' => $passwordValue !== '' ? $passwordValue : $birthDate,
            'role' => 'siswa',
            'class_group' => $classData['class_group'],
            'major' => $classData['major'],
            'email' => $normalized->get('email', ''),
            'phone' => $phone !== '' ? $phone : null,
        ];
    }

    private function hasLegacyFormat(Collection $normalized): bool
    {
        return $normalized->has('tanggal_lahir')
            || $normalized->has('birth_date')
            || $normalized->has('jurusan')
            || $normalized->has('nis')
            || $normalized->has('nip');
    }

    private function normalizeColumnKey(string $key): string
    {
        return Str::of($key)
            ->trim()
            ->lower()
            ->replace([' ', '.', '-', '/', '(', ')'], '_')
            ->replaceMatches('/_+/', '_')
            ->rtrim('_')
            ->toString();
    }

    private function firstNonEmpty(Collection $normalized, array $keys): string
    {
        foreach ($keys as $key) {
            if ($normalized->has($key) && trim((string) $normalized->get($key)) !== '') {
                return trim((string) $normalized->get($key));
            }
        }

        return '';
    }

    private function resolveRoleFromSimpleImport(string $identityNumber, ?string $classGroup, string $classValue): string
    {
        $classValue = trim((string) $classValue);

        if (preg_match('/^(karyawan|pegawai|staff)$/i', $classValue)) {
            return 'karyawan';
        }

        if ($classValue === '' || preg_match('/^(guru|teacher)$/i', $classValue)) {
            return 'guru';
        }

        if (! blank($classGroup) || preg_match('/^(x|xi|xii|10|11|12)/i', $classValue)) {
            return 'siswa';
        }

        if (preg_match('/^nip/i', $identityNumber) || (ctype_digit($identityNumber) && strlen($identityNumber) >= 10)) {
            return 'guru';
        }

        return 'siswa';
    }

    private function parseClassValue(?string $classValue, int $rowNumber): array
    {
        $raw = trim((string) ($classValue ?? ''));
        if ($raw === '') {
            return ['class_group' => null, 'major' => null];
        }

        $normalized = preg_replace('/\s+/', ' ', strtoupper($raw));
        if ($normalized === null) {
            $normalized = strtoupper($raw);
        }

        if (preg_match('/^(X|XI|XII|10|11|12)(?:\s+(.+))?$/', $normalized, $matches)) {
            $classGroup = match (strtoupper($matches[1])) {
                'X', '10' => '10',
                'XI', '11' => '11',
                'XII', '12' => '12',
                default => null,
            };

            if (! in_array($classGroup, ['10', '11', '12'], true)) {
                throw new \RuntimeException(sprintf('Kolom "Kelas" harus berisi nilai seperti X, XI, XII, atau 10, 11, 12 pada baris %d.', $rowNumber));
            }

            $major = $matches[2] ?? null;
            if ($major !== null && trim($major) !== '') {
                $major = trim($major);
                if (! in_array(strtoupper($major), $this->allowedMajors(), true)) {
                    $major = strtoupper($major);
                }
            }

            return ['class_group' => $classGroup, 'major' => $major ? strtoupper($major) : null];
        }

        throw new \RuntimeException(sprintf('Kolom "Kelas" tidak valid pada baris %d. Gunakan format seperti "XII PPLG 2" atau "12".', $rowNumber));
    }

    private function generateImportIdentity(): string
    {
        do {
            $identity = 'IMPORT-'.strtoupper(Str::random(12));
        } while (User::query()->where('identity_number', $identity)->exists());

        return $identity;
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

            $generatedLocalPassword = $data['password'] ?? User::generateLocalPassword($data['identity_number'] ?? null, $data['name'] ?? null);

            $values = [
                'identity_number' => $data['identity_number'],
                'name' => $data['name'],
                'birth_date' => $data['birth_date'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'login_password' => (string) $generatedLocalPassword,
                'password' => Hash::make($generatedLocalPassword),
                'role' => $data['role'],
                'class_group' => $data['class_group'] ?? null,
                'major' => $data['major'] ?? null,
                'is_active' => true,
            ];

            $model = User::query()->where('identity_number', $data['identity_number'])->first();

            if (! $model && ! empty($data['email'])) {
                $model = User::query()->where('email', $data['email'])->first();
            }

            if ($model) {
                $model->update($values);
            } else {
                $model = User::create($values);
            }

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
