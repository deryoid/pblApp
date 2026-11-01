<?php

namespace Database\Seeders;

use App\Models\RubrikKategori;
use App\Models\RubrikSkala;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RubrikSkalaSeeder extends Seeder
{
    /**
     * Run the database seeds based on the provided Rubric Excel.
     */
    public function run(): void
    {
        $categories = [
            'kesesuaian_matkul' => [
                'nama_kategori' => 'Relevansi & Konsep',
                'bobot' => 25.00,
                'deskripsi' => 'Kesesuaian dan akurasi konsep proyek terhadap capaian pembelajaran dan tujuan proyek',
                'urutan' => 1,
                'descriptors' => [
                    'A' => [
                        'rentang' => '80 – 100',
                        'min' => 80.00,
                        'max' => 100.00,
                        'text' => 'Sangat akurat, menjawab seluruh tujuan proyek tanpa kesalahan konsep.',
                    ],
                    'B+' => [
                        'rentang' => '75 – 79',
                        'min' => 75.00,
                        'max' => 79.99,
                        'text' => 'Akurat, menjawab sebagian besar tujuan proyek dengan baik.',
                    ],
                    'B' => [
                        'rentang' => '70 – 74',
                        'min' => 70.00,
                        'max' => 74.99,
                        'text' => 'Relevan dengan topik, memenuhi syarat minimal tujuan proyek.',
                    ],
                    'C+' => [
                        'rentang' => '65 – 69',
                        'min' => 65.00,
                        'max' => 69.99,
                        'text' => 'Cukup relevan, terdapat sedikit kesalahan minor pada konsep.',
                    ],
                    'C' => [
                        'rentang' => '60 – 64',
                        'min' => 60.00,
                        'max' => 64.99,
                        'text' => 'Kurang relevan, beberapa tujuan utama proyek tidak terpenuhi.',
                    ],
                    'D+' => [
                        'rentang' => '50 – 59',
                        'min' => 50.00,
                        'max' => 59.99,
                        'text' => 'Relevansi rendah, banyak terjadi kesalahan konsep dasar.',
                    ],
                    'D' => [
                        'rentang' => '40 – 49',
                        'min' => 40.00,
                        'max' => 49.99,
                        'text' => 'Tidak relevan, tidak menjawab tantangan atau tujuan proyek.',
                    ],
                    'E' => [
                        'rentang' => '0 – 39',
                        'min' => 0.00,
                        'max' => 39.99,
                        'text' => 'Tidak ada keterkaitan konsep atau tidak mengumpulkan proyek.',
                    ],
                ],
            ],
            'kualitas_fungsi' => [
                'nama_kategori' => 'Kualitas & Fungsi Produk',
                'bobot' => 25.00,
                'deskripsi' => 'Keandalan fungsional produk, kekokohan sistem, serta kedalaman analisis dan data pendukung',
                'urutan' => 2,
                'descriptors' => [
                    'A' => [
                        'rentang' => '80 – 100',
                        'min' => 80.00,
                        'max' => 100.00,
                        'text' => 'Berfungsi sempurna, kokoh, analisis atau argumen sangat mendalam.',
                    ],
                    'B+' => [
                        'rentang' => '75 – 79',
                        'min' => 75.00,
                        'max' => 79.99,
                        'text' => 'Berfungsi baik, hanya memerlukan sedikit penyesuaian minor.',
                    ],
                    'B' => [
                        'rentang' => '70 – 74',
                        'min' => 70.00,
                        'max' => 74.99,
                        'text' => 'Berfungsi dengan baik sesuai standar dasar proyek.',
                    ],
                    'C+' => [
                        'rentang' => '65 – 69',
                        'min' => 65.00,
                        'max' => 69.99,
                        'text' => 'Berfungsi sebagian, terdapat kelemahan pada fungsi atau data pendukung.',
                    ],
                    'C' => [
                        'rentang' => '60 – 64',
                        'min' => 60.00,
                        'max' => 64.99,
                        'text' => 'Fungsi produk kurang optimal, argumen atau analisis masih dangkal.',
                    ],
                    'D+' => [
                        'rentang' => '50 – 59',
                        'min' => 50.00,
                        'max' => 59.99,
                        'text' => 'Produk sering mengalami kegagalan fungsi atau analisis sangat minim.',
                    ],
                    'D' => [
                        'rentang' => '40 – 49',
                        'min' => 40.00,
                        'max' => 49.99,
                        'text' => 'Produk gagal berfungsi total atau tidak menyertakan analisis.',
                    ],
                    'E' => [
                        'rentang' => '0 – 39',
                        'min' => 0.00,
                        'max' => 39.99,
                        'text' => 'Produk tidak berfungsi sama sekali atau tidak diselesaikan.',
                    ],
                ],
            ],
            'inovasi' => [
                'nama_kategori' => 'Kreativitas & Inovasi',
                'bobot' => 25.00,
                'deskripsi' => 'Orisinalitas ide, keunikan gagasan, dan inovasi pemecahan masalah',
                'urutan' => 3,
                'descriptors' => [
                    'A' => [
                        'rentang' => '80 – 100',
                        'min' => 80.00,
                        'max' => 100.00,
                        'text' => 'Ide sangat orisinal, unik, dan memberikan solusi inovatif.',
                    ],
                    'B+' => [
                        'rentang' => '75 – 79',
                        'min' => 75.00,
                        'max' => 79.99,
                        'text' => 'Ide orisinal dengan modifikasi cerdas pada konsep yang ada.',
                    ],
                    'B' => [
                        'rentang' => '70 – 74',
                        'min' => 70.00,
                        'max' => 74.99,
                        'text' => 'Ide tergolong standar tetapi dieksekusi dengan baik.',
                    ],
                    'C+' => [
                        'rentang' => '65 – 69',
                        'min' => 65.00,
                        'max' => 69.99,
                        'text' => 'Ide cenderung meniru contoh yang sudah ada tanpa banyak variasi.',
                    ],
                    'C' => [
                        'rentang' => '60 – 64',
                        'min' => 60.00,
                        'max' => 64.99,
                        'text' => 'Memakai ide yang sudah umum tanpa ada upaya pengembangan.',
                    ],
                    'D+' => [
                        'rentang' => '50 – 59',
                        'min' => 50.00,
                        'max' => 59.99,
                        'text' => 'Hampir seluruhnya meniru karya lain tanpa keaslian ide.',
                    ],
                    'D' => [
                        'rentang' => '40 – 49',
                        'min' => 40.00,
                        'max' => 49.99,
                        'text' => 'Menjiplak secara penuh karya atau produk milik orang lain.',
                    ],
                    'E' => [
                        'rentang' => '0 – 39',
                        'min' => 0.00,
                        'max' => 39.99,
                        'text' => 'Plagiasi penuh atau tidak ada unsur kreativitas.',
                    ],
                ],
            ],
            'estetik' => [
                'nama_kategori' => 'Estetika & Pengemasan',
                'bobot' => 25.00,
                'deskripsi' => 'Kerapian desain, daya tarik visual, presisi, kejelasan penyampaian pesan, dan profesionalisme',
                'urutan' => 4,
                'descriptors' => [
                    'A' => [
                        'rentang' => '80 – 100',
                        'min' => 80.00,
                        'max' => 100.00,
                        'text' => 'Desain sangat menarik, rapi, presisi, dan tampak profesional.',
                    ],
                    'B+' => [
                        'rentang' => '75 – 79',
                        'min' => 75.00,
                        'max' => 79.99,
                        'text' => 'Desain rapi, menarik, dan pesan tersampaikan dengan jelas.',
                    ],
                    'B' => [
                        'rentang' => '70 – 74',
                        'min' => 70.00,
                        'max' => 74.99,
                        'text' => 'Desain cukup rapi, menarik, namun ada detail kecil yang terlewat.',
                    ],
                    'C+' => [
                        'rentang' => '65 – 69',
                        'min' => 65.00,
                        'max' => 69.99,
                        'text' => 'Desain kurang rapi dan penyampaian pesan agak kabur.',
                    ],
                    'C' => [
                        'rentang' => '60 – 64',
                        'min' => 60.00,
                        'max' => 64.99,
                        'text' => 'Desain terkesan asal jadi, kurang memperhatikan kerapian.',
                    ],
                    'D+' => [
                        'rentang' => '50 – 59',
                        'min' => 50.00,
                        'max' => 59.99,
                        'text' => 'Desain tidak terstruktur, tidak rapi, dan sulit dipahami.',
                    ],
                    'D' => [
                        'rentang' => '40 – 49',
                        'min' => 40.00,
                        'max' => 49.99,
                        'text' => 'Tidak memperhatikan estetika sama sekali atau produk tidak selesai.',
                    ],
                    'E' => [
                        'rentang' => '0 – 39',
                        'min' => 0.00,
                        'max' => 39.99,
                        'text' => 'Tidak ada estetika sama sekali atau produk tidak ada.',
                    ],
                ],
            ],
        ];

        foreach ($categories as $kode => $catData) {
            $kategori = RubrikKategori::updateOrCreate(
                ['kode' => $kode],
                [
                    'uuid' => (string) Str::uuid(),
                    'nama_kategori' => $catData['nama_kategori'],
                    'bobot' => $catData['bobot'],
                    'deskripsi' => $catData['deskripsi'],
                    'urutan' => $catData['urutan'],
                    'is_active' => true,
                ]
            );

            $order = 1;
            foreach ($catData['descriptors'] as $huruf => $descData) {
                RubrikSkala::updateOrCreate(
                    [
                        'rubrik_kategori_id' => $kategori->id,
                        'nilai_huruf' => $huruf,
                    ],
                    [
                        'rentang_nilai' => $descData['rentang'],
                        'nilai_min' => $descData['min'],
                        'nilai_max' => $descData['max'],
                        'deskripsi' => $descData['text'],
                        'urutan' => $order++,
                    ]
                );
            }
        }
    }
}
