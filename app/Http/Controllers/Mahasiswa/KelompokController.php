<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Kelompok;
use App\Models\KelompokJoinRequest;
use App\Models\Mahasiswa;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class KelompokController extends Controller
{
    /**
     * Tampilkan halaman kelompok mahasiswa (detail kelompok, formulir invite, dan undangan masuk).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $mahasiswa = $user->mahasiswa;

        $activePeriode = Periode::where('status_periode', 'Aktif')->orderByDesc('id')->first();
        $periodeId = $activePeriode?->id;

        $kelompok = null;
        if ($mahasiswa) {
            if ($periodeId) {
                $kelompok = $mahasiswa->kelompoks()
                    ->wherePivot('periode_id', $periodeId)
                    ->with(['mahasiswas.user', 'periode'])
                    ->first();
            }
            if (! $kelompok) {
                $kelompok = $mahasiswa->kelompoks()
                    ->with(['mahasiswas.user', 'periode'])
                    ->latest('kelompok_mahasiswa.created_at')
                    ->first();
            }
        }

        if ($kelompok) {
            $isKetua = ($kelompok->ketua?->id === $mahasiswa?->id);

            // Undangan terkirim oleh kelompok ini
            $sentInvitations = KelompokJoinRequest::where('kelompok_id', $kelompok->id)
                ->where('type', 'invite')
                ->with(['mahasiswa.user', 'mahasiswa.kelas', 'invitedBy'])
                ->latest()
                ->get();

            // Mahasiswa yang tersedia untuk diundang (belum memiliki kelompok pada periode ini)
            $availableStudents = collect();
            if (! $kelompok->isFull()) {
                $takenMahasiswaIds = DB::table('kelompok_mahasiswa')
                    ->where('periode_id', $kelompok->periode_id)
                    ->pluck('mahasiswa_id');

                $pendingInvitedIds = KelompokJoinRequest::where('kelompok_id', $kelompok->id)
                    ->where('status', 'pending')
                    ->pluck('mahasiswa_id');

                $availableStudents = Mahasiswa::with(['user', 'kelas'])
                    ->whereNotIn('id', $takenMahasiswaIds)
                    ->whereNotIn('id', $pendingInvitedIds)
                    ->orderBy('nama_mahasiswa')
                    ->get();
            }

            return view('mahasiswa.kelompok.index', compact(
                'kelompok',
                'activePeriode',
                'isKetua',
                'sentInvitations',
                'availableStudents'
            ));
        }

        // Jika mahasiswa belum memiliki kelompok: tampilkan undangan masuk dan formulir buat kelompok baru
        $incomingInvitations = collect();
        if ($mahasiswa) {
            $incomingInvitations = KelompokJoinRequest::where('mahasiswa_id', $mahasiswa->id)
                ->where('type', 'invite')
                ->where('status', 'pending')
                ->with(['kelompok.periode', 'kelompok.mahasiswas.user', 'invitedBy'])
                ->latest()
                ->get();
        }

        return view('mahasiswa.kelompok.index', compact(
            'kelompok',
            'activePeriode',
            'incomingInvitations'
        ));
    }

    /**
     * Buat kelompok baru oleh mahasiswa.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $mahasiswa = $user->mahasiswa;

        if (! $mahasiswa) {
            Alert::toast('Data profil mahasiswa tidak ditemukan.', 'error');

            return back();
        }

        $activePeriode = Periode::where('status_periode', 'Aktif')->orderByDesc('id')->first();
        if (! $activePeriode) {
            Alert::toast('Tidak ada periode PBL yang sedang aktif.', 'warning');

            return back();
        }

        // Cek apakah mahasiswa sudah memiliki kelompok di periode aktif
        $exists = $mahasiswa->kelompoks()->wherePivot('periode_id', $activePeriode->id)->exists();
        if ($exists) {
            Alert::toast('Anda sudah memiliki kelompok pada periode aktif ini.', 'warning');

            return back();
        }

        $validated = $request->validate([
            'nama_kelompok' => 'required|string|max:100',
            'skema_kategori' => 'required|string|in:Internal,External,Hibah/Penelitian,Lomba',
            'semester' => 'nullable|integer|min:1|max:8',
            'link_drive' => 'nullable|url|max:255',
        ], [
            'skema_kategori.required' => 'Setiap kelompok wajib memilih 1 skema kategori (Internal, External, Hibah/Penelitian, Lomba).',
            'skema_kategori.in' => 'Skema kategori harus salah satu dari: Internal, External, Hibah/Penelitian, Lomba.',
        ]);

        DB::transaction(function () use ($validated, $activePeriode, $mahasiswa) {
            $kelompok = Kelompok::create([
                'nama_kelompok' => $validated['nama_kelompok'],
                'skema_kategori' => $validated['skema_kategori'],
                'periode_id' => $activePeriode->id,
                'semester' => $validated['semester'] ?? $mahasiswa->semester ?? 1,
                'link_drive' => $validated['link_drive'] ?? null,
                'max_members' => 4,
                'status_bergabung' => 'terbuka',
            ]);

            // Pembuat kelompok otomatis menjadi Ketua
            $kelompok->mahasiswas()->attach($mahasiswa->id, [
                'role' => 'Ketua',
                'periode_id' => $activePeriode->id,
            ]);
        });

        Alert::toast('Kelompok berhasil dibuat. Anda sekarang adalah Ketua Kelompok.', 'success');

        return redirect()->route('mahasiswa.kelompok.index');
    }

    /**
     * Kirim undangan ke mahasiswa lain untuk bergabung ke kelompok.
     */
    public function invite(Request $request)
    {
        $user = Auth::user();
        $mahasiswa = $user->mahasiswa;

        $activePeriode = Periode::where('status_periode', 'Aktif')->orderByDesc('id')->first();
        if (! $activePeriode) {
            Alert::toast('Periode aktif tidak ditemukan.', 'error');

            return back();
        }

        $kelompok = $mahasiswa?->kelompoks()->wherePivot('periode_id', $activePeriode->id)->first();
        if (! $kelompok) {
            Alert::toast('Anda belum tergabung dalam kelompok periode ini.', 'error');

            return back();
        }

        if ($kelompok->isFull()) {
            Alert::toast('Kelompok sudah mencapai batas maksimum anggota.', 'warning');

            return back();
        }

        $validated = $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswa,id',
            'pesan' => 'nullable|string|max:500',
        ]);

        $targetMahasiswa = Mahasiswa::findOrFail($validated['mahasiswa_id']);

        // Cek apakah target sudah punya kelompok di periode ini
        $isTargetGrouped = $targetMahasiswa->kelompoks()->wherePivot('periode_id', $kelompok->periode_id)->exists();
        if ($isTargetGrouped) {
            Alert::toast('Mahasiswa yang dipilih sudah tergabung dalam kelompok lain.', 'warning');

            return back();
        }

        // Cek apakah sudah ada undangan pending ke mahasiswa ini
        $alreadyInvited = KelompokJoinRequest::where('kelompok_id', $kelompok->id)
            ->where('mahasiswa_id', $targetMahasiswa->id)
            ->where('status', 'pending')
            ->exists();

        if ($alreadyInvited) {
            Alert::toast('Undangan untuk mahasiswa ini sudah pernah dikirim dan masih menunggu konfirmasi.', 'info');

            return back();
        }

        KelompokJoinRequest::create([
            'kelompok_id' => $kelompok->id,
            'mahasiswa_id' => $targetMahasiswa->id,
            'type' => 'invite',
            'status' => 'pending',
            'pesan' => $validated['pesan'] ?? 'Halo, kami mengundang Anda untuk bergabung ke kelompok PBL kami.',
            'invited_by' => $user->id,
        ]);

        Alert::toast('Undangan berhasil dikirim ke '.$targetMahasiswa->nama_mahasiswa.'.', 'success');

        return redirect()->route('mahasiswa.kelompok.index');
    }

    /**
     * Terima undangan bergabung kelompok.
     */
    public function acceptInvite(KelompokJoinRequest $request)
    {
        $user = Auth::user();
        $mahasiswa = $user->mahasiswa;

        if (! $mahasiswa || $request->mahasiswa_id !== $mahasiswa->id) {
            Alert::toast('Akses tidak valid.', 'error');

            return back();
        }

        if ($request->status !== 'pending') {
            Alert::toast('Undangan ini sudah diproses sebelumnya.', 'info');

            return back();
        }

        $kelompok = $request->kelompok;
        if (! $kelompok) {
            Alert::toast('Data kelompok tidak ditemukan.', 'error');

            return back();
        }

        // Cek apakah kelompok sudah penuh
        if ($kelompok->isFull()) {
            $request->update(['status' => 'rejected', 'responded_by' => $user->id, 'responded_at' => now()]);
            Alert::toast('Mohon maaf, kelompok tersebut sudah penuh.', 'warning');

            return back();
        }

        // Cek apakah mahasiswa sudah punya kelompok lain
        $alreadyGrouped = $mahasiswa->kelompoks()->wherePivot('periode_id', $kelompok->periode_id)->exists();
        if ($alreadyGrouped) {
            Alert::toast('Anda sudah tergabung dalam kelompok lain pada periode ini.', 'warning');

            return back();
        }

        DB::transaction(function () use ($kelompok, $mahasiswa, $request, $user) {
            // Tambahkan ke pivot kelompok_mahasiswa
            $kelompok->mahasiswas()->attach($mahasiswa->id, [
                'role' => 'Anggota',
                'periode_id' => $kelompok->periode_id,
            ]);

            // Setujui undangan ini
            $request->update([
                'status' => 'approved',
                'responded_by' => $user->id,
                'responded_at' => now(),
            ]);

            // Batalkan semua undangan pending lain untuk mahasiswa ini
            KelompokJoinRequest::where('mahasiswa_id', $mahasiswa->id)
                ->where('id', '!=', $request->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'canceled',
                    'responded_by' => $user->id,
                    'responded_at' => now(),
                ]);

            // Jika kelompok sekarang penuh, perbarui status_bergabung
            if ($kelompok->isFull()) {
                $kelompok->update(['status_bergabung' => 'penuh']);
            }
        });

        Alert::toast('Selamat! Anda telah resmi bergabung ke kelompok '.$kelompok->nama_kelompok.'.', 'success');

        return redirect()->route('mahasiswa.kelompok.index');
    }

    /**
     * Tolak undangan bergabung kelompok.
     */
    public function rejectInvite(KelompokJoinRequest $request)
    {
        $user = Auth::user();
        $mahasiswa = $user->mahasiswa;

        if (! $mahasiswa || $request->mahasiswa_id !== $mahasiswa->id) {
            Alert::toast('Akses tidak valid.', 'error');

            return back();
        }

        $request->update([
            'status' => 'rejected',
            'responded_by' => $user->id,
            'responded_at' => now(),
        ]);

        Alert::toast('Undangan kelompok telah ditolak.', 'info');

        return redirect()->route('mahasiswa.kelompok.index');
    }

    /**
     * Batalkan undangan yang telah dikirim oleh kelompok.
     */
    public function cancelInvite(KelompokJoinRequest $request)
    {
        $user = Auth::user();
        $mahasiswa = $user->mahasiswa;

        $kelompok = $request->kelompok;
        if (! $kelompok || ! $kelompok->isMember($mahasiswa?->id)) {
            Alert::toast('Anda tidak memiliki izin membatalkan undangan ini.', 'error');

            return back();
        }

        $request->delete();

        Alert::toast('Undangan berhasil dibatalkan.', 'success');

        return redirect()->route('mahasiswa.kelompok.index');
    }
}
