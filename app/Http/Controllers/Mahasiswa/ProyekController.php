<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Periode;
use App\Models\ProjectCard;
use App\Models\ProjectList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProyekController extends Controller
{
    public function index()
    {
        [$kelompok, $periodeAktif] = $this->resolveKelompokSaya();

        if (! $kelompok) {
            $board = null;

            return view('mahasiswa.proyek.index', [
                'kelompok' => null,
                'periodeAktif' => $periodeAktif,
                'board' => null,
                'lists' => [],
                'mataKuliahList' => collect(),
                'kelompokSemester' => 1,
            ]);
        }

        $mhs = Mahasiswa::resolveForUser(Auth::user());
        $kelompokSemester = (int) ($kelompok->semester ?? ($mhs->semester ?? 1));

        $mataKuliahList = MataKuliah::with('dosenPengampu')
            ->where('is_active', true)
            ->orderBy('semester')
            ->orderBy('nama_matkul')
            ->get();

        // Muat list (kolom) dinamis dan kartu per list berdasarkan kelompok+periode
        $pid = $kelompok->periode_id ?? optional($periodeAktif)->id;
        $lists = ProjectList::where('kelompok_id', $kelompok->id)
            ->when($pid, fn ($q) => $q->where(fn ($sub) => $sub->where('periode_id', $pid)->orWhereNull('periode_id')))
            ->orderBy('position')
            ->get()
            ->map(function ($list) use ($kelompok) {
                $cards = ProjectCard::with(['createdBy', 'updatedBy', 'mataKuliahs.dosenPengampu'])
                    ->where('list_id', $list->id)
                    ->where('kelompok_id', $kelompok->id)
                    ->orderBy('position')
                    ->orderBy('id')
                    ->get()
                    ->map(function (ProjectCard $c) {
                        $hasUpdate = $c->updated_at && $c->created_at && ! $c->updated_at->equalTo($c->created_at);
                        $createdByName = optional($c->createdBy)->nama_user;
                        $updatedByName = optional($c->updatedBy)->nama_user;
                        // Gunakan foto profil data URL jika tersedia (sesuai topbar), fallback ke profile_photo_url
                        $createdByAvatar = optional($c->createdBy)->profile_photo_data_url
                            ?: optional($c->createdBy)->profile_photo_url;
                        $updatedByAvatar = optional($c->updatedBy)->profile_photo_data_url
                            ?: optional($c->updatedBy)->profile_photo_url;

                        return [
                            'id' => $c->uuid,
                            'title' => $c->title,
                            'labels' => $c->labels ?? [],
                            // tanggal baru + fallback due lama
                            'tanggal_mulai' => optional($c->tanggal_mulai)->toDateString(),
                            'tanggal_selesai' => optional($c->tanggal_selesai)->toDateString() ?: optional($c->due_date)->toDateString(),
                            // atribut tambahan skema revisi (Opsi A)
                            'nama_mitra' => $c->nama_mitra,
                            'kontak_mitra' => $c->kontak_mitra,
                            'skema_pbl' => $c->skema_pbl,
                            'biaya_barang' => $c->biaya_barang ?? 0,
                            'biaya_jasa' => $c->biaya_jasa ?? 0,
                            'status_proyek' => $c->status_proyek,
                            'link_drive_proyek' => $c->link_drive_proyek,
                            'proposal_file_path' => $c->proposal_file_path,
                            'proposal_url' => $c->proposal_url,
                            'status_proposal' => $c->status_proposal,
                            'created_by_name' => $createdByName,
                            'updated_by_name' => $updatedByName,
                            'created_at_human' => ($c->created_at ? $c->created_at->locale('id')->diffForHumans() : null),
                            'updated_at_human' => ($c->updated_at ? $c->updated_at->locale('id')->diffForHumans() : null),
                            'has_update' => $hasUpdate,
                            'created_by_avatar' => $createdByAvatar,
                            'updated_by_avatar' => $updatedByAvatar,
                            // counters & lainnya
                            'comments' => $c->comments_count ?? 0,
                            'attachments' => $c->attachments_count ?? 0,
                            'progress' => $c->progress ?? 0,
                            'description' => $c->description,
                            'mahasiswa_id' => $c->created_by,
                            'mahasiswa_nama' => $createdByName,
                            'kendala' => $c->kendala,
                            'catatan' => $c->catatan,
                            'mata_kuliah_ids' => $c->mataKuliahs->pluck('id')->values()->toArray(),
                            'mata_kuliahs' => $c->mataKuliahs->map(fn ($mk) => [
                                'id' => $mk->id,
                                'kode_matkul' => $mk->kode_matkul,
                                'nama_matkul' => $mk->nama_matkul,
                                'semester' => $mk->semester,
                                'sks' => $mk->sks,
                                'dosen_pengampu' => $mk->dosenPengampu->pluck('nama_user')->join(', '),
                                'dosen_list' => $mk->dosenPengampu->map(fn ($d) => [
                                    'id' => $d->id,
                                    'nama_user' => $d->nama_user,
                                ])->values()->toArray(),
                            ])->values()->toArray(),
                        ];
                    })->toArray();

                return [
                    'id' => $list->uuid,
                    'title' => $list->name,
                    'cards' => $cards,
                ];
            })->toArray();

        return view('mahasiswa.proyek.index', [
            'lists' => $lists,
            'kelompok' => $kelompok,
            'periodeAktif' => $periodeAktif,
            'mataKuliahList' => $mataKuliahList,
            'kelompokSemester' => $kelompokSemester,
        ]);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'card_id' => 'required|string',
            'to_list' => 'required|string',
            'position' => 'required|integer|min:0',
        ]);

        [$kelompok] = $this->resolveKelompokSaya();
        if (! $kelompok) {
            return response()->json(['status' => 'no_group'], 403);
        }

        $card = ProjectCard::where('kelompok_id', $kelompok->id)
            ->where(fn ($q) => $q->where('uuid', $request->card_id)->orWhere('id', $request->card_id))
            ->first();
        if (! $card) {
            return response()->json(['status' => 'not_found'], 404);
        }
        $this->authorize('update', $card);

        $fromListId = $card->list_id;
        $toList = \App\Models\ProjectList::where('kelompok_id', $kelompok->id)
            ->where(fn ($q) => $q->where('uuid', $request->to_list)->orWhere('id', $request->to_list))
            ->first();
        if (! $toList) {
            return response()->json(['status' => 'invalid_list'], 422);
        }
        $newPos = (int) $request->position;

        DB::transaction(function () use ($card, $fromListId, $toList, $newPos) {
            $oldPos = $card->position;
            if ($fromListId === $toList->id) {
                if ($newPos === $oldPos) {
                    return;
                }
                if ($newPos > $oldPos) {
                    ProjectCard::where('list_id', $fromListId)
                        ->whereBetween('position', [$oldPos + 1, $newPos])
                        ->decrement('position');
                } else {
                    ProjectCard::where('list_id', $fromListId)
                        ->whereBetween('position', [$newPos, $oldPos - 1])
                        ->increment('position');
                }
            } else {
                ProjectCard::where('list_id', $fromListId)
                    ->where('position', '>', $oldPos)
                    ->decrement('position');
                ProjectCard::where('list_id', $toList->id)
                    ->where('position', '>=', $newPos)
                    ->increment('position');
            }

            $card->update([
                'list_id' => $toList->id,
                'position' => $newPos,
            ]);
        });

        return response()->json(['status' => 'ok']);
    }

    private function resolveKelompokSaya(): array
    {
        $mhs = Mahasiswa::resolveForUser(Auth::user());
        $periodeAktif = Periode::getLatestActive();
        $kelompokSaya = null;
        if ($mhs && $periodeAktif) {
            $kelompokSaya = $mhs->kelompoks()->wherePivot('periode_id', $periodeAktif->id)->first();
        } elseif ($mhs) {
            $kelompokSaya = $mhs->kelompoks()->latest('kelompok.id')->first();
        }

        return [$kelompokSaya, $periodeAktif];
    }
}
