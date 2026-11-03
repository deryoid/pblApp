<?php

namespace Tests\Feature;

use App\Models\AktivitasCard;
use App\Models\AktivitasList;
use App\Models\EvaluasiDosen;
use App\Models\EvaluasiMaster;
use App\Models\Kelas;
use App\Models\Kelompok;
use App\Models\Mahasiswa;
use App\Models\Periode;
use App\Models\ProjectCard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RiwayatPblTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $evaluator;

    protected User $studentUser;

    protected Mahasiswa $mahasiswa;

    protected Kelompok $kelompok;

    protected Periode $periode;

    protected ProjectCard $projectCard;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Admin
        $this->admin = User::create([
            'nama_user' => 'Admin TRKJ',
            'username' => 'admin_trkj',
            'email' => 'admin_trkj@politala.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'no_hp' => '0811111111',
        ]);

        // 2. Evaluator
        $this->evaluator = User::create([
            'nama_user' => 'Dosen Penilai M.Kom',
            'username' => 'evaluator_pbl',
            'email' => 'evaluator_pbl@politala.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'evaluator',
            'no_hp' => '0822222222',
        ]);

        // 3. Mahasiswa
        $this->studentUser = User::create([
            'nama_user' => 'Ahmad Fajar Maulana',
            'username' => 'C030324100',
            'email' => 'ahmad.fajar@politala.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'mahasiswa',
            'no_hp' => '0833333333',
        ]);

        $kelas = Kelas::create([
            'kelas' => 'TRKJ 4A',
            'semester' => 4,
        ]);

        $this->mahasiswa = Mahasiswa::create([
            'nim' => 'C030324100',
            'nama_mahasiswa' => 'Ahmad Fajar Maulana',
            'user_id' => $this->studentUser->id,
            'kelas_id' => $kelas->id,
            'semester' => 4,
            'is_profile_complete' => true,
        ]);

        // 4. Periode
        $this->periode = Periode::create([
            'periode' => 'Semester Genap 2024/2025',
            'status_periode' => 'Aktif',
            'tgl_mulai' => now()->subMonths(3),
            'tgl_akhir' => now()->addMonths(2),
        ]);

        // 5. Kelompok & Relasi Mahasiswa (sebagai Ketua)
        $this->kelompok = Kelompok::create([
            'nama_kelompok' => 'TRKJ-Network-01',
            'skema_kategori' => 'External',
            'periode_id' => $this->periode->id,
            'semester' => 4,
            'max_members' => 5,
        ]);

        $this->kelompok->mahasiswas()->attach($this->mahasiswa->id, [
            'role' => 'Ketua',
            'periode_id' => $this->periode->id,
        ]);

        // 6. Project Card (Keterangan Dimana dan Apa yang Dikerjakan)
        $this->projectCard = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'title' => 'Sistem Otomasi Monitoring Server Jaringan Kampus',
            'description' => 'Membangun dashboard telemetry berbasis SNMP dan Telegram Bot untuk pemantauan switch core jaringan.',
            'jenis_proyek' => 'external',
            'skema_pbl' => 'External',
            'nama_mitra' => 'Diskominfo Kabupaten Tanah Laut',
            'alamat_mitra' => 'Jl. A. Syairani, Pelaihari, Kab. Tanah Laut',
            'kontak_mitra' => '0512-21234 (Pak Bambang)',
            'progress' => 100,
            'status_proyek' => 'Selesai',
        ]);

        // 7. Evaluasi 4 Pilar (masing-masing 25%)
        $evalMaster = EvaluasiMaster::create([
            'periode_id' => $this->periode->id,
            'kelompok_id' => $this->kelompok->id,
        ]);

        EvaluasiDosen::create([
            'evaluasi_master_id' => $evalMaster->id,
            'kelompok_id' => $this->kelompok->id,
            'mahasiswa_id' => $this->mahasiswa->id,
            'project_card_id' => $this->projectCard->id,
            'periode_id' => $this->periode->id,
            'evaluator_id' => $this->evaluator->id,
            'd_relevansi' => 90,
            'd_kualitas' => 88,
            'd_kreativitas' => 92,
            'd_estetika' => 86,
            'nilai_akhir' => 89.0,
            'catatan' => 'Implementasi bot monitoring bekerja prima dan responsif.',
        ]);

        // 8. Logbook Aktivitas
        $listAktivitas = AktivitasList::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'name' => 'Sprint 1 - Instalasi & Desain Arsitektur',
        ]);

        AktivitasCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_aktivitas_id' => $listAktivitas->id,
            'tanggal_aktivitas' => now()->subWeeks(2),
            'description' => 'Konfigurasi SNMP daemon pada server router dan integrasi database InfluxDB.',
            'created_by' => $this->studentUser->id,
        ]);
    }

    /**
     * Tes mahasiswa dapat melihat riwayat PBL miliknya sendiri secara lengkap (Dimana, Apa yang dikerjakan, 4 Pilar, Kesimpulan).
     */
    public function test_mahasiswa_can_view_own_riwayat_pbl_with_location_tasks_and_conclusion(): void
    {
        $response = $this->actingAs($this->studentUser)->get('/mahasiswa/riwayat');

        $response->assertStatus(200);
        $response->assertSee('Riwayat & Portofolio PBL Saya', false);

        // Keterangan Dimana PBL dilaksanakan
        $response->assertSee('1. Keterangan Tempat PBL (PBL Dimana)', false);
        $response->assertSee('Diskominfo Kabupaten Tanah Laut');
        $response->assertSee('Jl. A. Syairani, Pelaihari, Kab. Tanah Laut');

        // Keterangan Apa yang dikerjakan
        $response->assertSee('2. Keterangan Pekerjaan Proyek (Apa yang Dikerjakan)', false);
        $response->assertSee('Sistem Otomasi Monitoring Server Jaringan Kampus');
        $response->assertSee('Membangun dashboard telemetry berbasis SNMP');
        $response->assertSee('Konfigurasi SNMP daemon pada server router');

        // Evaluasi 4 Pilar (25% masing-masing)
        $response->assertSee('3. Evaluasi Produk Berdasarkan 4 Pilar (Bobot Masing-masing 25%)', false);
        $response->assertSee('Relevansi Konsep');
        $response->assertSee('Kualitas Produk');
        $response->assertSee('Kreativitas');
        $response->assertSee('Estetika');
        $response->assertSee('Bobot 25%');

        // Kesimpulan Riwayat Proyek & Portofolio
        $response->assertSee('Kesimpulan Riwayat Proyek & Portofolio Mahasiswa', false);
        $response->assertSee('Pernyataan Portofolio SKPI:', false);
    }

    /**
     * Tes mahasiswa dapat mengakses lembar cetak resmi riwayat PBL portofolio.
     */
    public function test_mahasiswa_can_render_official_printable_pbl_portfolio(): void
    {
        $response = $this->actingAs($this->studentUser)->get('/mahasiswa/riwayat/cetak');

        $response->assertStatus(200);
        $response->assertSee('LEMBAR PORTOFOLIO & RIWAYAT PROJECT BASED LEARNING (PBL)', false);
        $response->assertSee('POLITEKNIK NEGERI TANAH LAUT');
        $response->assertSee('Diskominfo Kabupaten Tanah Laut');
        $response->assertSee('Sistem Otomasi Monitoring Server Jaringan Kampus');
        $response->assertSee('Dery Yuswanto Jaya, M.Kom');
    }

    /**
     * Tes administrator dapat melihat direktori riwayat PBL seluruh mahasiswa dan membuka dossier mahasiswa.
     */
    public function test_admin_can_access_riwayat_index_and_view_student_dossier(): void
    {
        // 1. Akses index direktori
        $resIndex = $this->actingAs($this->admin)->get('/admin/riwayat');
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Riwayat PBL Per Orang (Mahasiswa)', false);
        $resIndex->assertSee('Ahmad Fajar Maulana');
        $resIndex->assertSee('C030324100');

        // 2. Akses halaman detail mahasiswa
        $resShow = $this->actingAs($this->admin)->get('/admin/riwayat/'.$this->mahasiswa->uuid);
        $resShow->assertStatus(200);
        $resShow->assertSee('Ahmad Fajar Maulana');
        $resShow->assertSee('Diskominfo Kabupaten Tanah Laut');
        $resShow->assertSee('Sistem Otomasi Monitoring Server Jaringan Kampus');

        // 3. Akses cetak dokumen
        $resCetak = $this->actingAs($this->admin)->get('/admin/riwayat/'.$this->mahasiswa->uuid.'/cetak');
        $resCetak->assertStatus(200);
        $resCetak->assertSee('LEMBAR PORTOFOLIO & RIWAYAT PROJECT BASED LEARNING', false);
    }

    /**
     * Tes evaluator / dosen dapat melihat direktori riwayat mahasiswa dan memeriksa detailnya.
     */
    public function test_evaluator_can_access_riwayat_index_and_view_student_dossier(): void
    {
        // 1. Akses index direktori
        $resIndex = $this->actingAs($this->evaluator)->get('/evaluator/riwayat');
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Riwayat PBL Per Orang (Mahasiswa)');
        $resIndex->assertSee('Ahmad Fajar Maulana');

        // 2. Akses detail dossier
        $resShow = $this->actingAs($this->evaluator)->get('/evaluator/riwayat/'.$this->mahasiswa->uuid);
        $resShow->assertStatus(200);
        $resShow->assertSee('Diskominfo Kabupaten Tanah Laut');
        $resShow->assertSee('Sistem Otomasi Monitoring Server Jaringan Kampus');
    }

    /**
     * Tes mahasiswa tidak diizinkan mengakses panel admin riwayat.
     */
    public function test_student_cannot_access_admin_riwayat(): void
    {
        $response = $this->actingAs($this->studentUser)->get('/admin/riwayat');
        $response->assertRedirect();
    }
}
