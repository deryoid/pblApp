<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Periode;
use App\Models\ProjectCard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class ProyekCardController extends Controller
{
    public function store(Request $request)
    {
        [$kelompok] = $this->resolveKelompokSaya();
        if (!$kelompok) return back()->withErrors('Anda belum tergabung dalam kelompok.');
        $this->authorize('create', \App\Models\ProjectCard::class);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'list' => 'required|string', // uuid list tujuan
            'labels' => 'nullable|string', // CSV
            'due_date' => 'nullable|date',
        ]);

        $labels = $this->parseLabels($data['labels'] ?? null);

        // Tentukan list tujuan
        $list = \App\Models\ProjectList::where('uuid', $data['list'])->first();
        if (!$list) return back()->withErrors('List/kolom tidak ditemukan.');

        // Tentukan position di kolom tujuan (append di akhir)
        $maxPos = ProjectCard::where('list_id', $list->id)->max('position');
        $pos = is_null($maxPos) ? 0 : ($maxPos + 1);

        ProjectCard::create([
            'kelompok_id' => $kelompok->id,
            'periode_id' => $kelompok->periode_id ?? null,
            'list_id' => $list->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'position' => $pos,
            'labels' => $labels,
            'due_date' => $data['due_date'] ?? null,
            'progress' => 0,
        ]);
        Alert::toast('Kartu ditambahkan.', 'success');
        return back();
    }

    public function update(Request $request, ProjectCard $card)
    {
        [$kelompok] = $this->resolveKelompokSaya();
        $this->authorize('update', $card);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'labels' => 'nullable|string',
            'due_date' => 'nullable|date',
            'progress' => 'nullable|integer|min:0|max:100',
        ]);

        $card->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'labels' => $this->parseLabels($data['labels'] ?? null),
            'due_date' => $data['due_date'] ?? null,
            'progress' => $data['progress'] ?? $card->progress,
        ]);
        Alert::toast('Kartu diperbarui.', 'success');
        return back();
    }

    public function destroy(ProjectCard $card)
    {
        [$kelompok] = $this->resolveKelompokSaya();
        $this->authorize('delete', $card);

        DB::transaction(function () use ($card) {
            $listId = $card->list_id;
            $pos = $card->position;
            $kelompokId = $card->kelompok_id;
            $card->delete();

            // Rapikan posisi setelah delete
            ProjectCard::where('kelompok_id', $kelompokId)
                ->where('list_id', $listId)
                ->where('position', '>', $pos)
                ->decrement('position');
        });

        Alert::toast('Kartu dihapus.', 'success');
        return back();
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

    private function parseLabels(?string $csv): ?array
    {
        if (!$csv) return null;
        $arr = array_filter(array_map(fn($s) => trim($s), explode(',', $csv)));
        return array_values($arr);
    }
}
