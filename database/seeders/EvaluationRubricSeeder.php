<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EvaluationRubricGroup;
use App\Models\EvaluationRubricIndicator;
use App\Models\EvaluationSetting;

class EvaluationRubricSeeder extends Seeder
{
    public function run()
    {
        // Root groups
        EvaluationRubricGroup::updateOrCreate(
            ['code'=>'ap'], ['name'=>'Aktivitas Partisipatif','weight'=>100,'parent_code'=>null]
        );

        EvaluationRubricGroup::updateOrCreate(
            ['code'=>'project'], ['name'=>'Hasil Proyek','weight'=>100,'parent_code'=>null]
        );

        // Sub groups of project
        EvaluationRubricGroup::updateOrCreate(
            ['code'=>'project_dosen'], ['name'=>'Dosen','weight'=>80,'parent_code'=>'project']
        );
        EvaluationRubricGroup::updateOrCreate(
            ['code'=>'project_mitra'], ['name'=>'Mitra','weight'=>20,'parent_code'=>'project']
        );

        // AP indicators (2 item, 50/50 bawaan)
        EvaluationRubricIndicator::updateOrCreate(['code'=>'ap_kehadiran'], [
            'group_code'=>'ap','name'=>'Kehadiran','weight'=>50
        ]);
        EvaluationRubricIndicator::updateOrCreate(['code'=>'ap_presentasi'], [
            'group_code'=>'ap','name'=>'Presentator','weight'=>50
        ]);

        // Dosen indicators (mengikuti gambar)
        $dosen = [
            ['d_hasil','Kualitas Hasil Proyek',30],
            ['d_teknis','Tingkat Kompleksitas Teknis',20],
            ['d_user','Kesesuaian dengan Kebutuhan Pengguna',15],
            ['d_efisiensi','Efisiensi Waktu dan Biaya',10],
            ['d_dokpro','Dokumentasi dan Profesionalisme',15],
            ['d_inisiatif','Kemandirian dan Inisiatif',10],
        ];
        foreach ($dosen as [$code,$name,$w]) {
            EvaluationRubricIndicator::updateOrCreate(['code'=>$code], [
                'group_code'=>'project_dosen','name'=>$name,'weight'=>$w
            ]);
        }

        // Mitra indicators (2 item 50/50)
        $mitra = [
            ['m_komunikasi','Komunikasi dan Sikap di Lapangan',50],
            ['m_hasil','Hasil Pekerjaan',50],
        ];
        foreach ($mitra as [$code,$name,$w]) {
            EvaluationRubricIndicator::updateOrCreate(['code'=>$code], [
                'group_code'=>'project_mitra','name'=>$name,'weight'=>$w
            ]);
        }

        // Settings distribusi akhir: Proyek vs AP (boleh ganti)
        EvaluationSetting::set('w_kelompok', 70);  // Hasil Proyek
        EvaluationSetting::set('w_ap', 30);        // Aktivitas Partisipatif
    }
}
