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
     * Dapatkan daftar dosen yang terlibat dan data proyek.
     */
    public function getStatus(ProjectCard $card): JsonResponse
    {
        $involved = $card->getInvolvedDosen();
        $user = Auth::user();
        $isAdmin = $user ? $user->isAdmin() : false;

        $availableDosen = $isAdmin
            ? User::whereIn('role', ['dosen', 'admin'])
                ->orderBy('nama_user')
                ->get(['id', 'nama_user', 'username', 'email', 'role'])
            : [];

        return response()->json([
            'success' => true,
            'card' => [
                'id' => $card->id,
                'uuid' => $card->uuid,
                'title' => $card->title,
                'proposal_url' => $card->proposal_url,
                'proposal_file_path' => $card->proposal_file_path,
            ],
            'involved_dosen' => $involved,
            'is_admin' => $isAdmin,
            'available_dosen' => $availableDosen,
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

        return response()->json([
            'success' => true,
            'message' => 'Penugasan dosen pada proyek berhasil dihapus.',
            'involved_dosen' => $card->getInvolvedDosen(),
        ]);
    }

    /**
     * Admin menghubungkan mata kuliah ke dalam proyek mahasiswa.
     */
    public function attachMataKuliah(Request $request, ProjectCard $card): JsonResponse
    {
        $user = Auth::user();
        if (! $user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Administrator yang dapat menghubungkan mata kuliah ke dalam proyek.',
            ], 403);
        }

        $validated = $request->validate([
            'mata_kuliah_id' => 'required|exists:mata_kuliah,id',
        ]);

        $mk = \App\Models\MataKuliah::with('dosenPengampu')->findOrFail($validated['mata_kuliah_id']);

        if (! $card->mataKuliahs()->where('mata_kuliah.id', $mk->id)->exists()) {
            $card->mataKuliahs()->attach($mk->id);
        }

        return response()->json([
            'success' => true,
            'message' => "Mata kuliah {$mk->nama_matkul} berhasil dihubungkan ke proyek.",
            'mata_kuliahs' => $card->fresh('mataKuliahs.dosenPengampu')->mataKuliahs,
            'involved_dosen' => $card->getInvolvedDosen(),
        ]);
    }

    /**
     * Admin melepaskan mata kuliah dari proyek mahasiswa.
     */
    public function detachMataKuliah(ProjectCard $card, $mataKuliah): JsonResponse
    {
        $user = Auth::user();
        if (! $user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Administrator yang dapat melepaskan mata kuliah dari proyek.',
            ], 403);
        }

        $mk = $mataKuliah instanceof \App\Models\MataKuliah
            ? $mataKuliah
            : \App\Models\MataKuliah::where('uuid', $mataKuliah)->orWhere('id', $mataKuliah)->firstOrFail();

        $card->mataKuliahs()->detach($mk->id);

        $card->load('mataKuliahs.dosenPengampu', 'dosenPenilai', 'dosenPenentu');

        return response()->json([
            'success' => true,
            'message' => "Mata kuliah {$mk->nama_matkul} berhasil dilepas dari proyek.",
            'mata_kuliahs' => $card->fresh('mataKuliahs.dosenPengampu')->mataKuliahs,
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

        return response()->json([
            'success' => true,
            'message' => 'Dosen penilai berhasil dihapus dari proyek.',
            'involved_dosen' => $card->getInvolvedDosen(),
        ]);
    }
}
