<?php

namespace App\Exports;

use App\Models\Election;
use App\Models\User;
use App\Models\VotingToken;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VoterCredentialsExport
{
    public function __construct(protected string $filter = 'semua', protected string $major = 'semua')
    {
        $normalizedFilter = strtolower((string) $this->filter);
        $allowed = ['semua', 'guru', 'karyawan', 'siswa', 'x', 'xi', 'xii', 'kelas_10', 'kelas_11', 'kelas_12'];
        $isClassFilter = str_starts_with($normalizedFilter, 'kelas_');

        $this->filter = in_array($normalizedFilter, $allowed, true) || $isClassFilter
            ? $normalizedFilter
            : 'semua';

        $this->major = in_array($this->major, ['semua', 'to_1', 'to_2', 'pplg_1', 'pplg_2', 'tkj_1', 'tkj_2'], true)
            ? $this->major
            : 'semua';
    }

    public function download(): BinaryFileResponse
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'voters-export-');

        if ($tempPath === false) {
            abort(500, 'Tidak dapat membuat file ekspor.');
        }

        $zip = new \ZipArchive;
        $opened = $zip->open($tempPath, \ZipArchive::OVERWRITE | \ZipArchive::CREATE);

        if ($opened !== true) {
            @unlink($tempPath);
            abort(500, 'Tidak dapat menyiapkan arsip Excel.');
        }

        $rows = $this->buildRows();

        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFromString('_rels/.rels', $this->rootRelationshipsXml());
        $zip->addFromString('docProps/app.xml', $this->appPropertiesXml());
        $zip->addFromString('docProps/core.xml', $this->corePropertiesXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelationshipsXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->sheetXml($rows));
        $zip->close();

        return response()->download($tempPath, $this->fileName(), [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function fileName(): string
    {
        $slug = match ($this->filter) {
            'guru' => 'guru',
            'karyawan' => 'karyawan',
            'siswa' => 'siswa',
            'kelas_10', 'x' => 'kelas_10',
            'kelas_11', 'xi' => 'kelas_11',
            'kelas_12', 'xii' => 'kelas_12',
            default => str_starts_with($this->filter, 'kelas_') ? str_replace('_', '-', $this->filter) : 'semua',
        };

        return sprintf('data-pemilih-%s-%s.xlsx', $slug, now()->format('Ymd'));
    }

    private function buildRows(): array
    {
        $activeElection = Election::where('status', Election::STATUS_ACTIVE)->first();

        $query = User::eligibleVoters();
        $classCriteria = $this->parseClassFilter($this->filter);

        if ($this->filter === 'guru') {
            $query->where('role', 'guru');
        } elseif ($this->filter === 'karyawan') {
            $query->where('role', 'karyawan');
        } elseif ($this->filter === 'siswa') {
            $query->where('role', 'siswa');
        } elseif ($classCriteria['class_group'] !== null) {
            $query->where('role', 'siswa')
                ->where('class_group', $classCriteria['class_group']);

            if ($classCriteria['major'] !== null) {
                $query->whereRaw('LOWER(REPLACE(COALESCE(major, ""), " ", "_")) = ?', [$classCriteria['major']]);
            }
        }

        $rows = [['NIS/NIP', 'Email', 'Password', 'Token']];

        $voters = $query->get()->sort(function ($firstVoter, $secondVoter) {
            $firstIdentity = trim((string) ($firstVoter->identity_number ?? ''));
            $secondIdentity = trim((string) ($secondVoter->identity_number ?? ''));

            if ($firstIdentity === '' || $secondIdentity === '') {
                return $firstIdentity === '' ? ($secondIdentity === '' ? 0 : 1) : -1;
            }

            return strnatcasecmp(
                $firstIdentity,
                $secondIdentity
            );
        });

        foreach ($voters as $voter) {
            $token = $this->resolveToken($voter->id, $activeElection);
            $passwordValue = $voter->login_password ?: ($voter->birth_date ? $voter->birth_date->format('Y-m-d') : '-');

            $rows[] = [
                $voter->identity_number ?? '-',
                $voter->email ?? '-',
                $passwordValue,
                $token ?? '-',
            ];
        }

        return $rows;
    }

    private function parseClassFilter(string $filter): array
    {
        if (! str_starts_with($filter, 'kelas_')) {
            return ['class_group' => null, 'major' => null];
        }

        $raw = substr($filter, strlen('kelas_'));
        $parts = array_values(array_filter(explode('_', strtolower($raw)), fn (string $part) => $part !== ''));

        if ($parts === []) {
            return ['class_group' => null, 'major' => null];
        }

        $classGroup = match ($parts[0]) {
            'x', '10' => '10',
            'xi', '11' => '11',
            'xii', '12' => '12',
            default => null,
        };

        $major = count($parts) > 1 ? implode('_', array_slice($parts, 1)) : null;

        return ['class_group' => $classGroup, 'major' => $major];
    }

    private function resolveToken(int $userId, ?Election $activeElection): ?string
    {
        if (! Schema::hasTable('voting_tokens')) {
            return null;
        }

        $query = VotingToken::where('user_id', $userId);

        if ($activeElection) {
            $query->where('election_id', $activeElection->id);
        }

        return $query->value('token');
    }

    private function sheetXml(array $rows): string
    {
        $writer = new \XMLWriter();
        $writer->openMemory();
        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement('worksheet');
        $writer->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $writer->startElement('sheetData');

        foreach ($rows as $rowIndex => $cells) {
            $writer->startElement('row');
            $writer->writeAttribute('r', (string) ($rowIndex + 1));

            foreach ($cells as $columnIndex => $cellValue) {
                $writer->startElement('c');
                $writer->writeAttribute('r', $this->columnName($columnIndex + 1).($rowIndex + 1));
                $writer->writeAttribute('t', 'inlineStr');
                $writer->startElement('is');
                $writer->startElement('t');
                $writer->text((string) $cellValue);
                $writer->endElement();
                $writer->endElement();
                $writer->endElement();
            }

            $writer->endElement();
        }

        $writer->endElement();
        $writer->endElement();

        return $writer->outputMemory();
    }

    private function columnName(int $index): string
    {
        $letters = '';

        while ($index > 0) {
            $index--;
            $letters = chr(65 + ($index % 26)).$letters;
            $index = intdiv($index, 26);
        }

        return $letters;
    }

    private function contentTypesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
  <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
</Types>
XML;
    }

    private function rootRelationshipsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>
XML;
    }

    private function workbookXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Data Pemilih" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>
XML;
    }

    private function workbookRelationshipsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
</Relationships>
XML;
    }

    private function appPropertiesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties"
            xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">
  <Application>Microsoft Excel</Application>
</Properties>
XML;
    }

    private function corePropertiesXml(): string
    {
        $created = now()->toAtomString();

        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties"
                  xmlns:dc="http://purl.org/dc/elements/1.1/"
                  xmlns:dcterms="http://purl.org/dc/terms/"
                  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <dc:title>Data Pemilih</dc:title>
  <dc:creator>Admin</dc:creator>
  <cp:lastModifiedBy>Admin</cp:lastModifiedBy>
  <dcterms:created xsi:type="dcterms:W3CDTF">{$created}</dcterms:created>
  <dcterms:modified xsi:type="dcterms:W3CDTF">{$created}</dcterms:modified>
</cp:coreProperties>
XML;
    }
}
