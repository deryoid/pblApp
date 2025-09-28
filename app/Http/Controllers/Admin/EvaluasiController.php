<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProjectExport;
use App\Http\Controllers\Controller;
use App\Models\AktivitasList;
use App\Models\EvaluasiAbsensi;
use App\Models\EvaluasiDosen;
use App\Models\EvaluasiMaster;
use App\Models\EvaluasiMitra;
use App\Models\EvaluasiNilaiDetail;
use App\Models\EvaluasiProyekNilai;
use App\Models\EvaluasiSesiIndikator;
use App\Models\EvaluasiSetting;
use App\Models\Kelompok;
use App\Models\KunjunganMitra;
use App\Models\Periode;
use App\Models\ProjectCard;
use App\Models\ProjectList;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use RealRashid\SweetAlert\Facades\Alert;

class EvaluasiController extends Controller
{
    protected function setting(string $key, $default = null)
    {
        try {
            if (Schema::hasTable((new EvaluasiSetting)->getTable())) {
                $val = EvaluasiSetting::get($key);
                if ($val !== null) {
                    return $val;
                }
            }
        } catch (\Throwable $e) {
            // ignore and fallback
        }
        $cfg = config('evaluasi.defaults.'.$key);

        return $cfg !== null ? $cfg : $default;
    }

    /** ===== LIST KELOMPOK ===== */
    public function index(Request $request)
    {
        $periodes = Periode::orderByDesc('id')->get(['id', 'periode', 'status_periode']);
        $activePeriode = Periode::where('status_periode', 'Aktif')->orderByDesc('id')->first();

        $periodeId = (int) ($request->get('periode_id') ?: ($activePeriode?->id ?? 0));
        $keyword = trim((string) $request->get('q', ''));

        $query = Kelompok::query()
            ->with(['periode:id,periode'])
            ->when($periodeId, fn ($q) => $q->where('periode_id', $periodeId))
            ->withCount([
                'mahasiswas as mahasiswas_count' => function ($q) use ($periodeId) {
                    if ($periodeId) {
                        $q->where('kelompok_mahasiswa.periode_id', $periodeId);
                    }
                },
            ])
            ->with(['mahasiswas' => function ($q) use ($periodeId) {
                $q->select('mahasiswa.id', 'nim', 'nama_mahasiswa as nama');
                if ($periodeId) {
                    $q->where('kelompok_mahasiswa.periode_id', $periodeId);
                }
            }]);

        if ($keyword !== '') {
            $kw = "%{$keyword}%";
            $query->where(function ($w) use ($kw, $periodeId) {
                $w->where('nama_kelompok', 'like', $kw)
                    ->orWhereHas('mahasiswas', function ($k) use ($kw, $periodeId) {
                        if ($periodeId) {
                            $k->where('kelompok_mahasiswa.periode_id', $periodeId);
                        }
                        $k->whereIn('kelompok_mahasiswa.role', ['ketua', 'Ketua'])
                            ->where(function ($sub) use ($kw) {
                                $sub->where('mahasiswa.nama', 'like', $kw)
                                    ->orWhere('mahasiswa.nama_mahasiswa', 'like', $kw)
                                    ->orWhere('mahasiswa.nim', 'like', $kw);
                            });
                    });
            });
        }

        $kelompoks = $query->orderBy('nama_kelompok')->paginate(20)->withQueryString();

        $kelompokIds = $kelompoks->pluck('id');
        $sesiAll = EvaluasiMaster::query()
            ->when($periodeId, fn ($q) => $q->where('periode_id', $periodeId))
            ->whereIn('kelompok_id', $kelompokIds)
            ->get();
        $sesiMap = $sesiAll->sortByDesc('id')->keyBy('kelompok_id');

        // Perhitungan mingguan (AP) per kelompok
        $sesiByKel = $sesiAll->groupBy('kelompok_id');
        $weeklyMap = [];
        if ($sesiAll->isNotEmpty()) {
            $sesiIds = $sesiAll->pluck('id');
            // Fallback aman bila tabel belum ada
            $abs = collect();
            if (Schema::hasTable('evaluasi_absensi')) {
                $abs = \App\Models\EvaluasiAbsensi::whereIn('sesi_id', $sesiIds)->get()->groupBy('sesi_id');
            }

            // cari indikator 'm_presentasi' per sesi
            $sesiIndis = collect();
            if (Schema::hasTable('evaluasi_sesi_indikator')) {
                $sesiIndis = \App\Models\EvaluasiMasterIndikator::with('indikator')
                    ->whereIn('sesi_id', $sesiIds)
                    ->get()
                    ->groupBy('sesi_id');
            }

            $nilai = collect();
            if (Schema::hasTable('evaluasi_nilai_detail')) {
                $nilai = \App\Models\EvaluasiNilaiDetail::whereIn('sesi_id', $sesiIds)->get()->groupBy('sesi_id');
            }

            foreach ($sesiByKel as $kid => $list) {
                $evalCount = $list->count();
                $anggotaCount = (int) ($kelompoks->firstWhere('id', $kid)->mahasiswas_count ?? 0);
                $attPercents = [];
                $presentasiAverages = [];
                foreach ($list as $s) {
                    $attRows = $abs->get($s->id) ?? collect();
                    $hadir = $attRows->filter(fn ($r) => in_array($r->status, ['Hadir', 'Terlambat']))->count();
                    $perc = $anggotaCount > 0 ? ($hadir * 100 / $anggotaCount) : 0;
                    $attPercents[] = $perc;

                    // presentasi average
                    $indis = $sesiIndis->get($s->id) ?? collect();
                    $idPresent = optional($indis->first(fn ($x) => optional($x->indikator)->kode === 'm_presentasi'))->indikator_id;
                    if ($idPresent) {
                        $rows = ($nilai->get($s->id) ?? collect())->where('indikator_id', $idPresent);
                        $avgPre = $rows->avg('skor') ?: 0;
                        $presentasiAverages[] = $avgPre;
                    }
                }
                $attAvg = $attPercents ? array_sum($attPercents) / count($attPercents) : 0;
                $preAvg = $presentasiAverages ? array_sum($presentasiAverages) / count($presentasiAverages) : 0;
                $weeklyMap[$kid] = [
                    'eval_count' => $evalCount,
                    'avg_kehadiran' => (int) round($attAvg),
                    'avg_keaktifan' => (int) round($preAvg),
                ];
            }
        }

        return view('admin.evaluasi.index', [
            'periodes' => $periodes,
            'kelompoks' => $kelompoks,
            'sesiMap' => $sesiMap,
            'periodeAktif' => $activePeriode,
            'periodeId' => $periodeId,
            'weeklyMap' => $weeklyMap,
        ]);
    }

