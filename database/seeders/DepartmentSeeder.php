<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Keuangan dan Pengembangan Bisnis',            'code' => 'KEU',  'description' => 'Bidang Keuangan dan Pengembangan Bisnis'],
            ['name' => 'Human Capital dan Kemitraan Tenaga Kerja',    'code' => 'HC',   'description' => 'Bidang Human Capital dan Kemitraan Tenaga Kerja'],
            ['name' => 'Operasi dan Pemeliharaan Pembangkit',         'code' => 'OPS',  'description' => 'Bidang Operasi dan Pemeliharaan Pembangkit'],
            ['name' => 'Sekretaris Perusahaan (Sekper)',              'code' => 'SKPR', 'description' => 'Bidang Sekretaris Perusahaan'],
            ['name' => 'Pengadaan',                                   'code' => 'PGD',  'description' => 'Bidang Pengadaan Barang dan Jasa'],
            ['name' => 'K3L',                                         'code' => 'K3L',  'description' => 'Keselamatan, Kesehatan Kerja & Lingkungan'],
            ['name' => 'Satuan Pengawasan Internal',                  'code' => 'SPI',  'description' => 'Unit SPI (Internal)'],
            ['name' => 'Lainnya',                                     'code' => 'LAIN', 'description' => 'Bidang/Unit Lainnya'],
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(['code' => $dept['code']], $dept);
        }
    }
}
