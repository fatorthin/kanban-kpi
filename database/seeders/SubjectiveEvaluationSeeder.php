<?php

namespace Database\Seeders;

use App\Models\EvalCategory;
use App\Models\EvalCriterion;
use App\Models\EvalIndicator;
use Illuminate\Database\Seeder;

class SubjectiveEvaluationSeeder extends Seeder
{
    public function run(): void
    {
        // Category I: Kompetensi Dasar
        $category = EvalCategory::firstOrCreate(
            ['code' => 'I'],
            ['name' => 'Kompetensi Dasar', 'sort_order' => 1]
        );

        $data = [
            [
                'number' => 1,
                'name'   => 'Rispek',
                'indicators' => [
                    ['letter' => 'a', 'statement' => 'Menerima dan menghargai perbedaan pendapat dan ide dari rekan kerja.'],
                    ['letter' => 'b', 'statement' => 'Berkomunikasi dengan sopan dan profesional dalam semua interaksi kerja.'],
                ],
            ],
            [
                'number' => 2,
                'name'   => 'Antusias',
                'indicators' => [
                    ['letter' => 'a', 'statement' => 'Menyelesaikan tugas dan tanggung jawab dengan integritas dan kejujuran.'],
                    ['letter' => 'b', 'statement' => 'Menjaga kerahasiaan informasi perusahaan dan klien.'],
                ],
            ],
            [
                'number' => 3,
                'name'   => 'Fatanah',
                'indicators' => [
                    ['letter' => 'a', 'statement' => 'Menerapkan pengetahuan dan keterampilan dalam pekerjaan sehari-hari.'],
                    ['letter' => 'b', 'statement' => 'Belajar dari kesalahan dan berusaha untuk terus meningkatkan kemampuan.'],
                ],
            ],
            [
                'number' => 4,
                'name'   => 'Amanah',
                'indicators' => [
                    ['letter' => 'a', 'statement' => 'Menunjukkan semangat dan dedikasi dalam pekerjaan.'],
                    ['letter' => 'b', 'statement' => 'Berinisiatif dan proaktif dalam mengambil tugas dan proyek.'],
                ],
            ],
        ];

        foreach ($data as $cIndex => $critData) {
            $criterion = EvalCriterion::firstOrCreate(
                [
                    'eval_category_id' => $category->id,
                    'number'           => $critData['number'],
                ],
                [
                    'name'       => $critData['name'],
                    'sort_order' => $cIndex + 1,
                ]
            );

            foreach ($critData['indicators'] as $iIndex => $indData) {
                EvalIndicator::firstOrCreate(
                    [
                        'eval_criterion_id' => $criterion->id,
                        'letter'            => $indData['letter'],
                    ],
                    [
                        'statement'  => $indData['statement'],
                        'sort_order' => $iIndex + 1,
                    ]
                );
            }
        }
    }
}
