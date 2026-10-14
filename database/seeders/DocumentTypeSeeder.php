<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            // PKPT Module
            ['name' => 'PKPT',                    'slug' => 'pkpt',              'module' => 'pkpt',           'sort_order' => 1],
            ['name' => 'Kertas Kerja PKPT',        'slug' => 'kertas-kerja-pkpt', 'module' => 'pkpt',           'sort_order' => 2, 'requires_approval' => false],

            // Assurance Module
            ['name' => 'Surat Tugas Audit',        'slug' => 'surat-tugas',       'module' => 'assurance',      'sort_order' => 1],
            ['name' => 'Program Kerja Audit',      'slug' => 'program-kerja',     'module' => 'assurance',      'sort_order' => 2, 'requires_approval' => false],
            ['name' => 'Slide Entry Meeting',      'slug' => 'slide-entry',       'module' => 'assurance',      'sort_order' => 3, 'requires_approval' => false],
            ['name' => 'Kertas Kerja Audit',       'slug' => 'kertas-kerja-audit','module' => 'assurance',      'sort_order' => 4, 'requires_approval' => false],
            ['name' => 'Laporan Hasil Audit (LHA)','slug' => 'lha',               'module' => 'assurance',      'sort_order' => 5, 'requires_approval' => false],

            // Advisory Module
            ['name' => 'Berita Acara Konsultasi',  'slug' => 'bak',               'module' => 'advisory',       'sort_order' => 1],
            ['name' => 'Laporan Konsultasi',       'slug' => 'lap-konsultasi',    'module' => 'advisory',       'sort_order' => 2, 'requires_approval' => false],

            // Pendamping Eksternal Module
            ['name' => 'LHA Eksternal',            'slug' => 'lha-eksternal',     'module' => 'pendamping',     'sort_order' => 1, 'requires_approval' => false],
            ['name' => 'Laporan Pendamping',       'slug' => 'lap-pendamping',    'module' => 'pendamping',     'sort_order' => 2, 'requires_approval' => false],

            // Quality Assurance (QA) Module
            ['name' => 'Surat Tugas QA',           'slug' => 'st-qa',             'module' => 'qa',             'sort_order' => 1],
            ['name' => 'Program Kerja QA',         'slug' => 'pk-qa',             'module' => 'qa',             'sort_order' => 2, 'requires_approval' => false],
            ['name' => 'Kertas Kerja QA',          'slug' => 'kk-qa',             'module' => 'qa',             'sort_order' => 3, 'requires_approval' => false],
            ['name' => 'Laporan QA',               'slug' => 'lap-qa',            'module' => 'qa',             'sort_order' => 4, 'requires_approval' => false],

            // Monitoring Tindak Lanjut
            ['name' => 'Monitoring Tindak Lanjut', 'slug' => 'monitoring-tl',     'module' => 'tindak-lanjut',  'sort_order' => 1],

            // Reports
            ['name' => 'Laporan Kinerja Bulanan',  'slug' => 'laporan-kinerja',   'module' => 'report',         'sort_order' => 1, 'requires_approval' => false],
        ];

        foreach ($types as $type) {
            DocumentType::updateOrCreate(
                ['slug' => $type['slug']],
                array_merge(['requires_approval' => true, 'is_active' => true], $type)
            );
        }
    }
}
