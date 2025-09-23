<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EvaluasiSesi;
use App\Models\EvaluasiSetting;
use App\Models\Kelompok;
use App\Models\Periode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;
use Carbon\Carbon;

class EvaluasiController extends Controller
{
    /** ===== LIST KELOMPOK ===== */
    public function index(Request $request)
    {
        $periodes      = Periode::orderByDesc('id')->get(['id','periode','status_periode']);
        $activePeriode = Periode::where('status_periode','Aktif')->orderByDesc('id')->first();

        $periodeId = (int) ($request->get('periode_id') ?: ($activePeriode?->id ?? 0));
        $keyword   = trim((string) $request->get('q',''));

        $query = Kelompok::query()
            ->with(['periode:id,periode'])
            ->when($periodeId, fn($q) => $q->where('periode_id', $periodeId))
            ->withCount([
                'mahasiswas as mahasiswas_count' => function($q) use ($periodeId) {
                    if ($periodeId) $q->where('kelompok_mahasiswa.periode_id', $periodeId);
                }
            ])
            ->with(['mahasiswas' => function($q) use ($periodeId) {
                $q->select('mahasiswa.id','nim','nama_mahasiswa as nama');
                if ($periodeId) $q->where('kelompok_mahasiswa.periode_id', $periodeId);
            }]);

        if ($keyword !== '') {
            $kw = "%{$keyword}%";
            $query->where(function($w) use ($kw, $periodeId) {
                $w->where('nama_kelompok','like',$kw)
                  ->orWhereHas('mahasiswas', function($k) use ($kw, $periodeId) {
                      if ($periodeId) $k->where('kelompok_mahasiswa.periode_id', $periodeId);
                      $k->whereIn('kelompok_mahasiswa.role', ['ketua','Ketua'])
                        ->where(function($sub) use ($kw) {
                            $sub->where('mahasiswa.nama','like',$kw)
                                ->orWhere('mahasiswa.nama_mahasiswa','like',$kw)
                                ->orWhere('mahasiswa.nim','like',$kw);
                        });
                  });
            });
        }

        $kelompoks = $query->orderBy('nama_kelompok')->paginate(20)->withQueryString();

        $sesiMap = EvaluasiSesi::query()
            ->when($periodeId, fn($q)=>$q->where('periode_id',$periodeId))
            ->whereIn('kelompok_id', $kelompoks->pluck('id'))
            ->with('evaluator:id,nama_user')
            ->get()
            ->keyBy('kelompok_id');

        return view('admin.evaluasi.index', [
            'periodes'     => $periodes,
            'kelompoks'    => $kelompoks,
            'sesiMap'      => $sesiMap,
            'periodeAktif' => $activePeriode,
            'periodeId'    => $periodeId,
        ]);
    }

    /** ===== FORM JADWAL (per kelompok) =====
     * GET admin/evaluasi/kelompok/{kelompok:uuid}/schedule
     */
    public function scheduleForm(Kelompok $kelompok)
    {
        $activePeriode = Periode::where('status_periode','Aktif')->orderByDesc('id')->first();
        abort_unless($activePeriode, 404, 'Periode aktif tidak ditemukan');

        $sesi = EvaluasiSesi::firstOrCreate(
            ['periode_id' => $activePeriode->id, 'kelompok_id' => $kelompok->id],
            ['uuid' => (string) Str::uuid(), 'status' => EvaluasiSesi::ST_BELUM, 'created_by' => Auth::id()]
        );

    $evaluators = User::orderBy('nama_user')->get(['id','nama_user']);

        return view('admin.evaluasi.sesi-schedule', [
            'kelompok'   => $kelompok,
            'periode'    => $activePeriode,
            'sesi'       => $sesi,
            'evaluators' => $evaluators,
        ]);
    }

