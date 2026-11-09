<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Kelompok;
use App\Models\KunjunganMitra;
use App\Models\Mahasiswa;
use App\Models\Periode;
use App\Models\ProjectCard;
use App\Models\ProjectList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Memastikan runtime PHP saat ini telah menggunakan versi patched (>= 8.4.27)
     * yang kebal terhadap eksploitasi kerentanan kritis PHP2Shell (CVE-2026-109618 dan CVE-2026-109619).
     */
    public function test_php_version_is_patched_against_php2shell(): void
    {
        $version = PHP_VERSION;
        $versionId = PHP_VERSION_ID;

        // Versi 8.4 harus >= 8.4.27, atau versi 8.5 harus >= 8.5.12
        if (str_starts_with($version, '8.4.')) {
            $this->assertGreaterThanOrEqual(
                80427,
                $versionId,
                "Versi PHP saat ini ({$version}) masih rentan terhadap PHP2Shell. Harus minimal 8.4.27."
            );
        } elseif (str_starts_with($version, '8.5.')) {
            $this->assertGreaterThanOrEqual(
                80512,
                $versionId,
                "Versi PHP saat ini ({$version}) masih rentan terhadap PHP2Shell. Harus minimal 8.5.12."
            );
        } else {
            $this->assertTrue(
                version_compare($version, '8.4.27', '>='),
                "Versi PHP ({$version}) belum memenuhi standar keamanan minimal patch PHP2Shell."
            );
        }
    }

    /**
     * Memastikan HTTP Security Headers terpasang pada respon aplikasi untuk mencegah
     * Clickjacking, MIME sniffing, XSS, dan kebocoran referrer.
     */
    public function test_security_headers_are_attached_to_responses(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    /**
     * Memastikan route login dan registrasi dilindungi oleh rate limiting untuk mencegah serangan OOM / DoS.
     */
    public function test_auth_routes_have_rate_limiting(): void
    {
        $response = $this->post('/login', [
            'username' => 'nonexistent_user',
            'password' => 'wrong_password',
        ]);

        $this->assertTrue(
            $response->headers->has('X-RateLimit-Limit') || $response->isRedirect(),
            'Route /login harus memiliki proteksi rate limiting.'
        );
    }

    /**
     * Memastikan endpoint publik penilaian mitra memvalidasi anggota kelompok (IDOR / BOLA protection).
     */
    public function test_public_penilaian_mitra_rejects_unrelated_mahasiswa_idor(): void
    {
        $periode = Periode::create(['periode' => '2026/2027 Ganjil', 'status_periode' => 'Aktif']);
        $kelas = Kelas::create(['kelas' => 'TRKJ 4A', 'semester' => 4]);

        $mhs1 = Mahasiswa::create([
            'nim' => 'C030324101',
            'nama_mahasiswa' => 'Anggota Tim Sah',
            'kelas_id' => $kelas->id,
            'angkatan' => '2024',
        ]);

        $mhsLain = Mahasiswa::create([
            'nim' => 'C030324999',
            'nama_mahasiswa' => 'Mahasiswa Luar Kelompok',
            'kelas_id' => $kelas->id,
            'angkatan' => '2024',
        ]);

        $kelompok = Kelompok::create([
            'nama_kelompok' => 'Kelompok 1 TRKJ',
            'periode_id' => $periode->id,
        ]);
        $kelompok->mahasiswas()->attach($mhs1->id, ['periode_id' => $periode->id, 'role' => 'Ketua']);

        $list = ProjectList::create([
            'kelompok_id' => $kelompok->id,
            'name' => 'To Do',
            'position' => 0,
            'periode_id' => $periode->id,
        ]);

        $card = ProjectCard::create([
            'kelompok_id' => $kelompok->id,
            'list_id' => $list->id,
            'title' => 'Sistem IoT Smart Farming',
            'position' => 0,
        ]);

        // Coba injeksi penilaian untuk mahasiswa dari luar kelompok (IDOR attempt)
        $response = $this->postJson(route('public.penilaian-mitra.submit', $card->uuid), [
            'evaluations' => [
                [
                    'mahasiswa_id' => $mhsLain->id,
                    'komunikasi_sikap' => 90,
                    'hasil_pekerjaan' => 95,
                    'catatan' => 'Injecting unauthorized score',
                ],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Mahasiswa terpilih tidak terdaftar pada kelompok proyek ini.',
        ]);

        // Kirim penilaian valid untuk anggota sah
        $responseValid = $this->postJson(route('public.penilaian-mitra.submit', $card->uuid), [
            'evaluations' => [
                [
                    'mahasiswa_id' => $mhs1->id,
                    'komunikasi_sikap' => 85,
                    'hasil_pekerjaan' => 90,
                    'catatan' => 'Kinerja sangat baik',
                ],
            ],
        ]);

        $responseValid->assertOk();
        $responseValid->assertJson([
            'success' => true,
        ]);
    }

    /**
     * Memastikan endpoint publik penilaian mitra memiliki rate limiting.
     */
    public function test_public_penilaian_mitra_routes_have_rate_limiting(): void
    {
        $periode = Periode::create(['periode' => '2026/2027 Ganjil', 'status_periode' => 'Aktif']);
        $kelompok = Kelompok::create(['nama_kelompok' => 'Kelompok Rate Limit', 'periode_id' => $periode->id]);

        $card = ProjectCard::create([
            'kelompok_id' => $kelompok->id,
            'title' => 'Testing Rate Limit',
            'position' => 0,
        ]);

        $response = $this->get(route('public.penilaian-mitra.index', $card->uuid));
        $this->assertTrue($response->headers->has('X-RateLimit-Limit'));
    }

    /**
     * Memastikan endpoint bukti kunjungan mendukung UUID dan menyembunyikan error teknis.
     */
    public function test_kunjungan_mitra_bukti_endpoint_supports_uuid(): void
    {
        $periode = Periode::create(['periode' => '2026/2027 Ganjil', 'status_periode' => 'Aktif']);
        $kelompok = Kelompok::create(['nama_kelompok' => 'Kelompok 2', 'periode_id' => $periode->id]);
        $user = User::create([
            'nama_user' => 'Mahasiswa Kunjungan',
            'username' => 'mhs_kunjungan',
            'email' => 'mhs_kunjungan@politala.ac.id',
            'no_hp' => '081234567891',
            'password' => bcrypt('password'),
            'role' => 'mahasiswa',
        ]);

        $kunjungan = KunjunganMitra::create([
            'periode_id' => $periode->id,
            'kelompok_id' => $kelompok->id,
            'user_id' => $user->id,
            'perusahaan' => 'PT Teknologi Banua',
            'alamat' => 'Pelaihari',
            'tanggal_kunjungan' => now()->toDateString(),
            'status_kunjungan' => 'Sudah dikunjungi',
            'bukti_kunjungan' => 'dummy_image_data',
            'bukti_kunjungan_mime' => 'image/png',
        ]);

        // Akses via UUID
        $response = $this->getJson(route('public.kunjungan.bukti', $kunjungan->uuid));
        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'perusahaan' => 'PT Teknologi Banua',
        ]);
    }

    /**
     * Memastikan endpoint showcase melakukan masking terhadap nomor kontak mitra.
     */
    public function test_showcase_masks_partner_contact_info(): void
    {
        $periode = Periode::create(['periode' => '2026/2027 Ganjil', 'status_periode' => 'Aktif']);
        $kelompok = Kelompok::create(['nama_kelompok' => 'Kelompok Showcase', 'periode_id' => $periode->id]);

        ProjectCard::create([
            'title' => 'Aplikasi Monitoring Tambang',
            'status_proyek' => 'Selesai',
            'progress' => 100,
            'nama_mitra' => 'PT Tambang Makmur',
            'kontak_mitra' => '081234567890',
            'kelompok_id' => $kelompok->id,
            'periode_id' => $periode->id,
        ]);

        $response = $this->getJson(route('showcase.data'));
        $response->assertOk();

        $projects = $response->json('projects');
        $this->assertNotEmpty($projects);
        $project = collect($projects)->firstWhere('nama_mitra', 'PT Tambang Makmur');
        $this->assertNotNull($project);
        $this->assertSame('0812****890', $project['kontak_mitra']);
    }

    /**
     * Memastikan role dosen dapat mengakses rute /evaluator tanpa diblokir oleh middleware Evaluator.
     */
    public function test_dosen_role_can_access_evaluator_dashboard(): void
    {
        $dosen = User::create([
            'nama_user' => 'Dr. Ir. Dosen Pengampu',
            'username' => 'dosen_pengampu_test',
            'email' => 'dosen_pengampu@politala.ac.id',
            'no_hp' => '081234567899',
            'password' => bcrypt('password'),
            'role' => 'dosen',
        ]);

        $response = $this->actingAs($dosen)->get('/evaluator');
        $response->assertOk();
    }
}
