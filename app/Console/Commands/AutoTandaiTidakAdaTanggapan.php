<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\KunjunganMitra;

class AutoTandaiTidakAdaTanggapan extends Command
{
    protected $signature = 'kunjungan:auto-tanggapan {--dry-run : Hanya hitung tanpa update}';

    protected $description = 'Tandai "Tidak ada tanggapan" jika >2 hari dari tanggal kunjungan dan status masih Sudah dikunjungi';

    public function handle(): int
    {
        $cutoff = now()->subDays(2)->toDateString();

        $query = KunjunganMitra::where('status_kunjungan', 'Sudah dikunjungi')
            ->whereDate('tanggal_kunjungan', '<=', $cutoff);

        $total = $query->count();

        if ($this->option('dry-run')) {
            $this->info("Dry run: {$total} baris akan diupdate.");
            return self::SUCCESS;
        }

        $updated = $query->update([
            'status_kunjungan' => 'Tidak ada tanggapan',
            'updated_at' => now(),
        ]);

        $this->info("Selesai. {$updated} baris diperbarui.");
        return self::SUCCESS;
    }
}
