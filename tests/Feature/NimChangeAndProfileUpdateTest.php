<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NimChangeAndProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected Kelas $kelas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kelas = Kelas::create([
            'kelas' => 'TRPL 3A',
            'semester' => 3,
        ]);
    }

    public function test_student_can_save_profile_without_nim_already_taken_error(): void
    {
        $user = User::create([
            'nama_user' => 'Budi Santoso',
            'username' => '230411100010',
            'email' => 'budi@politala.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'mahasiswa',
            'no_hp' => '081234567890',
        ]);

        $mhs = Mahasiswa::create([
            'nama_mahasiswa' => 'Budi Santoso',
            'nim' => '230411100010',
            'kelas_id' => $this->kelas->id,
            'user_id' => $user->id,
            'semester' => 3,
            'is_profile_complete' => true,
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'nama_user' => 'Budi Santoso S.Tr.Kom',
            'email' => 'budi@politala.ac.id',
            'no_hp' => '081234567890',
            'username' => '230411100010',
            'nim' => '230411100010',
            'kelas_id' => $this->kelas->id,
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHasNoErrors();

        $mhs->refresh();
        $this->assertSame('Budi Santoso S.Tr.Kom', $mhs->nama_mahasiswa);
        $this->assertSame('230411100010', $mhs->nim);
    }

    public function test_student_can_change_nim_to_unlinked_bank_data_nim_without_error(): void
    {
        // User awal terdaftar dengan NIM typo
        $user = User::create([
            'nama_user' => 'Siti Nurhaliza',
            'username' => '230411100020_old',
            'email' => 'siti@politala.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'mahasiswa',
            'no_hp' => '081234567891',
        ]);

        $oldMhs = Mahasiswa::create([
            'nama_mahasiswa' => 'Siti Nurhaliza',
            'nim' => '230411100020_OLD',
            'kelas_id' => $this->kelas->id,
            'user_id' => $user->id,
            'semester' => 3,
            'is_profile_complete' => true,
        ]);

        // Bank data resmi dari admin dengan NIM asli tapi belum punya user_id
        $bankMhs = Mahasiswa::create([
            'nama_mahasiswa' => 'Siti Nurhaliza',
            'nim' => '230411100020',
            'kelas_id' => $this->kelas->id,
            'user_id' => null,
            'angkatan' => '2023',
            'is_profile_complete' => true,
        ]);

        // Mahasiswa mengganti NIM ke NIM bank data resmi
        $response = $this->actingAs($user)->put(route('profile.update'), [
            'nama_user' => 'Siti Nurhaliza',
            'email' => 'siti@politala.ac.id',
            'no_hp' => '081234567891',
            'username' => '230411100020',
            'nim' => '230411100020',
            'kelas_id' => $this->kelas->id,
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHasNoErrors();

        $oldMhs->refresh();
        $this->assertSame('230411100020', $oldMhs->nim);
        $this->assertSame($user->id, $oldMhs->user_id);
        $this->assertSame('2023', $oldMhs->angkatan);

        // Record duplikat unlinked harus terhapus/termerge
        $this->assertDatabaseMissing('mahasiswa', ['id' => $bankMhs->id]);
    }

    public function test_student_cannot_change_nim_to_another_active_student_nim(): void
    {
        $userA = User::create([
            'nama_user' => 'Mahasiswa A',
            'username' => '230411100030',
            'email' => 'a@politala.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'mahasiswa',
            'no_hp' => '081234567892',
        ]);
        Mahasiswa::create([
            'nama_mahasiswa' => 'Mahasiswa A',
            'nim' => '230411100030',
            'kelas_id' => $this->kelas->id,
            'user_id' => $userA->id,
            'semester' => 3,
        ]);

        $userB = User::create([
            'nama_user' => 'Mahasiswa B',
            'username' => '230411100040',
            'email' => 'b@politala.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'mahasiswa',
            'no_hp' => '081234567893',
        ]);
        Mahasiswa::create([
            'nama_mahasiswa' => 'Mahasiswa B',
            'nim' => '230411100040',
            'kelas_id' => $this->kelas->id,
            'user_id' => $userB->id,
            'semester' => 3,
        ]);

        // User B mencoba mengubah NIM menjadi NIM milik Mahasiswa A
        $response = $this->actingAs($userB)->put(route('profile.update'), [
            'nama_user' => 'Mahasiswa B',
            'email' => 'b@politala.ac.id',
            'no_hp' => '081234567893',
            'username' => '230411100040',
            'nim' => '230411100030', // NIM milik User A
            'kelas_id' => $this->kelas->id,
        ]);

        $response->assertSessionHasErrors('nim');
    }

    public function test_admin_can_update_mahasiswa_nim_without_already_taken_conflict(): void
    {
        $admin = User::create([
            'nama_user' => 'Administrator',
            'username' => 'admin_pbl',
            'email' => 'admin@politala.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'no_hp' => '081234567899',
        ]);

        $studentUser = User::create([
            'nama_user' => 'Randi Pratama',
            'username' => '230411100050',
            'email' => 'randi@politala.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'mahasiswa',
            'no_hp' => '081234567894',
        ]);

        // Record mahasiswa di bank data belum terlink user_id
        $mhs = Mahasiswa::create([
            'nama_mahasiswa' => 'Randi Pratama',
            'nim' => '230411100050',
            'kelas_id' => $this->kelas->id,
            'user_id' => null,
            'semester' => 3,
        ]);

        // Ada unlinked placeholder record dengan NIM baru
        $placeholder = Mahasiswa::create([
            'nama_mahasiswa' => 'Randi Pratama Duplicate',
            'nim' => '230411100055',
            'kelas_id' => $this->kelas->id,
            'user_id' => null,
            'semester' => 3,
        ]);

        // Admin mengubah NIM Randi dari 230411100050 menjadi 230411100055
        $response = $this->actingAs($admin)->put(route('mahasiswa.update', $mhs->uuid), [
            'nama_mahasiswa' => 'Randi Pratama',
            'nim' => '230411100055',
            'kelas_id' => $this->kelas->id,
            'angkatan' => '2023',
            'no_hp' => '081234567894',
            'email_kampus' => 'randi@politala.ac.id',
        ]);

        $response->assertRedirect(route('mahasiswa.index'));
        $response->assertSessionHasNoErrors();

        $mhs->refresh();
        $this->assertSame('230411100055', $mhs->nim);
        $this->assertSame($studentUser->id, $mhs->user_id);

        $studentUser->refresh();
        $this->assertSame('230411100055', $studentUser->username);

        // Placeholder harus terhapus/termerge
        $this->assertDatabaseMissing('mahasiswa', ['id' => $placeholder->id]);
    }
}