    /** ===== NILAI FINAL PER MAHASISWA ===== */
    public function nilaiFinal(Request $request)
    {
        $periodeId = $request->get('periode_id');
        $kelompokId = $request->get('kelompok_id');

        $query = EvaluasiDosen::with([
            'mahasiswa:id,nama_mahasiswa,nim',
            'kelompok:id,nama_kelompok',
            'projectCard:id,title',
            'evaluator:id,name',
        ])
            ->when($periodeId, fn ($q) => $q->where('periode_id', $periodeId))
            ->when($kelompokId, fn ($q) => $q->where('kelompok_id', $kelompokId))
            ->orderByDesc('updated_at');

        $evaluations = $query->get();

        // Group by mahasiswa untuk menampilkan semua nilai per mahasiswa
        $mahasiswaNilai = $evaluations->groupBy('mahasiswa_id')->map(function ($group) {
            $mahasiswa = $group->first()->mahasiswa;

            return [
                'mahasiswa' => $mahasiswa,
                'kelompok' => $group->first()->kelompok,
                'evaluations' => $group->map(function ($eval) {
                    return [
                        'project' => $eval->projectCard,
                        'nilai_akhir' => $eval->nilai_akhir,
                        'grade' => $eval->grade,
                        'status' => $eval->status,
                        'tanggal_evaluasi' => $eval->tanggal_evaluasi,
                        'evaluator' => $eval->evaluator,
                    ];
                }),
            ];
        });

        $periodes = Periode::orderByDesc('id')->get(['id', 'periode']);
        $kelompoks = Kelompok::when($periodeId, fn ($q) => $q->where('periode_id', $periodeId))
            ->orderBy('nama_kelompok')->get(['id', 'nama_kelompok']);

        return view('admin.evaluasi.nilai-final', compact('mahasiswaNilai', 'periodes', 'kelompoks', 'periodeId', 'kelompokId'));
    }

    /** ===== FORM JADWAL (per kelompok) ===== */
    public function scheduleForm(Kelompok $kelompok)
    {
        $activePeriode = Periode::where('status_periode', 'Aktif')->orderByDesc('id')->first();
        abort_unless($activePeriode, 404, 'Periode aktif tidak ditemukan');

        $sesi = EvaluasiMaster::firstOrCreate(
            ['periode_id' => $activePeriode->id, 'kelompok_id' => $kelompok->id],
            ['uuid' => (string) Str::uuid(), 'created_by' => Auth::id()]
        );

        return view('admin.evaluasi.sesi-schedule', [
            'kelompok' => $kelompok,
            'periode' => $activePeriode,
            'sesi' => $sesi,
        ]);
    }

    /** ===== SIMPAN JADWAL ===== */
    public function scheduleSave(Request $req, EvaluasiMaster $sesi)
    {
        $data = $req->validate([
            'evaluator_id' => 'nullable|exists:users,id',
            'lokasi' => 'nullable|string|max:150',
            // opsi A: datetime-local
            'mulai' => 'nullable|date',
            'selesai' => 'nullable|date|after:mulai',
            'jadwal_mulai' => 'nullable|date',
            'jadwal_selesai' => 'nullable|date|after:jadwal_mulai',
            // opsi B: field terpisah
            'mulai_tanggal' => 'nullable|date',
            'mulai_jam' => 'nullable|date_format:H:i',
            'selesai_jam' => 'nullable|date_format:H:i',
            // default durasi
            'durasi_menit' => 'nullable|integer|min:15|max:600',
        ]);

        $mulai = $data['mulai'] ?? $data['jadwal_mulai'] ?? null;
        $selesai = $data['selesai'] ?? $data['jadwal_selesai'] ?? null;

        if (! $mulai && ! empty($data['mulai_tanggal']) && ! empty($data['mulai_jam'])) {
            $mulai = $data['mulai_tanggal'].' '.$data['mulai_jam'].':00';
        }
        if (! $selesai && ! empty($data['mulai_tanggal']) && ! empty($data['selesai_jam'])) {
            $selesai = $data['mulai_tanggal'].' '.$data['selesai_jam'].':00';
        }
        if ($mulai && ! $selesai) {
            $durasiMenit = (int) ($data['durasi_menit'] ?? 90);
            $selesai = Carbon::parse($mulai)->addMinutes($durasiMenit)->format('Y-m-d H:i:s');
        }

        $sesi->update([
            'updated_by' => Auth::id(),
        ]);

        Alert::success('Tersimpan', 'Jadwal sesi berhasil disimpan.');

        $kelompok = $sesi->kelompok()->first();

        return redirect()->route('admin.evaluasi.kelompok.show', $kelompok->uuid);
    }

    /** ===== DETAIL KELOMPOK ===== */
    public function showKelompok(Kelompok $kelompok, Request $request)
    {
        $activePeriode = Periode::where('status_periode', 'Aktif')->orderByDesc('id')->first();
        abort_unless($activePeriode, 404, 'Periode aktif tidak ditemukan');

        // Data mitra
        $mitra = KunjunganMitra::where('kelompok_id', $kelompok->id)
            ->where('periode_id', $activePeriode->id)
            ->first();

        // Guard: hanya eager load relasi jika tabelnya ada
        $withRels = [];
        if (\Illuminate\Support\Facades\Schema::hasTable((new EvaluasiAbsensi)->getTable())) {
            $withRels[] = 'absensis';
        }
        if (\Illuminate\Support\Facades\Schema::hasTable((new EvaluasiSesiIndikator)->getTable())) {
            $withRels[] = 'sesiIndikators.indikator';
        }
        if (\Illuminate\Support\Facades\Schema::hasTable((new EvaluasiNilaiDetail)->getTable())) {
            $withRels[] = 'nilaiDetails';
        }

        $sesi = EvaluasiMaster::with($withRels)
            ->where('periode_id', $activePeriode->id)
            ->where('kelompok_id', $kelompok->id)
            ->latest('id')
            ->first();

        if (! $sesi) {
            $sesi = EvaluasiMaster::create([
                'uuid' => (string) Str::uuid(),
                'periode_id' => $activePeriode->id,
                'kelompok_id' => $kelompok->id,
                'created_by' => Auth::id(),
            ]);
            Alert::info('Sesi Dibuat', 'Sesi evaluasi baru dibuat otomatis untuk kelompok ini.');
        }

        $anggota = $kelompok->mahasiswas()
            ->wherePivot('periode_id', $activePeriode->id)
            ->with('kelas:id,kelas')
            ->withPivot('role') // Include the role field from pivot table
            ->orderBy('nama_mahasiswa')
            ->get(['mahasiswa.id', 'nim', 'nama_mahasiswa as nama', 'kelas_id']);

        // Load evaluation data per student
        $studentEvaluations = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable((new EvaluasiDosen)->getTable())) {
            $studentEvaluations = EvaluasiDosen::where('evaluasi_sesi_id', $sesi->id)
                ->where('kelompok_id', $kelompok->id)
                ->whereNotNull('mahasiswa_id')
                ->get()
                ->groupBy('mahasiswa_id');
        }

        // Calculate student evaluation metrics
        $studentEvalStats = [
            'total' => $anggota->count(),
            'evaluated' => $studentEvaluations->count(),
            'avg_grade' => 0,
            'grades' => [
                'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0,
            ],
        ];

        if ($studentEvaluations->isNotEmpty()) {
            $grades = $studentEvaluations->map(function ($evals) {
                return $evals->first()->grade;
            });

            $studentEvalStats['avg_grade'] = $studentEvaluations->avg(function ($evals) {
                return $evals->first()->nilai_akhir;
            });

            foreach ($grades as $grade) {
                if (isset($studentEvalStats['grades'][$grade])) {
                    $studentEvalStats['grades'][$grade]++;
                }
            }
        }