    /** ===== SIMPAN JADWAL =====
     * PATCH admin/evaluasi/{sesi}/schedule
     */
    public function scheduleSave(Request $req, EvaluasiSesi $sesi)
    {
        $data = $req->validate([
            'evaluator_id'     => 'nullable|exists:users,id',
            'lokasi'           => 'nullable|string|max:150',
            // opsi A: datetime-local (controller menerima beberapa nama field untuk kompatibilitas)
            'mulai'            => 'nullable|date',
            'selesai'          => 'nullable|date|after:mulai',
            'jadwal_mulai'     => 'nullable|date',
            'jadwal_selesai'   => 'nullable|date|after:jadwal_mulai',
            // opsi B: field terpisah
            'mulai_tanggal'    => 'nullable|date',
            'mulai_jam'        => 'nullable|date_format:H:i',
            'selesai_jam'      => 'nullable|date_format:H:i',
            // default durasi jika hanya isi mulai
            'durasi_menit'     => 'nullable|integer|min:15|max:600',
        ]);

        // Ambil nilai mulai/selesai dari beberapa sumber supaya kompatibel dengan view
        $mulai   = $data['mulai'] ?? $data['jadwal_mulai'] ?? null;
        $selesai = $data['selesai'] ?? $data['jadwal_selesai'] ?? null;

        // Rakitan dari field terpisah jika diperlukan
        if (!$mulai && !empty($data['mulai_tanggal']) && !empty($data['mulai_jam'])) {
            $mulai = $data['mulai_tanggal'].' '.$data['mulai_jam'].':00';
        }
        if (!$selesai && !empty($data['mulai_tanggal']) && !empty($data['selesai_jam'])) {
            $selesai = $data['mulai_tanggal'].' '.$data['selesai_jam'].':00';
        }

        // Jika hanya mulai tersedia, berikan durasi default
        if ($mulai && !$selesai) {
            $durasiMenit = (int)($data['durasi_menit'] ?? 90);
            $selesai = Carbon::parse($mulai)->addMinutes($durasiMenit)->format('Y-m-d H:i:s');
        }

        $sesi->update([
            'evaluator_id'   => $data['evaluator_id'] ?? null,
            'lokasi'         => $data['lokasi'] ?? null,
            'jadwal_mulai'   => $mulai,
            'jadwal_selesai' => $selesai,
            'status'         => $mulai ? EvaluasiSesi::ST_JADWAL : EvaluasiSesi::ST_BELUM,
            'updated_by'     => Auth::id(),
        ]);

        Alert::success('Tersimpan', 'Jadwal sesi berhasil disimpan.');

        $kelompok = $sesi->kelompok()->first();
        return redirect()->route('admin.evaluasi.kelompok.show', $kelompok->uuid);
    }

    /** ===== DETAIL KELOMPOK ===== */
    public function showKelompok(Kelompok $kelompok, Request $request)
    {
        $activePeriode = Periode::where('status_periode','Aktif')->orderByDesc('id')->first();
        abort_unless($activePeriode, 404, 'Periode aktif tidak ditemukan');

        $sesi = EvaluasiSesi::with(['evaluator','absensis','sesiIndikators.indikator','nilaiDetails'])
            ->where('periode_id', $activePeriode->id)
            ->where('kelompok_id', $kelompok->id)
            ->latest('id')
            ->first();

        if (!$sesi) {
            $sesi = EvaluasiSesi::create([
                'uuid'        => (string) Str::uuid(),
                'periode_id'  => $activePeriode->id,
                'kelompok_id' => $kelompok->id,
                'status'      => EvaluasiSesi::ST_BELUM,
                'created_by'  => Auth::id(),
            ]);
            Alert::info('Sesi Dibuat', 'Sesi evaluasi baru dibuat otomatis untuk kelompok ini.');
        }

        $anggota = $kelompok->mahasiswas()
            ->wherePivot('periode_id', $activePeriode->id)
            ->orderBy('nama_mahasiswa')
            ->get(['mahasiswa.id','nim','nama_mahasiswa as nama']);

        $settings = EvaluasiSetting::getMany(
            ['w_ap_kehadiran','w_ap_presentasi','w_dosen','w_mitra','w_kelompok','w_ap'],
            ['w_ap_kehadiran'=>50,'w_ap_presentasi'=>50,'w_dosen'=>80,'w_mitra'=>20,'w_kelompok'=>70,'w_ap'=>30]
        );

        return view('admin.evaluasi.show', [
            'kelompok'           => $kelompok,
            'periode'            => $activePeriode,
            'sesi'               => $sesi,
            'evaluator'          => $sesi->evaluator,
            'anggota'            => $anggota,
            'proyekLists'        => [],
            'proyek_total_cards' => 0,
            'aktivitasLists'     => [],
            'aktivitas_total'    => 0,
            'settings'           => $settings,
        ]);
    }

