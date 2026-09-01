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
        $allowed = ['semua', 'guru', 'siswa', 'kelas_10', 'kelas_11', 'kelas_12', 'x', 'xi', 'xii'];
        $this->filter = in_array($this->filter, $allowed, true)
            ? $this->filter
            : 'semua';

        $this->major = 'semua';
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
            'siswa' => 'siswa',
            'kelas_10', 'x' => 'kelas_10',
            'kelas_11', 'xi' => 'kelas_11',
            'kelas_12', 'xii' => 'kelas_12',
            default => 'semua',
        };

        return sprintf('data-pemilih-%s-%s.xlsx', $slug, now()->format('Ymd'));
    }

    private function buildRows(): array
    {
        $activeElection = Election::where('status', Election::STATUS_ACTIVE)->first();

        $query = User::where('is_active', true);

        if ($this->filter === 'guru') {
            $query->where('role', 'guru');
        } elseif ($this->filter === 'siswa') {
            $query->where('role', 'siswa');
        } elseif (in_array($this->filter, ['kelas_10', 'kelas_11', 'kelas_12', 'x', 'xi', 'xii'], true)) {
            $query->where('role', 'siswa')
                ->where('class_group', $this->filter === 'x' ? '10' : ($this->filter === 'xi' ? '11' : '12'));
        }

        $rows = [['NIS/NIP', 'Password', 'Token']];

        foreach ($query->get() as $voter) {
            $token = $this->resolveToken($voter->id, $activeElection);

            $rows[] = [
                $voter->identity_number ?? '-',
                $voter->birth_date ? $voter->birth_date->format('Y-m-d') : '-',
                $token ?? '-',
            ];
        }

        return $rows;
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
