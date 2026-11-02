<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Top Level (Managers, Direct Asmans, SPI, Lainnya)
        $topLevel = [
            [
                'name' => 'Manager Keuangan dan Pengembangan Bisnis',
                'code' => 'KEU',
                'level' => 'manager',
                'description' => 'Bidang Keuangan dan Pengembangan Bisnis',
            ],
            [
                'name' => 'Manager Operasi dan Pemeliharaan Pembangkit',
                'code' => 'OPS',
                'level' => 'manager',
                'description' => 'Bidang Operasi dan Pemeliharaan Pembangkit',
            ],
            [
                'name' => 'Manager Human Capital dan Kemitraan Tenaga Kerja',
                'code' => 'HC',
                'level' => 'manager',
                'description' => 'Bidang Human Capital dan Kemitraan Tenaga Kerja',
            ],
            [
                'name' => 'Sekretaris Perusahaan',
                'code' => 'SKPR',
                'level' => 'manager',
                'description' => 'Bidang Sekretaris Perusahaan (Sekper)',
            ],
            [
                'name' => 'Kepala Satuan Pengawasan Intern',
                'code' => 'SPI',
                'level' => 'kspi',
                'description' => 'Satuan Pengawasan Internal (SPI)',
            ],
            [
                'name' => 'Assistant Manager Pengadaan',
                'code' => 'PGD',
                'level' => 'asman',
                'description' => 'Unit Pengadaan Barang dan Jasa (Langsung Direktur)',
            ],
            [
                'name' => 'Assistant Manager K3L',
                'code' => 'K3L',
                'level' => 'asman',
                'description' => 'Unit Keselamatan, Kesehatan Kerja & Lingkungan (Langsung Direktur)',
            ],
            [
                'name' => 'Lainnya',
                'code' => 'LAIN',
                'level' => 'other',
                'description' => 'Bidang/Unit Lainnya',
            ],
        ];

        foreach ($topLevel as $dept) {
            Department::updateOrCreate(
                ['code' => $dept['code']],
                [
                    'name'        => $dept['name'],
                    'level'       => $dept['level'],
                    'description' => $dept['description'],
                    'parent_id'   => null,
                    'is_active'   => true,
                ]
            );
        }

        // Cache parent models by code
        $parents = Department::all()->keyBy('code');

        // 2. Level 2: Assistant Managers under Managers
        $asmanDepts = [
            // Under KEU
            [
                'parent_code' => 'KEU',
                'name'        => 'Assistant Manager Kinerja dan Manajemen Risiko',
                'code'        => 'AM_KMR',
                'level'       => 'asman',
                'description' => 'Sub-bidang Kinerja dan Manajemen Risiko',
            ],
            [
                'parent_code' => 'KEU',
                'name'        => 'Assistant Manager Keuangan, Akuntansi dan Pajak',
                'code'        => 'AM_KAP',
                'level'       => 'asman',
                'description' => 'Sub-bidang Keuangan, Akuntansi dan Pajak',
            ],
            [
                'parent_code' => 'KEU',
                'name'        => 'Assistant Manager Revenue dan Billing',
                'code'        => 'AM_RB',
                'level'       => 'asman',
                'description' => 'Sub-bidang Revenue dan Billing',
            ],
            [
                'parent_code' => 'KEU',
                'name'        => 'Assistant Manager Pengembangan Bisnis',
                'code'        => 'AM_PB',
                'level'       => 'asman',
                'description' => 'Sub-bidang Pengembangan Bisnis',
            ],

            // Under OPS
            [
                'parent_code' => 'OPS',
                'name'        => 'Assistant Manager Pembangkit',
                'code'        => 'AM_PBG',
                'level'       => 'asman',
                'description' => 'Sub-bidang Pembangkit',
            ],
            [
                'parent_code' => 'OPS',
                'name'        => 'Assistant Manager Energi Baru Terbarukan',
                'code'        => 'AM_EBT',
                'level'       => 'asman',
                'description' => 'Sub-bidang Energi Baru Terbarukan',
            ],
            [
                'parent_code' => 'OPS',
                'name'        => 'Assistant Manager Infrastruktur Gas',
                'code'        => 'AM_GAS',
                'level'       => 'asman',
                'description' => 'Sub-bidang Infrastruktur Gas',
            ],

            // Under HC
            [
                'parent_code' => 'HC',
                'name'        => 'Assistant Manager Kemitraan Tenaga Kerja',
                'code'        => 'AM_KTK',
                'level'       => 'asman',
                'description' => 'Sub-bidang Kemitraan Tenaga Kerja',
            ],
            [
                'parent_code' => 'HC',
                'name'        => 'Assistant Manager Human Capital dan General Affair',
                'code'        => 'AM_HCGA',
                'level'       => 'asman',
                'description' => 'Sub-bidang Human Capital dan General Affair',
            ],
        ];

        foreach ($asmanDepts as $dept) {
            Department::updateOrCreate(
                ['code' => $dept['code']],
                [
                    'name'        => $dept['name'],
                    'level'       => $dept['level'],
                    'description' => $dept['description'],
                    'parent_id'   => $parents[$dept['parent_code']]->id ?? null,
                    'is_active'   => true,
                ]
            );
        }

        // Re-cache parents to include newly created Asmans
        $parents = Department::all()->keyBy('code');

        // 3. Level 3: Staff / Sub-units
        $staffUnits = [
            // Under AM Kinerja dan Manajemen Risiko (AM_KMR)
            [
                'parent_code' => 'AM_KMR',
                'name'        => 'Kinerja dan Perencanaan Korporat',
                'code'        => 'ST_KPK',
                'level'       => 'staff',
                'description' => 'Unit Kinerja dan Perencanaan Korporat',
            ],
            [
                'parent_code' => 'AM_KMR',
                'name'        => 'Manajemen Risiko dan Kepatuhan',
                'code'        => 'ST_MRK',
                'level'       => 'staff',
                'description' => 'Unit Manajemen Risiko dan Kepatuhan',
            ],

            // Under AM Keuangan, Akuntansi dan Pajak (AM_KAP)
            [
                'parent_code' => 'AM_KAP',
                'name'        => 'Keuangan, Akuntansi dan Pajak',
                'code'        => 'ST_KAP',
                'level'       => 'staff',
                'description' => 'Unit Keuangan, Akuntansi dan Pajak',
            ],

            // Under AM Revenue dan Billing (AM_RB)
            [
                'parent_code' => 'AM_RB',
                'name'        => 'Billing dan Settlement',
                'code'        => 'ST_BS',
                'level'       => 'staff',
                'description' => 'Unit Billing dan Settlement',
            ],

            // Under AM Pengembangan Bisnis (AM_PB)
            [
                'parent_code' => 'AM_PB',
                'name'        => 'Pengembangan Bisnis',
                'code'        => 'ST_PB',
                'level'       => 'staff',
                'description' => 'Unit Pengembangan Bisnis',
            ],

            // Under AM Pembangkit (AM_PBG)
            [
                'parent_code' => 'AM_PBG',
                'name'        => 'Manajemen Proyek Pembangkit',
                'code'        => 'ST_MPP',
                'level'       => 'staff',
                'description' => 'Unit Manajemen Proyek Pembangkit',
            ],
            [
                'parent_code' => 'AM_PBG',
                'name'        => 'Operasi dan Pemeliharaan Pembangkit',
                'code'        => 'ST_OPP',
                'level'       => 'staff',
                'description' => 'Unit Operasi dan Pemeliharaan Pembangkit',
            ],

            // Under AM Energi Baru Terbarukan (AM_EBT)
            [
                'parent_code' => 'AM_EBT',
                'name'        => 'Manajemen Proyek Pembangkit EBT',
                'code'        => 'ST_MPE',
                'level'       => 'staff',
                'description' => 'Unit Manajemen Proyek Pembangkit EBT',
            ],
            [
                'parent_code' => 'AM_EBT',
                'name'        => 'Operasi dan Pemeliharaan Pembangkit EBT',
                'code'        => 'ST_OPE',
                'level'       => 'staff',
                'description' => 'Unit Operasi dan Pemeliharaan Pembangkit EBT',
            ],

            // Under AM Infrastruktur Gas (AM_GAS)
            [
                'parent_code' => 'AM_GAS',
                'name'        => 'Perencanaan Gas',
                'code'        => 'ST_PG',
                'level'       => 'staff',
                'description' => 'Unit Perencanaan Gas',
            ],
            [
                'parent_code' => 'AM_GAS',
                'name'        => 'Operasi dan Pemeliharaan Gas',
                'code'        => 'ST_OPG',
                'level'       => 'staff',
                'description' => 'Unit Operasi dan Pemeliharaan Gas',
            ],

            // Under AM Kemitraan Tenaga Kerja (AM_KTK)
            [
                'parent_code' => 'AM_KTK',
                'name'        => 'Kemitraan Tenaga Kerja',
                'code'        => 'ST_KTK',
                'level'       => 'staff',
                'description' => 'Unit Kemitraan Tenaga Kerja',
            ],

            // Under AM Human Capital dan General Affair (AM_HCGA)
            [
                'parent_code' => 'AM_HCGA',
                'name'        => 'Human Capital',
                'code'        => 'ST_HC',
                'level'       => 'staff',
                'description' => 'Unit Human Capital',
            ],
            [
                'parent_code' => 'AM_HCGA',
                'name'        => 'General Affair dan Infrastruktur IT',
                'code'        => 'ST_GAIT',
                'level'       => 'staff',
                'description' => 'Unit General Affair dan Infrastruktur IT',
            ],

            // Under Sekretaris Perusahaan (SKPR)
            [
                'parent_code' => 'SKPR',
                'name'        => 'Hubungan Masyarakat dan TJSL',
                'code'        => 'ST_HUMAS',
                'level'       => 'staff',
                'description' => 'Unit Hubungan Masyarakat dan Tanggung Jawab Sosial Lingkungan',
            ],
            [
                'parent_code' => 'SKPR',
                'name'        => 'Hukum',
                'code'        => 'ST_HKM',
                'level'       => 'staff',
                'description' => 'Unit Hukum Sekretaris Perusahaan',
            ],

            // Under SPI
            [
                'parent_code' => 'SPI',
                'name'        => 'Operasi dan Manajemen Audit (Auditor SPI)',
                'code'        => 'ST_AUDIT',
                'level'       => 'auditor',
                'description' => 'Unit Operasi dan Manajemen Audit SPI',
            ],

            // Under AM Pengadaan (PGD)
            [
                'parent_code' => 'PGD',
                'name'        => 'Administrasi Kontrak Pengadaan',
                'code'        => 'ST_AKP',
                'level'       => 'staff',
                'description' => 'Unit Administrasi Kontrak Pengadaan',
            ],

            // Under AM K3L (K3L)
            [
                'parent_code' => 'K3L',
                'name'        => 'Keselamatan, Kesehatan Kerja & Lingkungan (K3L)',
                'code'        => 'ST_K3L',
                'level'       => 'staff',
                'description' => 'Unit Keselamatan, Kesehatan Kerja & Lingkungan',
            ],
        ];

        foreach ($staffUnits as $dept) {
            Department::updateOrCreate(
                ['code' => $dept['code']],
                [
                    'name'        => $dept['name'],
                    'level'       => $dept['level'],
                    'description' => $dept['description'],
                    'parent_id'   => $parents[$dept['parent_code']]->id ?? null,
                    'is_active'   => true,
                ]
            );
        }
    }
}
