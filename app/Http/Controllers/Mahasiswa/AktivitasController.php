<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\AktivitasList;
use App\Models\AktivitasCard;
use App\Models\Mahasiswa;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AktivitasController extends Controller
{
    public function index()
    {
        [$kelompok, $periodeAktif] = $this->resolveKelompokSaya();

        if (!$kelompok) {
            return view('mahasiswa.aktivitas.index', [
                'lists'        => [],
                'kelompok'     => null,
                'periodeAktif' => $periodeAktif,
            ]);
        }

        $pid = $kelompok->periode_id ?? optional($periodeAktif)->id;

        // 1) Ambil semua list yang relevan (urut sesuai position)
        $lists = AktivitasList::where('kelompok_id', $kelompok->id)
            ->where('periode_id', $pid)
            ->orderBy('position')
            ->get();

        if ($lists->isEmpty()) {
            return view('mahasiswa.aktivitas.index', [
                'lists'        => [],
                'kelompok'     => $kelompok,
                'periodeAktif' => $periodeAktif,
            ]);
        }

        // 2) Ambil SEMUA card untuk list tersebut dalam 1 query, plus relasi createdBy & updatedBy
        $cards = AktivitasCard::with(['createdBy', 'updatedBy'])
            ->whereIn('list_aktivitas_id', $lists->pluck('id'))
            ->orderBy('list_aktivitas_id')
            ->orderBy('position')
            ->orderBy('id')
            ->get()
            ->map(function (AktivitasCard $c) {
                $hasUpdate = $c->updated_at && $c->created_at && !$c->updated_at->equalTo($c->created_at);
                $createdByName = optional($c->createdBy)->name;
                $updatedByName = optional($c->updatedBy)->name;
                // Gunakan foto profil data URL jika tersedia (sesuai topbar), fallback ke profile_photo_url
                $createdByAvatar = optional($c->createdBy)->profile_photo_data_url
                    ?: optional($c->createdBy)->profile_photo_url;
                $updatedByAvatar = optional($c->updatedBy)->profile_photo_data_url
                    ?: optional($c->updatedBy)->profile_photo_url;
                return [
                    // identitas & isi
                    'id'                => $c->uuid,
                    'list_id'           => $c->list_aktivitas_id, // untuk pengelompokan nanti
                    'tanggal_aktivitas' => optional($c->tanggal_aktivitas)->toDateString(),
                    'description'       => $c->description,
                    'created_by_name' => $createdByName,
                    'updated_by_name' => $updatedByName,
                    'created_at_human' => ($c->created_at ? $c->created_at->locale('id')->diffForHumans() : null),
                    'updated_at_human' => ($c->updated_at ? $c->updated_at->locale('id')->diffForHumans() : null),
                    'has_update' => $hasUpdate,
                    'created_by_avatar' => $createdByAvatar,
                    'updated_by_avatar' => $updatedByAvatar,
                ];
            })
            ->groupBy('list_id'); // => koleksi: key = list_aktivitas_id

        // 3) Susun payload lists (array) + inject cards masing-masing list
        $listsPayload = $lists->map(function ($list) use ($cards) {
            $listCards = $cards->get($list->id, collect())->values()->all();

            return [
                'id'                 => $list->uuid,
                'title'              => $list->name,
                'rentang_tanggal'    => $list->rentang_tanggal,
                'link_drive_logbook' => $list->link_drive_logbook,
                'status_evaluasi'    => $list->status_evaluasi,
                'cards'              => $listCards,
            ];
        })->values()->all();

        return view('mahasiswa.aktivitas.index', [
            'lists'        => $listsPayload,
            'kelompok'     => $kelompok,
            'periodeAktif' => $periodeAktif,
        ]);
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'card_id'  => 'required|string',
            'to_list'  => 'required|string',
            'position' => 'required|integer|min:0',
        ]);

        [$kelompok] = $this->resolveKelompokSaya();
        if (!$kelompok) {
            return response()->json(['status' => 'no_group'], 403);
        }

        $card = AktivitasCard::where('uuid', $data['card_id'])
            ->where('kelompok_id', $kelompok->id)
            ->first();

        if (!$card) {
            return response()->json(['status' => 'not_found'], 404);
        }

        $this->authorize('update', $card);

        $toList = AktivitasList::where('uuid', $data['to_list'])->first();
        if (!$toList || $toList->kelompok_id !== $kelompok->id) {
            return response()->json(['status' => 'invalid_list'], 422);
        }

        $newPos = (int) $data['position'];

        DB::transaction(function () use ($card, $toList, $newPos) {
            $fromListId = $card->list_aktivitas_id;
            $oldPos     = $card->position;

            if ($fromListId === $toList->id) {
                // Geser dalam list yang sama
                if ($newPos === $oldPos) return;

                if ($newPos > $oldPos) {
                    AktivitasCard::where('list_aktivitas_id', $fromListId)
                        ->whereBetween('position', [$oldPos + 1, $newPos])
                        ->decrement('position');
                } else {
                    AktivitasCard::where('list_aktivitas_id', $fromListId)
                        ->whereBetween('position', [$newPos, $oldPos - 1])
                        ->increment('position');
                }
            } else {
                // Pindah list
                AktivitasCard::where('list_aktivitas_id', $fromListId)
                    ->where('position', '>', $oldPos)
                    ->decrement('position');

                AktivitasCard::where('list_aktivitas_id', $toList->id)
                    ->where('position', '>=', $newPos)
                    ->increment('position');
            }

            $card->update([
                'list_aktivitas_id' => $toList->id,
                'position'          => $newPos,
            ]);
        });

        return response()->json(['status' => 'ok']);
    }

    private function resolveKelompokSaya(): array
    {
        $mhs = Mahasiswa::where('user_id', Auth::id())->first();
        $periodeAktif = Periode::where('status_periode', 'Aktif')
            ->orderByDesc('created_at')
            ->first();

        $kelompokSaya = null;

        if ($mhs) {
            if ($periodeAktif) {
                $kelompokSaya = $mhs->kelompoks()
                    ->wherePivot('periode_id', $periodeAktif->id)
                    ->first();
            }
            if (!$kelompokSaya) {
                $kelompokSaya = $mhs->kelompoks()
                    ->latest('kelompok_mahasiswa.created_at')
                    ->first();
            }
        }

        return [$kelompokSaya, $periodeAktif];
    }
}
