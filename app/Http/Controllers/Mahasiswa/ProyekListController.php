<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Periode;
use App\Models\ProjectList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class ProyekListController extends Controller
{
    public function store(Request $request)
    {
        $this->authorize('create', ProjectList::class);
        [$kelompok, $periodeAktif] = $this->resolveContext();
        if (! $kelompok) {
            Alert::error('Gagal', 'Anda belum tergabung dalam kelompok.');

            return back()->withErrors('Anda belum tergabung dalam kelompok.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $pid = $kelompok->periode_id ?? optional($periodeAktif)->id;
        $maxPos = ProjectList::where('kelompok_id', $kelompok->id)
            ->where(fn ($q) => $q->where('periode_id', $pid)->orWhereNull('periode_id'))
            ->max('position');
        $pos = is_null($maxPos) ? 0 : ($maxPos + 1);

        ProjectList::create([
            'kelompok_id' => $kelompok->id,
            'periode_id' => $pid,
            'name' => $data['name'],
            'position' => $pos,
        ]);
        Alert::toast('Kolom ditambahkan.', 'success');

        return back();
    }

    public function quickSetup(Request $request)
    {
        $this->authorize('create', ProjectList::class);
        [$kelompok, $periodeAktif] = $this->resolveContext();
        if (! $kelompok) {
            Alert::error('Gagal', 'Anda belum tergabung dalam kelompok.');

            return back()->withErrors('Anda belum tergabung dalam kelompok.');
        }

        $pid = $kelompok->periode_id ?? optional($periodeAktif)->id;
        $defaults = ['Perencanaan', 'Pelaksanaan', 'Selesai'];

        foreach ($defaults as $idx => $name) {
            ProjectList::firstOrCreate([
                'kelompok_id' => $kelompok->id,
                'periode_id' => $pid,
                'name' => $name,
            ], [
                'position' => $idx,
            ]);
        }

        Alert::success('Berhasil', 'Kolom standar proyek berhasil dibuat.');

        return back();
    }

    public function update(Request $request, ProjectList $list)
    {
        $this->authorize('update', $list);
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $list->update($data);
        Alert::toast('Kolom diperbarui.', 'success');

        return back();
    }

    public function destroy(ProjectList $list)
    {
        $this->authorize('delete', $list);

        if ($list->cards()->exists()) {
            Alert::error('Gagal', 'Tidak dapat menghapus kolom yang masih berisi kartu. Pindahkan atau hapus kartu di dalamnya terlebih dahulu.');

            return back()->withErrors('Tidak dapat menghapus kolom yang masih berisi kartu. Pindahkan atau hapus kartu di dalamnya terlebih dahulu.');
        }

        $kid = $list->kelompok_id;
        $pos = $list->position;

        DB::transaction(function () use ($list, $kid, $pos) {
            $list->delete();

            ProjectList::where('kelompok_id', $kid)
                ->where('position', '>', $pos)
                ->decrement('position');
        });

        Alert::toast('Kolom berhasil dihapus.', 'success');

        return back();
    }

    public function reorder(Request $request)
    {
        [$kelompok] = $this->resolveContext();
        if (! $kelompok) {
            return response()->json(['status' => 'no_group'], 403);
        }

        $ids = $request->input('list_ids');
        if (! is_array($ids) || empty($ids)) {
            return response()->json(['status' => 'invalid'], 422);
        }

        DB::transaction(function () use ($ids, $kelompok) {
            // Pass 1: Set ke posisi sementara untuk mencegah tabrakan unique constraint
            foreach ($ids as $idx => $uuid) {
                ProjectList::where('kelompok_id', $kelompok->id)
                    ->where(fn ($q) => $q->where('uuid', $uuid)->orWhere('id', $uuid))
                    ->update(['position' => $idx + 10000]);
            }
            // Pass 2: Set ke posisi final urut
            foreach ($ids as $idx => $uuid) {
                ProjectList::where('kelompok_id', $kelompok->id)
                    ->where(fn ($q) => $q->where('uuid', $uuid)->orWhere('id', $uuid))
                    ->update(['position' => $idx]);
            }
        });

        return response()->json(['status' => 'ok']);
    }

    private function resolveContext(): array
    {
        $mhs = Mahasiswa::resolveForUser(Auth::user());
        $periodeAktif = Periode::getLatestActive();
        $kelompokSaya = null;
        if ($mhs && $periodeAktif) {
            $kelompokSaya = $mhs->kelompoks()->wherePivot('periode_id', $periodeAktif->id)->first();
        }
        if ($mhs && ! $kelompokSaya) {
            $kelompokSaya = $mhs->kelompoks()->latest('kelompok.id')->first();
        }

        return [$kelompokSaya, $periodeAktif];
    }
}
