<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Kelompok;
use App\Models\Mahasiswa;
use App\Models\Periode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SidebarComponentTest extends TestCase
{
    use RefreshDatabase;

    protected Periode $activePeriode;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activePeriode = Periode::create([
            'periode' => '2026/2027 Ganjil',
            'status_periode' => 'Aktif',
            'keterangan' => 'Semester Gasal PBL',
        ]);
    }

    public function test_admin_sidebar_renders_expected_sections_and_links(): void
    {
        $admin = User::create([
            'nama_user' => 'Admin Utama PBL',
            'username' => 'admin_pbl',
            'email' => 'admin@politala.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'no_hp' => '081234567890',
        ]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('PBL TRKJ');
        $response->assertSee('Politala');
        $response->assertSee('Admin Utama PBL');
        $response->assertSee('Administrator');
        $response->assertSee('Data Akademik');
        $response->assertSee('Data Master');
        $response->assertSee('Bank Data Mahasiswa');
        $response->assertSee('Bank Data Dosen');
        $response->assertSee('Manajemen PBL');
        $response->assertSee('Pengaturan Evaluasi');
        $response->assertSee('Rubrik Penilaian');
        $response->assertSee('Kelompok PBL');
        $response->assertSee('Evaluasi & Nilai', false);
        $response->assertSee('Evaluasi Proyek');
        $response->assertSee('Rekap Nilai');
        $response->assertSee('Riwayat PBL Mahasiswa');
        $response->assertSee('2026/2027 Ganjil');
    }

    public function test_evaluator_sidebar_renders_expected_sections_and_links(): void
    {
        $evaluator = User::create([
            'nama_user' => 'Dosen Penilai PBL',
            'username' => 'dosen_eval',
            'email' => 'evaluator@politala.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'evaluator',
            'no_hp' => '081234567891',
        ]);

        $response = $this->actingAs($evaluator)->get('/evaluator');

        $response->assertStatus(200);
        $response->assertSee('PBL TRKJ');
        $response->assertSee('Dosen Penilai PBL');
        $response->assertSee('Dosen Evaluator');
        $response->assertSee('Data Kelompok');
        $response->assertSee('Kelompok PBL');
        $response->assertSee('Penilaian & Evaluasi', false);
        $response->assertSee('Evaluasi Proyek');
        $response->assertSee('Rubrik Penilaian');
        $response->assertSee('Nilai Akhir');
        $response->assertSee('Riwayat PBL Mahasiswa');
    }

    public function test_mahasiswa_sidebar_renders_expected_sections_and_links(): void
    {
        $kelas = Kelas::create([
            'kelas' => 'TRPL 3A',
            'semester' => 3,
        ]);

        $studentUser = User::create([
            'nama_user' => 'Ahmad Mahasiswa PBL',
            'username' => 'ahmad_pbl',
            'email' => 'ahmad@politala.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'mahasiswa',
            'no_hp' => '081234567892',
        ]);

        $studentMhs = Mahasiswa::create([
            'user_id' => $studentUser->id,
            'nama_mahasiswa' => $studentUser->nama_user,
            'nim' => '230411100099',
            'kelas_id' => $kelas->id,
            'semester' => 3,
            'is_profile_complete' => true,
        ]);

        $kelompok = Kelompok::create([
            'nama_kelompok' => 'Kelompok Smart IoT',
            'periode_id' => $this->activePeriode->id,
        ]);

        $kelompok->mahasiswas()->attach($studentMhs->id, [
            'periode_id' => $this->activePeriode->id,
            'role' => 'Ketua',
        ]);

        $response = $this->actingAs($studentUser)->get('/mahasiswa');

        $response->assertStatus(200);
        $response->assertSee('PBL TRKJ');
        $response->assertSee('Ahmad Mahasiswa PBL');
        $response->assertSee('Mahasiswa');
        $response->assertSee('Kolaborasi');
        $response->assertSee('Kelompok Saya');
        $response->assertSee('Kelompok Smart IoT');
        $response->assertSee('Ketua');
        $response->assertSee('Mitra Dikunjungi');
        $response->assertSee('Proyek & Kegiatan', false);
        $response->assertSee('Papan Proyek PBL');
        $response->assertSee('Aktivitas & Logbook', false);
        $response->assertSee('Capaian Akademik');
        $response->assertSee('Transkrip Nilai');
        $response->assertSee('Riwayat PBL Saya');
        $response->assertSee('Akun');
        $response->assertSee('Biodata & Profil', false);
    }
}
