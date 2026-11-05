<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DosenTemplateExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles
{
    /**
     * Define the header columns for Bank Data Dosen.
     */
    public function headings(): array
    {
        return [
            'NIP',
            'Nama Dosen',
            'Nomor Telp',
            'Keahlian',
        ];
    }

    /**
     * Provide sample guidance rows.
     */
    public function array(): array
    {
        return [
            [
                '198501012010121001',
                'Dr. Ahmad Fauzi, M.Kom.',
                '081234567890',
                'Rekayasa Perangkat Lunak, Cloud Computing',
            ],
            [
                '199003152018032002',
                'Nurul Hidayati, S.Kom., M.T.',
                '085678901234',
                'Jaringan Komputer, Keamanan Siber, IoT',
            ],
            [
                '',
                'Rahmat Hidayat, M.Kom.',
                '081987654321',
                'Kecerdasan Buatan, Data Science',
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
