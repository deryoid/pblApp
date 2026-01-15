<?php

namespace App\Exports;

use App\Models\ProjectCard;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MitraSelesaiExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    protected $kelompokId;

    protected $periodeId;

    public function __construct($kelompokId = null, $periodeId = null)
    {
        $this->kelompokId = $kelompokId;
        $this->periodeId = $periodeId;
    }

    public function collection()
    {
        $query = ProjectCard::with(['kelompok', 'periode'])
            ->where('status_proyek', 'Selesai')
            ->whereNotNull('nama_mitra');

        if ($this->kelompokId) {
            $query->where('kelompok_id', $this->kelompokId);
        }
        if ($this->periodeId) {
            $query->where('periode_id', $this->periodeId);
        }

        $cards = $query->orderBy('periode_id')
            ->orderBy('kelompok_id')
            ->orderBy('nama_mitra')
            ->get();

        $data = collect();
        $no = 1;

        foreach ($cards as $card) {
            $data->push([
                'No' => $no++,
                'Nama Mitra' => $card->nama_mitra ?? '-',
                'Kontak Mitra' => $card->kontak_mitra ?? '-',
                'Judul Proyek' => $card->title ?? '-',
                'Kelompok' => $card->kelompok?->nama_kelompok ?? '-',
                'Periode' => $card->periode?->periode ?? '-',
                'Skema PBL' => $card->skema_pbl ?? '-',
                'Progress' => ($card->progress ?? 0).'%',
                'Status' => $card->status_proyek ?? '-',
                'Tanggal Selesai' => $card->updated_at?->format('d/m/Y') ?? '-',
            ]);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Mitra',
            'Kontak Mitra',
            'Judul Proyek',
            'Kelompok',
            'Periode',
            'Skema PBL',
            'Progress',
            'Status',
            'Tanggal Selesai',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            'A1:J1' => [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F81BD'],
                ],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            ],
        ];
    }

    public function title(): string
    {
        if ($this->periodeId) {
            $periode = \App\Models\Periode::find($this->periodeId);
            if ($periode) {
                return 'Mitra Selesai - '.$periode->periode;
            }
        }

        if ($this->kelompokId) {
            $kelompok = \App\Models\Kelompok::find($this->kelompokId);
            if ($kelompok) {
                return 'Mitra Selesai - '.$kelompok->nama_kelompok;
            }
        }

        return 'Mitra Proyek Selesai';
    }
}
