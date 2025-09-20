<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Periode;
use App\Models\ProjectCard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ProyekController extends Controller
{
    public function index()
    {
        [$kelompok, $periodeAktif] = $this->resolveKelompokSaya();

        if (!$kelompok) {
            $board = null;
            return view('mahasiswa.proyek.index', compact('kelompok', 'periodeAktif', 'board'));
        }

        // Muat list (kolom) dinamis dan kartu per list berdasarkan kelompok+periode
        $pid = $kelompok->periode_id ?? optional($periodeAktif)->id;
        $lists = \App\Models\ProjectList::where('kelompok_id', $kelompok->id)
            ->where('periode_id', $pid)
            ->orderBy('position')
            ->get()
            ->map(function ($list) {
                $cards = ProjectCard::where('list_id', $list->id)
                    ->orderBy('position')
                    ->orderBy('id')
                    ->get()
                    ->map(function (ProjectCard $c) {
                        return [
                            'id' => $c->uuid,
                            'title' => $c->title,
                            'labels' => $c->labels ?? [],
                            'due' => optional($c->due_date)->toDateString(),
                            'members' => [],
                            'comments' => $c->comments_count ?? 0,
                            'attachments' => $c->attachments_count ?? 0,
                            'progress' => $c->progress ?? 0,
                            'description' => $c->description,
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
        ]);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'card_id' => 'required|string',
            'to_list' => 'required|string',
            'position'=> 'required|integer|min:0',
        ]);

        [$kelompok] = $this->resolveKelompokSaya();
        if (!$kelompok) return response()->json(['status' => 'no_group'], 403);

        $card = ProjectCard::where('uuid', $request->card_id)
            ->where('kelompok_id', $kelompok->id)
            ->first();
        if (!$card) return response()->json(['status' => 'not_found'], 404);
        $this->authorize('update', $card);

        $fromListId = $card->list_id;
        $toList = \App\Models\ProjectList::where('uuid', $request->to_list)->first();
        if (!$toList) return response()->json(['status' => 'invalid_list'], 422);
        $newPos = (int) $request->position;

        DB::transaction(function () use ($card, $fromListId, $toList, $newPos) {
            $oldPos = $card->position;
            if ($fromListId === $toList->id) {
                if ($newPos === $oldPos) return;
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
        $mhs = Mahasiswa::where('user_id', Auth::id())->first();
        $periodeAktif = Periode::where('status_periode','Aktif')->orderByDesc('created_at')->first();
        $kelompokSaya = null;
        if ($mhs) {
            if ($periodeAktif) {
                $kelompokSaya = $mhs->kelompoks()->wherePivot('periode_id', $periodeAktif->id)->first();
            }
            if (!$kelompokSaya) {
                $kelompokSaya = $mhs->kelompoks()->latest('kelompok_mahasiswa.created_at')->first();
            }
        }
        return [$kelompokSaya, $periodeAktif];
    }
}
