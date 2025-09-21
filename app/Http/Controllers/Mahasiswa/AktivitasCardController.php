<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\AktivitasCard;
use App\Models\AktivitasList;
use App\Models\Mahasiswa;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AktivitasCardController extends Controller
{
    public function store(Request $req)
    {
        [$kelompok] = $this->resolveKelompokSaya();
        if (!$kelompok) return back()->withErrors('Anda belum tergabung dalam kelompok.');
        $this->authorize('create', AktivitasCard::class);

        $data = $req->validate([
            'list' => 'required|string',
            'tanggal_aktivitas' => 'nullable|date',
            'description' => 'nullable|string',
            'bukti_kegiatan' => 'nullable|string', // 50MB
        ]);

        $list = AktivitasList::where('uuid',$data['list'])->first();
        if (!$list) return back()->withErrors('List/kolom tidak ditemukan.');

        $maxPos = AktivitasCard::where('list_aktivitas_id',$list->id)->max('position');
        $pos = is_null($maxPos) ? 0 : $maxPos+1;


        AktivitasCard::create([
            'kelompok_id' => $kelompok->id,
            'periode_id' => $list->periode_id,
            'list_aktivitas_id' => $list->id,
            'tanggal_aktivitas' => $data['tanggal_aktivitas'] ?? null,
            'description' => $data['description'] ?? null,
            'bukti_kegiatan' => $data['bukti_kegiatan'] ?? null,
            'position' => $pos,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return back();
    }

    public function update(Request $req, AktivitasCard $card)
    {
        $this->authorize('update', $card);

        $data = $req->validate([
            'tanggal_aktivitas' => 'nullable|date',
            'description' => 'nullable|string',
            'bukti_kegiatan' => 'nullable|file|max:51200',
        ]);

        $payload = [
            'tanggal_aktivitas' => $data['tanggal_aktivitas'] ?? $card->tanggal_aktivitas,
            'description' => $data['description'] ?? $card->description,
            'updated_by' => Auth::id(),
        ];

        $card->update($payload);
        return back();
    }

    public function destroy(AktivitasCard $card)
    {
        $this->authorize('delete', $card);

        DB::transaction(function () use ($card) {
            $listId = $card->list_aktivitas_id;
            $pos = $card->position;
            $kid = $card->kelompok_id;
            $card->delete();

            AktivitasCard::where('kelompok_id',$kid)
                ->where('list_aktivitas_id',$listId)
                ->where('position','>', $pos)
                ->decrement('position');
        });

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
}
