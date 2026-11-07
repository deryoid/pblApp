<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Kelompok;
use App\Models\KelompokJoinRequest;
use App\Models\Mahasiswa;
use App\Models\Periode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KelompokPeriodeTerbaruTest extends TestCase
{
    use RefreshDatabase;

    protected Periode $periode1;

    protected Periode $periode2;

    protected Kelas $kelas;

    protected User $userA;

    protected Mahasiswa $mhsA;

    protected User $userB;

    protected Mahasiswa $mhsB;

    protected function setUp(): void
    {
        parent::setUp();

        // Periode 1 (Awal)
        $this->periode1 = Periode::create([
            'periode' => '2025/2026 Ganjil',
            'status_periode' => 'Aktif',
        ]);

        $this->kelas = Kelas::create([
            'kelas' => 'TRKJ 3A',
            'semester' => 3,
        ]);

        // Mahasiswa A
        $this->userA = User::create([
            'nama_user' => 'Mahasiswa Alpha',
            'username' => 'mhs_alpha',
            'email' => 'alpha@politala.ac.id',
            'password' => bcrypt('password123'),
            'role' => 'mahasiswa',
            'no_hp' => '081234567891',
        ]);

        $this->mhsA = Mahasiswa::create([
            'user_id' => $this->userA->id,
            'nama_mahasiswa' => 'Mahasiswa Alpha',
            'nim' => 'C030324001',
            'kelas_id' => $this->kelas->id,
            'semester' => 3,
            'is_profile_complete' => true,
        ]);

        // Mahasiswa B
        $this->userB = User::create([
            'nama_user' => 'Mahasiswa Beta',
            'username' => 'mhs_beta',
            'email' => 'beta@politala.ac.id',
            'password' => bcrypt('password123'),
            'role' => 'mahasiswa',
            'no_hp' => '081234567892',
        ]);

        $this->mhsB = Mahasiswa::create([
            'user_id' => $this->userB->id,
            'nama_mahasiswa' => 'Mahasiswa Beta',
            'nim' => 'C030324002',
            'kelas_id' => $this->kelas->id,
            'semester' => 3,
            'is_profile_complete' => true,
        ]);
    }

    /**
     * 1. Kelompok diulang untuk periode PBL terbaru: Mahasiswa yang punya kelompok di periode lama
     * tidak terkunci ke kelompok lama saat periode baru aktif, melainkan mulai kelompok baru.
     */
    public function test_kelompok_resets_for_student_when_new_pbl_period_is_active(): void
    {
        // Buat kelompok di Periode 1 untuk Mahasiswa A
        $kelompokP1 = Kelompok::create([
            'nama_kelompok' => 'Kelompok 1 Periode Lama',
            'skema_kategori' => 'Internal',
            'periode_id' => $this->periode1->id,
            'semester' => 3,
            'max_members' => 4,
            'status_bergabung' => 'terbuka',
        ]);
        $kelompokP1->mahasiswas()->attach($this->mhsA->id, [
            'role' => 'Ketua',
            'periode_id' => $this->periode1->id,
        ]);

        // Saat Periode 1 aktif, Mahasiswa A melihat kelompok lamanya
        $responseP1 = $this->actingAs($this->userA)->get(route('mahasiswa.kelompok.index'));
        $responseP1->assertStatus(200);
        $responseP1->assertSee('Kelompok 1 Periode Lama');

        // Buat Periode 2 (Terbaru) dan jadikan Aktif
        $this->periode1->update(['status_periode' => 'Tidak Aktif']);
        $this->periode2 = Periode::create([
            'periode' => '2025/2026 Genap',
            'status_periode' => 'Aktif',
        ]);

        // Sekarang Periode 2 aktif: Mahasiswa A tidak lagi terkunci ke kelompok Periode 1!
        // Kelompoknya diulang untuk periode baru (status: belum punya kelompok di periode 2)
        $responseP2 = $this->actingAs($this->userA)->get(route('mahasiswa.kelompok.index'));
        $responseP2->assertStatus(200);
        $responseP2->assertDontSee('Kelompok 1 Periode Lama');
        $responseP2->assertSee('Formulir Buat Kelompok Baru');
        $responseP2->assertSee('2025/2026 Genap');

        // Mahasiswa A dapat membentuk kelompok baru untuk Periode 2
        $storeResponse = $this->actingAs($this->userA)->post(route('mahasiswa.kelompok.store'), [
            'nama_kelompok' => 'Kelompok Inovasi Baru P2',
            'skema_kategori' => 'Internal',
            'semester' => 4,
        ]);

        $storeResponse->assertRedirect(route('mahasiswa.kelompok.index'));

        // Pastikan kelompok baru tercatat di database dengan periode_id terbaru (Periode 2)
        $this->assertDatabaseHas('kelompok', [
            'nama_kelompok' => 'Kelompok Inovasi Baru P2',
            'periode_id' => $this->periode2->id,
        ]);

        $this->assertDatabaseHas('kelompok_mahasiswa', [
            'mahasiswa_id' => $this->mhsA->id,
            'periode_id' => $this->periode2->id,
            'role' => 'Ketua',
        ]);

        // Kedua riwayat keanggotaan (Periode 1 dan Periode 2) tetap aman tersimpan
        $this->assertDatabaseHas('kelompok_mahasiswa', [
            'mahasiswa_id' => $this->mhsA->id,
            'periode_id' => $this->periode1->id,
        ]);
    }

    /**
     * 2. Mahasiswa yang belum tergabung kelompok pada periode terbaru melihat pesan info di board proyek & aktivitas.
     */
    public function test_student_without_group_in_new_period_sees_alert_in_boards(): void
    {
        // Periode 1 kelompok lama
        $kelompokP1 = Kelompok::create([
            'nama_kelompok' => 'Kelompok P1',
            'skema_kategori' => 'Internal',
            'periode_id' => $this->periode1->id,
            'semester' => 3,
        ]);
        $kelompokP1->mahasiswas()->attach($this->mhsA->id, [
            'role' => 'Ketua',
            'periode_id' => $this->periode1->id,
        ]);

        // Aktifkan Periode 2 baru
        $this->periode1->update(['status_periode' => 'Tidak Aktif']);
        $this->periode2 = Periode::create([
            'periode' => '2025/2026 Genap',
            'status_periode' => 'Aktif',
        ]);

        // Akses Proyek Board -> melihat info belum memiliki kelompok di periode baru
        $proyekResponse = $this->actingAs($this->userA)->get(route('proyek.index'));
        $proyekResponse->assertStatus(200);
        $proyekResponse->assertSee('Anda belum memiliki kelompok pada periode PBL');
        $proyekResponse->assertSee('Kelola Kelompok');

        // Akses Aktivitas Board -> melihat info belum memiliki kelompok di periode baru
        $aktivitasResponse = $this->actingAs($this->userA)->get(route('aktivitas.index'));
        $aktivitasResponse->assertStatus(200);
        $aktivitasResponse->assertSee('Anda belum memiliki kelompok pada periode PBL');
        $aktivitasResponse->assertSee('Kelola Kelompok');
    }

    /**
     * 3. Mahasiswa dari kelompok periode lama dapat diundang dan menerima undangan kelompok di periode terbaru.
     */
    public function test_student_from_old_period_can_be_invited_and_join_new_period_group(): void
    {
        // Mahasiswa A dan B ada kelompok di Periode 1
        $kelompokP1 = Kelompok::create([
            'nama_kelompok' => 'Kelompok Lama Bersama',
            'skema_kategori' => 'Internal',
            'periode_id' => $this->periode1->id,
        ]);
        $kelompokP1->mahasiswas()->attach([
            $this->mhsA->id => ['role' => 'Ketua', 'periode_id' => $this->periode1->id],
            $this->mhsB->id => ['role' => 'Anggota', 'periode_id' => $this->periode1->id],
        ]);

        // Periode 2 aktif
        $this->periode1->update(['status_periode' => 'Tidak Aktif']);
        $this->periode2 = Periode::create([
            'periode' => '2025/2026 Genap',
            'status_periode' => 'Aktif',
        ]);

        // Mahasiswa A membuat kelompok di Periode 2
        $kelompokP2 = Kelompok::create([
            'nama_kelompok' => 'Kelompok Masa Depan P2',
            'skema_kategori' => 'External',
            'periode_id' => $this->periode2->id,
            'max_members' => 4,
            'status_bergabung' => 'terbuka',
        ]);
        $kelompokP2->mahasiswas()->attach($this->mhsA->id, [
            'role' => 'Ketua',
            'periode_id' => $this->periode2->id,
        ]);

        // Mahasiswa A mengundang Mahasiswa B (yang di periode 1 sudah pernah punya kelompok)
        $inviteResponse = $this->actingAs($this->userA)->post(route('mahasiswa.kelompok.invite'), [
            'mahasiswa_id' => $this->mhsB->id,
            'pesan' => 'Ayo gabung kelompok di semester genap!',
        ]);

        $inviteResponse->assertRedirect();
        $this->assertDatabaseHas('kelompok_join_requests', [
            'kelompok_id' => $kelompokP2->id,
            'mahasiswa_id' => $this->mhsB->id,
            'status' => 'pending',
        ]);

        // Mahasiswa B melihat undangan masuk di halamannya
        $invitation = KelompokJoinRequest::where('kelompok_id', $kelompokP2->id)
            ->where('mahasiswa_id', $this->mhsB->id)
            ->first();

        $pageResponseB = $this->actingAs($this->userB)->get(route('mahasiswa.kelompok.index'));
        $pageResponseB->assertStatus(200);
        $pageResponseB->assertSee('Kelompok Masa Depan P2');
        $pageResponseB->assertSee('Terima Undangan');

        // Mahasiswa B menerima undangan
        $acceptResponse = $this->actingAs($this->userB)->post(
            route('mahasiswa.kelompok.invite.accept', $invitation->uuid)
        );
        $acceptResponse->assertRedirect(route('mahasiswa.kelompok.index'));

        // Mahasiswa B resmi bergabung ke kelompok Periode 2
        $this->assertDatabaseHas('kelompok_mahasiswa', [
            'kelompok_id' => $kelompokP2->id,
            'mahasiswa_id' => $this->mhsB->id,
            'periode_id' => $this->periode2->id,
            'role' => 'Anggota',
        ]);
    }

    /**
     * 4. Riwayat PBL tetap mencatat keikutsertaan kelompok dari seluruh periode.
     */
    public function test_riwayat_pbl_keeps_track_of_all_periods(): void
    {
        // Kelompok Periode 1
        $k1 = Kelompok::create([
            'nama_kelompok' => 'Kelompok Riset AI P1',
            'skema_kategori' => 'Internal',
            'periode_id' => $this->periode1->id,
        ]);
        $k1->mahasiswas()->attach($this->mhsA->id, ['role' => 'Ketua', 'periode_id' => $this->periode1->id]);

        // Kelompok Periode 2
        $this->periode2 = Periode::create([
            'periode' => '2025/2026 Genap',
            'status_periode' => 'Aktif',
        ]);
        $k2 = Kelompok::create([
            'nama_kelompok' => 'Kelompok Mobile App P2',
            'skema_kategori' => 'External',
            'periode_id' => $this->periode2->id,
        ]);
        $k2->mahasiswas()->attach($this->mhsA->id, ['role' => 'Anggota', 'periode_id' => $this->periode2->id]);

        // Cek halaman riwayat PBL Mahasiswa A
        $riwayatResponse = $this->actingAs($this->userA)->get(route('mahasiswa.riwayat.index'));
        $riwayatResponse->assertStatus(200);
        $riwayatResponse->assertSee('Kelompok Riset AI P1');
        $riwayatResponse->assertSee('Kelompok Mobile App P2');
    }
}
