<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PeriodeController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\DataMahasiswaAdminController;

Route::get('/', function () {
    return view('auth.login'); // Form login ditampilkan langsung 
})->name('login');

Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::prefix('admin')->middleware(['auth','admin'])->group(function () {
    Route::get('/', function () {
       return view('admin.index');
    });

    //User Routes
    Route::get('user', [UserController::class, 'index'])->name('user.index');
    Route::get('user/create', [UserController::class, 'create'])->name('user.create');
    Route::post('user', [UserController::class, 'store'])->name('user.store');
    Route::get('user/{id}/edit', [UserController::class, 'edit'])->name('user.edit');
    Route::put('user/{id}', [UserController::class, 'update'])->name('user.update');
    Route::delete('user/{id}', [UserController::class, 'destroy'])->name('user.destroy');
    Route::post('user/{user}/reset-password', [UserController::class, 'resetPassword'])
    ->name('user.reset-password');   

    //Periode Routes
    Route::get('periode', [PeriodeController::class, 'index'])->name('periode.index');
    Route::get('periode/create', [PeriodeController::class, 'create'])->name('periode.create');
    Route::post('periode', [PeriodeController::class, 'store'])->name('periode.store');
    Route::get('periode/{uuid}/edit', [PeriodeController::class, 'edit'])->name('periode.edit');
    Route::put('periode/{uuid}', [PeriodeController::class, 'update'])->name('periode.update');
    Route::delete('periode/{uuid}', [PeriodeController::class, 'destroy'])->name('periode.destroy');

    //Kelas Routes
    Route::get('kelas', [KelasController::class, 'index'])->name('kelas.index');
    Route::get('kelas/create', [KelasController::class, 'create'])->name('kelas.create');
    Route::post('kelas', [KelasController::class, 'store'])->name('kelas.store');
    Route::get('kelas/{uuid}/edit', [KelasController::class, 'edit'])->name('kelas.edit');
    Route::put('kelas/{uuid}', [KelasController::class, 'update'])->name('kelas.update');
    Route::delete('kelas/{uuid}', [KelasController::class, 'destroy'])->name('kelas.destroy');

    //Mahasiswa Routes
    Route::get('mahasiswa/import', [DataMahasiswaAdminController::class, 'importForm'])
    ->name('mahasiswa.import.form');
    Route::post('mahasiswa/import', [DataMahasiswaAdminController::class, 'import'])
    ->name('mahasiswa.import');
    
    Route::post('mahasiswa/sync', [DataMahasiswaAdminController::class, 'sync'])
    ->name('mahasiswa.sync');
    Route::resource('mahasiswa', DataMahasiswaAdminController::class);
});

Route::prefix('evaluator')->middleware(['auth','evaluator'])->group(function () {
    Route::get('/', function () {
       return view('evaluator.index');
    });
});

// Route::prefix('mahasiswa')->middleware(['auth','role:mahasiswa'])->group(function () {
//     Route::get('/', function () {
//        return view('mahasiswa.index');
//     });
// });