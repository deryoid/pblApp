<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Periode;
use App\Models\ProjectCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class ProyekCardController extends Controller
{
    public function store(Request $request)
    {
        [$kelompok, $periodeAktif] = $this->resolveKelompokSaya();
        if (! $kelompok) {
            Alert::error('Gagal', 'Anda belum tergabung dalam kelompok.');

            return back()->withErrors('Anda belum tergabung dalam kelompok.');
        }
        $this->authorize('create', \App\Models\ProjectCard::class);

        if ($request->filled('link_drive_proyek')) {
            $driveUrl = trim($request->input('link_drive_proyek'));
            if (! preg_match('~^https?://~i', $driveUrl)) {
                $request->merge(['link_drive_proyek' => 'https://'.$driveUrl]);
            }
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'list' => 'nullable|string', // uuid atau id list tujuan
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
            'proposal_file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'mata_kuliah_ids' => 'nullable|array',
            'mata_kuliah_ids.*' => 'exists:mata_kuliah,id',
        ]);

        $skema = ! empty($data['skema_pbl']) ? $data['skema_pbl'] : ($kelompok->skema_kategori ?: 'Internal');
        $proposalPath = null;
        if ($request->hasFile('proposal_file')) {
            $proposalPath = $request->file('proposal_file')->store('proposals', 'public');
        }

        // Syarat wajib proposal jika skema Internal
        if (strtolower($skema) === 'internal' && empty($proposalPath)) {
            Alert::error('Proposal Wajib', 'Sebelum memulai proyek internal, kelompok wajib mengunggah berkas proposal proyek (PDF/DOC/DOCX).');

            return back()->withErrors([
                'proposal_file' => 'Sebelum memulai proyek internal, kelompok wajib mengunggah berkas proposal proyek (PDF/DOC/DOCX).',
            ])->withInput();
        }

        $labels = $this->parseLabels($data['labels'] ?? null);

        // Tentukan list tujuan (fallback ke list pertama atau buat list default jika belum ada)
        $list = null;
        if (! empty($data['list'])) {
            $list = \App\Models\ProjectList::where('kelompok_id', $kelompok->id)
                ->where(fn ($q) => $q->where('uuid', $data['list'])->orWhere('id', $data['list']))
                ->first();
        }

        if (! $list) {
            $pid = $kelompok->periode_id ?? optional($periodeAktif)->id;
            $list = \App\Models\ProjectList::where('kelompok_id', $kelompok->id)
                ->where(fn ($q) => $q->where('periode_id', $pid)->orWhereNull('periode_id'))
                ->orderBy('position')
                ->first();
        }

        if (! $list) {
            $pid = $kelompok->periode_id ?? optional($periodeAktif)->id;
            $list = \App\Models\ProjectList::create([
                'kelompok_id' => $kelompok->id,
                'periode_id' => $pid,
                'name' => 'Perencanaan',
                'position' => 0,
            ]);
        }

        // Tentukan position di kolom tujuan (append di akhir)
        $maxPos = ProjectCard::where('list_id', $list->id)->max('position');
        $pos = is_null($maxPos) ? 0 : ($maxPos + 1);

        $createdCard = ProjectCard::create([
            'kelompok_id' => $kelompok->id,
            'periode_id' => $kelompok->periode_id ?? optional($periodeAktif)->id,
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
            'skema_pbl' => $skema,
            'jenis_proyek' => strtolower($skema) === 'external' ? 'external' : 'internal',
            'proposal_file_path' => $proposalPath,
            'status_proposal' => $proposalPath ? 'disetujui' : 'pending',
            'biaya_barang' => $data['biaya_barang'] ?? 0,
            'biaya_jasa' => $data['biaya_jasa'] ?? 0,
            'status_proyek' => $data['status_proyek'] ?? 'Proses',
            'status' => $data['status_proyek'] ?? 'Proses',
            'link_drive_proyek' => $data['link_drive_proyek'] ?? null,
            'kendala' => $data['kendala'] ?? null,
            'catatan' => $data['catatan'] ?? null,
            'progress' => 0,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        if (! empty($data['mata_kuliah_ids'])) {
            $createdCard->mataKuliahs()->sync($data['mata_kuliah_ids']);
        }

        Alert::toast('Kartu ditambahkan.', 'success');

        return back();
    }

    public function update(Request $request, ProjectCard $card)
    {
        [$kelompok] = $this->resolveKelompokSaya();
        $this->authorize('update', $card);

        if ($request->filled('link_drive_proyek')) {
            $driveUrl = trim($request->input('link_drive_proyek'));
            if (! preg_match('~^https?://~i', $driveUrl)) {
                $request->merge(['link_drive_proyek' => 'https://'.$driveUrl]);
            }
        }

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
            'proposal_file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'mata_kuliah_ids' => 'nullable|array',
            'mata_kuliah_ids.*' => 'exists:mata_kuliah,id',
        ]);

        $skema = ! empty($data['skema_pbl']) ? $data['skema_pbl'] : ($card->skema_pbl ?: ($kelompok?->skema_kategori ?: 'Internal'));
        $proposalPath = $card->proposal_file_path;

        if ($request->hasFile('proposal_file')) {
            if ($card->proposal_file_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($card->proposal_file_path);
            }
            $proposalPath = $request->file('proposal_file')->store('proposals', 'public');
        }

        // Syarat wajib proposal jika proyek Internal
        if (strtolower($skema) === 'internal' && empty($proposalPath)) {
            Alert::error('Proposal Wajib', 'Sebelum memulai proyek internal, kelompok wajib mengunggah berkas proposal proyek (PDF/DOC/DOCX).');

            return back()->withErrors([
                'proposal_file' => 'Sebelum memulai proyek internal, kelompok wajib mengunggah berkas proposal proyek (PDF/DOC/DOCX).',
            ])->withInput();
        }

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
            'skema_pbl' => $skema,
            'jenis_proyek' => strtolower($skema) === 'external' ? 'external' : 'internal',
            'proposal_file_path' => $proposalPath,
            'status_proposal' => $proposalPath ? 'disetujui' : ($card->status_proposal ?? 'pending'),
            'biaya_barang' => $data['biaya_barang'] ?? $card->biaya_barang,
            'biaya_jasa' => $data['biaya_jasa'] ?? $card->biaya_jasa,
            'status_proyek' => $data['status_proyek'] ?? $card->status_proyek,
            'status' => $data['status_proyek'] ?? $card->status,
            'link_drive_proyek' => $data['link_drive_proyek'] ?? $card->link_drive_proyek,
            'kendala' => $data['kendala'] ?? $card->kendala,
            'catatan' => $data['catatan'] ?? $card->catatan,
            'updated_by' => Auth::id(),
        ]);

        // Sync mata kuliah (jika form dikirim, update sesuai input, kosong jika tidak dicentang)
        $card->mataKuliahs()->sync($request->input('mata_kuliah_ids', []));

        Alert::toast('Kartu diperbarui.', 'success');

        return back();
    }

    public function destroy(ProjectCard $card)
    {
        [$kelompok] = $this->resolveKelompokSaya();
        $this->authorize('delete', $card);

        if ($card->proposal_file_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($card->proposal_file_path);
        }

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

    private function parseLabels(?string $csv): ?array
    {
        if (! $csv) {
            return null;
        }
        $arr = array_filter(array_map(fn ($s) => trim($s), explode(',', $csv)));

        return array_values($arr);
    }
}
