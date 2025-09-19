<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwal tiap menit: Auto update status kunjungan >2 hari menjadi "Tidak ada tanggapan"
Schedule::command('kunjungan:auto-tanggapan')->everyMinute();
