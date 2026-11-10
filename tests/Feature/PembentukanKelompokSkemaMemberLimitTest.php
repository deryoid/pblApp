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

class PembentukanKelompokSkemaMemberLimitTest extends TestCase
{
    use RefreshDatabase;

    protected Periode $periode;

    protected Kelas $kelas;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->periode = Periode::create([
            'periode' => '2026/2027 Ganjil',
            'status_periode' => 'Aktif',
        ]);

        $this->kelas = Kelas::create([
            'kelas' => 'TRKJ 3A',
            'semester' => 3,
        ]);

        $this->admin = User::create([
            'nama_user' => 'Admin Test',
            'username' => 'admin_pbl',
            'email' => 'admin@politala.ac.id',
            'no_hp' => '081234567800',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }

    protected function createStudentUser(string $nim, string $name): array
    {
        $user = User::create([
            'nama_user' => $name,
            'username' => $nim,
            'email' => strtolower($nim).'@politala.ac.id',
            'no_hp' => '081234'.substr($nim, -6),
            'password' => bcrypt('password'),
            'role' => 'mahasiswa',
        ]);

        $mhs = Mahasiswa::create([
            'user_id' => $user->id,
            'nama_mahasiswa' => $name,
            'nim' => $nim,
            'kelas_id' => $this->kelas->id,
            'semester' => 3,
            'is_profile_complete' => true,
        ]);

        return [$user, $mhs];
    }

    /**
     * Mahasiswa membuat kelompok Internal: dibatasi maksimal 4 orang.
     */
    public function test_student_creating_internal_group_is_limited_to_four_members(): void
    {
        [$ketuaUser, $ketuaMhs] = $this->createStudentUser('C030324001', 'Ketua Internal');

        $response = $this->actingAs($ketuaUser)->post(route('mahasiswa.kelompok.store'), [
            'nama_kelompok' => 'Tim Riset Internal',
            'skema_kategori' => 'Internal',
            'semester' => 3,
            'max_members' => 8, // Dicoba kirim 8, tapi untuk internal harus tetap dipaksa 4
        ]);

        $response->assertRedirect(route('mahasiswa.kelompok.index'));

        $kelompok = Kelompok::where('skema_kategori', 'Internal')->first();
        $this->assertNotNull($kelompok);
        $this->assertSame(4, $kelompok->max_members);
        $this->assertTrue($kelompok->isInternalScheme());

        // Tambah 3 anggota hingga pas 4 orang
        for ($i = 2; $i <= 4; $i++) {
            [, $mhs] = $this->createStudentUser('C03032400'.$i, 'Anggota '.$i);
            $kelompok->mahasiswas()->attach($mhs->id, [
                'role' => 'Anggota',
                'periode_id' => $this->periode->id,
            ]);
        }

        $this->assertCount(4, $kelompok->mahasiswas);
        $this->assertTrue($kelompok->isFull());

        // Coba undang orang ke-5 untuk kelompok internal
        [, $mhs5] = $this->createStudentUser('C030324005', 'Anggota 5');
        $inviteRes = $this->actingAs($ketuaUser)->post(route('mahasiswa.kelompok.invite'), [
            'mahasiswa_id' => $mhs5->id,
        ]);

        $inviteRes->assertStatus(302);
        $this->assertDatabaseMissing('kelompok_join_requests', [
            'kelompok_id' => $kelompok->id,
            'mahasiswa_id' => $mhs5->id,
        ]);
    }

    /**
     * Mahasiswa membuat kelompok selain Internal (External): bisa lebih dari 4 orang.
     */
    public function test_student_creating_external_group_can_have_more_than_four_members(): void
    {
        [$ketuaUser, $ketuaMhs] = $this->createStudentUser('C030324010', 'Ketua External');

        $response = $this->actingAs($ketuaUser)->post(route('mahasiswa.kelompok.store'), [
            'nama_kelompok' => 'Tim Mitra Industri',
            'skema_kategori' => 'External',
            'semester' => 3,
            'max_members' => 6,
        ]);

        $response->assertRedirect(route('mahasiswa.kelompok.index'));

        $kelompok = Kelompok::where('skema_kategori', 'External')->first();
        $this->assertNotNull($kelompok);
        $this->assertGreaterThan(4, $kelompok->max_members);
        $this->assertSame(6, $kelompok->max_members);

        // Tambahkan 3 anggota (total menjadi 4)
        for ($i = 11; $i <= 13; $i++) {
            [, $mhs] = $this->createStudentUser('C0303240'.$i, 'Anggota '.$i);
            $kelompok->mahasiswas()->attach($mhs->id, [
                'role' => 'Anggota',
                'periode_id' => $this->periode->id,
            ]);
        }

        $this->assertCount(4, $kelompok->mahasiswas);
        // Kelompok External belum penuh di 4 orang
        $this->assertFalse($kelompok->isFull());

        // Ketua berhasil mengundang orang ke-5
        [, $mhs5] = $this->createStudentUser('C030324014', 'Anggota 5 External');
        $inviteRes = $this->actingAs($ketuaUser)->post(route('mahasiswa.kelompok.invite'), [
            'mahasiswa_id' => $mhs5->id,
        ]);

        $inviteRes->assertRedirect(route('mahasiswa.kelompok.index'));
        $this->assertDatabaseHas('kelompok_join_requests', [
            'kelompok_id' => $kelompok->id,
            'mahasiswa_id' => $mhs5->id,
            'status' => 'pending',
        ]);

        // Anggota ke-5 menerima undangan
        [$user5] = $this->createStudentUser('C030324015_tmp', 'Tmp'); // prevent conflict
        $joinReq = KelompokJoinRequest::where('kelompok_id', $kelompok->id)
            ->where('mahasiswa_id', $mhs5->id)
            ->first();

        $acceptRes = $this->actingAs($mhs5->user)->post(route('mahasiswa.kelompok.invite.accept', $joinReq));
        $kelompok->refresh();

        // Total anggota resmi menjadi 5 orang (lebih dari 4 orang)
        $this->assertCount(5, $kelompok->mahasiswas);
        $this->assertTrue($kelompok->isMember($mhs5->id));
    }

    /**
     * Admin ditolak jika menambahkan lebih dari 4 orang untuk skema Internal.
     */
    public function test_admin_cannot_create_internal_group_with_more_than_four_members(): void
    {
        $nims = [];
        for ($i = 21; $i <= 25; $i++) {
            $nim = 'C0303240'.$i;
            $this->createStudentUser($nim, 'Mhs '.$i);
            $nims[] = ['nim' => $nim];
        }

        $response = $this->actingAs($this->admin)->post(route('kelompok.store'), [
            'periode_id' => $this->periode->id,
            'nama_kelompok' => 'Kelompok Internal Lebih 4',
            'skema_kategori' => 'Internal',
            'entries' => $nims, // 5 orang
        ]);

        $response->assertSessionHasErrors('entries');
        $this->assertDatabaseMissing('kelompok', [
            'nama_kelompok' => 'Kelompok 1 - Kelompok Internal Lebih 4',
        ]);
    }

    /**
     * Admin berhasil membuat kelompok dengan lebih dari 4 orang jika selain skema Internal.
     */
    public function test_admin_can_create_non_internal_group_with_more_than_four_members(): void
    {
        $nims = [];
        for ($i = 31; $i <= 36; $i++) {
            $nim = 'C0303240'.$i;
            $this->createStudentUser($nim, 'Mhs Lomba '.$i);
            $nims[] = ['nim' => $nim];
        }

        $response = $this->actingAs($this->admin)->post(route('kelompok.store'), [
            'periode_id' => $this->periode->id,
            'nama_kelompok' => 'Tim Gemastik Lomba',
            'skema_kategori' => 'Lomba',
            'entries' => $nims, // 6 orang
        ]);

        $response->assertRedirect(route('kelompok.index'));

        $kelompok = Kelompok::where('skema_kategori', 'Lomba')->first();
        $this->assertNotNull($kelompok);
        $this->assertCount(6, $kelompok->mahasiswas);
        $this->assertGreaterThanOrEqual(6, $kelompok->max_members);
    }

    /**
     * Admin ditolak jika memperbarui kelompok Internal menjadi lebih dari 4 anggota.
     */
    public function test_admin_cannot_update_internal_group_with_more_than_four_members(): void
    {
        $nims = [];
        for ($i = 41; $i <= 44; $i++) {
            $nim = 'C0303240'.$i;
            $this->createStudentUser($nim, 'Mhs Init '.$i);
            $nims[] = ['nim' => $nim];
        }

        $kelompok = Kelompok::create([
            'nama_kelompok' => 'Kelompok 1 - Internal Initial',
            'skema_kategori' => 'Internal',
            'periode_id' => $this->periode->id,
            'max_members' => 4,
        ]);

        $this->createStudentUser('C030324045', 'Mhs Tambahan 5');
        $nims[] = ['nim' => 'C030324045']; // Sekarang 5 orang

        $response = $this->actingAs($this->admin)->put(route('kelompok.update', $kelompok->uuid), [
            'periode_id' => $this->periode->id,
            'nama_kelompok' => 'Kelompok 1 - Internal Initial',
            'skema_kategori' => 'Internal',
            'entries' => $nims,
        ]);

        $response->assertSessionHasErrors('entries');
    }

    /**
     * Admin dapat memperbarui kelompok non-Internal dengan lebih dari 4 anggota.
     */
    public function test_admin_can_update_external_group_with_more_than_four_members(): void
    {
        $nims = [];
        for ($i = 51; $i <= 54; $i++) {
            $nim = 'C0303240'.$i;
            $this->createStudentUser($nim, 'Mhs Init Ext '.$i);
            $nims[] = ['nim' => $nim];
        }

        $kelompok = Kelompok::create([
            'nama_kelompok' => 'Kelompok 2 - External Initial',
            'skema_kategori' => 'External',
            'periode_id' => $this->periode->id,
            'max_members' => 4,
        ]);

        $this->createStudentUser('C030324055', 'Mhs Tambahan Ext 5');
        $nims[] = ['nim' => 'C030324055']; // 5 orang

        $response = $this->actingAs($this->admin)->put(route('kelompok.update', $kelompok->uuid), [
            'periode_id' => $this->periode->id,
            'nama_kelompok' => 'Kelompok 2 - External Initial',
            'skema_kategori' => 'External',
            'entries' => $nims,
        ]);

        $response->assertRedirect(route('kelompok.index'));
        $kelompok->refresh();
        $this->assertCount(5, $kelompok->mahasiswas);
        $this->assertGreaterThanOrEqual(5, $kelompok->max_members);
    }
}
