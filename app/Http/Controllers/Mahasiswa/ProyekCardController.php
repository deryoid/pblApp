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
            // field lama (kompat) & baru (skema revisi)
            'due_date' => 'nullable|date',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date',
            'nama_mitra' => 'nullable|string|max:255',
            'kontak_mitra' => 'nullable|string|max:255',
            'skema_pbl' => 'nullable|string|max:50',
            'biaya_barang' => 'nullable|numeric|min:0',
            'biaya_jasa' => 'nullable|numeric|min:0',
            'status_proyek' => 'nullable|string|in:Proses,Dibatalkan,Selesai',
            'link_drive_proyek' => 'nullable|url',
            'kendala' => 'nullable|string',
            'catatan' => 'nullable|string',
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
            // tanggal & fallback kompat
            'tanggal_mulai' => $data['tanggal_mulai'] ?? null,
            'tanggal_selesai' => $data['tanggal_selesai'] ?? ($data['due_date'] ?? null),
            'due_date' => $data['due_date'] ?? ($data['tanggal_selesai'] ?? null),
            // atribut tambahan skema revisi (Opsi A)
            'nama_mitra' => $data['nama_mitra'] ?? null,
            'kontak_mitra' => $data['kontak_mitra'] ?? null,
            'skema_pbl' => $data['skema_pbl'] ?? null,
            'biaya_barang' => $data['biaya_barang'] ?? 0,
            'biaya_jasa' => $data['biaya_jasa'] ?? 0,
            'status_proyek' => $data['status_proyek'] ?? 'Proses',
            'link_drive_proyek' => $data['link_drive_proyek'] ?? null,
            'kendala' => $data['kendala'] ?? null,
            'catatan' => $data['catatan'] ?? null,
            'progress' => 0,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
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
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date',
            'progress' => 'nullable|integer|min:0|max:100',
            'nama_mitra' => 'nullable|string|max:255',
            'kontak_mitra' => 'nullable|string|max:255',
            'skema_pbl' => 'nullable|string|max:50',
            'biaya_barang' => 'nullable|numeric|min:0',
            'biaya_jasa' => 'nullable|numeric|min:0',
            'status_proyek' => 'nullable|string|in:Proses,Dibatalkan,Selesai',
            'link_drive_proyek' => 'nullable|url',
            'kendala' => 'nullable|string',
            'catatan' => 'nullable|string',
        ]);

        // Update kolom utama
        $card->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'labels' => $this->parseLabels($data['labels'] ?? null),
            // tanggal & fallback kompat
            'tanggal_mulai' => $data['tanggal_mulai'] ?? $card->tanggal_mulai,
            'tanggal_selesai' => $data['tanggal_selesai'] ?? ($data['due_date'] ?? $card->tanggal_selesai),
            'due_date' => $data['due_date'] ?? ($data['tanggal_selesai'] ?? $card->due_date),
            // atribut tambahan
            'progress' => $data['progress'] ?? $card->progress,
            'nama_mitra' => $data['nama_mitra'] ?? $card->nama_mitra,
            'kontak_mitra' => $data['kontak_mitra'] ?? $card->kontak_mitra,
            'skema_pbl' => $data['skema_pbl'] ?? $card->skema_pbl,
            'biaya_barang' => $data['biaya_barang'] ?? $card->biaya_barang,
            'biaya_jasa' => $data['biaya_jasa'] ?? $card->biaya_jasa,
            'status_proyek' => $data['status_proyek'] ?? $card->status_proyek,
            'link_drive_proyek' => $data['link_drive_proyek'] ?? $card->link_drive_proyek,
            'kendala' => $data['kendala'] ?? $card->kendala,
            'catatan' => $data['catatan'] ?? $card->catatan,
            'updated_by' => Auth::id(),
        ]);

        // members dihapus: tidak perlu append apa pun
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
