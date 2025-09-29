<?php

use App\Http\Controllers\Admin\DataKelompokAdminController;
use App\Http\Controllers\Admin\DataMahasiswaAdminController;
use App\Http\Controllers\Admin\EvaluasiController as AdminEval;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\PenilaianEvaluasiController;
use App\Http\Controllers\Admin\PeriodeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login'); // Form login ditampilkan langsung
})->name('login');

Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Debug route for testing validation
Route::get('/debug-validation', function () {
    $evaluasiMaster = \App\Models\EvaluasiMaster::first();
    $periode = \App\Models\Periode::first();
    $kelompok = \App\Models\Kelompok::first();
    $mahasiswa = \App\Models\Mahasiswa::first();
    $projectCard = \App\Models\ProjectCard::first();
    $user = \App\Models\User::first();

    return [
        'evaluasi_master' => $evaluasiMaster ? ['id' => $evaluasiMaster->id] : 'NOT FOUND',
        'periode' => $periode ? ['id' => $periode->id, 'nama' => $periode->nama] : 'NOT FOUND',
        'kelompok' => $kelompok ? ['id' => $kelompok->id, 'nama' => $kelompok->nama] : 'NOT FOUND',
        'mahasiswa' => $mahasiswa ? ['id' => $mahasiswa->id, 'nama' => $mahasiswa->nama] : 'NOT FOUND',
        'project_card' => $projectCard ? ['id' => $projectCard->id, 'title' => $projectCard->title] : 'NOT FOUND',
        'user' => $user ? ['id' => $user->id, 'name' => $user->name] : 'NOT FOUND',
    ];
});

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', fn () => view('admin.index'));

    // User (UUID-based binding)
    Route::get('user', [UserController::class, 'index'])->name('user.index');
    Route::get('user/create', [UserController::class, 'create'])->name('user.create');
    Route::post('user', [UserController::class, 'store'])->name('user.store');
    Route::get('user/{user}/edit', [UserController::class, 'edit'])->name('user.edit');
    Route::put('user/{user}', [UserController::class, 'update'])->name('user.update');
    Route::delete('user/{user}', [UserController::class, 'destroy'])->name('user.destroy');
    Route::post('user/{user}/reset-password', [UserController::class, 'resetPassword'])->name('user.reset-password');

    // Periode
    Route::get('periode', [PeriodeController::class, 'index'])->name('periode.index');
    Route::get('periode/create', [PeriodeController::class, 'create'])->name('periode.create');
    Route::post('periode', [PeriodeController::class, 'store'])->name('periode.store');
    Route::get('periode/{uuid}/edit', [PeriodeController::class, 'edit'])->name('periode.edit');
    Route::put('periode/{uuid}', [PeriodeController::class, 'update'])->name('periode.update');
    Route::delete('periode/{uuid}', [PeriodeController::class, 'destroy'])->name('periode.destroy');

    // Kelas
    Route::get('kelas', [KelasController::class, 'index'])->name('kelas.index');
    Route::get('kelas/create', [KelasController::class, 'create'])->name('kelas.create');
    Route::post('kelas', [KelasController::class, 'store'])->name('kelas.store');
    Route::get('kelas/{uuid}/edit', [KelasController::class, 'edit'])->name('kelas.edit');
    Route::put('kelas/{uuid}', [KelasController::class, 'update'])->name('kelas.update');
    Route::delete('kelas/{uuid}', [KelasController::class, 'destroy'])->name('kelas.destroy');

    // Mahasiswa
    Route::get('mahasiswa/import', [DataMahasiswaAdminController::class, 'importForm'])->name('mahasiswa.import.form');
    Route::post('mahasiswa/import', [DataMahasiswaAdminController::class, 'import'])->name('mahasiswa.import');
    Route::get('mahasiswa/select2', [DataMahasiswaAdminController::class, 'select2'])->name('mahasiswa.select2');
    Route::post('mahasiswa/sync', [DataMahasiswaAdminController::class, 'sync'])->name('mahasiswa.sync');
    Route::get('/admin/mahasiswa/template-csv', function () {
        $csv = "NIM,Nama\n230411100001,Budi Santoso\n230411100002,Siti Aminah\n230411100003,Agus Saputra\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template-mahasiswa.csv"',
        ]);
    })->name('mahasiswa.template.csv');
    Route::resource('mahasiswa', DataMahasiswaAdminController::class);

    // Kelompok
    Route::get('kelompok', [DataKelompokAdminController::class, 'index'])->name('kelompok.index');
    Route::get('kelompok/create', [DataKelompokAdminController::class, 'create'])->name('kelompok.create');
    Route::post('kelompok', [DataKelompokAdminController::class, 'store'])->name('kelompok.store');
    Route::get('kelompok/{uuid}', [DataKelompokAdminController::class, 'show'])->name('kelompok.show');
    Route::get('kelompok/{uuid}/edit', [DataKelompokAdminController::class, 'edit'])->name('kelompok.edit');
    Route::put('kelompok/{uuid}', [DataKelompokAdminController::class, 'update'])->name('kelompok.update');
    Route::delete('kelompok/{uuid}', [DataKelompokAdminController::class, 'destroy'])->name('kelompok.destroy');

    Route::get('/penilaian/{sesi}/rekap', [PenilaianEvaluasiController::class, 'rekap'])->name('penilaian.rekap');
    Route::post('/penilaian/score/save', [PenilaianEvaluasiController::class, 'saveScore'])->name('penilaian.score.save');
    /** =========================
     *  Evaluasi (BERSIH & RAPIH)
     *  ========================= */
    Route::prefix('evaluasi')->name('admin.evaluasi.')->group(function () {

        // Daftar kelompok evaluasi
        Route::get('/', [AdminEval::class, 'index'])->name('index');

        // Nilai final per mahasiswa
        Route::get('nilai-final', [AdminEval::class, 'nilaiFinal'])->name('nilai-final');

        // Detail evaluasi per kelompok (binding by UUID)
        Route::get('kelompok/{kelompok:uuid}', [AdminEval::class, 'showKelompok'])->name('kelompok.show');

        // Jadwal evaluasi dihapus karena tidak ada kolom jadwal, lokasi, status
        // Route::get('kelompok/{kelompok:uuid}/schedule', [AdminEval::class, 'scheduleForm'])->name('schedule.form');
        // Route::patch('{evaluasi}/schedule', [AdminEval::class, 'scheduleSave'])->name('schedule.save');

        // Pengaturan evaluasi
        Route::get('settings', [AdminEval::class, 'settings'])->name('settings');
        Route::post('settings', [AdminEval::class, 'saveSettings'])->name('settings.save');

        // Jadwal massal dihapus
        // Route::get('evaluasi/jadwal-massal', [AdminEval::class, 'scheduleBulkForm'])->name('schedule.bulk.form');
        // Route::post('evaluasi/jadwal-massal', [AdminEval::class, 'scheduleBulk'])->name('schedule.bulk');

        // Aksi mulai/selesai dihapus karena tidak ada status
        // Route::patch('{evaluasi}/start', [AdminEval::class, 'start'])->name('start');
        // Route::patch('{evaluasi}/finish', [AdminEval::class, 'finish'])->name('finish');

        // Project timeline & export
        Route::get('project/{kelompok:uuid}/timeline', [AdminEval::class, 'projectTimeline'])->name('project.timeline');
        Route::get('project/{kelompok:uuid}/export', [AdminEval::class, 'projectExport'])->name('project.export');

        // Board: drag & drop (kolom & kartu)
        Route::post('project/reorder', [AdminEval::class, 'reorderProjectCard'])->name('project.reorder');
        Route::post('lists/reorder', [AdminEval::class, 'reorderProjectLists'])->name('lists.reorder');

        // Quick action: progress & status kartu
        Route::post('project/{card}/progress', [AdminEval::class, 'updateProjectProgress'])->name('project.progress');
        Route::delete('project/{card}', [AdminEval::class, 'destroyProject'])->name('project.destroy');
        Route::post('project/{card}/update', [AdminEval::class, 'updateProject'])->name('project.update');
        Route::post('project/{card}/status', [AdminEval::class, 'updateProjectStatus'])->name('project.status');

        // Penilaian (AJAX)
        Route::post('evaluasi/{evaluasi}/absensi', [AdminEval::class, 'saveAbsensi'])->name('absensi.save');
        Route::post('evaluasi/{evaluasi}/ap', [AdminEval::class, 'saveAP'])->name('ap.save');
        Route::post('evaluasi/{evaluasi}/indikator', [AdminEval::class, 'saveSesiIndikators'])->name('indikator.save');

        // Penilaian per proyek (per card)
        Route::post('project/{card}/grade/dosen', [AdminEval::class, 'saveProjectGradeDosen'])->name('project.grade.dosen');
        Route::post('project/{card}/grade/mitra', [AdminEval::class, 'saveProjectGradeMitra'])->name('project.grade.mitra');
        Route::post('aktivitas/{list}/status', [AdminEval::class, 'updateAktivitasStatus'])->name('aktivitas.status');

        // CRUD Evaluasi Dosen
        Route::get('penilaian-dosen/show-by-project/{project}', [AdminEval::class, 'getPenilaianDosenByProject'])->name('penilaian-dosen.show-by-project');
        Route::post('penilaian-dosen', [AdminEval::class, 'storePenilaianDosen'])->name('penilaian-dosen.store');
        Route::post('penilaian-dosen/batch-store', [AdminEval::class, 'batchStorePenilaianDosen'])->name('penilaian-dosen.batch-store');
        Route::put('penilaian-dosen/{penilaian}', [AdminEval::class, 'updatePenilaianDosen'])->name('penilaian-dosen.update');
        Route::delete('penilaian-dosen/{penilaian}', [AdminEval::class, 'destroyPenilaianDosen'])->name('penilaian-dosen.destroy');

        // CRUD Evaluasi Mitra (AJAX)
        Route::get('penilaian-mitra/show-by-project/{project}', [AdminEval::class, 'getPenilaianMitraByProject'])->name('penilaian-mitra.show-by-project');

    });

});