        // Settings bobot dengan fallback jika tabel belum tersedia
        $settingsDefaults = [
            'w_ap_kehadiran' => 50,
            'w_ap_presentasi' => 50,
            'w_dosen' => 80,
            'w_mitra' => 20,
            'w_kelompok' => 70,
            'w_ap' => 30,
        ];
        if (Schema::hasTable((new EvaluasiSetting)->getTable())) {
            $settings = EvaluasiSetting::getMany(
                ['w_ap_kehadiran', 'w_ap_presentasi', 'w_dosen', 'w_mitra', 'w_kelompok', 'w_ap'],
                $settingsDefaults
            );
        } else {
            $settings = $settingsDefaults;
        }
        // Project lists/cards
        $proyekLists = ProjectList::with(['cards' => function ($q) use ($kelompok) {
            $q->where('kelompok_id', $kelompok->id)->orderBy('position');
        }])->orderBy('position')->get();

        $proyek_total_cards = $proyekLists->sum(fn ($list) => $list->cards->count());

        // Aktivitas lists
        $aktivitasLists = AktivitasList::with(['cards' => function ($q) use ($kelompok) {
            $q->where('kelompok_id', $kelompok->id)->orderBy('position');
        }])->orderBy('position')->get();

        $aktivitas_total = $aktivitasLists->sum(fn ($list) => $list->cards->count());

