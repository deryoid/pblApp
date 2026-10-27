<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Kelompok;
use App\Models\KelompokJoinRequest;
use App\Models\Mahasiswa;
use App\Models\Periode;
use App\Models\ProjectCard;
use App\Models\ProjectList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewPBLRequirementsTest extends TestCase
{
    use RefreshDatabase;

    protected Periode $periode;

    protected Kelas $kelas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->periode = Periode::create([
            'periode' => '2026/2027 Ganjil',
            'status_periode' => 'Aktif',
        ]);

        $this->kelas = Kelas::create([
            'kelas' => 'TRPL 3A',
            'semester' => 3,
        ]);
    }

    /**
     * 1. Menu penilaian mitra tidak ditampilkan di navigasi publik
     */
    public function test_menu_penilaian_mitra_is_not_displayed_in_public_navigation(): void
    {
        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $response->assertDontSee('>Penilaian Mitra<', false);
        $response->assertDontSee('>Rekap Penilaian Mitra<', false);

        $responseKunjungan = $this->get(route('public.kunjungan.index'));
        $responseKunjungan->assertStatus(200);
        $responseKunjungan->assertDontSee('>Penilaian Mitra<', false);
    }

    /**
     * 2. Proyek dan kelompok memiliki 4 skema kategori (Internal, External, Hibah/Penelitian, Lomba)
     */
    public function test_kelompok_requires_and_stores_one_of_four_categories(): void
    {
        $categories = ['Internal', 'External', 'Hibah/Penelitian', 'Lomba'];

        foreach ($categories as $index => $cat) {
            $kelompok = Kelompok::create([
                'nama_kelompok' => 'Kelompok Kategori '.$cat,
                'skema_kategori' => $cat,
                'periode_id' => $this->periode->id,
                'semester' => 3,
                'max_members' => 4,
            ]);

            $this->assertDatabaseHas('kelompok', [
                'id' => $kelompok->id,
                'skema_kategori' => $cat,
            ]);
            $this->assertEquals($cat, $kelompok->skema_kategori);
        }
    }

    /**
     * 3. Syarat untuk mengakses menu wajib diperbarui profile
     */
    public function test_uncompleted_profile_is_redirected_to_profile_edit(): void
    {
        // User baru tanpa no_hp dan profil belum lengkap
        $user = User::create([
            'nama_user' => 'Mahasiswa Belum Lengkap',
            'username' => 'mhs_incomplate',
            'email' => 'incomplete@politala.ac.id',
            'password' => bcrypt('password123'),
            'role' => 'mahasiswa',
            'no_hp' => null,
        ]);

        $mhs = Mahasiswa::create([
            'user_id' => $user->id,
            'nama_mahasiswa' => $user->nama_user,
            'nim' => '230411122233',
            'kelas_id' => null,
            'semester' => 3,
            'is_profile_complete' => false,
        ]);

        // Coba akses menu mahasiswa -> harus di-redirect ke profile.edit
        $response = $this->actingAs($user)->get('/mahasiswa/proyek');
        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('warning');

        // Sekarang lengkapi profil via update
        $updateResponse = $this->actingAs($user)->put(route('profile.update'), [
            'nama_user' => 'Mahasiswa Belum Lengkap',
            'username' => 'mhs_incomplate',
            'email' => 'incomplete@politala.ac.id',
            'no_hp' => '081234567890',
            'nim' => '230411122233',
            'kelas_id' => $this->kelas->id,
        ]);

        $updateResponse->assertRedirect(route('profile.edit'));
        $updateResponse->assertSessionHas('status');

        $user->refresh();
        $mhs->refresh();

        $this->assertTrue($user->isProfileComplete());
        $this->assertTrue((bool) $mhs->is_profile_complete);

        // Setelah lengkap, akses menu diperbolehkan
        $accessResponse = $this->actingAs($user)->get('/mahasiswa/proyek');
        $accessResponse->assertStatus(200);
    }

    /**
     * 4. Alur invite kelompok dan penerima dapat menerima (accept) undangan
     */
    public function test_group_invitation_flow_can_invite_and_accept(): void
    {
        // 1. User Ketua & Kelompoknya
        $ketuaUser = User::create([
            'nama_user' => 'Ketua Kelompok',
            'username' => 'ketua_pbl',
            'email' => 'ketua@politala.ac.id',
            'password' => bcrypt('password123'),
            'role' => 'mahasiswa',
            'no_hp' => '081234567801',
        ]);

        $ketuaMhs = Mahasiswa::create([
            'user_id' => $ketuaUser->id,
            'nama_mahasiswa' => 'Ketua Kelompok',
            'nim' => '230411100001',
            'kelas_id' => $this->kelas->id,
            'semester' => 3,
            'is_profile_complete' => true,
        ]);

        $kelompok = Kelompok::create([
            'nama_kelompok' => 'Kelompok Alpha',
            'skema_kategori' => 'Internal',
            'periode_id' => $this->periode->id,
            'semester' => 3,
            'max_members' => 4,
            'status_bergabung' => 'terbuka',
        ]);

        $kelompok->mahasiswas()->attach($ketuaMhs->id, [
            'role' => 'Ketua',
            'periode_id' => $this->periode->id,
        ]);

        // 2. User Calon Anggota
        $calonUser = User::create([
            'nama_user' => 'Calon Anggota',
            'username' => 'calon_anggota',
            'email' => 'calon@politala.ac.id',
            'password' => bcrypt('password123'),
            'role' => 'mahasiswa',
            'no_hp' => '081234567802',
        ]);

        $calonMhs = Mahasiswa::create([
            'user_id' => $calonUser->id,
            'nama_mahasiswa' => 'Calon Anggota',
            'nim' => '230411100002',
            'kelas_id' => $this->kelas->id,
            'semester' => 3,
            'is_profile_complete' => true,
        ]);

        // 3. Ketua mengirim undangan ke Calon Anggota
        $inviteResponse = $this->actingAs($ketuaUser)->post(route('mahasiswa.kelompok.invite'), [
            'mahasiswa_id' => $calonMhs->id,
            'pesan' => 'Mari bergabung di tim Alpha proyek internal!',
        ]);

        $inviteResponse->assertRedirect(route('mahasiswa.kelompok.index'));

        $this->assertDatabaseHas('kelompok_join_requests', [
            'kelompok_id' => $kelompok->id,
            'mahasiswa_id' => $calonMhs->id,
            'type' => 'invite',
            'status' => 'pending',
            'invited_by' => $ketuaUser->id,
        ]);

        $invitation = KelompokJoinRequest::where('kelompok_id', $kelompok->id)
            ->where('mahasiswa_id', $calonMhs->id)
            ->first();

        // 4. Calon Anggota melihat undangan masuk
        $calonPageResponse = $this->actingAs($calonUser)->get(route('mahasiswa.kelompok.index'));
        $calonPageResponse->assertStatus(200);
        $calonPageResponse->assertSee('Kelompok Alpha');
        $calonPageResponse->assertSee('Terima Undangan');

        // 5. Calon Anggota meng-accept undangan
        $acceptResponse = $this->actingAs($calonUser)->post(
            route('mahasiswa.kelompok.invite.accept', $invitation->uuid)
        );

        $acceptResponse->assertRedirect(route('mahasiswa.kelompok.index'));

        // Verifikasi status undangan dan keanggotaan kelompok
        $this->assertDatabaseHas('kelompok_join_requests', [
            'id' => $invitation->id,
            'status' => 'approved',
            'responded_by' => $calonUser->id,
        ]);

        $this->assertDatabaseHas('kelompok_mahasiswa', [
            'kelompok_id' => $kelompok->id,
            'mahasiswa_id' => $calonMhs->id,
            'periode_id' => $this->periode->id,
            'role' => 'Anggota',
        ]);

        $this->assertTrue($kelompok->mahasiswas->contains($calonMhs));
    }

    /**
     * 5. Sebelum memulai proyek wajib mengunggah proposal khusus proyek internal
     */
    public function test_internal_project_requires_proposal_file_upload(): void
    {
        Storage::fake('public');

        $user = User::create([
            'nama_user' => 'Ketua Tim Internal',
            'username' => 'ketua_internal',
            'email' => 'internal@politala.ac.id',
            'password' => bcrypt('password123'),
            'role' => 'mahasiswa',
            'no_hp' => '081234567803',
        ]);

        $mhs = Mahasiswa::create([
            'user_id' => $user->id,
            'nama_mahasiswa' => 'Ketua Tim Internal',
            'nim' => '230411100003',
            'kelas_id' => $this->kelas->id,
            'semester' => 3,
            'is_profile_complete' => true,
        ]);

        $kelompok = Kelompok::create([
            'nama_kelompok' => 'Kelompok Internal Riset',
            'skema_kategori' => 'Internal',
            'periode_id' => $this->periode->id,
            'semester' => 3,
            'max_members' => 4,
            'status_bergabung' => 'terbuka',
        ]);

        $kelompok->mahasiswas()->attach($mhs->id, [
            'role' => 'Ketua',
            'periode_id' => $this->periode->id,
        ]);

        $list = ProjectList::create([
            'kelompok_id' => $kelompok->id,
            'periode_id' => $this->periode->id,
            'name' => 'To Do / Rencana',
            'position' => 0,
        ]);

        // Percobaan 1: Buat proyek internal TANPA proposal -> harus ditolak
        $responseFail = $this->actingAs($user)->post(route('proyek.cards.store'), [
            'title' => 'Sistem Informasi Manajemen Riset Kampus',
            'list' => $list->uuid,
            'skema_pbl' => 'Internal',
            'status_proyek' => 'Proses',
        ]);

        $responseFail->assertSessionHasErrors(['proposal_file']);
        $this->assertDatabaseMissing('project_cards', [
            'title' => 'Sistem Informasi Manajemen Riset Kampus',
        ]);

        // Percobaan 2: Buat proyek internal DENGAN berkas proposal -> berhasil
        $file = UploadedFile::fake()->create('proposal_riset_internal.pdf', 500, 'application/pdf');

        $responseSuccess = $this->actingAs($user)->post(route('proyek.cards.store'), [
            'title' => 'Sistem Informasi Manajemen Riset Kampus',
            'list' => $list->uuid,
            'skema_pbl' => 'Internal',
            'status_proyek' => 'Proses',
            'proposal_file' => $file,
        ]);

        $responseSuccess->assertRedirect();
        $this->assertDatabaseHas('project_cards', [
            'title' => 'Sistem Informasi Manajemen Riset Kampus',
            'skema_pbl' => 'Internal',
            'jenis_proyek' => 'internal',
            'status_proposal' => 'disetujui',
        ]);

        $card = ProjectCard::where('title', 'Sistem Informasi Manajemen Riset Kampus')->first();
        $this->assertNotNull($card->proposal_file_path);
        Storage::disk('public')->assertExists($card->proposal_file_path);
    }
}
