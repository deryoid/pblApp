<?php

namespace App\Exports;

use App\Models\Kelompok;
use App\Models\ProjectList;
use App\Models\AktivitasList;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProjectExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    protected $kelompok;
    protected $proyekLists;
    protected $aktivitasLists;

    public function __construct(Kelompok $kelompok, $proyekLists, $aktivitasLists)
    {
        $this->kelompok = $kelompok;
        $this->proyekLists = $proyekLists;
        $this->aktivitasLists = $aktivitasLists;
    }

    public function collection()
    {
        $data = collect();
        
        // Add projects
        foreach ($this->proyekLists as $list) {
            foreach ($list->cards as $card) {
                $data->push([
                    'type' => 'Proyek',
                    'list' => $list->name,
                    'card' => $card
                ]);
            }
        }

        // Add activities
        foreach ($this->aktivitasLists as $list) {
            foreach ($list->cards as $card) {
                $data->push([
                    'type' => 'Aktivitas',
                    'list' => $list->name,
                    'card' => $card
                ]);
            }
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Tipe',
            'List',
            'Judul',
            'Deskripsi',
            'Tanggal Mulai',
            'Deadline',
            'Progress',
            'Status',
            'Dibuat Oleh',
            'Diperbarui Oleh',
        ];
    }

    public function map($row): array
    {
        $card = $row['card'];
        
        return [
            $row['type'],
            $row['list'],
            $card->title,
            $card->description,
            optional($card->tanggal_mulai)->format('d/m/Y'),
            optional($card->due_date)->format('d/m/Y'),
            $card->progress . '%',
            $this->getStatus($card->progress),
            optional($card->createdBy)->nama_user ?? '-',
            optional($card->updatedBy)->nama_user ?? '-',
        ];
    }

    public function title(): string
    {
        return 'Proyek ' . $this->kelompok->nama_kelompok;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function getStatus($progress)
    {
        if ($progress == 100) return 'Selesai';
        if ($progress > 0) return 'In Progress';
        return 'Belum Dimulai';
    }
}