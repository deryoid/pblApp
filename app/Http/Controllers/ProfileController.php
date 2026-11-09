<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $kelasList = Kelas::orderBy('kelas')->get(['id', 'kelas']);
        $mahasiswa = $user->role === 'mahasiswa'
            ? $user->mahasiswa()->with('kelas')->first()
            : null;

        return view('profile.edit', compact('user', 'kelasList', 'mahasiswa'));
    }

    public function update(Request $request)
    {
        $user = $request->user();

        // Normalisasi
        if ($request->has('nama_user')) {
            $request->merge(['nama_user' => preg_replace('/\s+/', ' ', trim($request->nama_user))]);
        }
        if ($request->has('email')) {
            $request->merge(['email' => strtolower(trim($request->email))]);
        }
        if ($request->has('username')) {
            $request->merge(['username' => strtolower(trim($request->username))]);
        }
        if ($request->has('no_hp')) {
            $request->merge(['no_hp' => preg_replace('/\s+/', '', trim($request->no_hp))]);
        }
        if ($request->has('nim')) {
            $request->merge(['nim' => strtoupper(preg_replace('/\s+/', '', (string) $request->nim))]);
        }

        $rules = [
            'nama_user' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'no_hp' => 'required|string|max:30',
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // Max 2MB
            'remove_photo' => 'sometimes|boolean',
        ];

        if ($user->role === 'mahasiswa') {
            $mhs = $user->getOrResolveMahasiswa();
            $mhsId = $mhs?->id;
            $rules['nim'] = [
                'nullable',
                'string',
                'max:50',
                function ($attribute, $value, $fail) use ($user, $mhsId) {
                    if (empty($value)) {
                        return;
                    }
                    $normalizedNim = strtoupper(preg_replace('/\s+/', '', (string) $value));

                    // Cek apakah NIM ini sudah dipakai oleh akun mahasiswa lain yang aktif
                    $takenByOther = \App\Models\Mahasiswa::where('nim', $normalizedNim)
                        ->when($mhsId, fn ($q) => $q->where('id', '!=', $mhsId))
                        ->whereNotNull('user_id')
                        ->where('user_id', '!=', $user->id)
                        ->exists();

                    if ($takenByOther) {
                        $fail('NIM '.$normalizedNim.' sudah digunakan oleh akun mahasiswa lain.');
                    }
                },
            ];
            $rules['kelas_id'] = ['nullable', 'integer', 'exists:kelas,id'];
            $rules['angkatan'] = ['nullable', 'string', 'max:10'];
            $rules['nik'] = ['nullable', 'string', 'max:30'];
            $rules['jk'] = ['nullable', 'in:L,P'];
            $rules['nama_ibu'] = ['nullable', 'string', 'max:255'];
            $rules['agama'] = ['nullable', 'string', 'max:50'];
            $rules['tempat_lahir'] = ['nullable', 'string', 'max:255'];
            $rules['tanggal_lahir'] = ['nullable', 'date'];
            $rules['alamat'] = ['nullable', 'string', 'max:1000'];
        }

        $validated = $request->validate($rules);

        $user->nama_user = $validated['nama_user'];
        $user->email = $validated['email'];
        $user->no_hp = $validated['no_hp'];
        $user->username = $validated['username'];

        // Handle photo file upload
        if ($request->boolean('remove_photo')) {
            // Delete old photo if exists
            if ($user->profile_photo_path) {
                $this->deleteProfilePhoto($user->profile_photo_path);
            }
            $user->profile_photo_path = null;
        }

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');

            // Delete old photo if exists
            if ($user->profile_photo_path) {
                $this->deleteProfilePhoto($user->profile_photo_path);
            }

            // Store new photo
            $path = $this->storeProfilePhoto($file, $user->id);
            $user->profile_photo_path = $path;
        }

        $user->save();

        // Update data mahasiswa & status kelengkapan profil
        if ($user->role === 'mahasiswa') {
            $mhs = $user->getOrResolveMahasiswa();
            $targetNim = ! empty($validated['nim']) ? $validated['nim'] : null;

            if ($targetNim) {
                // Cek apakah ada record bank data lain dengan NIM ini yang belum terhubung ke user manapun (unlinked)
                $duplicateBankMhs = \App\Models\Mahasiswa::where('nim', $targetNim)
                    ->when($mhs, fn ($q) => $q->where('id', '!=', $mhs->id))
                    ->whereNull('user_id')
                    ->first();

                if ($duplicateBankMhs) {
                    if (! $mhs) {
                        // User belum punya record mhs, langsung link ke record bank data ini
                        $duplicateBankMhs->user_id = $user->id;
                        $mhs = $duplicateBankMhs;
                    } else {
                        // Salin atribut bank data jika di record pengguna saat ini masih kosong
                        $mhs->kelas_id = $mhs->kelas_id ?: $duplicateBankMhs->kelas_id;
                        $mhs->angkatan = $mhs->angkatan ?: $duplicateBankMhs->angkatan;
                        $mhs->nik = $mhs->nik ?: $duplicateBankMhs->nik;
                        $mhs->jk = $mhs->jk ?: $duplicateBankMhs->jk;
                        $mhs->nama_ibu = $mhs->nama_ibu ?: $duplicateBankMhs->nama_ibu;
                        $mhs->agama = $mhs->agama ?: $duplicateBankMhs->agama;
                        $mhs->tempat_lahir = $mhs->tempat_lahir ?: $duplicateBankMhs->tempat_lahir;
                        $mhs->tanggal_lahir = $mhs->tanggal_lahir ?: $duplicateBankMhs->tanggal_lahir;
                        $mhs->alamat = $mhs->alamat ?: $duplicateBankMhs->alamat;

                        // Pindahkan relasi kelompok jika ada di record duplikat
                        \Illuminate\Support\Facades\DB::table('kelompok_mahasiswa')
                            ->where('mahasiswa_id', $duplicateBankMhs->id)
                            ->update(['mahasiswa_id' => $mhs->id]);

                        $duplicateBankMhs->delete();
                    }
                }
            }

            if (! $mhs && $targetNim) {
                $mhs = \App\Models\Mahasiswa::create([
                    'user_id' => $user->id,
                    'nim' => $targetNim,
                    'nama_mahasiswa' => $user->nama_user,
                    'kelas_id' => $validated['kelas_id'] ?? null,
                ]);
            }

            if ($mhs) {
                if (! $mhs->user_id) {
                    $mhs->user_id = $user->id;
                }
                $mhs->nama_mahasiswa = $user->nama_user;
                $mhs->no_hp = $user->no_hp;
                $mhs->email_kampus = $user->email;

                if (! empty($validated['nim'])) {
                    $mhs->nim = $validated['nim'];
                }
                if (array_key_exists('kelas_id', $validated)) {
                    $mhs->kelas_id = $validated['kelas_id'];
                }
                if (array_key_exists('angkatan', $validated)) {
                    $mhs->angkatan = $validated['angkatan'];
                }
                if (array_key_exists('nik', $validated)) {
                    $mhs->nik = $validated['nik'];
                }
                if (array_key_exists('jk', $validated)) {
                    $mhs->jk = $validated['jk'];
                }
                if (array_key_exists('nama_ibu', $validated)) {
                    $mhs->nama_ibu = $validated['nama_ibu'];
                }
                if (array_key_exists('agama', $validated)) {
                    $mhs->agama = $validated['agama'];
                }
                if (array_key_exists('tempat_lahir', $validated)) {
                    $mhs->tempat_lahir = $validated['tempat_lahir'];
                }
                if (array_key_exists('tanggal_lahir', $validated)) {
                    $mhs->tanggal_lahir = $validated['tanggal_lahir'];
                }
                if (array_key_exists('alamat', $validated)) {
                    $mhs->alamat = $validated['alamat'];
                }

                $mhs->checkAndUpdateProfileCompletion();
                $mhs->save();
            }
        }

        return redirect()->route('profile.edit')->with('status', 'Profil berhasil diperbarui. Seluruh menu kini dapat diakses.');
    }

    /**
     * Store profile photo and return the path
     */
    private function storeProfilePhoto($file, int $userId): string
    {
        $extension = $file->extension() ?: $file->getClientOriginalExtension() ?: 'jpg';
        $fileName = 'profile_'.$userId.'_'.time().'.'.$extension;
        $file->storeAs('profile-photos', $fileName, 'public');

        return 'profile-photos/'.$fileName;
    }

    /**
     * Delete profile photo from storage
     */
    private function deleteProfilePhoto(?string $path): void
    {
        if (! $path) {
            return;
        }

        $fullPath = storage_path('app/public/'.$path);
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
}
