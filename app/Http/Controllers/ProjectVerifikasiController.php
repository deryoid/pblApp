<?php

namespace App\Http\Controllers;

use App\Models\ProjectCard;
use App\Models\ProjectDosenPenilai;
use App\Models\ProjectVerifikasiDosen;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectVerifikasiController extends Controller
{
    /**
     * Dapatkan status verifikasi proposal dan daftar dosen yang terlibat.
     */
    public function getStatus(ProjectCard $card): JsonResponse
    {
        $card->syncVerifikasiDosen();

        $involved = $card->getInvolvedDosen();
        $user = Auth::user();

        $isUserInvolved = $user ? $involved->contains(fn ($i) => $i->dosen_id == $user->id) : false;
        $userVerifikasi = $user ? $involved->firstWhere('dosen_id', $user->id) : null;
        $isAdmin = $user ? $user->isAdmin() : false;

        $totalInvolved = $involved->count();
        $accCount = $involved->where('status', 'acc')->count();
        $revisiCount = $involved->where('status', 'revisi')->count();
        $pendingCount = $involved->where('status', 'pending')->count();

        $availableDosen = $isAdmin
            ? User::whereIn('role', ['dosen', 'evaluator', 'admin'])
                ->orderBy('nama_user')
                ->get(['id', 'nama_user', 'username', 'email'])
            : [];

        return response()->json([
            'success' => true,
            'card' => [
                'id' => $card->id,
                'uuid' => $card->uuid,
                'title' => $card->title,
                'status_proposal' => $card->status_proposal ?? 'pending',
                'proposal_url' => $card->proposal_url,
                'proposal_file_path' => $card->proposal_file_path,
            ],
            'involved_dosen' => $involved,
            'stats' => [
                'total' => $totalInvolved,
                'acc' => $accCount,
                'revisi' => $revisiCount,
                'pending' => $pendingCount,
                'percent' => $totalInvolved > 0 ? (int) round(($accCount / $totalInvolved) * 100) : 0,
                'is_all_acc' => $totalInvolved > 0 && $accCount === $totalInvolved,
            ],
            'can_acc' => $isAdmin || $isUserInvolved,
            'user_verifikasi' => $userVerifikasi,
            'is_admin' => $isAdmin,
            'available_dosen' => $availableDosen,
        ]);
    }

    /**
     * Dosen yang terlibat atau Admin memberikan verifikasi (ACC, Revisi, Pending) pada proposal proyek.
     */
    public function verifikasiAcc(Request $request, ProjectCard $card): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:acc,revisi,pending',
            'catatan' => 'nullable|string|max:1000',
            'dosen_id' => 'nullable|exists:users,id',
        ]);

        $user = Auth::user();
        $dosenId = $validated['dosen_id'] ?? $user->id;

        // Validasi otoritas: Hanya admin atau dosen bersangkutan yang berhak
        if ($dosenId != $user->id && ! $user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak untuk memverifikasi atas nama dosen lain.',
            ], 403);
        }

        // Pastikan dosen tersebut memang terlibat pada proyek ini
        $involved = $card->getInvolvedDosen();
        $lecturerRecord = $involved->firstWhere('dosen_id', $dosenId);

        if (! $lecturerRecord && ! $user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda bukan dosen yang terlibat pada proyek ini.',
            ], 403);
        }

        $peran = $lecturerRecord?->peran ?? ($user->isAdmin() ? 'Administrator PBL' : 'Dosen Pengampu');

        // Update atau buat record verifikasi
        ProjectVerifikasiDosen::updateOrCreate(
            [
                'project_card_id' => $card->id,
                'dosen_id' => $dosenId,
            ],
            [
                'peran' => $peran,
                'status' => $validated['status'],
                'catatan' => $validated['catatan'] ?? null,
                'verified_at' => $validated['status'] === 'acc' ? now() : null,
            ]
        );

        // Perbarui status_proposal proyek secara otomatis
        $newStatusProposal = $card->updateStatusProposalAcc();

        $message = match ($validated['status']) {
            'acc' => 'Proposal proyek berhasil di-ACC (Disetujui).',
            'revisi' => 'Permintaan revisi proposal berhasil dikirim ke mahasiswa.',
            default => 'Status verifikasi proposal diubah menjadi Menunggu (Pending).',
        };

        return response()->json([
            'success' => true,
            'message' => $message,
            'status' => $validated['status'],
            'status_proposal' => $newStatusProposal,
            'involved_dosen' => $card->getInvolvedDosen(),
        ]);
    }

    /**
     * Admin menambahkan dosen ke dalam proyek (sebagai Penilai atau Pembimbing/Penentu).
     */
    public function assignDosen(Request $request, ProjectCard $card): JsonResponse
    {
        $user = Auth::user();
        if (! $user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Administrator yang dapat menambahkan Dosen ke dalam proyek.',
            ], 403);
        }

        $validated = $request->validate([
            'dosen_id' => 'required|exists:users,id',
            'peran' => 'required|string|in:penilai,pembimbing,penentu',
            'mata_kuliah_id' => 'nullable|exists:mata_kuliah,id',
        ]);

        $dosen = User::findOrFail($validated['dosen_id']);

        if (in_array($validated['peran'], ['pembimbing', 'penentu'])) {
            $card->dosen_penentu_id = $dosen->id;
            $card->save();
            $message = "Dosen {$dosen->nama_user} berhasil ditugaskan sebagai Pembimbing Proyek.";
        } else {
            ProjectDosenPenilai::firstOrCreate(
                [
                    'project_card_id' => $card->id,
                    'dosen_id' => $dosen->id,
                    'mata_kuliah_id' => $validated['mata_kuliah_id'] ?? null,
                ],
                [
                    'status_penilaian' => 'belum',
                    'assigned_by' => $user->id,
                ]
            );
            $message = "Dosen {$dosen->nama_user} berhasil ditugaskan sebagai Dosen Penilai Proyek.";
        }

        $card->syncVerifikasiDosen();
        $card->updateStatusProposalAcc();

        return response()->json([
            'success' => true,
            'message' => $message,
            'involved_dosen' => $card->getInvolvedDosen(),
        ]);
    }

    /**
     * Admin menghapus penugasan dosen dari proyek (sebagai Penilai atau Pembimbing).
     */
    public function removeDosen(Request $request, ProjectCard $card): JsonResponse
    {
        $user = Auth::user();
        if (! $user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Administrator yang dapat menghapus Dosen dari proyek.',
            ], 403);
        }

        $validated = $request->validate([
            'dosen_id' => 'required|exists:users,id',
            'peran_tipe' => 'nullable|string|in:penilai,penentu,pembimbing',
            'penilai_id' => 'nullable|integer',
        ]);

        $dosenId = (int) $validated['dosen_id'];
        $peranTipe = $validated['peran_tipe'] ?? null;

        if ($peranTipe === 'pembimbing' || $peranTipe === 'penentu') {
            if ($card->dosen_penentu_id == $dosenId) {
                $card->dosen_penentu_id = null;
                $card->save();
            }
        } elseif (! empty($validated['penilai_id'])) {
            ProjectDosenPenilai::where('id', $validated['penilai_id'])
                ->where('project_card_id', $card->id)
                ->delete();
        } else {
            // Hapus dari dosen penilai jika ada
            ProjectDosenPenilai::where('project_card_id', $card->id)
                ->where('dosen_id', $dosenId)
                ->delete();

            if ($card->dosen_penentu_id == $dosenId && ($peranTipe === null || $peranTipe === 'penentu')) {
                $card->dosen_penentu_id = null;
                $card->save();
            }
        }

        // Cek apakah dosen ini masih memiliki keterlibatan lain pada proyek
        $card->load('mataKuliahs.dosenPengampu', 'dosenPenilai', 'dosenPenentu');
        $stillInvolved = $card->mataKuliahs->flatMap(fn ($mk) => $mk->dosenPengampu)->contains('id', $dosenId)
            || $card->dosenPenilai->contains('dosen_id', $dosenId)
            || ($card->dosen_penentu_id == $dosenId);

        if (! $stillInvolved) {
            ProjectVerifikasiDosen::where('project_card_id', $card->id)
                ->where('dosen_id', $dosenId)
                ->delete();
        }

        $card->updateStatusProposalAcc();

        return response()->json([
            'success' => true,
            'message' => 'Penugasan dosen pada proyek berhasil dihapus.',
            'involved_dosen' => $card->getInvolvedDosen(),
        ]);
    }

    /**
     * Admin menambahkan dosen penilai ke dalam proyek.
     */
    public function assignDosenPenilai(Request $request, ProjectCard $card): JsonResponse
    {
        $user = Auth::user();
        if (! $user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Administrator yang dapat menambahkan Dosen Penilai.',
            ], 403);
        }

        $validated = $request->validate([
            'dosen_id' => 'required|exists:users,id',
            'mata_kuliah_id' => 'nullable|exists:mata_kuliah,id',
        ]);

        $penilai = ProjectDosenPenilai::firstOrCreate(
            [
                'project_card_id' => $card->id,
                'dosen_id' => $validated['dosen_id'],
                'mata_kuliah_id' => $validated['mata_kuliah_id'] ?? null,
            ],
            [
                'status_penilaian' => 'belum',
                'assigned_by' => $user->id,
            ]
        );

        $card->syncVerifikasiDosen();
        $card->updateStatusProposalAcc();

        return response()->json([
            'success' => true,
            'message' => 'Dosen penilai berhasil ditugaskan ke proyek.',
            'penilai' => $penilai->load('dosen', 'mataKuliah'),
            'involved_dosen' => $card->getInvolvedDosen(),
        ]);
    }

    /**
     * Admin menghapus dosen penilai dari proyek.
     */
    public function removeDosenPenilai(ProjectCard $card, ProjectDosenPenilai $penilai): JsonResponse
    {
        $user = Auth::user();
        if (! $user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Administrator yang dapat menghapus Dosen Penilai.',
            ], 403);
        }

        if ($penilai->project_card_id !== $card->id) {
            return response()->json([
                'success' => false,
                'message' => 'Data penugasan penilai tidak sesuai dengan proyek ini.',
            ], 400);
        }

        $dosenId = $penilai->dosen_id;
        $penilai->delete();

        // Cek apakah dosen ini masih memiliki keterlibatan lain pada proyek
        $card->load('mataKuliahs.dosenPengampu', 'dosenPenilai', 'dosenPenentu');
        $stillInvolved = $card->mataKuliahs->flatMap(fn ($mk) => $mk->dosenPengampu)->contains('id', $dosenId)
            || $card->dosenPenilai->contains('dosen_id', $dosenId)
            || ($card->dosen_penentu_id == $dosenId);

        if (! $stillInvolved) {
            ProjectVerifikasiDosen::where('project_card_id', $card->id)
                ->where('dosen_id', $dosenId)
                ->delete();
        }

        $card->updateStatusProposalAcc();

        return response()->json([
            'success' => true,
            'message' => 'Dosen penilai berhasil dihapus dari proyek.',
            'involved_dosen' => $card->getInvolvedDosen(),
        ]);
    }
}
