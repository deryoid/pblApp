<?php

namespace Tests\Feature;

use App\Models\AktivitasCard;
use App\Models\AktivitasList;
use App\Models\Dosen;
use App\Models\EvaluasiDosen;
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
        $dosenViewResp->assertSee('window.gradeAP = async function');
        $dosenViewResp->assertDontSee('roleText = (result.is_non_course_scheme');

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
        $adminViewResp->assertSee('window.gradeAP = async function');
        $adminViewResp->assertDontSee('roleText = (result.is_non_course_scheme');
    }

    public function test_user_can_select_specific_evaluator_to_store_weekly_ap(): void
    {
        // Admin menilai dengan memilih evaluator Dosen B (user yang bersangkutan)
        $adminUser = User::create([
            'nama_user' => 'Admin Test Evaluator Choice',
            'username' => 'admin_choice',
            'email' => 'admin_choice@politala.ac.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'no_hp' => '081234567877',
            'is_profile_complete' => true,
        ]);

        $response = $this->actingAs($adminUser)
            ->postJson(route('admin.evaluasi.nilai-ap.store-per-aktivitas-list'), [
                'evaluator_id' => $this->dosenUserB->id,
                'mata_kuliah_id' => $this->matkulB->id,
                'nilai_ap' => [
                    [
                        'mahasiswa_id' => $this->mahasiswa->id,
                        'aktivitas_list_id' => $this->aktivitasList->id,
                        'evaluator_id' => $this->dosenUserB->id,
                        'mata_kuliah_id' => $this->matkulB->id,
                        'w_ap_kehadiran' => 'Hadir',
                        'tanggal_hadir' => now()->format('Y-m-d'),
                        'w_ap_presentasi' => 88.0,
                        'catatan_presentasi' => 'Dinilai atas nama Dosen B',
                    ],
                ],
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'evaluator_id' => $this->dosenUserB->id,
        ]);

        $savedRecord = EvaluasiNilaiAP::where('mahasiswa_id', $this->mahasiswa->id)
            ->where('evaluator_id', $this->dosenUserB->id)
            ->first();

        $this->assertNotNull($savedRecord);
        $this->assertEquals(88.0, (float) $savedRecord->w_ap_presentasi);
        $this->assertEquals('Dinilai atas nama Dosen B', $savedRecord->catatan_presentasi);
    }

    public function test_subsequent_evaluators_can_grade_without_collision_and_update_cumulatively(): void
    {
        // 1. Dosen A menilai (skor 80)
        $this->actingAs($this->dosenUserA)
            ->postJson(route('dosen.evaluasi.nilai-ap.store-per-aktivitas-list'), [
                'evaluator_id' => $this->dosenUserA->id,
                'nilai_ap' => [
                    [
                        'mahasiswa_id' => $this->mahasiswa->id,
                        'aktivitas_list_id' => $this->aktivitasList->id,
                        'w_ap_kehadiran' => 'Hadir',
                        'tanggal_hadir' => now()->format('Y-m-d'),
                        'w_ap_presentasi' => 80.0,
                    ],
                ],
            ])
            ->assertOk();

        // 2. Dosen B menilai (skor 90)
        $this->actingAs($this->dosenUserB)
            ->postJson(route('dosen.evaluasi.nilai-ap.store-per-aktivitas-list'), [
                'evaluator_id' => $this->dosenUserB->id,
                'nilai_ap' => [
                    [
                        'mahasiswa_id' => $this->mahasiswa->id,
                        'aktivitas_list_id' => $this->aktivitasList->id,
                        'w_ap_kehadiran' => 'Hadir',
                        'tanggal_hadir' => now()->format('Y-m-d'),
                        'w_ap_presentasi' => 90.0,
                    ],
                ],
            ])
            ->assertOk();

        // 3. Periksa get API: harus ada 2 evaluasi dan rata-rata 85.0
        $getResp = $this->actingAs($this->dosenUserA)
            ->getJson(route('dosen.evaluasi.nilai-ap.show-by-aktivitas-list', ['aktivitasList' => $this->aktivitasList->id]));

        $getResp->assertOk();
        $data = $getResp->json();
        $this->assertCount(2, $data['all_evaluations']);
        $studentItem = collect($data['nilai_ap'])->firstWhere('mahasiswa_id', $this->mahasiswa->id);
        $this->assertEquals(85.0, (float) $studentItem['cumulative_presentasi']);

        // 4. Dosen A memperbarui nilainya menjadi 85.0
        $this->actingAs($this->dosenUserA)
            ->postJson(route('dosen.evaluasi.nilai-ap.store-per-aktivitas-list'), [
                'evaluator_id' => $this->dosenUserA->id,
                'nilai_ap' => [
                    [
                        'mahasiswa_id' => $this->mahasiswa->id,
                        'aktivitas_list_id' => $this->aktivitasList->id,
                        'w_ap_kehadiran' => 'Hadir',
                        'tanggal_hadir' => now()->format('Y-m-d'),
                        'w_ap_presentasi' => 85.0,
                    ],
                ],
            ])
            ->assertOk();

        // 5. Total rekam tetap 2, dan rata-rata sekarang menjadi (85 + 90) / 2 = 87.5
        $totalRecords = EvaluasiNilaiAP::where('mahasiswa_id', $this->mahasiswa->id)
            ->where('aktivitas_list_id', $this->aktivitasList->id)
            ->count();
        $this->assertEquals(2, $totalRecords);

        $getRespAfter = $this->actingAs($this->dosenUserA)
            ->getJson(route('dosen.evaluasi.nilai-ap.show-by-aktivitas-list', ['aktivitasList' => $this->aktivitasList->id]));
        $afterData = $getRespAfter->json();
        $updatedStudent = collect($afterData['nilai_ap'])->firstWhere('mahasiswa_id', $this->mahasiswa->id);
        $this->assertEquals(87.5, (float) $updatedStudent['cumulative_presentasi']);
    }

    public function test_multiple_dosen_can_grade_project_card_without_overwriting(): void
    {
        // 1. Dosen A menilai proyek mahasiswa (skor 80)
        $responseA = $this->actingAs($this->dosenUserA)
            ->postJson(route('dosen.evaluasi.project.grade.dosen', ['card' => $this->projectCard->id]), [
                'evaluator_id' => $this->dosenUserA->id,
                'mata_kuliah_id' => $this->matkulA->id,
                'items' => [
                    $this->mahasiswa->id => [
                        'nilai' => [
                            'd_relevansi' => 80,
                            'd_kualitas' => 80,
                            'd_kreativitas' => 80,
                            'd_estetika' => 80,
                        ],
                    ],
                ],
            ]);

        $responseA->assertOk();
        $responseA->assertJson(['success' => true]);

        // 2. Dosen B menilai proyek mahasiswa yang sama (skor 90)
        $responseB = $this->actingAs($this->dosenUserB)
            ->postJson(route('dosen.evaluasi.project.grade.dosen', ['card' => $this->projectCard->id]), [
                'evaluator_id' => $this->dosenUserB->id,
                'mata_kuliah_id' => $this->matkulB->id,
                'items' => [
                    $this->mahasiswa->id => [
                        'nilai' => [
                            'd_relevansi' => 90,
                            'd_kualitas' => 90,
                            'd_kreativitas' => 90,
                            'd_estetika' => 90,
                        ],
                    ],
                ],
            ]);

        $responseB->assertOk();
        $responseB->assertJson(['success' => true]);

        // 3. Pastikan tersimpan 2 baris terpisah di database (Dosen A dan Dosen B tidak saling menimpa)
        $evals = EvaluasiDosen::where('project_card_id', $this->projectCard->id)
            ->where('mahasiswa_id', $this->mahasiswa->id)
            ->get();

        $this->assertCount(2, $evals);
        $this->assertEquals(1, $evals->where('evaluator_id', $this->dosenUserA->id)->count());
        $this->assertEquals(1, $evals->where('evaluator_id', $this->dosenUserB->id)->count());

        // 4. API getPenilaianDosenByProject mengembalikan kumulatif rata-rata 85.0 dari 2 dosen yang menilai
        $getResp = $this->actingAs($this->dosenUserA)
            ->getJson(route('dosen.evaluasi.penilaian-dosen.show-by-project', ['project' => $this->projectCard->id]));

        $getResp->assertOk();
        $data = $getResp->json();
        $this->assertTrue($data['success']);
        $this->assertCount(2, $data['evaluations']);

        $summary = $data['summary_per_mahasiswa'][$this->mahasiswa->id] ?? null;
        $this->assertNotNull($summary);
        $this->assertEquals(85.0, (float) $summary['cumulative_score']);
        $this->assertEquals(2, $summary['evaluators_count']);
    }

    public function test_unauthorized_dosen_cannot_grade_project_card(): void
    {
        $response = $this->actingAs($this->dosenUserUnauth)
            ->postJson(route('dosen.evaluasi.project.grade.dosen', ['card' => $this->projectCard->id]), [
                'evaluator_id' => $this->dosenUserUnauth->id,
                'items' => [
                    $this->mahasiswa->id => [
                        'nilai' => [
                            'd_relevansi' => 75,
                            'd_kualitas' => 75,
                            'd_kreativitas' => 75,
                            'd_estetika' => 75,
                        ],
                    ],
                ],
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_grade_project_card_for_any_evaluator_and_updates_cumulatively(): void
    {
        $adminUser = User::create([
            'nama_user' => 'Super Admin',
            'name' => 'Super Admin',
            'username' => 'admin_pbl',
            'email' => 'admin_pbl@politala.ac.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'no_hp' => '081234567888',
            'is_profile_complete' => true,
        ]);

        // 1. Admin menilai atas nama Dosen A
        $respAdminA = $this->actingAs($adminUser)
            ->postJson(route('admin.evaluasi.project.grade.dosen', ['card' => $this->projectCard->id]), [
                'evaluator_id' => $this->dosenUserA->id,
                'mata_kuliah_id' => $this->matkulA->id,
                'items' => [
                    $this->mahasiswa->id => [
                        'nilai' => [
                            'd_relevansi' => 84,
                            'd_kualitas' => 84,
                            'd_kreativitas' => 84,
                            'd_estetika' => 84,
                        ],
                    ],
                ],
            ]);

        $respAdminA->assertOk();
        $respAdminA->assertJson(['success' => true]);

        // 2. Admin menilai atas nama Dosen B
        $respAdminB = $this->actingAs($adminUser)
            ->postJson(route('admin.evaluasi.project.grade.dosen', ['card' => $this->projectCard->id]), [
                'evaluator_id' => $this->dosenUserB->id,
                'mata_kuliah_id' => $this->matkulB->id,
                'items' => [
                    $this->mahasiswa->id => [
                        'nilai' => [
                            'd_relevansi' => 92,
                            'd_kualitas' => 92,
                            'd_kreativitas' => 92,
                            'd_estetika' => 92,
                        ],
                    ],
                ],
            ]);

        $respAdminB->assertOk();
        $respAdminB->assertJson(['success' => true]);

        // 3. Admin memeriksa hasil kumulatif: (84 + 92) / 2 = 88.0
        $getResp = $this->actingAs($adminUser)
            ->getJson(route('admin.evaluasi.penilaian-dosen.show-by-project', ['project' => $this->projectCard->id]));

        $getResp->assertOk();
        $data = $getResp->json();
        $this->assertTrue($data['success']);

        $summary = $data['summary_per_mahasiswa'][$this->mahasiswa->id] ?? null;
        $this->assertNotNull($summary);
        $this->assertEquals(88.0, (float) $summary['cumulative_score']);
        $this->assertEquals(2, $summary['evaluators_count']);
    }

    public function test_dosen_can_update_their_own_project_grade_without_affecting_other_dosen(): void
    {
        // 1. Dosen A (80) & Dosen B (90)
        $this->actingAs($this->dosenUserA)
            ->postJson(route('dosen.evaluasi.project.grade.dosen', ['card' => $this->projectCard->id]), [
                'evaluator_id' => $this->dosenUserA->id,
                'mata_kuliah_id' => $this->matkulA->id,
                'items' => [
                    $this->mahasiswa->id => [
                        'nilai' => [
                            'd_relevansi' => 80,
                            'd_kualitas' => 80,
                            'd_kreativitas' => 80,
                            'd_estetika' => 80,
                        ],
                    ],
                ],
            ])
            ->assertOk();

        $this->actingAs($this->dosenUserB)
            ->postJson(route('dosen.evaluasi.project.grade.dosen', ['card' => $this->projectCard->id]), [
                'evaluator_id' => $this->dosenUserB->id,
                'mata_kuliah_id' => $this->matkulB->id,
                'items' => [
                    $this->mahasiswa->id => [
                        'nilai' => [
                            'd_relevansi' => 90,
                            'd_kualitas' => 90,
                            'd_kreativitas' => 90,
                            'd_estetika' => 90,
                        ],
                    ],
                ],
            ])
            ->assertOk();

        // 2. Dosen A update nilainya menjadi 86
        $this->actingAs($this->dosenUserA)
            ->postJson(route('dosen.evaluasi.project.grade.dosen', ['card' => $this->projectCard->id]), [
                'evaluator_id' => $this->dosenUserA->id,
                'mata_kuliah_id' => $this->matkulA->id,
                'items' => [
                    $this->mahasiswa->id => [
                        'nilai' => [
                            'd_relevansi' => 86,
                            'd_kualitas' => 86,
                            'd_kreativitas' => 86,
                            'd_estetika' => 86,
                        ],
                    ],
                ],
            ])
            ->assertOk();

        // 3. Rekam tetap 2, Dosen B tetap 90, rata-rata baru (86 + 90) / 2 = 88.0
        $this->assertEquals(2, EvaluasiDosen::where('project_card_id', $this->projectCard->id)->count());

        $evalB = EvaluasiDosen::where('project_card_id', $this->projectCard->id)
            ->where('evaluator_id', $this->dosenUserB->id)
            ->first();
        $this->assertEquals(90.0, (float) $evalB->nilai_akhir);

        $getResp = $this->actingAs($this->dosenUserB)
            ->getJson(route('dosen.evaluasi.penilaian-dosen.show-by-project', ['project' => $this->projectCard->id]));
        $getResp->assertOk();
        $summary = $getResp->json('summary_per_mahasiswa.'.$this->mahasiswa->id);
        $this->assertEquals(88.0, (float) $summary['cumulative_score']);
    }

    public function test_teaching_factory_scheme_does_not_require_dosen_mata_kuliah(): void
    {
        // Buat kartu proyek skema Teaching Factory tanpa mata kuliah
        $tefaCard = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->projectCard->list_id,
            'title' => 'Produksi Smart IoT Gateway - Teaching Factory',
            'created_by' => $this->mahasiswa->user_id,
            'skema_pbl' => 'Teaching Factory',
            'status' => 'Proses',
        ]);

        $this->assertTrue($tefaCard->isNonCourseScheme());

        // Dosen C yang tidak mengampu mata kuliah semester tetap berwenang menilai proyek Teaching Factory
        $this->assertTrue($tefaCard->canBeAssessedBy($this->dosenUserUnauth));

        // Dosen C dapat memanggil API get dan submit nilai tanpa error 403
        $getResp = $this->actingAs($this->dosenUserUnauth)
            ->getJson(route('dosen.evaluasi.penilaian-dosen.show-by-project', ['project' => $tefaCard->id]));

        $getResp->assertOk();
        $this->assertTrue($getResp->json('is_non_course_scheme'));
        $this->assertEmpty($getResp->json('mata_kuliahs'));

        // Dosen C menyimpan nilai tanpa mata_kuliah_id
        $postResp = $this->actingAs($this->dosenUserUnauth)
            ->postJson(route('dosen.evaluasi.project.grade.dosen', ['card' => $tefaCard->id]), [
                'evaluator_id' => $this->dosenUserUnauth->id,
                'items' => [
                    $this->mahasiswa->id => [
                        'nilai' => [
                            'd_relevansi' => 95,
                            'd_kualitas' => 95,
                            'd_kreativitas' => 95,
                            'd_estetika' => 95,
                        ],
                    ],
                ],
            ]);

        $postResp->assertOk();
        $postResp->assertJson(['success' => true]);

        $saved = EvaluasiDosen::where('project_card_id', $tefaCard->id)
            ->where('mahasiswa_id', $this->mahasiswa->id)
            ->first();

        $this->assertNotNull($saved);
        $this->assertEquals(95.0, (float) $saved->nilai_akhir);
        $this->assertNull($saved->mata_kuliah_id);
    }

    public function test_hibah_and_lomba_schemes_do_not_require_dosen_mata_kuliah(): void
    {
        $hibahCard = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->projectCard->list_id,
            'title' => 'Riset Sensor Agrikultur - Hibah Penelitian',
            'created_by' => $this->mahasiswa->user_id,
            'skema_pbl' => 'Hibah/Penelitian',
            'status' => 'Proses',
        ]);

        $lombaCard = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->projectCard->list_id,
            'title' => 'Aplikasi Gemastik 2026 - Lomba',
            'created_by' => $this->mahasiswa->user_id,
            'skema_pbl' => 'Lomba',
            'status' => 'Proses',
        ]);

        $this->assertTrue($hibahCard->isNonCourseScheme());
        $this->assertTrue($lombaCard->isNonCourseScheme());

        $this->assertTrue($hibahCard->canBeAssessedBy($this->dosenUserUnauth));
        $this->assertTrue($lombaCard->canBeAssessedBy($this->dosenUserUnauth));

        // Dosen dapat menilai proyek Hibah
        $this->actingAs($this->dosenUserUnauth)
            ->postJson(route('dosen.evaluasi.project.grade.dosen', ['card' => $hibahCard->id]), [
                'evaluator_id' => $this->dosenUserUnauth->id,
                'items' => [
                    $this->mahasiswa->id => [
                        'nilai' => [
                            'd_relevansi' => 88,
                            'd_kualitas' => 88,
                            'd_kreativitas' => 88,
                            'd_estetika' => 88,
                        ],
                    ],
                ],
            ])
            ->assertOk();

        // Dosen dapat menilai proyek Lomba
        $this->actingAs($this->dosenUserUnauth)
            ->postJson(route('dosen.evaluasi.project.grade.dosen', ['card' => $lombaCard->id]), [
                'evaluator_id' => $this->dosenUserUnauth->id,
                'items' => [
                    $this->mahasiswa->id => [
                        'nilai' => [
                            'd_relevansi' => 92,
                            'd_kualitas' => 92,
                            'd_kreativitas' => 92,
                            'd_estetika' => 92,
                        ],
                    ],
                ],
            ])
            ->assertOk();

        $this->assertDatabaseHas('evaluasi_dosen', [
            'project_card_id' => $hibahCard->id,
            'mahasiswa_id' => $this->mahasiswa->id,
            'nilai_akhir' => 88.0,
        ]);

        $this->assertDatabaseHas('evaluasi_dosen', [
            'project_card_id' => $lombaCard->id,
            'mahasiswa_id' => $this->mahasiswa->id,
            'nilai_akhir' => 92.0,
        ]);
    }

    public function test_safe_update_or_create_handles_multiple_evaluators_properly(): void
    {
        $evaluasiMaster = EvaluasiMaster::ensureForKelompok($this->periode->id, $this->kelompok->id, $this->dosenUserA->id);

        // First evaluator creates a record
        $recordA = EvaluasiNilaiAP::safeUpdateOrCreate(
            [
                'evaluasi_master_id' => $evaluasiMaster->id,
                'mahasiswa_id' => $this->mahasiswa->id,
                'aktivitas_list_id' => $this->aktivitasList->id,
                'evaluator_id' => $this->dosenUserA->id,
            ],
            [
                'periode_id' => $this->periode->id,
                'kelompok_id' => $this->kelompok->id,
                'w_ap_kehadiran' => 'Hadir',
                'w_ap_presentasi' => 85.0,
                'status' => 'Submitted',
            ]
        );

        $this->assertNotNull($recordA);
        $this->assertEquals(85.0, (float) $recordA->w_ap_presentasi);

        // Second evaluator creates a record without collision
        $recordB = EvaluasiNilaiAP::safeUpdateOrCreate(
            [
                'evaluasi_master_id' => $evaluasiMaster->id,
                'mahasiswa_id' => $this->mahasiswa->id,
                'aktivitas_list_id' => $this->aktivitasList->id,
                'evaluator_id' => $this->dosenUserB->id,
            ],
            [
                'periode_id' => $this->periode->id,
                'kelompok_id' => $this->kelompok->id,
                'w_ap_kehadiran' => 'Hadir',
                'w_ap_presentasi' => 95.0,
                'status' => 'Submitted',
            ]
        );

        $this->assertNotNull($recordB);
        $this->assertEquals(95.0, (float) $recordB->w_ap_presentasi);
        $this->assertNotEquals($recordA->id, $recordB->id);
    }
}
