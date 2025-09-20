<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\ProjectList;
use App\Models\Mahasiswa;
use App\Models\Periode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ProyekListController extends Controller
{
    public function store(Request $request)
    {
        $this->authorize('create', ProjectList::class);
        [$kelompok, $periodeAktif] = $this->resolveContext();
        if (!$kelompok) return back()->withErrors('Anda belum tergabung dalam kelompok.');

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:32',
        ]);

        $pid = $kelompok->periode_id ?? optional($periodeAktif)->id;
        $maxPos = ProjectList::where('kelompok_id', $kelompok->id)
            ->where('periode_id', $pid)
            ->max('position');
        $pos = is_null($maxPos) ? 0 : ($maxPos + 1);

        ProjectList::create([
            'kelompok_id' => $kelompok->id,
            'periode_id' => $pid,
            'name' => $data['name'],
            'color' => $data['color'] ?? null,
            'position' => $pos,
        ]);

        return back()->with('success', 'Kolom ditambahkan.');
    }

    public function update(Request $request, ProjectList $list)
    {
        $this->authorize('update', $list);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:32',
        ]);
        $list->update($data);
        return back()->with('success', 'Kolom diperbarui.');
    }

    public function destroy(ProjectList $list)
    {
        $this->authorize('delete', $list);
        if ($list->cards()->exists()) {
            return back()->withErrors('Tidak dapat menghapus kolom yang berisi kartu.');
        }
        $kid = $list->kelompok_id;
        $pid = $list->periode_id;
        $pos = $list->position;
        DB::transaction(function () use ($list, $kid, $pid, $pos) {
            $list->delete();
            ProjectList::where('kelompok_id', $kid)
                ->where('periode_id', $pid)
                ->where('position', '>', $pos)
                ->decrement('position');
        });
        return back()->with('success', 'Kolom dihapus.');
    }

    public function reorder(Request $request)
    {
        [$kelompok, $periodeAktif] = $this->resolveContext();
        if (!$kelompok) return response()->json(['status' => 'no_group'], 403);

        $ids = $request->input('list_ids');
        if (!is_array($ids) || empty($ids)) return response()->json(['status' => 'invalid'], 422);

        $pid = $kelompok->periode_id ?? optional($periodeAktif)->id;
        DB::transaction(function () use ($ids, $kelompok, $pid) {
            foreach ($ids as $idx => $uuid) {
                ProjectList::where('kelompok_id', $kelompok->id)
                    ->where('periode_id', $pid)
                    ->where('uuid', $uuid)
                    ->update(['position' => $idx]);
            }
        });
        return response()->json(['status' => 'ok']);
    }

    private function resolveContext(): array
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