    /** ===== AKSI STATUS ===== */
    public function start(EvaluasiSesi $sesi)
    {
        $payload = [
            'status'     => EvaluasiSesi::ST_PROSES,
            'updated_by' => Auth::id(),
        ];
        if (empty($sesi->jadwal_mulai)) $payload['jadwal_mulai'] = now();
        $sesi->update($payload);

        Alert::success('Berlangsung', 'Sesi evaluasi dimulai.');
        return back();
    }

    public function finish(EvaluasiSesi $sesi)
    {
        $payload = [
            'status'       => EvaluasiSesi::ST_SELESAI,
            'updated_by'   => Auth::id(),
        ];
        if (empty($sesi->jadwal_selesai)) $payload['jadwal_selesai'] = now();
        $sesi->update($payload);

        Alert::success('Selesai', 'Sesi evaluasi diselesaikan.');
        return back();
    }



    /** ===== JADWAL MASSAL (form) ===== */
    public function scheduleBulkForm(Request $req)
    {
        $periodes = Periode::orderByDesc('id')->get(['id','periode','status_periode']);
        $periodeId = (int)$req->get('periode_id', 0);
        return view('admin.evaluasi.sesi-schedule-bulk', compact('periodes','periodeId'));
    }

    /** ===== JADWAL MASSAL (aksi) ===== */
    public function scheduleBulk(Request $req)
    {
        $data = $req->validate([
            'periode_id' => 'required|exists:periode,id',
            'jadwal_mulai' => 'required|date',
            'jadwal_selesai' => 'nullable|date|after:jadwal_mulai',
            'durasi_menit' => 'nullable|integer|min:15|max:1440',
            'lokasi' => 'nullable|string|max:150',
            'evaluator_id' => 'nullable|exists:users,id',
            'selected_ids' => 'nullable|array',
            'selected_ids.*' => 'integer|exists:kelompok,id',
        ]);

        $periodeId = $data['periode_id'];
        $durasi = (int)($data['durasi_menit'] ?? 90);
        // If selected_ids provided, schedule only those; otherwise all kelompok on periode
        $kelompokQuery = Kelompok::where('periode_id', $periodeId);
        if (!empty($data['selected_ids'])) {
            $kelompokQuery->whereIn('id', $data['selected_ids']);
        }
        $kelompokIds = $kelompokQuery->pluck('id');

        foreach ($kelompokIds as $kid) {
            $s = EvaluasiSesi::firstOrCreate([
                'periode_id'=>$periodeId,'kelompok_id'=>$kid
            ],['created_by'=>Auth::id(), 'status'=>EvaluasiSesi::ST_BELUM]);

            if (!empty($data['jadwal_mulai'])) {
                $s->jadwal_mulai = $data['jadwal_mulai'];
                if (!empty($data['jadwal_selesai'])) {
                    $s->jadwal_selesai = $data['jadwal_selesai'];
                } else {
                    $s->jadwal_selesai = Carbon::parse($data['jadwal_mulai'])->addMinutes($durasi)->format('Y-m-d H:i:s');
                }
                $s->evaluator_id = $data['evaluator_id'] ?? $s->evaluator_id;
                $s->lokasi = $data['lokasi'] ?? $s->lokasi;
                $s->status = EvaluasiSesi::ST_JADWAL;
                $s->save();
            }
        }

        Alert::success('Selesai', 'Penjadwalan massal selesai.');
        return redirect()->route('admin.evaluasi.sesi.index', ['periode_id'=>$periodeId]);
    }

