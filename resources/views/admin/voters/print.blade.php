<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kredensial Pemilih - Cetak</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
                background: white;
            }

            .no-print {
                display: none !important;
            }

            .print-container {
                width: 100%;
                max-width: 100%;
                page-break-after: always;
            }

            .print-header {
                text-align: center;
                margin-bottom: 2rem;
                border-bottom: 2px solid #000;
                padding-bottom: 1rem;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 1rem;
            }

            th,
            td {
                border: 1px solid #000;
                padding: 0.75rem;
                text-align: left;
            }

            th {
                background-color: #f3f4f6;
                font-weight: bold;
            }

            tr:nth-child(even) {
                background-color: #f9fafb;
            }
        }

        @page {
            size: A4;
            margin: 1cm;
        }
    </style>
</head>

<body class="bg-slate-50">
    <!-- No-Print Action Buttons -->
    <div class="no-print sticky top-0 z-50 border-b border-slate-200 bg-white p-4 shadow-sm">
        <div class="mx-auto max-w-6xl flex items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Preview Cetak Data Kredensial Pemilih</h2>
                <p class="text-sm text-slate-500">
                    Filter: <span class="font-semibold">{{ $filters[$selectedFilter] ?? 'Semua' }}</span>
                </p>
            </div>
            <div class="flex gap-2">
                <button onclick="window.print()"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    <i class="fa-solid fa-print"></i>
                    Cetak
                </button>
                <a href="{{ route('admin.voters.index', ['filter' => $selectedFilter]) }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    <i class="fa-solid fa-arrow-left"></i>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Print Container -->
    <div class="mx-auto max-w-6xl py-8">
        <div class="print-container rounded-lg border border-slate-200 bg-white p-8 shadow-sm">
            <!-- Print Header -->
            <div class="print-header">
                <h1 class="text-2xl font-bold text-slate-900">DATA KREDENSIAL PEMILIH</h1>
                <p class="mt-2 text-lg font-semibold text-slate-700">Pemilihan Ketua OSIS SMKN 1 Bangsri</p>
                <p class="mt-1 text-sm text-slate-600">
                    @if ($activeElection)
                        Periode: {{ $activeElection->title }}
                    @else
                        Periode: Tidak ada pemilihan aktif
                    @endif
                </p>
                <p class="mt-1 text-xs text-slate-500">
                    Dicetak pada: {{ now()->locale('id')->format('d F Y H:i') }}
                </p>
            </div>

            <!-- Data Summary -->
            <div class="mb-6 rounded-lg bg-blue-50 p-4 text-sm text-blue-900">
                <p class="font-semibold">Total Data: <span class="text-lg">{{ count($accounts) }}</span> pemilih</p>
                <p class="mt-1 text-xs">Filter: {{ $filters[$selectedFilter] ?? 'Semua' }}</p>
            </div>

            <!-- Data Table -->
            @if (count($accounts) > 0)
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-100">
                            <th class="w-12 px-4 py-3 text-center font-bold">No</th>
                            <th class="px-4 py-3 font-bold">NIS / NIP</th>
                            <th class="px-4 py-3 font-bold">Password</th>
                            <th class="px-4 py-3 font-bold">Token Voting</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($accounts as $index => $account)
                            <tr class="{{ $loop->even ? 'bg-white' : 'bg-slate-50' }}">
                                <td class="px-4 py-3 text-center font-semibold">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3 font-mono text-sm">{{ $account['credential'] }}</td>
                                <td class="px-4 py-3 font-mono text-sm">{{ $account['password'] }}</td>
                                <td class="px-4 py-3 font-mono text-sm font-bold">{{ $account['token'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-8 text-center">
                    <i class="fa-solid fa-inbox text-3xl text-slate-400 mb-3 block"></i>
                    <p class="text-slate-600">Tidak ada data untuk filter ini</p>
                </div>
            @endif

            <!-- Footer -->
            <div class="mt-8 border-t border-slate-300 pt-4 text-center text-xs text-slate-500">
                <p>Data ini hanya boleh diberikan kepada pemilih yang berhak.</p>
                <p>Jaga kerahasiaan password dan token voting.</p>
            </div>
        </div>
    </div>

    <!-- No-Print Bottom Action -->
    <div class="no-print border-t border-slate-200 bg-white py-4">
        <div class="mx-auto max-w-6xl flex justify-center gap-2">
            <button onclick="window.print()"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                <i class="fa-solid fa-print"></i>
                Cetak Sekarang
            </button>
            <a href="{{ route('admin.voters.index', ['filter' => $selectedFilter]) }}"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali ke Data Pemilih
            </a>
        </div>
    </div>
</body>

</html>