        // Ambil nilai proyek per card (jika ada) untuk sesi ini
        $allCardIds = $proyekLists->flatMap(fn ($l) => $l->cards->pluck('id'))->values();
        $cardGrades = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable((new EvaluasiProyekNilai)->getTable())) {
            $cardGrades = EvaluasiProyekNilai::where('sesi_id', $sesi->id)
                ->whereIn('card_id', $allCardIds)
                ->get()
                ->groupBy(['card_id', 'jenis']);
        }

        // Load data from EvaluasiDosen table as well (per project + per mahasiswa)
        $evaluasiDosenCollections = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable((new EvaluasiDosen)->getTable())) {
            $evaluasiDosenCollections = EvaluasiDosen::with(['mahasiswa:id,nim,nama_mahasiswa'])
                ->where('evaluasi_sesi_id', $sesi->id)
                ->whereIn('project_card_id', $allCardIds)
                ->get()
                ->groupBy('project_card_id');
        }

        $evaluasiMitraCollections = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable((new EvaluasiMitra)->getTable())) {
            $evaluasiMitraCollections = EvaluasiMitra::with(['mahasiswa:id,nim,nama_mahasiswa'])
                ->where('evaluasi_sesi_id', $sesi->id)
                ->whereIn('project_card_id', $allCardIds)
                ->get()
                ->groupBy('project_card_id');
        }

        $cardGradesMap = [];
        // First, initialize all cards with empty data
        foreach ($allCardIds as $cardId) {
            $cardGradesMap[$cardId] = [
                'dosen' => null,
                'mitra' => null,
                'evaluasi_dosen' => null,
                'evaluasi_dosen_details' => collect(),
                'evaluasi_dosen_summary' => [
                    'avg' => null,
                    'count' => 0,
                ],
                'evaluasi_mitra' => null,
                'evaluasi_mitra_details' => collect(),
                'evaluasi_mitra_summary' => [
                    'avg' => null,
                    'count' => 0,
                ],
            ];
        }

        // Fill in EvaluasiProyekNilai data
        foreach ($cardGrades as $cid => $byJenis) {
            $cardGradesMap[$cid]['dosen'] = isset($byJenis['dosen']) ? $byJenis['dosen'][0] : null;
            $cardGradesMap[$cid]['mitra'] = isset($byJenis['mitra']) ? $byJenis['mitra'][0] : null;
        }

        // Fill in EvaluasiDosen data
        foreach ($evaluasiDosenCollections as $projectCardId => $collection) {
            $collection = $collection->sortBy(fn ($row) => optional($row->mahasiswa)->nama ?? optional($row->mahasiswa)->nama_mahasiswa ?? $row->mahasiswa_id);
            $cardGradesMap[$projectCardId]['evaluasi_dosen_details'] = $collection;
            $cardGradesMap[$projectCardId]['evaluasi_dosen'] = $collection->sortByDesc('updated_at')->first();
            $cardGradesMap[$projectCardId]['evaluasi_dosen_summary'] = [
                'avg' => $collection->avg('nilai_akhir') !== null ? (int) round($collection->avg('nilai_akhir')) : null,
                'count' => $collection->count(),
            ];
        }

        foreach ($evaluasiMitraCollections as $projectCardId => $collection) {
            $collection = $collection->sortBy(fn ($row) => optional($row->mahasiswa)->nama ?? optional($row->mahasiswa)->nama_mahasiswa ?? $row->mahasiswa_id);
            $cardGradesMap[$projectCardId]['evaluasi_mitra_details'] = $collection;
            $cardGradesMap[$projectCardId]['evaluasi_mitra'] = $collection->sortByDesc('updated_at')->first();
            $cardGradesMap[$projectCardId]['evaluasi_mitra_summary'] = [
                'avg' => $collection->avg('nilai_akhir') !== null ? (int) round($collection->avg('nilai_akhir')) : null,
                'count' => $collection->count(),
            ];
        }

        // Hitung agregat per list (rata-rata per list)
        $listAggDosen = [];
        $listAggMitra = [];
        foreach ($proyekLists as $list) {
            $totalsD = [];
            $totalsM = [];
            foreach ($list->cards as $c) {
                $dSummary = $cardGradesMap[$c->id]['evaluasi_dosen_summary'] ?? null;
                $dTot = (int) ($dSummary['avg'] ?? 0);
                if ($dTot <= 0) {
                    $d = optional(optional($cardGrades[$c->id] ?? null)['dosen'] ?? null);
                    $dTot = (int) ($d[0]->total ?? 0);
                }
                if ($dTot > 0) {
                    $totalsD[] = $dTot;
                }

                $mSummary = $cardGradesMap[$c->id]['evaluasi_mitra_summary'] ?? null;
                $mTot = (int) ($mSummary['avg'] ?? 0);
                if ($mTot <= 0) {
                    $m = optional(optional($cardGrades[$c->id] ?? null)['mitra'] ?? null);
                    $mTot = (int) ($m[0]->total ?? 0);
                }
                if ($mTot > 0) {
                    $totalsM[] = $mTot;
                }
            }
            $listAggDosen[$list->id] = $totalsD ? (int) round(array_sum($totalsD) / count($totalsD)) : 0;
            $listAggMitra[$list->id] = $totalsM ? (int) round(array_sum($totalsM) / count($totalsM)) : 0;
        }

        $nilaiDosenByCard = (int) round(($listAggDosen ? array_sum($listAggDosen) / max(1, count($listAggDosen)) : 0));
        $nilaiMitraByCard = (int) round(($listAggMitra ? array_sum($listAggMitra) / max(1, count($listAggMitra)) : 0));

        $nilaiProyekByCard = (int) round($nilaiDosenByCard * ($settings['w_dosen'] ?? 80) / 100 + $nilaiMitraByCard * ($settings['w_mitra'] ?? 20) / 100);

        return view('admin.evaluasi.show', [
            'kelompok' => $kelompok,
            'periode' => $activePeriode,
            'sesi' => $sesi,
            'evaluator' => $sesi->evaluator,
            'anggota' => $anggota,
            'proyekLists' => $proyekLists,
            'proyek_total_cards' => $proyek_total_cards,
            'aktivitasLists' => $aktivitasLists,
            'aktivitas_total' => $aktivitas_total,
            'settings' => $settings,
            'mitra' => $mitra,
            'listAggDosen' => $listAggDosen,
            'listAggMitra' => $listAggMitra,
            'nilaiDosenByCard' => $nilaiDosenByCard,
            'nilaiMitraByCard' => $nilaiMitraByCard,
            'nilaiProyekByCard' => $nilaiProyekByCard,
            'cardGrades' => $cardGradesMap,
            'studentEvaluations' => $studentEvaluations,
            'studentEvalStats' => $studentEvalStats,
        ]);
    }

    /** ===== AKSI STATUS SESI ===== */
    public function start(EvaluasiMaster $sesi)
    {
        $payload = [
            'updated_by' => Auth::id(),
        ];
        if (empty($sesi->jadwal_mulai)) {
            $payload['jadwal_mulai'] = now();
        }
        $sesi->update($payload);

        Alert::success('Berlangsung', 'Sesi evaluasi dimulai.');

        return back();
    }

    public function finish(EvaluasiMaster $sesi)
    {
        $payload = [
            'updated_by' => Auth::id(),
        ];
        if (empty($sesi->jadwal_selesai)) {
            $payload['jadwal_selesai'] = now();
        }
        $sesi->update($payload);

        Alert::success('Selesai', 'Sesi evaluasi diselesaikan.');

        return back();
    }

    /** ===== JADWAL MASSAL ===== */
    public function scheduleBulkForm(Request $req)
    {
        $periodes = Periode::orderByDesc('id')->get(['id', 'periode', 'status_periode']);
        $periodeId = (int) $req->get('periode_id', 0);

        return view('admin.evaluasi.sesi-schedule-bulk', compact('periodes', 'periodeId'));
    }

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
        $durasi = (int) ($data['durasi_menit'] ?? 90);

        $kelompokQuery = Kelompok::where('periode_id', $periodeId);
        if (! empty($data['selected_ids'])) {
            $kelompokQuery->whereIn('id', $data['selected_ids']);
        }
        $kelompokIds = $kelompokQuery->pluck('id');

        foreach ($kelompokIds as $kid) {
            $s = EvaluasiMaster::firstOrCreate([
                'periode_id' => $periodeId, 'kelompok_id' => $kid,
            ], ['created_by' => Auth::id()]);

            if (! empty($data['jadwal_mulai'])) {
                $s->jadwal_mulai = $data['jadwal_mulai'];
                $s->jadwal_selesai = ! empty($data['jadwal_selesai'])
                    ? $data['jadwal_selesai']
                    : Carbon::parse($data['jadwal_mulai'])->addMinutes($durasi)->format('Y-m-d H:i:s');
                $s->evaluator_id = $data['evaluator_id'] ?? $s->evaluator_id;
                $s->lokasi = $data['lokasi'] ?? $s->lokasi;
                $s->status = // JADWAL;
                $s->save();
            }
        }

        Alert::success('Selesai', 'Penjadwalan massal selesai.');

        return redirect()->route('admin.evaluasi.index', ['periode_id' => $periodeId]);
    }

    /** ===== PENGATURAN ===== */
    public function settings()
    {
        $keys = [
            'w_dosen', 'w_mitra',
            'd_hasil', 'd_teknis', 'd_user', 'd_efisiensi', 'd_dokpro', 'd_inisiatif',
            'm_kehadiran', 'm_presentasi',
            'w_kelompok', 'w_ap',
            'w_ap_kehadiran', 'w_ap_presentasi',
        ];
        $defaults = [
            'w_dosen' => 80, 'w_mitra' => 20,
            'd_hasil' => 30, 'd_teknis' => 20, 'd_user' => 15, 'd_efisiensi' => 10, 'd_dokpro' => 15, 'd_inisiatif' => 10,
            'm_kehadiran' => 50, 'm_presentasi' => 50,
            'w_kelompok' => 70, 'w_ap' => 30,
            'w_ap_kehadiran' => 50, 'w_ap_presentasi' => 50,
        ];
        if (Schema::hasTable((new EvaluasiSetting)->getTable())) {
            $settings = EvaluasiSetting::getMany($keys, $defaults);
        } else {
            $settings = $defaults;
        }

        return view('admin.evaluasi.settings', compact('settings'));
    }

    public function saveSettings(Request $req)
    {
        $data = $req->validate([
            'w_dosen' => 'required|integer|min:0|max:100',
            'w_mitra' => 'required|integer|min:0|max:100',
            'd_hasil' => 'required|integer|min:0|max:100',
            'd_teknis' => 'required|integer|min:0|max:100',
            'd_user' => 'required|integer|min:0|max:100',
            'd_efisiensi' => 'required|integer|min:0|max:100',
            'd_dokpro' => 'required|integer|min:0|max:100',
            'd_inisiatif' => 'required|integer|min:0|max:100',
            'm_kehadiran' => 'required|integer|min:0|max:100',
            'm_presentasi' => 'required|integer|min:0|max:100',
            'w_kelompok' => 'required|integer|min:0|max:100',
            'w_ap' => 'required|integer|min:0|max:100',
            'w_ap_kehadiran' => 'required|integer|min:0|max:100',
            'w_ap_presentasi' => 'required|integer|min:0|max:100',
        ]);

        $sumDosen = $data['d_hasil'] + $data['d_teknis'] + $data['d_user'] + $data['d_efisiensi'] + $data['d_dokpro'] + $data['d_inisiatif'];
        if ($sumDosen != 100) {
            $data['d_inisiatif'] = max(0, 100 - ($sumDosen - $data['d_inisiatif']));
        }

        $sumMitra = $data['m_kehadiran'] + $data['m_presentasi'];
        if ($sumMitra != 100) {
            $data['m_presentasi'] = max(0, 100 - ($sumMitra - $data['m_presentasi']));
        }

        if ($data['w_kelompok'] + $data['w_ap'] != 100) {
            $data['w_ap'] = max(0, 100 - $data['w_kelompok']);
        }
        if ($data['w_ap_kehadiran'] + $data['w_ap_presentasi'] != 100) {
            $data['w_ap_presentasi'] = max(0, 100 - $data['w_ap_kehadiran']);
        }

        EvaluasiSetting::putMany($data);
        Alert::success('Tersimpan', 'Pengaturan evaluasi berhasil disimpan.');

        return back();
    }

    /** ===== PROJECT TIMELINE ===== */
    public function projectTimeline(Kelompok $kelompok)
    {
        $activePeriode = Periode::where('status_periode', 'Aktif')->orderByDesc('id')->first();
        abort_unless($activePeriode, 404, 'Periode aktif tidak ditemukan');

        $proyekLists = ProjectList::with(['cards' => function ($q) use ($kelompok) {
            $q->where('kelompok_id', $kelompok->id)
                ->orderBy('tanggal_mulai')
                ->orderBy('due_date');
        }])->orderBy('position')->get();

        return view('admin.evaluasi.project-timeline', [
            'kelompok' => $kelompok,
            'periode' => $activePeriode,
            'proyekLists' => $proyekLists,
        ]);
    }

    /** ===== Drag & Drop: kartu ===== */
    public function reorderProjectCard(Request $request)
    {
        $data = $request->validate([
            'card_id' => 'required|integer',
            'to_list' => 'required|integer',
            'position' => 'required|integer|min:0',
        ]);

        $card = ProjectCard::find($data['card_id']);
        $toList = ProjectList::find($data['to_list']);
        if (! $card || ! $toList) {
            return response()->json(['status' => 'not_found'], 404);
        }

        DB::transaction(function () use ($card, $toList, $data) {
            $fromListId = $card->list_id;
            $oldPos = (int) $card->position;
            $newPos = (int) $data['position'];

            if ($fromListId === $toList->id) {
                if ($newPos > $oldPos) {
                    ProjectCard::where('list_id', $fromListId)
                        ->whereBetween('position', [$oldPos + 1, $newPos])
                        ->decrement('position');
                } elseif ($newPos < $oldPos) {
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
                'updated_by' => Auth::id(),
            ]);
        });

        return response()->json(['status' => 'ok']);
    }

    /** ===== Drag & Drop: kolom ===== */
    public function reorderProjectLists(Request $request)
    {
        $ids = $request->input('list_ids');
        if (! is_array($ids) || empty($ids)) {
            return response()->json(['status' => 'invalid'], 422);
        }

        DB::transaction(function () use ($ids) {
            foreach ($ids as $idx => $listId) {
                ProjectList::where('id', (int) $listId)->update(['position' => $idx]);
            }
        });

        return response()->json(['status' => 'ok']);
    }

    /** ===== Quick action: update progress ===== */
    public function updateProjectProgress(Request $request, ProjectCard $card)
    {
        $data = $request->validate(['progress' => 'required|integer|min:0|max:100']);
        $card->update([
            'progress' => $data['progress'],
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'progress' => $card->progress]);
    }

    /** ===== Quick action: update status ===== */
    public function updateProjectStatus(Request $request, ProjectCard $card)
    {
        $data = $request->validate(['status' => 'required|string|in:Proses,Selesai,Dibatalkan']);

        // Simpan ke kedua kolom untuk kompatibilitas
        $card->update([
            'status' => $data['status'],
            'status_proyek' => $data['status'],
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'status' => $card->status]);
    }

    /** ===== PROJECT EXPORT ===== */
    public function projectExport(Kelompok $kelompok)
    {
        $activePeriode = Periode::where('status_periode', 'Aktif')->orderByDesc('id')->first();
        abort_unless($activePeriode, 404, 'Periode aktif tidak ditemukan');

        $proyekLists = ProjectList::with(['cards' => function ($q) use ($kelompok) {
            $q->where('kelompok_id', $kelompok->id)
                ->orderBy('tanggal_mulai')
                ->orderBy('due_date');
        }])->orderBy('position')->get();

        $aktivitasLists = AktivitasList::with(['cards' => function ($q) use ($kelompok) {
            $q->where('kelompok_id', $kelompok->id)
                ->orderBy('tanggal_mulai')
                ->orderBy('due_date');
        }])->orderBy('position')->get();

        $fileName = 'proyek_'.Str::slug($kelompok->nama_kelompok).'_'.date('Y-m-d').'.xlsx';

        return Excel::download(new ProjectExport($kelompok, $proyekLists, $aktivitasLists), $fileName);
    }

    /** ===== UTIL (opsional) ===== */
    private function ensureSessions(int $periodeId): int
    {
        $kelompokIds = Kelompok::where('periode_id', $periodeId)->pluck('id');
        if ($kelompokIds->isEmpty()) {
            return 0;
        }

        $existing = EvaluasiMaster::where('periode_id', $periodeId)
            ->whereIn('kelompok_id', $kelompokIds)
            ->pluck('kelompok_id')
            ->all();

        $missing = $kelompokIds->diff($existing);
        foreach ($missing as $kid) {
            EvaluasiMaster::create([
                'uuid' => (string) Str::uuid(),
                'periode_id' => $periodeId,
                'kelompok_id' => (int) $kid,
                'created_by' => Auth::id(),
            ]);
        }

        return $missing->count();
    }

    public function updateProject(Request $request, ProjectCard $card)
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
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
            'status' => 'nullable|in:Proses,Selesai,Dibatalkan',
        ]);

        $labels = null;
        if (! empty($data['labels'])) {
            $labels = collect(explode(',', $data['labels']))->map(fn ($s) => trim($s))->filter()->values()->all();
        }

        $card->title = $data['title'] ?? $card->title;
        $card->description = $data['description'] ?? $card->description;
        if ($labels !== null) {
            $card->labels = $labels;
        }
        $card->tanggal_mulai = $data['tanggal_mulai'] ?? $card->tanggal_mulai;
        $card->tanggal_selesai = $data['tanggal_selesai'] ?? ($data['due_date'] ?? $card->tanggal_selesai);
        $card->due_date = $data['due_date'] ?? ($data['tanggal_selesai'] ?? $card->due_date);
        if (array_key_exists('progress', $data)) {
            $card->progress = $data['progress'];
        }
        $card->nama_mitra = $data['nama_mitra'] ?? $card->nama_mitra;
        $card->kontak_mitra = $data['kontak_mitra'] ?? $card->kontak_mitra;
        $card->skema_pbl = $data['skema_pbl'] ?? $card->skema_pbl;
        if (array_key_exists('biaya_barang', $data)) {
            $card->biaya_barang = $data['biaya_barang'];
        }
        if (array_key_exists('biaya_jasa', $data)) {
            $card->biaya_jasa = $data['biaya_jasa'];
        }
        if (! empty($data['status_proyek'])) {
            $card->status_proyek = $data['status_proyek'];
        }
        if (! empty($data['status'])) {
            $card->status = $data['status'];
        }
        if (! empty($data['link_drive_proyek'])) {
            $card->link_drive_proyek = $data['link_drive_proyek'];
        }
        if (array_key_exists('kendala', $data)) {
            $card->kendala = $data['kendala'];
        }
        if (array_key_exists('catatan', $data)) {
            $card->catatan = $data['catatan'];
        }
        $card->updated_by = Auth::id();
        $card->save();

        return response()->json(['success' => true]);
    }

    public function destroyProject(ProjectCard $card)
    {
        $card->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kartu proyek berhasil dihapus',
        ]);
    }

    /** ===== PENILAIAN PROYEK: Dosen ===== */
    public function saveProjectGradeDosen(Request $request, ProjectCard $card)
    {
        try {
            // Check if user is authenticated
            if (! auth()->check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak terautentikasi. Silakan login ulang.',
                ], 401);
            }

            $sesiId = (int) $request->input('sesi_id');
            $items = $request->input('items', []);

            // Debug log
            \Log::info('saveProjectGradeDosen called', [
                'card_id' => $card->id,
                'sesi_id' => $sesiId,
                'items_count' => count($items),
                'user_id' => auth()->id(),
            ]);

            // Get project card details
            $kelompokId = $card->list?->kelompok_id;
            $periodeId = $card->list?->periode_id;

            if (! $kelompokId || ! $periodeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project card tidak memiliki kelompok atau periode. Silakan pilih project yang valid.',
                ]);
            }

            if (empty($items)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data nilai yang dikirim.',
                ]);
            }

            $savedEvaluations = [];
            $skippedCount = 0;

            foreach ($items as $mahasiswaId => $nilai) {
                // Validate mahasiswa exists
                $mahasiswa = \App\Models\Mahasiswa::find($mahasiswaId);
                if (! $mahasiswa) {
                    \Log::warning('Mahasiswa not found', ['mahasiswa_id' => $mahasiswaId]);
                    $skippedCount++;

                    continue;
                }

                // Check if mahasiswa belongs to kelompok using model relationship
                $isInKelompok = $mahasiswa->kelompoks()
                    ->where('kelompok.id', $kelompokId)
                    ->where('kelompok_mahasiswa.periode_id', $periodeId)
                    ->exists();

                if (! $isInKelompok) {
                    \Log::warning('Mahasiswa not in kelompok', [
                        'mahasiswa_id' => $mahasiswaId,
                        'mahasiswa_name' => $mahasiswa->nama_mahasiswa,
                        'expected_kelompok' => $kelompokId,
                        'expected_periode' => $periodeId,
                    ]);
                    $skippedCount++;

                    continue;
                }

                // Validate nilai values
                foreach ($nilai as $kriteria => $value) {
                    if (! in_array($kriteria, ['d_hasil', 'd_teknis', 'd_user', 'd_efisiensi', 'd_dokpro', 'd_inisiatif'])) {
                        unset($nilai[$kriteria]);
                    } else {
                        $nilai[$kriteria] = max(0, min(100, (int) $value));
                    }
                }

                // Create or update evaluation
                $evaluasi = \App\Models\EvaluasiDosen::updateOrCreate([
                    'evaluasi_sesi_id' => $sesiId ?: null,
                    'periode_id' => $periodeId,
                    'kelompok_id' => $kelompokId,
                    'mahasiswa_id' => $mahasiswaId,
                    'project_card_id' => $card->id,
                    'evaluator_id' => auth()->id(),
                ], [
                    'd_hasil' => $nilai['d_hasil'] ?? 0,
                    'd_teknis' => $nilai['d_teknis'] ?? 0,
                    'd_user' => $nilai['d_user'] ?? 0,
                    'd_efisiensi' => $nilai['d_efisiensi'] ?? 0,
                    'd_dokpro' => $nilai['d_dokpro'] ?? 0,
                    'd_inisiatif' => $nilai['d_inisiatif'] ?? 0,
                    'progress_percentage' => $card->progress ?? 0,
                    'status' => 'submitted',
                    'submitted_at' => now(),
                    'evaluation_type' => 'regular',
                ]);

                $savedEvaluations[] = $evaluasi;
            }

            if (empty($savedEvaluations)) {
                return response()->json([
                    'success' => false,
                    'message' => "Tidak ada data yang berhasil disimpan. {$skippedCount} data dilewati karena tidak valid.",
                ]);
            }

            $message = 'Nilai dosen berhasil disimpan untuk '.count($savedEvaluations).' mahasiswa';
            if ($skippedCount > 0) {
                $message .= " ({$skippedCount} data dilewati)";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'evaluations_count' => count($savedEvaluations),
                'skipped_count' => $skippedCount,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in saveProjectGradeDosen', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    /** ===== PENILAIAN PROYEK: Mitra ===== */
    public function saveProjectGradeMitra(Request $request, ProjectCard $card)
    {
        if (! auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak terautentikasi. Silakan login ulang.',
            ], 401);
        }

        $sesiId = (int) $request->input('sesi_id');
        $items = $request->input('items', []);

        if (! is_array($items) || empty($items)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data nilai yang dikirim.',
            ], 422);
        }

        $kelompokId = $card->kelompok_id ?? $card->list?->kelompok_id;
        $periodeId = $card->periode_id ?? $card->list?->periode_id;

        if (! $kelompokId || ! $periodeId) {
            return response()->json([
                'success' => false,
                'message' => 'Project card tidak memiliki informasi kelompok/periode yang valid.',
            ], 422);
        }

        $savedEvaluations = [];
        $skippedCount = 0;

        foreach ($items as $mahasiswaId => $nilai) {
            $mahasiswa = \App\Models\Mahasiswa::find($mahasiswaId);
            if (! $mahasiswa) {
                \Log::warning('Mahasiswa not found for mitra evaluation', ['mahasiswa_id' => $mahasiswaId]);
                $skippedCount++;

                continue;
            }

            $isInKelompok = $mahasiswa->kelompoks()
                ->where('kelompok.id', $kelompokId)
                ->where('kelompok_mahasiswa.periode_id', $periodeId)
                ->exists();

            if (! $isInKelompok) {
                \Log::warning('Mahasiswa not in kelompok for mitra evaluation', [
                    'mahasiswa_id' => $mahasiswaId,
                    'expected_kelompok' => $kelompokId,
                    'expected_periode' => $periodeId,
                ]);
                $skippedCount++;

                continue;
            }

            $scores = [
                'm_kehadiran' => array_key_exists('m_kehadiran', (array) $nilai) ? max(0, min(100, (int) $nilai['m_kehadiran'])) : null,
                'm_presentasi' => array_key_exists('m_presentasi', (array) $nilai) ? max(0, min(100, (int) $nilai['m_presentasi'])) : null,
            ];

            if ($scores['m_kehadiran'] === null && $scores['m_presentasi'] === null) {
                $skippedCount++;

                continue;
            }

            $evaluation = EvaluasiMitra::updateOrCreate([
                'evaluasi_sesi_id' => $sesiId ?: null,
                'periode_id' => $periodeId,
                'kelompok_id' => $kelompokId,
                'mahasiswa_id' => $mahasiswaId,
                'project_card_id' => $card->id,
            ], [
                'm_kehadiran' => $scores['m_kehadiran'],
                'm_presentasi' => $scores['m_presentasi'],
                'progress_percentage' => $card->progress ?? 0,
                'status' => 'submitted',
                'submitted_at' => now(),
                'evaluation_type' => 'regular',
                'evaluator_id' => Auth::id(),
            ]);

            $savedEvaluations[] = $evaluation;
        }

        if (empty($savedEvaluations)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data yang berhasil disimpan untuk mitra.',
                'skipped_count' => $skippedCount,
            ], 422);
        }

        // Update legacy aggregate table for compatibility
        $avgMitra = EvaluasiMitra::where('project_card_id', $card->id)
            ->whereNull('deleted_at')
            ->avg('nilai_akhir');

        if ($avgMitra !== null && Schema::hasTable((new EvaluasiProyekNilai)->getTable())) {
            $total = (int) round($avgMitra);
            EvaluasiProyekNilai::updateOrCreate([
                'sesi_id' => $sesiId ?: null,
                'card_id' => $card->id,
                'jenis' => 'mitra',
            ], [
                'nilai' => ['avg' => $avgMitra],
                'total' => $total,
                'updated_by' => Auth::id(),
                'created_by' => Auth::id(),
            ]);
        }

        $message = 'Nilai mitra berhasil disimpan untuk '.count($savedEvaluations).' mahasiswa';
        if ($skippedCount > 0) {
            $message .= " ({$skippedCount} data dilewati)";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'evaluations_count' => count($savedEvaluations),
            'skipped_count' => $skippedCount,
        ]);
    }

    /** ====== SAVE: Absensi per sesi & mahasiswa ====== */
    public function saveAbsensi(Request $request, EvaluasiMaster $sesi)
    {
        $data = $request->validate([
            'mahasiswa_id' => 'required|integer',
            'status' => 'required|string|in:Hadir,Terlambat,Sakit,Dispensasi,Alpa',
            'waktu_absen' => 'nullable|date',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $row = EvaluasiAbsensi::updateOrCreate(
            ['sesi_id' => $sesi->id, 'mahasiswa_id' => $data['mahasiswa_id']],
            [
                'status' => $data['status'],
                'waktu_absen' => $data['waktu_absen'] ?? now(),
                'keterangan' => $data['keterangan'] ?? null,
            ]
        );

        return response()->json(['success' => true, 'data' => $row]);
    }

    /** ====== SAVE: AP (Kehadiran & Presentasi) per mahasiswa ====== */
    public function saveAP(Request $request, EvaluasiMaster $sesi)
    {
        $data = $request->validate([
            'mahasiswa_id' => 'required|integer',
            'kehadiran' => 'required|integer|min:0|max:100',
            'presentasi' => 'required|integer|min:0|max:100',
        ]);

        // Ambil indikator_id untuk kode m_kehadiran & m_presentasi dari sesi ini
        $indikators = EvaluasiSesiIndikator::with('indikator')
            ->where('sesi_id', $sesi->id)
            ->get()
            ->keyBy(fn ($si) => optional($si->indikator)->kode);

        $idKeh = optional($indikators->get('m_kehadiran'))->indikator_id;
        $idPre = optional($indikators->get('m_presentasi'))->indikator_id;

        if (! $idKeh || ! $idPre) {
            return response()->json(['success' => false, 'message' => 'Indikator AP belum diset untuk sesi ini'], 422);
        }

        DB::transaction(function () use ($sesi, $data, $idKeh, $idPre) {
            EvaluasiNilaiDetail::updateOrCreate(
                ['sesi_id' => $sesi->id, 'mahasiswa_id' => $data['mahasiswa_id'], 'indikator_id' => $idKeh],
                ['skor' => $data['kehadiran']]
            );
            EvaluasiNilaiDetail::updateOrCreate(
                ['sesi_id' => $sesi->id, 'mahasiswa_id' => $data['mahasiswa_id'], 'indikator_id' => $idPre],
                ['skor' => $data['presentasi']]
            );
        });

        return response()->json(['success' => true]);
    }

    /** ====== SAVE: Skor indikator dosen (per sesi) ====== */
    public function saveSesiIndikators(Request $request, EvaluasiMaster $sesi)
    {
        $items = $request->input('items');
        if (! is_array($items) || empty($items)) {
            return response()->json(['success' => false, 'message' => 'Data kosong'], 422);
        }

        DB::transaction(function () use ($items, $sesi) {
            foreach ($items as $indikatorId => $val) {
                $skor = (int) ($val['skor'] ?? 0);
                $kom = isset($val['komentar']) ? (string) $val['komentar'] : null;
                EvaluasiSesiIndikator::where('sesi_id', $sesi->id)
                    ->where('indikator_id', (int) $indikatorId)
                    ->update([
                        'skor' => max(0, min(100, $skor)),
                        'komentar' => $kom,
                        'updated_at' => now(),
                    ]);
            }
        });

        return response()->json(['success' => true]);
    }

    /** ====== EVALUASI DOSEN CRUD ====== */

    /**
     * Get evaluations by project card
     */
    public function getPenilaianDosenByProject(Request $request, $project)
    {
        $projectCard = ProjectCard::where('uuid', $project)->orWhere('id', $project)->firstOrFail();

        $evaluations = EvaluasiDosen::where('project_card_id', $projectCard->id)
            ->with([
                'mahasiswa:id,nim,nama_mahasiswa',
                'evaluator:id,name',
            ])
            ->get()
            ->map(function ($evaluation) {
                $mahasiswa = $evaluation->mahasiswa;

                return [
                    'id' => $evaluation->uuid,
                    'mahasiswa_id' => $evaluation->mahasiswa_id,
                    'mahasiswa_nama' => optional($mahasiswa)->nama ?? optional($mahasiswa)->nama_mahasiswa ?? '',
                    'mahasiswa_nim' => optional($mahasiswa)->nim ?? '',
                    'd_hasil' => $evaluation->d_hasil,
                    'd_teknis' => $evaluation->d_teknis,
                    'd_user' => $evaluation->d_user,
                    'd_efisiensi' => $evaluation->d_efisiensi,
                    'd_dokpro' => $evaluation->d_dokpro,
                    'd_inisiatif' => $evaluation->d_inisiatif,
                    'rata_rata' => $evaluation->rata_rata,
                    'nilai_akhir' => $evaluation->nilai_akhir,
                    'grade' => $evaluation->grade,
                    'status' => $evaluation->status,
                    'evaluator_nama' => $evaluation->evaluator->name ?? '',
                    'created_at' => $evaluation->created_at,
                    'updated_at' => $evaluation->updated_at,
                ];
            });

        return response()->json([
            'success' => true,
            'evaluations' => $evaluations,
        ]);
    }

    public function getPenilaianMitraByProject(Request $request, $project)
    {
        $projectCard = ProjectCard::where('uuid', $project)->orWhere('id', $project)->firstOrFail();

        $evaluations = EvaluasiMitra::where('project_card_id', $projectCard->id)
            ->with([
                'mahasiswa:id,nim,nama_mahasiswa',
                'evaluator:id,name',
            ])
            ->get()
            ->map(function ($evaluation) {
                $mahasiswa = $evaluation->mahasiswa;

                return [
                    'id' => $evaluation->uuid,
                    'mahasiswa_id' => $evaluation->mahasiswa_id,
                    'mahasiswa_nama' => optional($mahasiswa)->nama ?? optional($mahasiswa)->nama_mahasiswa ?? '',
                    'mahasiswa_nim' => optional($mahasiswa)->nim ?? '',
                    'm_kehadiran' => $evaluation->m_kehadiran,
                    'm_presentasi' => $evaluation->m_presentasi,
                    'rata_rata' => $evaluation->rata_rata,
                    'nilai_akhir' => $evaluation->nilai_akhir,
                    'grade' => $evaluation->grade,
                    'status' => $evaluation->status,
                    'evaluator_nama' => optional($evaluation->evaluator)->name ?? '',
                    'created_at' => $evaluation->created_at,
                    'updated_at' => $evaluation->updated_at,
                ];
            });

        return response()->json([
            'success' => true,
            'evaluations' => $evaluations,
        ]);
    }

    /**
     * Store new evaluation
     */
    public function storePenilaianDosen(Request $request)
    {
        // Debug log the incoming request
        \Log::info('storePenilaianDosen called', [
            'request_data' => $request->all(),
            'request_headers' => $request->headers->all(),
            'content_type' => $request->header('Content-Type'),
            'user_id' => auth()->id(),
        ]);

        try {
            // Cek minimal satu kriteria harus diisi
            $criteria = ['d_hasil', 'd_teknis', 'd_user', 'd_efisiensi', 'd_dokpro', 'd_inisiatif'];
            $hasAnyCriteria = false;
            foreach ($criteria as $criterion) {
                if ($request->filled($criterion)) {
                    $hasAnyCriteria = true;
                    break;
                }
            }

            // Jika tidak ada kriteria yang diisi, masih boleh simpan sebagai draft
            $validated = $request->validate([
                'evaluasi_sesi_id' => 'nullable|exists:evaluasi_sesi,id',
                'periode_id' => 'required|exists:periode,id',
                'kelompok_id' => 'required|exists:kelompok,id',
                'mahasiswa_id' => 'required|exists:mahasiswa,id',
                'project_card_id' => 'required|exists:project_cards,uuid',
                'evaluator_id' => 'nullable|exists:users,id',
                'd_hasil' => 'nullable|integer|min:0|max:100',
                'd_teknis' => 'nullable|integer|min:0|max:100',
                'd_user' => 'nullable|integer|min:0|max:100',
                'd_efisiensi' => 'nullable|integer|min:0|max:100',
                'd_dokpro' => 'nullable|integer|min:0|max:100',
                'd_inisiatif' => 'nullable|integer|min:0|max:100',
                'progress_percentage' => 'nullable|numeric|min:0|max:100',
                'evaluation_type' => 'nullable|in:regular,remedial,improvement',
                'catatan' => 'nullable|string|max:1000',
            ]);

            // Add default values for nullable fields
            if (empty($validated['evaluator_id']) && auth()->check()) {
                $validated['evaluator_id'] = auth()->id();
            }
            $validated['evaluasi_sesi_id'] = $validated['evaluasi_sesi_id'] ?? null;
            $validated['progress_percentage'] = $validated['progress_percentage'] ?? 0;
            $validated['evaluation_type'] = $validated['evaluation_type'] ?? 'regular';
            $validated['status'] = 'draft';
            $validated['tanggal_evaluasi'] = now();

            // Manual create to bypass model saving event if all criteria are null
            if (! $hasAnyCriteria) {
                $evaluation = new EvaluasiDosen;
                $evaluation->fill($validated);
                $evaluation->uuid = \Illuminate\Support\Str::uuid();
                $evaluation->save();
            } else {
                $evaluation = EvaluasiDosen::create($validated);
            }

            return response()->json([
                'success' => true,
                'message' => 'Nilai berhasil disimpan',
                'evaluation_id' => $evaluation->uuid,
                'nilai_akhir' => $evaluation->nilai_akhir,
                'grade' => $evaluation->grade,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Log validation errors for debugging
            \Log::error('Validation error in storePenilaianDosen', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
                'failed_rules' => $e->validator->failed(),
            ]);

            // Return detailed validation errors
            return response()->json([
                'success' => false,
                'message' => 'Validation error: '.json_encode($e->errors()),
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan nilai: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Batch store multiple evaluations
     */
    public function batchStorePenilaianDosen(Request $request)
    {
        try {
            $validated = $request->validate([
                'evaluations' => 'required|array',
                'evaluations.*.evaluasi_sesi_id' => 'nullable|exists:evaluasi_sesi,id',
                'evaluations.*.periode_id' => 'required|exists:periode,id',
                'evaluations.*.kelompok_id' => 'required|exists:kelompok,id',
                'evaluations.*.mahasiswa_id' => 'required|exists:mahasiswa,id',
                'evaluations.*.project_card_id' => 'required|exists:project_cards,uuid',
                'evaluations.*.evaluator_id' => 'nullable|exists:users,id',
                'evaluations.*.d_hasil' => 'nullable|integer|min:0|max:100',
                'evaluations.*.d_teknis' => 'nullable|integer|min:0|max:100',
                'evaluations.*.d_user' => 'nullable|integer|min:0|max:100',
                'evaluations.*.d_efisiensi' => 'nullable|integer|min:0|max:100',
                'evaluations.*.d_dokpro' => 'nullable|integer|min:0|max:100',
                'evaluations.*.d_inisiatif' => 'nullable|integer|min:0|max:100',
                'evaluations.*.progress_percentage' => 'nullable|numeric|min:0|max:100',
                'evaluations.*.evaluation_type' => 'nullable|in:regular,remedial,improvement',
                'evaluations.*.catatan' => 'nullable|string|max:1000',
            ]);

            $evaluations = [];
            $successCount = 0;
            $errorCount = 0;

            foreach ($validated['evaluations'] as $evaluationData) {
                try {
                    // Add default values
                    if (empty($evaluationData['evaluator_id']) && auth()->check()) {
                        $evaluationData['evaluator_id'] = auth()->id();
                    }
                    $evaluationData['evaluasi_sesi_id'] = $evaluationData['evaluasi_sesi_id'] ?? null;
                    $evaluationData['progress_percentage'] = $evaluationData['progress_percentage'] ?? 0;
                    $evaluationData['evaluation_type'] = $evaluationData['evaluation_type'] ?? 'regular';
                    $evaluationData['status'] = 'draft';
                    $evaluationData['tanggal_evaluasi'] = now();

                    // Create evaluation
                    $evaluation = EvaluasiDosen::create($evaluationData);
                    $evaluations[] = [
                        'uuid' => $evaluation->uuid,
                        'nilai_akhir' => $evaluation->nilai_akhir,
                        'grade' => $evaluation->grade,
                    ];
                    $successCount++;

                } catch (\Exception $e) {
                    \Log::error('Error creating evaluation in batch', [
                        'error' => $e->getMessage(),
                        'data' => $evaluationData,
                    ]);
                    $errorCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Berhasil menyimpan {$successCount} evaluasi".($errorCount > 0 ? " dengan {$errorCount} error" : ''),
                'evaluations' => $evaluations,
                'success_count' => $successCount,
                'error_count' => $errorCount,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error: '.json_encode($e->errors()),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan nilai batch: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update existing evaluation
     */
    public function updatePenilaianDosen(Request $request, $penilaian)
    {
        $evaluation = EvaluasiDosen::where('uuid', $penilaian)->orWhere('id', $penilaian)->firstOrFail();

        $validated = $request->validate([
            'd_hasil' => 'nullable|integer|min:0|max:100',
            'd_teknis' => 'nullable|integer|min:0|max:100',
            'd_user' => 'nullable|integer|min:0|max:100',
            'd_efisiensi' => 'nullable|integer|min:0|max:100',
            'd_dokpro' => 'nullable|integer|min:0|max:100',
            'd_inisiatif' => 'nullable|integer|min:0|max:100',
            'catatan' => 'nullable|string|max:1000',
            'progress_percentage' => 'required|numeric|min:0|max:100',
        ]);

        try {
            $evaluation->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Nilai berhasil diperbarui',
                'nilai_akhir' => $evaluation->nilai_akhir,
                'grade' => $evaluation->grade,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui nilai: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete evaluation
     */
    public function destroyPenilaianDosen($penilaian)
    {
        try {
            $evaluation = EvaluasiDosen::where('uuid', $penilaian)->orWhere('id', $penilaian)->firstOrFail();
            $evaluation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Nilai berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus nilai: '.$e->getMessage(),
            ], 500);
        }
    }
}
