<?php

namespace Tests\Feature;

use App\Models\AktivitasCard;
use App\Models\AktivitasList;
use App\Models\Dosen;
use App\Models\EvaluasiMaster;
use App\Models\EvaluasiNilaiAP;
use App\Models\Kelompok;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Periode;
use App\Models\ProjectCard;
use App\Models\ProjectList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CumulativeWeeklyActivityEvaluationTest extends TestCase
{
    use RefreshDatabase;

    private Periode $periode;

    private Kelompok $kelompok;

    private Mahasiswa $mahasiswa;

    private User $dosenUserA;

    private User $dosenUserB;

    private User $dosenUserUnauth;

    private MataKuliah $matkulA;

    private MataKuliah $matkulB;

    private ProjectCard $projectCard;

    private AktivitasList $aktivitasList;

    protected function setUp(): void
    {
        parent::setUp();

        $this->periode = Periode::create([
            'periode' => '2025/2026 Ganjil',
            'status_periode' => 'Aktif',
        ]);

        $this->dosenUserA = User::create([
            'nama_user' => 'Dr. Dosen A',
            'name' => 'Dr. Dosen A',
            'username' => '19810101001',
            'email' => 'dosenA@politala.ac.id',
            'password' => bcrypt('password'),
            'role' => 'dosen',
            'no_hp' => '081234567801',
            'is_profile_complete' => true,
        ]);

        Dosen::create([
            'user_id' => $this->dosenUserA->id,
            'nip' => '19810101001',
            'nama_dosen' => 'Dr. Dosen A',
            'kode_dosen' => 'DA',
            'email' => 'dosenA@politala.ac.id',
            'status' => 'Aktif',
        ]);

        $this->dosenUserB = User::create([
            'nama_user' => 'Dr. Dosen B',
            'name' => 'Dr. Dosen B',
            'username' => '19810101002',
            'email' => 'dosenB@politala.ac.id',
            'password' => bcrypt('password'),
            'role' => 'dosen',
            'no_hp' => '081234567802',
            'is_profile_complete' => true,
        ]);

        Dosen::create([
            'user_id' => $this->dosenUserB->id,
            'nip' => '19810101002',
            'nama_dosen' => 'Dr. Dosen B',
            'kode_dosen' => 'DB',
            'email' => 'dosenB@politala.ac.id',
            'status' => 'Aktif',
        ]);

        $this->dosenUserUnauth = User::create([
            'nama_user' => 'Dr. Dosen C (Lain)',
            'name' => 'Dr. Dosen C (Lain)',
            'username' => '19810101003',
            'email' => 'dosenC@politala.ac.id',
            'password' => bcrypt('password'),
            'role' => 'dosen',
            'no_hp' => '081234567803',
            'is_profile_complete' => true,
        ]);

        Dosen::create([
            'user_id' => $this->dosenUserUnauth->id,
            'nip' => '19810101003',
            'nama_dosen' => 'Dr. Dosen C (Lain)',
            'kode_dosen' => 'DC',
            'email' => 'dosenC@politala.ac.id',
            'status' => 'Aktif',
        ]);

        $this->matkulA = MataKuliah::create([
            'kode_matkul' => 'MK-01',
            'nama_matkul' => 'Pemrograman Web Lanjut',
            'sks' => 3,
            'semester' => 3,
            'is_active' => true,
        ]);
        $this->matkulA->dosenPengampu()->attach($this->dosenUserA->id);

        $this->matkulB = MataKuliah::create([
            'kode_matkul' => 'MK-02',
            'nama_matkul' => 'Rekayasa Perangkat Lunak',
            'sks' => 3,
            'semester' => 3,
            'is_active' => true,
        ]);
        $this->matkulB->dosenPengampu()->attach($this->dosenUserB->id);

        $this->kelompok = Kelompok::create([
            'nama_kelompok' => 'Kelompok Alpha',
            'periode_id' => $this->periode->id,
        ]);

        $kelas = \App\Models\Kelas::create([
            'kelas' => 'TRKJ 3A',
            'semester' => 3,
        ]);

        $mhsUser = User::create([
            'nama_user' => 'Budi Mahasiswa',
            'name' => 'Budi Mahasiswa',
            'username' => '21010101',
            'email' => 'budi@mhs.politala.ac.id',
            'password' => bcrypt('password'),
            'role' => 'mahasiswa',
            'no_hp' => '081234567899',
            'is_profile_complete' => true,
        ]);

        $this->mahasiswa = Mahasiswa::create([
            'user_id' => $mhsUser->id,
            'nim' => '21010101',
            'nama_mahasiswa' => 'Budi Mahasiswa',
            'kelas_id' => $kelas->id,
            'is_profile_complete' => true,
        ]);

        $this->kelompok->mahasiswas()->attach($this->mahasiswa->id, [
            'periode_id' => $this->periode->id,
            'role' => 'Ketua',
        ]);

        $projList = ProjectList::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'name' => 'To Do',
        ]);

        $this->projectCard = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $projList->id,
            'title' => 'Sistem Informasi Manajemen Rumah Sakit',
            'created_by' => $mhsUser->id,
            'status' => 'Proses',
        ]);

        // Hubungkan kedua mata kuliah ke proyek ini
        $this->projectCard->mataKuliahs()->attach([$this->matkulA->id, $this->matkulB->id]);

        $this->aktivitasList = AktivitasList::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'name' => 'Minggu 1 - Analisis Kebutuhan',
            'status_evaluasi' => 'Belum Evaluasi',
        ]);

        AktivitasCard::create([
            'list_aktivitas_id' => $this->aktivitasList->id,
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'nama_aktivitas' => 'Wawancara User',
            'tanggal_aktivitas' => now(),
            'created_by' => $mhsUser->id,
        ]);
    }

    public function test_multiple_dosen_can_save_ap_evaluation_for_same_student_without_overwriting(): void
    {
        // 1. Dosen A (Pengampu MK Pemrograman Web) menilai mahasiswa
        $responseA = $this->actingAs($this->dosenUserA)
            ->postJson(route('dosen.evaluasi.nilai-ap.store-per-aktivitas-list'), [
                'nilai_ap' => [
                    [
                        'mahasiswa_id' => $this->mahasiswa->id,
                        'aktivitas_list_id' => $this->aktivitasList->id,
                        'w_ap_kehadiran' => 'Hadir',
                        'tanggal_hadir' => now()->format('Y-m-d'),
                        'w_ap_presentasi' => 80.0,
                        'catatan_presentasi' => 'Penyampaian fitur web cukup jelas',
                    ],
                ],
            ]);

        $responseA->assertOk();
        $responseA->assertJson(['success' => true]);

        // 2. Dosen B (Pengampu MK Rekayasa Perangkat Lunak) menilai mahasiswa yang sama pada minggu yang sama
        $responseB = $this->actingAs($this->dosenUserB)
            ->postJson(route('dosen.evaluasi.nilai-ap.store-per-aktivitas-list'), [
                'nilai_ap' => [
                    [
                        'mahasiswa_id' => $this->mahasiswa->id,
                        'aktivitas_list_id' => $this->aktivitasList->id,
                        'w_ap_kehadiran' => 'Hadir',
                        'tanggal_hadir' => now()->format('Y-m-d'),
                        'w_ap_presentasi' => 90.0,
                        'catatan_presentasi' => 'Desain arsitektur software sangat mantap',
                    ],
                ],
            ]);

        $responseB->assertOk();
        $responseB->assertJson(['success' => true]);

        // 3. Verifikasi di database: ada 2 record terpisah per dosen evaluator
        $records = EvaluasiNilaiAP::where('mahasiswa_id', $this->mahasiswa->id)
            ->where('aktivitas_list_id', $this->aktivitasList->id)
            ->get();

        $this->assertCount(2, $records);

        $evalA = $records->firstWhere('evaluator_id', $this->dosenUserA->id);
        $evalB = $records->firstWhere('evaluator_id', $this->dosenUserB->id);

        $this->assertNotNull($evalA);
        $this->assertNotNull($evalB);

        $this->assertEquals(80.0, (float) $evalA->w_ap_presentasi);
        $this->assertEquals('Penyampaian fitur web cukup jelas', $evalA->catatan_presentasi);
        $this->assertEquals($this->matkulA->id, $evalA->mata_kuliah_id);

        $this->assertEquals(90.0, (float) $evalB->w_ap_presentasi);
        $this->assertEquals('Desain arsitektur software sangat mantap', $evalB->catatan_presentasi);
        $this->assertEquals($this->matkulB->id, $evalB->mata_kuliah_id);

        // 4. Status aktivitas terupdate menjadi 'Sudah Evaluasi'
        $this->aktivitasList->refresh();
        $this->assertEquals('Sudah Evaluasi', $this->aktivitasList->status_evaluasi);
    }

    public function test_get_nilai_ap_by_aktivitas_list_returns_cumulative_presentasi_and_courses_list(): void
    {
        // Simpan evaluasi dari Dosen A dan Dosen B
        $evalMaster = EvaluasiMaster::ensureForKelompok($this->periode->id, $this->kelompok->id, $this->dosenUserA->id);

        EvaluasiNilaiAP::create([
            'periode_id' => $this->periode->id,
            'kelompok_id' => $this->kelompok->id,
            'evaluasi_master_id' => $evalMaster->id,
            'mahasiswa_id' => $this->mahasiswa->id,
            'aktivitas_list_id' => $this->aktivitasList->id,
            'evaluator_id' => $this->dosenUserA->id,
            'mata_kuliah_id' => $this->matkulA->id,
            'w_ap_kehadiran' => 'Hadir',
            'tanggal_hadir' => now()->format('Y-m-d'),
            'w_ap_presentasi' => 80.0,
            'catatan_presentasi' => 'Penyampaian fitur web cukup jelas',
            'status' => 'Submitted',
        ]);

        EvaluasiNilaiAP::create([
            'periode_id' => $this->periode->id,
            'kelompok_id' => $this->kelompok->id,
            'evaluasi_master_id' => $evalMaster->id,
            'mahasiswa_id' => $this->mahasiswa->id,
            'aktivitas_list_id' => $this->aktivitasList->id,
            'evaluator_id' => $this->dosenUserB->id,
            'mata_kuliah_id' => $this->matkulB->id,
            'w_ap_kehadiran' => 'Hadir',
            'tanggal_hadir' => now()->format('Y-m-d'),
            'w_ap_presentasi' => 90.0,
            'catatan_presentasi' => 'Desain arsitektur software sangat mantap',
            'status' => 'Submitted',
        ]);

        // Dosen A mengambil data AP
        $response = $this->actingAs($this->dosenUserA)
            ->getJson(route('dosen.evaluasi.nilai-ap.show-by-aktivitas-list', ['aktivitasList' => $this->aktivitasList->id]));

        $response->assertOk();
        $data = $response->json();

        $this->assertTrue($data['success']);
        $this->assertTrue($data['is_authorized']);
        $this->assertCount(2, $data['related_courses']);

        // Periksa daftar evaluators_summary
        $evaluators = collect($data['evaluators_summary']);
        $dosenAStatus = $evaluators->firstWhere('dosen_id', $this->dosenUserA->id);
        $dosenBStatus = $evaluators->firstWhere('dosen_id', $this->dosenUserB->id);

        $this->assertNotNull($dosenAStatus);
        $this->assertTrue($dosenAStatus['has_evaluated']);
        $this->assertNotNull($dosenBStatus);
        $this->assertTrue($dosenBStatus['has_evaluated']);

        // Periksa data mahasiswa dan nilai kumulatif
        $studentItem = collect($data['nilai_ap'])->firstWhere('mahasiswa_id', $this->mahasiswa->id);
        $this->assertNotNull($studentItem);

        // Kumulatif: rata-rata dari 80 dan 90 = 85.0
        $this->assertEquals(85.0, (float) $studentItem['cumulative_presentasi']);
        $this->assertEquals(2, $studentItem['evaluators_count']);

        // Nilai inputan Dosen A sendiri
        $this->assertEquals(80.0, (float) $studentItem['my_presentasi']);
        $this->assertEquals('Hadir', $studentItem['my_kehadiran']);

        // Rincian kedua dosen tercatat dalam breakdown
        $breakdown = collect($studentItem['evaluations_breakdown']);
        $this->assertCount(2, $breakdown);
        $this->assertTrue($breakdown->contains('evaluator_id', $this->dosenUserA->id));
        $this->assertTrue($breakdown->contains('evaluator_id', $this->dosenUserB->id));
    }

    public function test_unauthorized_dosen_cannot_evaluate_weekly_activity(): void
    {
        // Dosen C tidak mengampu mata kuliah proyek ini
        $response = $this->actingAs($this->dosenUserUnauth)
            ->postJson(route('dosen.evaluasi.nilai-ap.store-per-aktivitas-list'), [
                'nilai_ap' => [
                    [
                        'mahasiswa_id' => $this->mahasiswa->id,
                        'aktivitas_list_id' => $this->aktivitasList->id,
                        'w_ap_kehadiran' => 'Hadir',
                        'tanggal_hadir' => now()->format('Y-m-d'),
                        'w_ap_presentasi' => 75.0,
                    ],
                ],
            ]);

        $response->assertStatus(403);
        $response->assertJson(['success' => false]);
    }

    public function test_cumulative_ap_is_properly_averaged_in_nilai_index(): void
    {
        $evalMaster = EvaluasiMaster::ensureForKelompok($this->periode->id, $this->kelompok->id, $this->dosenUserA->id);

        // Minggu 1: Nilai presentasi Dosen A = 80, Dosen B = 90 (Rata-rata = 85)
        // Kehadiran = Hadir (100). Skor Minggu 1 = (100 * 0.5) + (85 * 0.5) = 92.5
        EvaluasiNilaiAP::create([
            'periode_id' => $this->periode->id,
            'kelompok_id' => $this->kelompok->id,
            'evaluasi_master_id' => $evalMaster->id,
            'mahasiswa_id' => $this->mahasiswa->id,
            'aktivitas_list_id' => $this->aktivitasList->id,
            'evaluator_id' => $this->dosenUserA->id,
            'mata_kuliah_id' => $this->matkulA->id,
            'w_ap_kehadiran' => 'Hadir',
            'w_ap_presentasi' => 80.0,
            'status' => 'Submitted',
        ]);

        EvaluasiNilaiAP::create([
            'periode_id' => $this->periode->id,
            'kelompok_id' => $this->kelompok->id,
            'evaluasi_master_id' => $evalMaster->id,
            'mahasiswa_id' => $this->mahasiswa->id,
            'aktivitas_list_id' => $this->aktivitasList->id,
            'evaluator_id' => $this->dosenUserB->id,
            'mata_kuliah_id' => $this->matkulB->id,
            'w_ap_kehadiran' => 'Hadir',
            'w_ap_presentasi' => 90.0,
            'status' => 'Submitted',
        ]);

        // Akses menu nilai oleh Dosen A
        $response = $this->actingAs($this->dosenUserA)
            ->get(route('dosen.nilai.index', ['periode_id' => $this->periode->id]));

        $response->assertOk();
        $response->assertSee('92.5'); // Nilai rata-rata AP yang dihitung secara kumulatif
    }

    public function test_admin_can_evaluate_weekly_activity_and_add_related_courses(): void
    {
        $adminUser = User::create([
            'nama_user' => 'Super Admin',
            'username' => 'admin_super',
            'email' => 'admin_super@politala.ac.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'no_hp' => '081234567890',
            'is_profile_complete' => true,
        ]);

        $newMatkul = MataKuliah::create([
            'kode_matkul' => 'MK-03',
            'nama_matkul' => 'Kecerdasan Buatan',
            'sks' => 2,
            'semester' => 3,
            'is_active' => true,
        ]);

        // 1. Admin menambahkan mata kuliah terkait ke proyek via update
        $updateResp = $this->actingAs($adminUser)
            ->putJson(route('admin.evaluasi.project.update', ['card' => $this->projectCard->id]), [
                'title' => 'Sistem Informasi Manajemen RS Terpadu',
                'mata_kuliah_ids' => [$this->matkulA->id, $this->matkulB->id, $newMatkul->id],
            ]);

        $updateResp->assertOk();
        $this->projectCard->refresh();
        $this->assertCount(3, $this->projectCard->mataKuliahs);

        // 2. Admin menilai aktivitas mingguan via admin endpoint
        $storeResp = $this->actingAs($adminUser)
            ->postJson(route('admin.evaluasi.nilai-ap.store-per-aktivitas-list'), [
                'nilai_ap' => [
                    [
                        'mahasiswa_id' => $this->mahasiswa->id,
                        'aktivitas_list_id' => $this->aktivitasList->id,
                        'w_ap_kehadiran' => 'Hadir',
                        'tanggal_hadir' => now()->format('Y-m-d'),
                        'w_ap_presentasi' => 95.0,
                        'catatan_presentasi' => 'Evaluasi dari Admin',
                    ],
                ],
            ]);

        $storeResp->assertOk();
        $storeResp->assertJson(['success' => true]);

        $adminEval = EvaluasiNilaiAP::where('mahasiswa_id', $this->mahasiswa->id)
            ->where('evaluator_id', $adminUser->id)
            ->first();

        $this->assertNotNull($adminEval);
        $this->assertEquals(95.0, (float) $adminEval->w_ap_presentasi);
    }

    public function test_evaluasi_show_views_render_responsive_modals_properly(): void
    {
        // 1. Dosen view
        $dosenViewResp = $this->actingAs($this->dosenUserA)
            ->get(route('dosen.evaluasi.showKelompok', ['kelompok' => $this->kelompok]));

        $dosenViewResp->assertOk();
        $dosenViewResp->assertSee('modalEditProyek');
        $dosenViewResp->assertSee('modalProjectCardDetail');
        $dosenViewResp->assertSee('modal-xl');
        $dosenViewResp->assertSee('Nilai AP');

        // 2. Admin view
        $adminUser = User::create([
            'nama_user' => 'Admin View Test',
            'username' => 'admin_view',
            'email' => 'admin_view@politala.ac.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'no_hp' => '081234567888',
            'is_profile_complete' => true,
        ]);

        $adminViewResp = $this->actingAs($adminUser)
            ->get(route('admin.evaluasi.kelompok.show', ['kelompok' => $this->kelompok]));

        $adminViewResp->assertOk();
        $adminViewResp->assertSee('modalAddProyekAdmin');
        $adminViewResp->assertSee('modalEditProyek');
        $adminViewResp->assertSee('modalProjectCardDetail');
        $adminViewResp->assertSee('modal-xl');
    }
}
