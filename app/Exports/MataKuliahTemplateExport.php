<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MataKuliahTemplateExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles
{
    /**
     * Define the header columns for Mata Kuliah.
     */
    public function headings(): array
    {
        return [
            'Kode MK',
            'Nama Mata Kuliah',
            'Semester',
            'SKS',
            'Dosen Pengampu',
            'Deskripsi',
        ];
    }

    /**
     * Provide sample guidance rows.
     */
    public function array(): array
    {
        return [
            [
                'TRKJ101',
                'Jaringan Komputer Dasar',
                '1',
                '3',
                '198501012010121001; Nurul Hidayati',
                'Pengenalan konsep OSI model, routing dasar, dan arsitektur LAN.',
            ],
            [
                'TRKJ203',
                'Pemrograman Berbasis Objek',
                '2',
                '3',
                'Dr. Ahmad Fauzi',
                'Penerapan OOP, design pattern, dan arsitektur aplikasi modular.',
            ],
            [
                'TRKJ305',
                'Keamanan Jaringan & Kriptografi',
                '3',
                '3',
                '',
                'Analisis celah keamanan, konfigurasi firewall, enkripsi data.',
            ],
        ];
    }

    /**
     * Style the header row.
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF2563EB'], // Primary blue
                ],
            ],
        ];
    }
}