// Evaluator
Route::prefix('evaluator')->middleware(['auth', 'evaluator'])->group(function () {
    Route::get('/', fn () => view('evaluator.index'));
});

// Mahasiswa
Route::prefix('mahasiswa')->middleware(['auth', 'mahasiswa'])->group(function () {
    Route::get('/', fn () => view('mahasiswa.index'));

    // Kunjungan Mitra
    Route::resource('kunjungan', \App\Http\Controllers\Mahasiswa\KunjunganMitraController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
        ->names('mahasiswa.kunjungan');

    // Board Proyek
    Route::get('proyek', [\App\Http\Controllers\Mahasiswa\ProyekController::class, 'index'])->name('proyek.index');
    Route::post('proyek/reorder', [\App\Http\Controllers\Mahasiswa\ProyekController::class, 'reorder'])->name('proyek.reorder');

    // List Proyek
    Route::post('proyek/lists', [\App\Http\Controllers\Mahasiswa\ProyekListController::class, 'store'])->name('proyek.lists.store');
    Route::put('proyek/lists/{list:uuid}', [\App\Http\Controllers\Mahasiswa\ProyekListController::class, 'update'])->name('proyek.lists.update');
    Route::delete('proyek/lists/{list:uuid}', [\App\Http\Controllers\Mahasiswa\ProyekListController::class, 'destroy'])->name('proyek.lists.destroy');
    Route::post('proyek/lists/reorder', [\App\Http\Controllers\Mahasiswa\ProyekListController::class, 'reorder'])->name('proyek.lists.reorder');

    // Card Proyek
    Route::post('proyek/cards', [\App\Http\Controllers\Mahasiswa\ProyekCardController::class, 'store'])->name('proyek.cards.store');
    Route::put('proyek/cards/{card:uuid}', [\App\Http\Controllers\Mahasiswa\ProyekCardController::class, 'update'])->name('proyek.cards.update');
    Route::delete('proyek/cards/{card:uuid}', [\App\Http\Controllers\Mahasiswa\ProyekCardController::class, 'destroy'])->name('proyek.cards.destroy');

    // Board Aktivitas
    Route::get('aktivitas', [\App\Http\Controllers\Mahasiswa\AktivitasController::class, 'index'])->name('aktivitas.index');
    Route::post('aktivitas/reorder', [\App\Http\Controllers\Mahasiswa\AktivitasController::class, 'reorder'])->name('aktivitas.reorder');

    // List Aktivitas
    Route::post('aktivitas/lists', [\App\Http\Controllers\Mahasiswa\AktivitasListController::class, 'store'])->name('aktivitas.lists.store');
    Route::put('aktivitas/lists/{list:uuid}', [\App\Http\Controllers\Mahasiswa\AktivitasListController::class, 'update'])->name('aktivitas.lists.update');
    Route::delete('aktivitas/lists/{list:uuid}', [\App\Http\Controllers\Mahasiswa\AktivitasListController::class, 'destroy'])->name('aktivitas.lists.destroy');
    Route::post('aktivitas/lists/reorder', [\App\Http\Controllers\Mahasiswa\AktivitasListController::class, 'reorder'])->name('aktivitas.lists.reorder');

    // Card Aktivitas
    Route::post('aktivitas/cards', [\App\Http\Controllers\Mahasiswa\AktivitasCardController::class, 'store'])->name('aktivitas.cards.store');
    Route::put('aktivitas/cards/{card:uuid}', [\App\Http\Controllers\Mahasiswa\AktivitasCardController::class, 'update'])->name('aktivitas.cards.update');
    Route::delete('aktivitas/cards/{card:uuid}', [\App\Http\Controllers\Mahasiswa\AktivitasCardController::class, 'destroy'])->name('aktivitas.cards.destroy');
});

// Profile (semua role)
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});
