<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\VoterImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function index()
    {
        return view('admin.imports.index');
    }

    public function store(Request $request)
    {
        if ($request->boolean('confirm_import')) {
            $request->validate([
                'temp_path' => ['required', 'string'],
            ]);

            $tempPath = $request->input('temp_path');
            if (! Storage::disk('local')->exists($tempPath)) {
                return back()->withErrors(['file' => 'File impor sementara tidak ditemukan. Silakan unggah ulang.']);
            }

            $fullPath = Storage::disk('local')->path($tempPath);
            $import = new VoterImport(false);

            DB::beginTransaction();
            try {
                Excel::import($import, $fullPath);

                if (count($import->errors) > 0) {
                    throw new \RuntimeException('Terdapat beberapa baris yang gagal diproses. Tidak ada data yang disimpan.');
                }

                DB::commit();
                Storage::disk('local')->delete($tempPath);

                return redirect()->route('admin.voters.index')
                    ->with('success', sprintf('Import selesai. Ditambahkan: %d, Diperbarui: %d.', $import->added, $import->updated));
            } catch (\Throwable $exception) {
                DB::rollBack();
                if (Storage::disk('local')->exists($tempPath)) {
                    Storage::disk('local')->delete($tempPath);
                }

                return view('admin.imports.index', [
                    'preview' => true,
                    'previewFileName' => basename($tempPath),
                    'tempPath' => $tempPath,
                    'previewRows' => [],
                    'importErrors' => array_merge([$exception->getMessage()], $import->errors),
                    'summary' => [
                        'added' => $import->added,
                        'updated' => $import->updated,
                        'total' => $import->totalRows,
                    ],
                ])->withErrors(['file' => 'Gagal mengimpor file Excel: ' . $exception->getMessage()]);
            }
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls'],
        ]);

        Storage::disk('local')->makeDirectory('imports');

        $file = $request->file('file');
        $tempPath = $file->store('imports', 'local');
        $fullPath = Storage::disk('local')->path($tempPath);
        $preview = new VoterImport(true);

        try {
            Excel::import($preview, $fullPath);
        } catch (\Throwable $exception) {
            if (Storage::disk('local')->exists($tempPath)) {
                Storage::disk('local')->delete($tempPath);
            }

            return view('admin.imports.index', [
                'preview' => false,
                'previewFileName' => $file->getClientOriginalName(),
                'tempPath' => $tempPath,
                'previewRows' => [],
                'importErrors' => [$exception->getMessage()],
                'summary' => [
                    'added' => 0,
                    'updated' => 0,
                    'total' => 0,
                ],
            ])->withErrors(['file' => 'Tidak dapat membaca file Excel: ' . $exception->getMessage()]);
        }

        return view('admin.imports.index', [
            'preview' => true,
            'previewFileName' => $file->getClientOriginalName(),
            'tempPath' => $tempPath,
            'previewRows' => $preview->previewRows,
            'importErrors' => $preview->errors,
            'summary' => [
                'added' => $preview->added,
                'updated' => $preview->updated,
                'total' => $preview->totalRows,
            ],
        ]);
    }
}
