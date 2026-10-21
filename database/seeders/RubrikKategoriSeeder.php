<?php

namespace Database\Seeders;

use App\Models\RubrikIndikator;
use App\Models\RubrikKategori;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RubrikKategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategoriList = [
            [
                'kode' => 'kesesuaian_matkul',
                'nama_kategori' => 'Kesesuaian Matkul',
                'bobot' => 25.00,
                'deskripsi' => 'Kesesuaian luaran proyek dengan capaian pembelajaran mata kuliah semester terkait',
                'urutan' => 1,
                'indikators' => [
                    [
                        'kode' => 'km_cpmk',
                        'nama_indikator' => 'Kesesuaian dengan Capaian Pembelajaran Mata Kuliah',
                        'bobot' => 50.00,
                        'urutan' => 1,
                    ],
                    [
                        'kode' => 'km_penerapan',
                        'nama_indikator' => 'Penerapan Teori dan Praktik Mata Kuliah pada Solusi Proyek',
                        'bobot' => 50.00,
                        'urutan' => 2,
                    ],
                ],
            ],
            [
                'kode' => 'kualitas_fungsi',
                'nama_kategori' => 'Kualitas dan Fungsi',
                'bobot' => 35.00,
                'deskripsi' => 'Kualitas implementasi teknis, stabilitas sistem, dan fungsionalitas fitur produk',
                'urutan' => 2,
                'indikators' => [
                    [
                        'kode' => 'kf_fitur',
                        'nama_indikator' => 'Kelengkapan dan Kebenaran Fungsionalitas Fitur',
                        'bobot' => 50.00,
                        'urutan' => 1,
                    ],
                    [
                        'kode' => 'kf_stabilitas',
                        'nama_indikator' => 'Stabilitas Sistem, Error Handling, dan Performa Teknis',
                        'bobot' => 50.00,
                        'urutan' => 2,
                    ],
                ],
            ],
            [
                'kode' => 'inovasi',
                'nama_kategori' => 'Inovasi',
                'bobot' => 20.00,
                'deskripsi' => 'Tingkat orisinalitas, kreativitas pemecahan masalah, dan kebaruan ide solusi',
                'urutan' => 3,
                'indikators' => [
                    [
                        'kode' => 'in_kebaruan',
                        'nama_indikator' => 'Orisinalitas Ide dan Kebaruan Solusi yang Ditawarkan',
                        'bobot' => 50.00,
                        'urutan' => 1,
                    ],
                    [
                        'kode' => 'in_nilai_tambah',
                        'nama_indikator' => 'Nilai Tambah dan Dampak Nyata bagi Pengguna/Mitra',
                        'bobot' => 50.00,
                        'urutan' => 2,
                    ],
                ],
            ],
            [
                'kode' => 'estetik',
                'nama_kategori' => 'Estetik',
                'bobot' => 20.00,
                'deskripsi' => 'Kerapihan desain antarmuka, konsistensi visual, dan kenyamanan interaksi pengguna (UI/UX)',
                'urutan' => 4,
                'indikators' => [
                    [
                        'kode' => 'es_ui',
                        'nama_indikator' => 'Kerapihan Desain Visual Antarmuka (UI) dan Layout',
                        'bobot' => 50.00,
                        'urutan' => 1,
                    ],
                    [
                        'kode' => 'es_ux',
                        'nama_indikator' => 'Kemudahan Navigasi dan Pengalaman Interaksi Pengguna (UX)',
                        'bobot' => 50.00,
                        'urutan' => 2,
                    ],
                ],
            ],
        ];

        foreach ($kategoriList as $data) {
            $indikators = $data['indikators'];
            unset($data['indikators']);

            $kategori = RubrikKategori::updateOrCreate(
                ['kode' => $data['kode']],
                array_merge($data, ['uuid' => (string) Str::uuid(), 'is_active' => true])
            );

            foreach ($indikators as $ind) {
                RubrikIndikator::updateOrCreate(
                    ['kode' => $ind['kode']],
                    array_merge($ind, [
                        'uuid' => (string) Str::uuid(),
                        'kategori_id' => $kategori->id,
                        'skala_min' => 0.00,
                        'skala_max' => 100.00,
                        'is_active' => true,
                    ])
                );
            }
        }
    }
}