    /** ===== PENGATURAN ===== */
    public function settings()
    {
        $keys = [
            'w_dosen','w_mitra',
            'd_hasil','d_teknis','d_user','d_efisiensi','d_dokpro','d_inisiatif',
            'm_kehadiran','m_presentasi',
            'w_kelompok','w_ap',
            'w_ap_kehadiran','w_ap_presentasi',
        ];
        $defaults = [
            'w_dosen'=>80,'w_mitra'=>20,
            'd_hasil'=>30,'d_teknis'=>20,'d_user'=>15,'d_efisiensi'=>10,'d_dokpro'=>15,'d_inisiatif'=>10,
            'm_kehadiran'=>50,'m_presentasi'=>50,
            'w_kelompok'=>70,'w_ap'=>30,
            'w_ap_kehadiran'=>50,'w_ap_presentasi'=>50,
        ];
        $settings = EvaluasiSetting::getMany($keys, $defaults);

        return view('admin.evaluasi.settings', compact('settings'));
    }

    public function saveSettings(Request $req)
    {
        $data = $req->validate([
            'w_dosen'         => 'required|integer|min:0|max:100',
            'w_mitra'         => 'required|integer|min:0|max:100',
            'd_hasil'         => 'required|integer|min:0|max:100',
            'd_teknis'        => 'required|integer|min:0|max:100',
            'd_user'          => 'required|integer|min:0|max:100',
            'd_efisiensi'     => 'required|integer|min:0|max:100',
            'd_dokpro'        => 'required|integer|min:0|max:100',
            'd_inisiatif'     => 'required|integer|min:0|max:100',
            'm_kehadiran'     => 'required|integer|min:0|max:100',
            'm_presentasi'    => 'required|integer|min:0|max:100',
            'w_kelompok'      => 'required|integer|min:0|max:100',
            'w_ap'            => 'required|integer|min:0|max:100',
            'w_ap_kehadiran'  => 'required|integer|min:0|max:100',
            'w_ap_presentasi' => 'required|integer|min:0|max:100',
        ]);

        $sumDosen = $data['d_hasil']+$data['d_teknis']+$data['d_user']+$data['d_efisiensi']+$data['d_dokpro']+$data['d_inisiatif'];
        if ($sumDosen != 100) $data['d_inisiatif'] = max(0, 100 - ($sumDosen - $data['d_inisiatif']));

        $sumMitra = $data['m_kehadiran']+$data['m_presentasi'];
        if ($sumMitra != 100) $data['m_presentasi'] = max(0, 100 - ($sumMitra - $data['m_presentasi']));

        if ($data['w_kelompok'] + $data['w_ap'] != 100) $data['w_ap'] = max(0, 100 - $data['w_kelompok']);
        if ($data['w_ap_kehadiran'] + $data['w_ap_presentasi'] != 100) $data['w_ap_presentasi'] = max(0, 100 - $data['w_ap_kehadiran']);

        EvaluasiSetting::putMany($data);
        Alert::success('Tersimpan', 'Pengaturan evaluasi berhasil disimpan.');
        return back();
    }

    /** ===== UTIL (opsional) ===== */
    private function ensureSessions(int $periodeId): int
    {
        $kelompokIds = Kelompok::where('periode_id',$periodeId)->pluck('id');
        if ($kelompokIds->isEmpty()) return 0;

        $existing = EvaluasiSesi::where('periode_id',$periodeId)
            ->whereIn('kelompok_id',$kelompokIds)
            ->pluck('kelompok_id')
            ->all();

        $missing = $kelompokIds->diff($existing);
        foreach ($missing as $kid) {
            EvaluasiSesi::create([
                'uuid'        => (string) Str::uuid(),
                'periode_id'  => $periodeId,
                'kelompok_id' => (int) $kid,
                'status'      => EvaluasiSesi::ST_BELUM,
                'created_by'  => Auth::id(),
            ]);
        }
        return $missing->count();
    }
}
