<?php

namespace Database\Seeders;

use App\Models\Candidate;
use App\Models\Election;
use Illuminate\Database\Seeder;

class ElectionSeeder extends Seeder
{
    public function run(): void
    {
        $election = Election::updateOrCreate(
            ['year' => '2026'],
            [
                'title' => 'Pemilihan Ketua OSIS 2026',
                'description' => 'Pemilihan Ketua OSIS SMKN 1 Bangsri periode 2026.',
                'start_time' => now()->subDays(2),
                'end_time' => now()->addDays(10),
                'status' => 'Sedang Berlangsung',
            ]
        );

        Candidate::updateOrCreate(
            [
                'election_id' => $election->id,
                'candidate_number' => 1,
            ],
            [
                'name' => 'Aisyah Putri',
                'class' => 'XI',
                'major' => 'PPLG 1',
                'biodata' => 'Calon ketua OSIS yang aktif dalam organisasi sekolah.',
                'vision' => 'Mewujudkan OSIS yang inovatif, komunikatif, dan berprestasi.',
                'mission' => 'Meningkatkan partisipasi siswa dan digitalisasi kegiatan sekolah.',
                'motto' => 'Layanan untuk semua.',
            ]
        );

        Candidate::updateOrCreate(
            [
                'election_id' => $election->id,
                'candidate_number' => 2,
            ],
            [
                'name' => 'Rizky Pratama',
                'class' => 'XI',
                'major' => 'PPLG 2',
                'biodata' => 'Calon ketua OSIS yang fokus pada pengembangan minat dan bakat.',
                'vision' => 'Membangun OSIS yang solid, kreatif, dan peduli lingkungan.',
                'mission' => 'Mengadakan program unggulan berbasis keterampilan dan kebersamaan.',
                'motto' => 'Bersatu untuk sekolah.',
            ]
        );

        Candidate::updateOrCreate(
            [
                'election_id' => $election->id,
                'candidate_number' => 3,
            ],
            [
                'name' => 'Dina Rahma',
                'class' => 'XII',
                'major' => 'AKL 2',
                'biodata' => 'Calon ketua OSIS yang terbiasa memimpin kegiatan ekstrakurikuler.',
                'vision' => 'Menciptakan suasana sekolah yang nyaman dan partisipatif.',
                'mission' => 'Mengoptimalkan aspirasi siswa melalui forum dan program nyata.',
                'motto' => 'Aspirasi berarti perubahan.',
            ]
        );
    }
}
