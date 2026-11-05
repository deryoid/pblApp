<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MahasiswaTemplateExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles
{
    /**
     * Define the header columns in the exact requested order.
     */
    public function headings(): array
    {
        return [
            'Angkatan',
            'NIM',
            'Nama Mahasiswa',
            'NIK',
            'JK',
            'Nama Ibu',
            'Agama',
            'Kelas',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Alamat',
            'No. HP',
            'Email Kampus',
        ];
    }

    /**
     * Provide sample row data for guidance.
     */
    public function array(): array
    {
        return [
            [
                '2024',
                'C030324001',
                'Budi Santoso',
                '6301012005040001',
                'L',
                'Siti Aminah',
                'Islam',
                '2A',
                'Pelaihari',
                '2004-05-12',
                'Jl. A. Yani KM 6, Sarang Halang',
                '081234567890',
                'c030324001@politala.ac.id',
            ],
            [
                '2024',
                'C030324002',
                'Ayu Lestari',
                '6301016508040002',
                'P',
                'Nurul Hidayah',
                'Islam',
                '2B',
                'Banjarmasin',
                '2004-08-25',
                'Jl. Gatot Subroto No. 15',
                '081398765432',
                'c030324002@politala.ac.id',
            ],
        ];
    }

    /**
     * Style the Excel sheet header row.
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1E40AF'], // Professional Navy Blue
                ],
            ],
        ];
    }
}
