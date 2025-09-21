<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\AktivitasList;
use App\Models\Mahasiswa;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class AktivitasListController extends Controller
{
    public function store(Request $req)
    {
        $this->authorize('create', AktivitasList::class);
        [$kelompok,$periodeAktif] = $this->resolveContext();
        if (!$kelompok) return back()->withErrors('Anda belum tergabung dalam kelompok.');

        $data = $req->validate([
            'name'               => 'required|string|max:255',
            'rentang_tanggal'    => 'nullable|string|max:255',
            'link_drive_logbook' => 'nullable|url|max:255',
            'status_evaluasi'    => 'nullable|in:Belum Evaluasi,Sudah Evaluasi',
        ]);

        $pid   = $kelompok->periode_id ?? optional($periodeAktif)->id;
        $maxPos= AktivitasList::where('kelompok_id',$kelompok->id)->where('periode_id',$pid)->max('position');
        $pos   = is_null($maxPos) ? 0 : $maxPos+1;

        AktivitasList::create([
            'kelompok_id'        => $kelompok->id,
            'periode_id'         => $pid,
            'name'               => $data['name'],
            'rentang_tanggal'    => $data['rentang_tanggal'] ?? null,
            'link_drive_logbook' => $data['link_drive_logbook'] ?? null,
            'status_evaluasi'    => $data['status_evaluasi'] ?? 'Belum Evaluasi',
            'position'           => $pos,
        ]);

        Alert::toast('Kolom aktivitas ditambahkan.', 'success');
        return back();
    }

    public function update(Request $req, AktivitasList $list)
    {
        $this->authorize('update', $list);

        $data = $req->validate([
            'name'               => 'required|string|max:255',
            'rentang_tanggal'    => 'nullable|string|max:255',
            'link_drive_logbook' => 'nullable|url|max:255',
            'status_evaluasi'    => 'nullable|in:Belum Evaluasi,Sudah Evaluasi',
        ]);

        $list->update($data);
        Alert::toast('Kolom aktivitas diperbarui.', 'success');
        return back();
    }

    public function destroy(AktivitasList $list)
    {
        $this->authorize('delete', $list);

        if ($list->cards()->exists()) {
            return back()->withErrors('Tidak dapat menghapus kolom yang berisi kartu.');
        }

        $kid = $list->kelompok_id;
        $pid = $list->periode_id;
        $pos = $list->position;

        DB::transaction(function() use ($list,$kid,$pid,$pos) {
            $list->delete();
            AktivitasList::where('kelompok_id',$kid)
                ->where('periode_id',$pid)
                ->where('position','>', $pos)
                ->decrement('position');
        });

        Alert::toast('Kolom aktivitas dihapus.', 'success');
        return back();
    }

    public function reorder(Request $req)
    {
        [$kelompok,$periodeAktif] = $this->resolveContext();
        if (!$kelompok) return response()->json(['status'=>'no_group'], 403);

        $uuids = $req->input('list_ids');
        if (!is_array($uuids) || empty($uuids)) {
            return response()->json(['status'=>'invalid'],422);
        }

        // guard: semua uuid harus milik kelompok & periode aktif
        $pid = $kelompok->periode_id ?? optional($periodeAktif)->id;
        $validCount = AktivitasList::where('kelompok_id',$kelompok->id)
            ->where('periode_id',$pid)
            ->whereIn('uuid',$uuids)
            ->count();
        if ($validCount !== count($uuids)) {
            return response()->json(['status'=>'mismatch'], 422);
        }

        DB::transaction(function() use ($uuids,$kelompok,$pid) {
            foreach ($uuids as $idx=>$uuid) {
                AktivitasList::where('kelompok_id',$kelompok->id)
                    ->where('periode_id',$pid)
                    ->where('uuid',$uuid)
                    ->update(['position'=>$idx]);
            }
        });

        return response()->json(['status'=>'ok']);
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
