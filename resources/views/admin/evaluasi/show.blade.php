@extends('layout.app')

@section('content')
@push('styles')
<style>
  :root{
    --muted:#6b7280;
    --border:#e5e7eb;
    --bg-soft:#f9fafb;
    --blue-50:#eff6ff;
    --green-50:#f0fdf4;
    --yellow-50:#fefce8;
    --indigo-50:#eef2ff;
  }

  /* ====== Utilities ====== */
  .truncate{max-width:260px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .clamp-2{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
  .hover-raise{transition:transform .12s ease, box-shadow .12s ease}
  .hover-raise:hover{transform:translateY(-1px);box-shadow:0 6px 18px rgba(0,0,0,.06)}
  .border-top-light{border-top:1px solid var(--border)}

  /* ====== Metrics ====== */
  .metric-card{border:1px solid var(--border);border-radius:1rem;padding:1rem;background:#fff}
  .metric-title{font-size:.8rem;color:var(--muted);letter-spacing:.2px}
  .metric-value{font-size:1.5rem;font-weight:800;line-height:1}
  .metric-sub{font-size:.8rem;color:var(--muted)}
  .icon-wrap{width:36px;height:36px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;background:var(--bg-soft);margin-right:.6rem}
  .badge-soft{background:#eef2ff;color:#4e73df}

  /* ====== Tables ====== */
  .table td,.table th{vertical-align:middle}
  .table thead th{position:sticky;top:0;background:#fff;z-index:2}

  /* ====== Sections ====== */
  .section-title{font-weight:700;font-size:1rem}
  .card-section{border:1px solid var(--border);border-radius:.75rem}
  .card-section .card-header{background:var(--bg-soft);border-bottom:1px solid var(--border)}

  /* ====== Board (lists) ====== */
  .board-wrapper{overflow-x:auto;overflow-y:hidden}
  .board{min-height:60vh;padding-bottom:.5rem;display:flex}
  .board-column{width:300px;min-width:300px;margin-right:1rem;display:flex;flex-direction:column}
  .board-col-head{position:sticky;top:0;z-index:2;background:#fff;border:1px solid var(--border);border-radius:.75rem;padding:.6rem .7rem;box-shadow:0 2px 6px rgba(0,0,0,.03);margin-bottom:.5rem}
  .board-list{min-height:20px}
  .board-card{border-left:3px solid #4e73df;border-radius:.75rem;overflow:hidden}
  .board-card.border-success{border-left-color:#28a745;border:1px solid #28a745}
  .board-card.sortable-chosen{opacity:.9}
  .board-card.sortable-ghost{border:1px dashed #4e73df;background:#f8f9fc}

  .card-title-full{font-size:.95rem;line-height:1.3;white-space:normal;word-break:break-word}

  /* ====== Chips / Badges ====== */
  .date-chip{display:inline-flex;align-items:center;font-size:.75rem;line-height:1;padding:.28rem .5rem;border-radius:999px;background:#f1f5ff;color:#2743d3;border:1px solid #e5e9ff;white-space:nowrap}
  .date-chip-danger{background:#fff2f2;color:#b42318;border-color:#ffd9d7}
</style>
@endpush

@php
  /** ====== Data Helper ====== */
  $members = collect($anggota ?? [])->map(function($m){
      $arr = is_array($m) ? (object)$m : $m;
      return (object)[
        'id'   => $arr->id   ?? ($arr->mahasiswa_id ?? null),
        'nim'  => $arr->nim  ?? null,
        'nama' => $arr->nama ?? ($arr->nama_mahasiswa ?? null),
        'kelas'=> optional($arr->kelas ?? null)->kelas ?? ($arr->kelas ?? null),
      ];
  });

  // Hapus dependensi Absensi/AP: hanya gunakan data indikator & nilai detail jika ada
  // Guard: hindari query ke tabel yang belum ada
  $hasSesiIndi   = \Illuminate\Support\Facades\Schema::hasTable((new \App\Models\EvaluasiSesiIndikator)->getTable());
  $hasNilaiDetil = \Illuminate\Support\Facades\Schema::hasTable((new \App\Models\EvaluasiNilaiDetail)->getTable());
  $sesiIndis  = $hasSesiIndi   ? collect($sesi->sesiIndikators ?? []) : collect();
  $nilaiDetil = $hasNilaiDetil ? collect($sesi->nilaiDetails ?? [])   : collect();
  $nilaiByMhs = $nilaiDetil->groupBy('mahasiswa_id');

  $byKode = $sesiIndis->mapWithKeys(function($si){
    $kode = optional($si->indikator)->kode;
    return $kode ? [
      $kode => [
        'id'    => $si->indikator_id,
        'bobot' => (int)$si->bobot,
        'skor'  => (int)($si->skor ?? 0),
        'nama'  => $si->indikator->nama ?? $kode,
      ]
    ] : [];
  });

  $wDosen         = (int)($settings['w_dosen'] ?? 80);
  $wMitra         = (int)($settings['w_mitra'] ?? 20);
  $wKelompok      = (int)($settings['w_kelompok'] ?? 70);

  $idKehadiran  = $byKode['m_kehadiran']['id']  ?? null;
  $idPresentasi = $byKode['m_presentasi']['id'] ?? null;

  $mitraScores = [];
  foreach ($members as $m) {
    $rows = $nilaiByMhs->get($m->id) ?? collect();
    $skKeh = (int)($rows->firstWhere('indikator_id',$idKehadiran)->skor ?? 0);
    $skPrs = (int)($rows->firstWhere('indikator_id',$idPresentasi)->skor ?? 0);
    $mitraScores[$m->id] = (int) round(($skKeh + $skPrs) / 2);
  }

  $skorDosen = (int) round($sesiIndis->reduce(function($carry, $si){
    $sk = (int)($si->skor ?? 0); $bb = (int)($si->bobot ?? 0);
    return $carry + ($sk * $bb);
  }, 0) / 100);

  $nilaiProyekPerMhs = [];
  foreach ($members as $m) {
    $nilaiProyekPerMhs[$m->id] = (int) round($skorDosen * $wDosen/100 + ($mitraScores[$m->id] ?? 0) * $wMitra/100);
  }
  $nilaiProyekKelompok = (int) round(collect($nilaiProyekPerMhs)->avg() ?? 0);

  // Tanpa AP: nilai akhir = nilai proyek
  $avgNilai = (int) round(collect($nilaiProyekPerMhs)->avg() ?? 0);

  function kategoriLabel($n){
    if ($n>=81) return 'Sangat Baik';
    if ($n>=61) return 'Baik';
    if ($n>=41) return 'Cukup';
    if ($n>=21) return 'Kurang';
    return 'Sangat Kurang';
  }

  // Progress keseluruhan (hanya dari proyek yang punya progress)
  $totalProgress = 0; $cardCount = 0;
  foreach(($proyekLists ?? []) as $list){
    foreach(($list->cards ?? []) as $card){
      if($card->progress !== null){
        $totalProgress += (int)$card->progress;
        $cardCount++;
      }
    }
  }
  
  // Jumlah proyek selesai (status 'Selesai' atau progress 100%)
  $proyekSelesai = 0;
  foreach(($proyekLists ?? []) as $list){
    foreach(($list->cards ?? []) as $card){
      $stNow = $card->status_proyek ?? ($card->status ?? '');
      if ($stNow === 'Selesai' || (int)($card->progress ?? 0) === 100) {
        $proyekSelesai++;
      }
    }
  }

  // Jumlah aktivitas 7 hari terakhir
  $aktivitasMingguan = 0;
  $cut = \Carbon\Carbon::now()->subDays(7)->startOfDay();
  foreach(($aktivitasLists ?? []) as $alist){
    foreach(($alist->cards ?? []) as $ac){
      $dt = $ac->tanggal_aktivitas ?? $ac->created_at ?? null;
      if ($dt && \Carbon\Carbon::parse($dt)->greaterThanOrEqualTo($cut)) {
        $aktivitasMingguan++;
      }
    }
  }
  $avgProgress = $cardCount ? round($totalProgress / $cardCount) : 0;
@endphp

<div class="container-fluid">

  {{-- Header --}}
  <div class="d-flex align-items-start mb-3 flex-wrap">
    <div>
      <div class="text-muted small mb-1">
        <i class="fas fa-home mr-1"></i> Evaluasi
        <i class="fas fa-angle-right mx-1"></i> Detail
      </div>
      <h1 class="h3 mb-1">Detail Evaluasi</h1>
      <div class="text-muted">
        <i class="fas fa-users mr-1"></i>{{ $kelompok->nama_kelompok ?? '—' }}
        <span class="mx-2">•</span>
        <span class="badge badge-light border">{{ $periode->periode ?? '—' }}</span>
      </div>
    </div>
    <div class="ml-auto text-right">
      <div class="text-muted small">Evaluator:
        <strong>{{ Auth::user()->name ?? '—' }}</strong>
      </div>
    </div>
  </div>

  {{-- Metrik ringkas --}}
  <div class="row mb-4">
    <div class="col-lg-6 mb-2">
      <div class="metric-card">
        <div class="metric-title">Anggota ({{ $anggota->count() }})</div>
        <div class="mt-2" style="max-height: 200px; overflow:auto">
          @forelse($anggota as $m)
            <div class="d-flex align-items-center small mb-2 p-2 border rounded border-light">
              <span class="text-monospace mr-2">{{ $m->nim ?: '-' }}</span>
              <span class="mr-2">{{ $m->nama ?: '-' }}</span>
              <span class="badge badge-light border">{{ $m->kelas?->kelas ?: '-' }}</span>
              @if($m->pivot->role === 'Ketua')
                <span class="badge badge-primary ml-auto">Ketua</span>
              @endif
            </div>
          @empty
            <div class="text-muted small">Tidak ada anggota.</div>
          @endforelse
        </div>
      </div>
    </div>
    <div class="col-lg-6 mb-2">
      <div class="row">
        <div class="col-12 mb-2">
          <div class="metric-card d-flex align-items-center">
            <div class="icon-wrap"><i class="fas fa-check-circle" aria-hidden="true"></i></div>
            <div>
              <div class="metric-title">Jumlah Proyek Selesai</div>
              <div class="metric-value">{{ $proyekSelesai }}</div>
              <div class="metric-sub">Dari {{ (int)($proyek_total_cards ?? 0) }} proyek</div>
            </div>
          </div>
        </div>
        <div class="col-12 mb-2">
          <div class="metric-card d-flex align-items-center">
            <div class="icon-wrap"><i class="fas fa-calendar-week" aria-hidden="true"></i></div>
            <div>
              <div class="metric-title">Jumlah Aktivitas (7 hari)</div>
              <div class="metric-value">{{ $aktivitasMingguan }}</div>
              <div class="metric-sub">Total aktivitas: {{ (int)($aktivitas_total ?? 0) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Overview Proyek --}}
  <div class="card card-section mb-4">
    <div class="card-header">
      <div class="section-title mb-0">Overview Proyek</div>
    </div>
    <div class="card-body">
      <div class="row text-center">
        <div class="col-md-3 mb-3">
          <div class="metric-title">Total Proyek</div>
          <div class="metric-value">{{ $proyek_total_cards }}</div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="metric-title">Total Aktivitas</div>
          <div class="metric-value">{{ $aktivitas_total }}</div>
        </div>
        <div class="col-md-3 mb-3">
          @php
            $evaluatedCards = 0;
            foreach ($proyekLists as $list) {
              foreach ($list->cards as $card) {
                $evalData = $cardGrades[$card->id]['evaluasi_dosen'] ?? null;
                if ($evalData && $evalData->nilai_akhir !== null) {
                  $evaluatedCards++;
                }
              }
            }
            $evalPercentage = $proyek_total_cards > 0 ? round(($evaluatedCards / $proyek_total_cards) * 100) : 0;
          @endphp
          <div class="metric-title">Progres Evaluasi</div>
          <div class="metric-value">{{ $evaluatedCards }}/{{ $proyek_total_cards }}</div>
          <div class="progress mt-2" style="height: 8px;">
            <div class="progress-bar {{ $evalPercentage == 100 ? 'bg-success' : ($evalPercentage > 50 ? 'bg-warning' : 'bg-danger') }}"
                 role="progressbar"
                 style="width: {{ $evalPercentage }}%"
                 aria-valuenow="{{ $evalPercentage }}" aria-valuemin="0" aria-valuemax="100">
            </div>
          </div>
          <div class="metric-sub">{{ $evalPercentage }}%</div>
        </div>
        <div class="col-md-3 mb-3">
          @php
            $totalNilai = 0;
            $countNilai = 0;
            foreach ($proyekLists as $list) {
              foreach ($list->cards as $card) {
                $evalData = $cardGrades[$card->id]['evaluasi_dosen'] ?? null;
                if ($evalData && $evalData->nilai_akhir !== null) {
                  $totalNilai += $evalData->nilai_akhir;
                  $countNilai++;
                }
              }
            }
            $avgNilai = $countNilai > 0 ? round($totalNilai / $countNilai) : 0;
          @endphp
          <div class="metric-title">Rata-rata Nilai</div>
          <div class="metric-value">{{ $avgNilai }}</div>
          <div class="metric-sub">Dari {{ $countNilai }} proyek</div>
        </div>
      </div>
    </div>
  </div>

  {{-- Daftar Proyek  --}}
  <div class="card card-section mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center">
        <div class="section-title mb-0 mr-2">Daftar Proyek</div>
        <button class="btn btn-sm btn-outline-secondary" type="button"
                data-toggle="collapse" data-target="#sectionProjects"
                aria-expanded="true" aria-controls="sectionProjects">Toggle</button>
      </div>
      <div class="d-flex align-items-center">
        <input id="boardSearch" type="text" class="form-control form-control-sm mr-2" placeholder="Cari Proyek..." style="max-width: 220px;" aria-label="Cari kartu proyek">
      </div>
    </div>

    <div id="sectionProjects" class="collapse show">
      <div class="card-body p-2">
        <div class="board-wrapper">
          <div id="board" class="board">
          @forelse ($proyekLists as $list)
            <div class="board-column" data-col-id="{{ $list->id }}">
              {{-- Head kolom --}}
              <div class="board-col-head d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                  <h6 class="mb-0 text-uppercase font-weight-bold truncate">{{ $list->name }}</h6>
                  <span class="badge badge-soft ml-2">{{ count($list->cards) }}</span>
                </div>
                <div class="text-right small">
                  @php
                    $listAvgDosen = $listAggDosen[$list->id] ?? 0;
                    $listAvgMitra = $listAggMitra[$list->id] ?? 0;
                    $evaluatedCards = 0;
                    foreach ($list->cards as $card) {
                      $evalData = $cardGrades[$card->id]['evaluasi_dosen'] ?? null;
                      if ($evalData && $evalData->nilai_akhir !== null) {
                        $evaluatedCards++;
                      }
                    }
                  @endphp
                  @if($evaluatedCards > 0)
                    <div class="text-success">
                      <i class="fas fa-check-circle mr-1"></i>
                      {{ $evaluatedCards }}/{{ count($list->cards) }} dievaluasi
                    </div>
                    @if($listAvgDosen > 0)
                      <div class="text-muted"></div>
                    @endif
                  @endif
                </div>
              </div>

              {{-- List kartu --}}
              <div class="board-list" data-list-id="{{ $list->id }}">
                @forelse ($list->cards as $card)
                  @php
                    $tglMulai   = optional($card->tanggal_mulai)->toDateString();
                    $tglSelesai = optional($card->tanggal_selesai)->toDateString() ?: optional($card->due_date)->toDateString();
                    $isOverdue  = $tglSelesai && \Carbon\Carbon::parse($tglSelesai)->isPast() && (($card->status ?? 'Proses') !== 'Selesai');
                    $rentang    = trim(($tglMulai ? \Carbon\Carbon::parse($tglMulai)->format('d M Y') : '?').' – '.($tglSelesai ? \Carbon\Carbon::parse($tglSelesai)->format('d M Y') : '?'));
                    $statusNow  = $card->status_proyek ?? ($card->status ?? 'Proses');
                    $cgRow  = $cardGrades[$card->id]['dosen'] ?? null;
                    $cgRowM = $cardGrades[$card->id]['mitra'] ?? null;
                    $evalDosen = $cardGrades[$card->id]['evaluasi_dosen'] ?? null;
                    $evalDetails = collect($cardGrades[$card->id]['evaluasi_dosen_details'] ?? []);
                    $evalSummary = $cardGrades[$card->id]['evaluasi_dosen_summary'] ?? ['avg' => null, 'count' => 0];
                    $evalMitra = $cardGrades[$card->id]['evaluasi_mitra'] ?? null;
                    $evalMitraDetails = collect($cardGrades[$card->id]['evaluasi_mitra_details'] ?? []);
                    $mitraSummary = $cardGrades[$card->id]['evaluasi_mitra_summary'] ?? ['avg' => null, 'count' => 0];
                    $avgNilaiDosen = $evalSummary['avg'] ?? null;
                    $avgNilaiMitra = $mitraSummary['avg'] ?? null;
                    $gradeColors = ['A' => 'success', 'B' => 'info', 'C' => 'warning', 'D' => 'danger', 'E' => 'dark'];
                  @endphp

                  <div class="card shadow-xs mb-2 hover-raise {{ ($avgNilaiDosen !== null) ? 'border-success' : '' }}"
                       data-card-id="{{ $card->id }}"
                       data-card-uuid="{{ $card->uuid }}"
                       data-title="{{ $card->title }}">
                    <div class="card-body p-2 d-flex flex-column">

                      {{-- Judul --}}
                      <div class="d-flex align-items-start mb-1">
                        <div class="card-title-full font-weight-bold flex-grow-1">{{ $card->title }}</div>
                      </div>

                      {{-- Tanggal --}}
                      @if($tglMulai || $tglSelesai)
                        <div class="mb-2">
                          <span class="date-chip {{ $isOverdue ? 'date-chip-danger' : '' }}">
                            <i class="far fa-calendar-alt mr-1" aria-hidden="true"></i>{{ $rentang }}
                          </span>
                        </div>
                      @endif

                      {{-- Deskripsi singkat --}}
                      @if(!empty($card->description))
                        <div class="text-muted text-body-2 mb-2 clamp-2">{{ \Illuminate\Support\Str::limit($card->description, 160) }}</div>
                      @endif

                      {{-- Progress --}}
                      <div class="d-flex align-items-center mb-2">
                        <div class="progress progress-sm flex-grow-1 mr-2" style="height: 8px;">
                          <div id="progress-{{ $card->id }}"
                               class="progress-bar {{ (int)($card->progress ?? 0) === 100 ? 'bg-success' : '' }}"
                               role="progressbar"
                               style="width: {{ (int)($card->progress ?? 0) }}%"
                               aria-valuenow="{{ (int)($card->progress ?? 0) }}" aria-valuemin="0" aria-valuemax="100">
                          </div>
                        </div>
                        <span class="small text-muted">{{ (int)($card->progress ?? 0) }}%</span>
                      </div>

                      {{-- Meta created/updated --}}
                      <div class="small text-muted mb-1">
                        @php
                          $cName = optional($card->createdBy)->nama_user ?? optional($card->createdBy)->name ?? optional($card->createdBy)->nama ?? '-';
                          $uName = optional($card->updatedBy)->nama_user ?? optional($card->updatedBy)->name ?? optional($card->updatedBy)->nama ?? '-';
                          $cAt = optional($card->created_at)->format('d M Y H:i') ?? '-';
                          $uAt = optional($card->updated_at)->format('d M Y H:i') ?? '-';
                        @endphp
                        <span class="badge badge-info" title="created/updated">
                          Dibuat: {{ $cName }} ({{ $cAt }}) <br>
                          Diupdate: {{ $uName }} ({{ $uAt }})
                        </span>
                      </div>

                      {{-- Nilai Dosen & Mitra --}}
                      @php
                        $dTot = optional($cgRow)->total;
                        $mTot = optional($cgRowM)->total;
                        $newDosenNilai = $evalDosen ? ($evalDosen->nilai_akhir ?? null) : null;
                        $finalDosenNilai = $avgNilaiDosen ?? $newDosenNilai ?? $dTot;
                        $finalMitraNilai = $avgNilaiMitra ?? optional($evalMitra)->nilai_akhir ?? $mTot;
                      @endphp
                      <div class="score-card mb-2 p-3" style="border:1px solid var(--border);border-radius:.5rem;background:var(--bg-soft)">
                        <div class="d-flex justify-content-between align-items-start flex-wrap">
                          <div class="mr-3 mb-2">
                            <div class="small text-muted text-uppercase">Dosen</div>
                            <div class="d-flex align-items-center">
                              <i class="fas fa-user-graduate mr-1" aria-hidden="true"></i>
                              <span class="font-weight-bold score-dosen-val"></span>
                              @if($evalDosen && $evalDosen->status)
                                <span class="badge badge-xs ml-2 {{ $evalDosen->status == 'draft' ? 'badge-warning' : ($evalDosen->status == 'submitted' ? 'badge-info' : 'badge-success') }}">
                                  {{ strtoupper($evalDosen->status) }}
                                </span>
                              @endif
                            </div>
                          </div>
                          <div class="mb-2">
                            <div class="small text-muted text-uppercase">Mitra</div>
                            <div class="d-flex align-items-center">
                              <i class="fas fa-handshake mr-1" aria-hidden="true"></i>
                              <span class="font-weight-bold score-mitra-val"></span>
                              @if($evalMitra && $evalMitra->status)
                                <span class="badge badge-xs ml-2 {{ $evalMitra->status == 'draft' ? 'badge-warning' : ($evalMitra->status == 'submitted' ? 'badge-info' : 'badge-success') }}">
                                  {{ strtoupper($evalMitra->status) }}
                                </span>
                              @endif
                            </div>
                          </div>
                        </div>

                        @if($evalSummary['count'] ?? 0)
                          <div class="mt-2 per-mahasiswa-wrapper per-mahasiswa-wrapper-dosen">
                            <div class="d-flex justify-content-between align-items-center text-muted text-uppercase small">
                              <span>Nilai Dosen per Mahasiswa</span>
                              <span class="badge badge-light border">{{ $evalSummary['count'] }} orang</span>
                            </div>
                            <div class="mt-2 rounded bg-white" style="border:1px solid var(--border);">
                              @foreach($evalDetails as $index => $detail)
                                @php
                                  $rowName = optional($detail->mahasiswa)->nama ?? optional($detail->mahasiswa)->nama_mahasiswa ?? 'Mahasiswa';
                                  $rowNim = optional($detail->mahasiswa)->nim ?? '-';
                                  $rowNilai = $detail->nilai_akhir !== null ? number_format((float) $detail->nilai_akhir, 1) : null;
                                  $rowGrade = $detail->grade ?? null;
                                  $gradeClass = $rowGrade && isset($gradeColors[$rowGrade]) ? $gradeColors[$rowGrade] : 'secondary';
                                @endphp
                                <div class="d-flex align-items-center justify-content-between px-2 py-1 {{ $index !== 0 ? 'border-top' : '' }}" style="border-color: var(--border);">
                                  <div class="small">
                                    <div class="font-weight-bold">{{ $rowName }}</div>
                                    <div class="text-muted">{{ $rowNim }}</div>
                                  </div>
                                  <div class="text-right">
                                    @if($rowNilai !== null)
                                      <div class="font-weight-bold">{{ $rowNilai }}</div>
                                      <span class="badge badge-{{ $gradeClass }}">{{ $rowGrade }}</span>
                                    @else
                                      <span class="text-muted small">Belum dinilai</span>
                                    @endif
                                  </div>
                                </div>
                              @endforeach
                            </div>
                          </div>
                        @endif

                        @if($mitraSummary['count'] ?? 0)
                          <div class="mt-2 per-mahasiswa-wrapper per-mahasiswa-wrapper-mitra">
                            <div class="d-flex justify-content-between align-items-center text-muted text-uppercase small">
                              <span>Nilai Mitra per Mahasiswa</span>
                              <span class="badge badge-light border">{{ $mitraSummary['count'] }} orang</span>
                            </div>
                            <div class="mt-2 rounded bg-white" style="border:1px solid var(--border);">
                              @foreach($evalMitraDetails as $index => $detail)
                                @php
                                  $rowNameM = optional($detail->mahasiswa)->nama ?? optional($detail->mahasiswa)->nama_mahasiswa ?? 'Mahasiswa';
                                  $rowNimM = optional($detail->mahasiswa)->nim ?? '-';
                                  $rowNilaiM = $detail->nilai_akhir !== null ? number_format((float) $detail->nilai_akhir, 1) : null;
                                  $rowGradeM = $detail->grade ?? null;
                                  $gradeClassM = $rowGradeM && isset($gradeColors[$rowGradeM]) ? $gradeColors[$rowGradeM] : 'secondary';
                                @endphp
                                <div class="d-flex align-items-center justify-content-between px-2 py-1 {{ $index !== 0 ? 'border-top' : '' }}" style="border-color: var(--border);">
                                  <div class="small">
                                    <div class="font-weight-bold">{{ $rowNameM }}</div>
                                    <div class="text-muted">{{ $rowNimM }}</div>
                                  </div>
                                  <div class="text-right">
                                    @if($rowNilaiM !== null)
                                      <div class="font-weight-bold">{{ $rowNilaiM }}</div>
                                      <span class="badge badge-{{ $gradeClassM }}">{{ $rowGradeM }}</span>
                                    @else
                                      <span class="text-muted small">Belum dinilai</span>
                                    @endif
                                  </div>
                                </div>
                              @endforeach
                            </div>
                          </div>
                        @endif

                        
                      </div>

                      {{-- Footer: status + actions --}}
                      <div class="d-flex align-items-center x-small mt-auto pt-1">
                        @php
                          $clsMap = ['Proses'=>'badge-info','Dibatalkan'=>'badge-danger','Selesai'=>'badge-success'];
                          $cls = $clsMap[$statusNow] ?? 'badge-light';
                        @endphp
                        <span id="status-{{ $card->id }}" class="badge {{ $cls }}">{{ $statusNow }}</span>

                        <div class="ml-auto btn-group btn-group-sm">
                          {{-- Drive --}}
                          @php $drive = $card->link_drive_proyek ?? $card->drive_link ?? null; @endphp
                          @if(!empty($drive))
                            <a href="{{ $drive }}" class="btn btn-outline-secondary" target="_blank" rel="noopener" title="Drive">
                              <i class="fab fa-google-drive" aria-hidden="true"></i>
                            </a>
                          @endif


                          {{-- Nilai Dosen --}}
                          <button type="button"
                                  class="btn btn-outline-success"
                                  title="Nilai Dosen"
                                  onclick="showProjectDetail('{{ $card->uuid }}','{{ addslashes($card->title) }}')">
                            <i class="fas fa-clipboard-list" aria-hidden="true"></i>
                          </button>

                          {{-- Nilai Mitra --}}
                          <button type="button"
                                  class="btn btn-outline-info"
                                  title="Nilai Mitra"
                                  onclick="gradeMitra('{{ $card->uuid }}','{{ addslashes($card->title) }}')">
                            <i class="fas fa-handshake" aria-hidden="true"></i>
                          </button>

                          {{-- Edit --}}
                          <button type="button"
                                  class="btn btn-outline-warning"
                                  title="Edit Proyek"
                                  data-toggle="modal"
                                  data-target="#modalEditProyek"
                                  data-id="{{ $card->id }}"
                                  data-uuid="{{ $card->uuid }}"
                                  data-title="{{ $card->title }}"
                                  data-desc="{{ $card->description }}"
                                  data-status_proyek="{{ $card->status_proyek ?? $statusNow }}"
                                  data-progress="{{ (int)($card->progress ?? 0) }}"
                                  data-tglmulai="{{ optional($card->tanggal_mulai)->format('Y-m-d') }}"
                                  data-tglselesai="{{ optional($card->tanggal_selesai)->format('Y-m-d') ?? optional($card->due_date)->format('Y-m-d') }}"
                                  data-biayabarang="{{ (int)($card->biaya_barang ?? 0) }}"
                                  data-biayajasa="{{ (int)($card->biaya_jasa ?? 0) }}"
                                  data-drivelink="{{ $drive ?? '' }}"
                                  data-labels='@json($card->labels ?? [])'
                                  data-skema="{{ $card->skema_pbl ?? '' }}"
                                  data-namami="{{ $card->nama_mitra ?? '' }}"
                                  data-kontak="{{ $card->kontak_mitra ?? '' }}"
                                  data-kendala="{{ addslashes($card->kendala ?? '') }}"
                                  data-catatan="{{ addslashes($card->catatan ?? '') }}">
                            <i class="fas fa-edit" aria-hidden="true"></i>
                          </button>

                          {{-- Hapus --}}
                          <button type="button"
                                  class="btn btn-outline-danger"
                                  title="Hapus Proyek"
                                  onclick="confirmDeleteProject('{{ $card->uuid }}', '{{ addslashes($card->title) }}')">
                            <i class="fas fa-trash" aria-hidden="true"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                @empty
                  <div class="card card-empty shadow-0">
                    <div class="card-body p-3 text-center text-muted small">
                      <i class="far fa-clipboard mr-1" aria-hidden="true"></i> Belum ada proyek
                    </div>
                  </div>
                @endforelse
              </div>
            </div>
          @empty
            <div class="w-100 text-center text-muted py-4">Belum ada daftar proyek</div>
          @endforelse
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Daftar Aktivitas --}}
  <div class="card card-section mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center">
        <div class="section-title mb-0 mr-2">Aktivitas Mingguan</div>
        <button class="btn btn-sm btn-outline-secondary" type="button"
                data-toggle="collapse" data-target="#sectionAktivitas"
                aria-expanded="true" aria-controls="sectionAktivitas">Toggle</button>
      </div>
      <div class="d-flex align-items-center">
        <input id="actSearch" type="text" class="form-control form-control-sm mr-2"
               placeholder="Cari Aktivitas…" style="max-width: 220px;" aria-label="Cari aktivitas">
      </div>
    </div>

    <div id="sectionAktivitas" class="collapse show">
      <div class="card-body p-2">
        <div class="board-wrapper">
          <div id="actBoard" class="board">
          @forelse ($aktivitasLists as $alist)
            <div class="board-column" data-col-id="{{ $alist->id }}">
              {{-- Head kolom: sama gaya dengan proyek --}}
              <div class="board-col-head d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                  <h6 class="mb-0 text-uppercase font-weight-bold truncate">
                    {{ $alist->title ?? $alist->name ?? 'Minggu' }}
                  </h6>
                  <span class="badge badge-soft ml-2">{{ count($alist->cards) }}</span>
                </div>
                @if(!empty($alist->status_evaluasi))
                  <span class="badge {{ $alist->status_evaluasi==='Sudah Evaluasi'?'badge-success':'badge-secondary' }}">
                    {{ $alist->status_evaluasi }}
                  </span>
                @endif
              </div>

              {{-- List aktivitas: bentuk & garis sama dengan proyek --}}
              <div class="board-list act-list" data-list-id="{{ $alist->id }}">
                @forelse ($alist->cards as $ac)
                  @php
                    $tgl = optional($ac->tanggal_aktivitas)->format('d M Y');
                    $cName = optional($ac->createdBy)->nama_user ?? optional($ac->createdBy)->name ?? optional($ac->createdBy)->nama ?? '-';
                    $uName = optional($ac->updatedBy)->nama_user ?? optional($ac->updatedBy)->name ?? optional($ac->updatedBy)->nama ?? '-';
                    $cAt = optional($ac->created_at)->format('d M Y H:i') ?? '-';
                    $uAt = optional($ac->updated_at)->format('d M Y H:i') ?? '-';
                  @endphp
                  <div class="card board-card shadow-xs mb-2 hover-raise" data-card-id="{{ $ac->id }}">
                    <div class="card-body p-2 d-flex flex-column">

                      {{-- Tanggal: chip seperti proyek --}}
                      @if($tgl)
                        <div class="mb-2">
                          <span class="date-chip">
                            <i class="far fa-calendar-alt mr-1" aria-hidden="true"></i>{{ $tgl }}
                          </span>
                        </div>
                      @endif

                      {{-- Deskripsi singkat --}}
                      @if($ac->description)
                        <div class="text-muted text-body-2 mb-2 clamp-2">
                          {{ \Illuminate\Support\Str::limit($ac->description, 160) }}
                        </div>
                      @endif

                      {{-- Meta created/updated --}}
                      @if($ac->created_at || $ac->updated_at || $ac->createdBy || $ac->updatedBy)
                        <div class="small text-muted mb-1">
                          <span class="badge badge-info" title="created/updated">
                            Dibuat: {{ $cName }} ({{ $cAt }}) <br>
                            Diupdate: {{ $uName }} ({{ $uAt }})
                          </span>
                        </div>
                      @endif

                      {{-- Footer aksi --}}
                      <div class="d-flex align-items-center x-small mt-auto pt-1">
                        <span class="text-muted"></span>
                        <div class="ml-auto btn-group btn-group-sm">
                          @if($ac->bukti_kegiatan)
                            <a href="{{ $ac->bukti_kegiatan }}" target="_blank" rel="noopener"
                               class="btn btn-outline-dark btn-sm" title="Bukti aktivitas">
                              <i class="fas fa-link" aria-hidden="true"></i> Bukti
                            </a>
                          @endif
                        </div>
                      </div>

                    </div>
                  </div>
                @empty
                  <div class="card card-empty shadow-0">
                    <div class="card-body p-3 text-center text-muted small">
                      <i class="far fa-clipboard mr-1" aria-hidden="true"></i> Belum ada aktivitas
                    </div>
                  </div>
                @endforelse
              </div>
            </div>
          @empty
            <div class="w-100 text-center text-muted py-4">Belum ada daftar aktivitas</div>
          @endforelse
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Modal Edit Proyek --}}
<div class="modal fade" id="modalEditProyek" tabindex="-1" role="dialog" aria-labelledby="modalEditProyekLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form id="formEditProyek" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEditProyekLabel">Edit Proyek</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="ep_id">
        <input type="hidden" id="ep_domid">

        <div class="form-row">
          <div class="form-group col-md-8">
            <label for="ep_title">Judul</label>
            <input type="text" class="form-control" name="title" id="ep_title" required>
          </div>
          <div class="form-group col-md-4">
            <label for="ep_status">Status</label>
            <select class="form-control" name="status_proyek" id="ep_status">
              <option>Proses</option>
              <option>Dibatalkan</option>
              <option>Selesai</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label for="ep_desc">Deskripsi</label>
          <textarea class="form-control" rows="3" name="description" id="ep_desc"></textarea>
        </div>
        <div class="form-group">
          <label for="ep_progress">Progress (%)</label>
          <input type="number" min="0" max="100" class="form-control" name="progress" id="ep_progress">
        </div>
        <div class="form-group">
          <label for="ep_labels">Label (pisahkan koma)</label>
          <input type="text" class="form-control" name="labels" id="ep_labels" placeholder="AI, IoT">
        </div>
        <div class="form-group">
          <label for="ep_kontak">Kontak Mitra</label>
          <input type="text" class="form-control" name="kontak_mitra" id="ep_kontak">
        </div>
        <div class="form-row">
          <div class="form-group col-md-6">
            <label for="ep_tglmulai">Tanggal Mulai</label>
            <input type="date" class="form-control" name="tanggal_mulai" id="ep_tglmulai">
          </div>
          <div class="form-group col-md-6">
            <label for="ep_tglselesai">Tanggal Selesai</label>
            <input type="date" class="form-control" name="tanggal_selesai" id="ep_tglselesai">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group col-md-6">
            <label for="ep_skema">Skema PBL</label>
            <select class="form-control" name="skema_pbl" id="ep_skema">
              <option value="">- Pilih -</option>
              <option>Penelitian</option><option>Pengabdian</option><option>Lomba</option><option>PBL x TeFa</option>
            </select>
          </div>
          <div class="form-group col-md-6">
            <label for="ep_namami">Nama Mitra</label>
            <input type="text" class="form-control" name="nama_mitra" id="ep_namami">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group col-md-6">
            <label for="ep_biayabarang">Biaya Barang</label>
            <input type="number" min="0" step="0.01" class="form-control" name="biaya_barang" id="ep_biayabarang">
          </div>
          <div class="form-group col-md-6">
            <label for="ep_biayajasa">Biaya Jasa</label>
            <input type="number" min="0" step="0.01" class="form-control" name="biaya_jasa" id="ep_biayajasa">
          </div>
        </div>
        <div class="form-group">
          <label for="ep_drivelink">Link Drive Proyek</label>
          <input type="url" class="form-control" name="link_drive_proyek" id="ep_drivelink" placeholder="https://...">
        </div>
        <div class="form-group">
          <label for="ep_kendala">Kendala</label>
          <textarea class="form-control" rows="2" name="kendala" id="ep_kendala"></textarea>
        </div>
        <div class="form-group mb-0">
          <label for="ep_catatan">Catatan</label>
          <textarea class="form-control" rows="2" name="catatan" id="ep_catatan"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-dismiss="modal" type="button">Batal</button>
        <button class="btn btn-primary" type="submit"><i class="fas fa-save" aria-hidden="true"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function(){
  /* ====== Helpers ====== */
  const qs  = (s,ctx=document)=>ctx.querySelector(s);
  const qsa = (s,ctx=document)=>Array.from(ctx.querySelectorAll(s));
  const norm = s => (s||'').toString().toLowerCase().trim();

  /* ====== Collapse Persist (first visit closed) ====== */
  (function manageSections($){
    const sections = [
      { target: '#sectionProjects',  key: 'ui.sectionProjects'  },
      { target: '#sectionAktivitas', key: 'ui.sectionAktivitas' },
    ];
    sections.forEach(({target, key})=>{
      const $body = $(target);
      const $btns = $(`[data-target="${target}"]`);
      if(!$body.length) return;

      const saved = localStorage.getItem(key);
      if (saved === null) {
        // First visit => force closed
        $body.removeClass('show');
        $btns.addClass('collapsed').attr('aria-expanded','false');
      } else {
        const open = saved === '1';
        $body.toggleClass('show', open);
        $btns.toggleClass('collapsed', !open).attr('aria-expanded', open ? 'true' : 'false');
      }
      $body.on('shown.bs.collapse',  ()=> localStorage.setItem(key,'1'));
      $body.on('hidden.bs.collapse', ()=> localStorage.setItem(key,'0'));
    });
  })(jQuery);

  /* ====== PROJECTS ====== */
  const reorderUrl      = "{{ route('admin.evaluasi.project.reorder') }}";
  const reorderListsUrl = "{{ route('admin.evaluasi.lists.reorder') }}";

  // Map numeric id to uuid (optional)
  window.__cardIdToUuid = {};
  document.querySelectorAll('#board .board-card').forEach(function(el){
    const nid = el.getAttribute('data-card-id');
    const uuid = el.getAttribute('data-card-uuid');
    if (nid && uuid) window.__cardIdToUuid[nid] = uuid;
  });

  // Drag cards within/between lists (Projects)
  document.querySelectorAll('#board .board-list').forEach(function(listEl){
    new Sortable(listEl, {
      group:'board', animation:150, ghostClass:'sortable-ghost', chosenClass:'sortable-chosen',
      onEnd:function(evt){
        const cardId  = evt.item.getAttribute('data-card-id');
        const toList  = evt.to.getAttribute('data-list-id');
        const newIdx  = evt.newIndex;
        $.post(reorderUrl, {card_id:cardId, to_list:toList, position:newIdx, _token:'{{ csrf_token() }}'})
         .fail(()=> Swal.fire('Gagal','Gagal menyimpan urutan','error'));
      }
    });
  });

  // Drag project columns
  (function(){
    const boardEl = document.getElementById('board');
    if (!boardEl) return;
    new Sortable(boardEl, {
      animation:150, handle:'.board-col-head',
      onEnd:function(){
        const ids = Array.from(boardEl.querySelectorAll('.board-column')).map(col=>col.getAttribute('data-col-id'));
        $.post(reorderListsUrl, {list_ids:ids, _token:'{{ csrf_token() }}'})
         .fail(()=> Swal.fire('Gagal','Gagal menyimpan urutan kolom','error'));
      }
    });
  })();

  // Search projects
  qs('#boardSearch')?.addEventListener('input', function(){
    const q = norm(this.value);
    qsa('#board .board-column').forEach(col=>{
      const head  = qs('.board-col-head', col);
      const headTxt = norm(head?.innerText||'');
      const colMatch = headTxt.includes(q);

      qsa('.board-card', col).forEach(card=>{
        const ok = colMatch || norm(card.innerText).includes(q) || q===''; 
        card.style.display = ok ? '' : 'none';
      });
    });
  });

  /* ====== AKTIVITAS ====== */
  // Search aktivitas (pakai #actBoard)
  qs('#actSearch')?.addEventListener('input', function(){
    const q = norm(this.value);
    qsa('#actBoard .board-column').forEach(col=>{
      const head = qs('.board-col-head', col);
      const headTxt = norm(head?.innerText || '');
      const colMatch = headTxt.includes(q);
      let any=false;

      qsa('.board-card', col).forEach(card=>{
        const ok = colMatch || norm(card.innerText).includes(q) || q===''; 
        card.style.display = ok ? '' : 'none';
        if(ok) any = true;
      });

      // Sembunyikan kolom saat pencarian jika tidak cocok
      col.style.display = (q==='' || any || colMatch) ? '' : 'none';
    });
  });

  /* ====== Modal Edit Proyek ====== */
  $('#modalEditProyek').on('show.bs.modal', function(e){
    const btn = $(e.relatedTarget);
    $('#ep_id').val(btn.data('uuid'));
    $('#ep_domid').val(btn.data('id'));
    $('#ep_title').val(btn.data('title'));
    $('#ep_desc').val(btn.data('desc') || '');
    $('#ep_status').val(btn.data('status_proyek'));
    $('#ep_progress').val(btn.data('progress'));
    $('#ep_tglmulai').val(btn.data('tglmulai') || '');
    $('#ep_tglselesai').val(btn.data('tglselesai') || '');
    $('#ep_biayabarang').val(btn.data('biayabarang') || 0);
    $('#ep_biayajasa').val(btn.data('biayajasa') || 0);
    $('#ep_drivelink').val(btn.data('drivelink') || '');
    const labels = btn.data('labels') || [];
    $('#ep_labels').val((labels||[]).join(', '));
    $('#ep_skema').val(btn.data('skema')||'');
    $('#ep_namami').val(btn.data('namami')||'');
    $('#ep_kontak').val(btn.data('kontak')||'');
    $('#ep_kendala').val(btn.data('kendala')||'');
    $('#ep_catatan').val(btn.data('catatan')||'');
  });

  function swalToast(icon, title){
    Swal.fire({toast:true, position:'top-end', showConfirmButton:false, timer:1800, icon:icon||'success', title:title||'Tersimpan'});
  }

  window.cardGrades = window.cardGrades || @json($cardGrades ?? []);

  // Submit EDIT
  $('#formEditProyek').on('submit', function(ev){
    ev.preventDefault();
    const uuid = $('#ep_id').val();
    const domid = $('#ep_domid').val();
    const url = "{{ route('admin.evaluasi.project.update', ['card'=>'__ID__']) }}".replace('__ID__', uuid);
    const payload = $(this).serialize();

    $.ajax({
      url, type:'POST',
      data: payload + '&_token={{ csrf_token() }}',
      success: function(r){
        if(r.success){
          const title = $('#ep_title').val();
          const status = $('#ep_status').val();
          const progress = parseInt($('#ep_progress').val() || 0, 10);

          let cardEl = $('.board-card[data-card-id="'+domid+'"]');
          if (cardEl.length === 0) { cardEl = $('.board-card[data-card-uuid="'+uuid+'"]'); }
          cardEl.find('.card-title-full').text(title);

          let badge = $('#status-'+domid);
          if (badge.length === 0) { badge = cardEl.find('span[id^="status-"]'); }
          badge.removeClass('badge-info badge-success badge-danger');
          let cls = 'badge-info'; if(status==='Selesai') cls='badge-success'; if(status==='Dibatalkan') cls='badge-danger';
          badge.addClass(cls).text(status);

          let bar = $('#progress-'+domid);
          if (bar.length === 0) { bar = cardEl.find('.progress .progress-bar').first(); }
          bar.css('width', progress+'%').attr('aria-valuenow', progress);
          progress===100 ? bar.addClass('bg-success') : bar.removeClass('bg-success');

          $('#modalEditProyek').modal('hide');
          swalToast('success','Perubahan proyek disimpan');
        } else {
          Swal.fire('Gagal', r.message || 'Gagal menyimpan perubahan.', 'error');
        }
      },
      error: function(){ Swal.fire('Gagal', 'Gagal menyimpan perubahan.', 'error'); }
    });
  });

  // Hapus proyek
  window.confirmDeleteProject = function(uuid, title){
    const url = "{{ route('admin.evaluasi.project.destroy', ['card'=>'__ID__']) }}".replace('__ID__', uuid);
    Swal.fire({
      title:'Hapus proyek?', text:title, icon:'warning', showCancelButton:true,
      confirmButtonText:'Ya, hapus', cancelButtonText:'Batal'
    }).then((res)=>{
      if(!res.isConfirmed) return;
      $.post(url, {_method:'DELETE', _token:'{{ csrf_token() }}'})
        .done(function(r){
          if(r.success){
            $('.board-card[data-card-uuid="'+uuid+'"]').remove();
            swalToast('success','Proyek dihapus');
          } else {
            Swal.fire('Gagal', r.message || 'Gagal menghapus proyek.', 'error');
          }
        })
        .fail(()=> Swal.fire('Gagal', 'Gagal menghapus proyek.', 'error'));
    });
  };

  // Grade Mitra per proyek per mahasiswa
  window.gradeMitra = async function(cardId, cardTitle) {
    if (!cardId) {
      return;
    }

    const mitraItems = [
      { kode: 'm_kehadiran', nama: 'Komunikasi & Sikap', bobot: {{ (int)($settings['m_kehadiran'] ?? 50) }} },
      { kode: 'm_presentasi', nama: 'Hasil Pekerjaan', bobot: {{ (int)($settings['m_presentasi'] ?? 50) }} }
    ];

    const weightMap = mitraItems.reduce((acc, item) => {
      acc[item.kode] = item.bobot;

      return acc;
    }, {});

    const members = [
      @foreach($members as $m)
        { id: '{{ $m->id }}', nim: '{{ $m->nim }}', nama: '{{ $m->nama }}' },
      @endforeach
    ];
    const membersMap = members.reduce((acc, member) => {
      acc[String(member.id)] = member;

      return acc;
    }, {});

    const fetchUrl = "{{ route('admin.evaluasi.penilaian-mitra.show-by-project', ['project' => '__ID__']) }}".replace('__ID__', cardId);
    const gradeUrl = "{{ route('admin.evaluasi.project.grade.mitra', ['card'=>'__ID__']) }}".replace('__ID__', cardId);
    const styleId = 'grade-mitra-modal-styles';

    const syncCardEvaluations = (evaluations) => {
      const cardEl = document.querySelector(`.board-card[data-card-uuid="${cardId}"]`);
      if (!cardEl) {
        return;
      }

      const list = Array.isArray(evaluations) ? evaluations : [];
      const validScores = list
        .map(item => item?.nilai_akhir)
        .filter(value => value !== null && value !== undefined)
        .map(value => parseFloat(value))
        .filter(value => ! Number.isNaN(value));

      const avg = validScores.length ? Math.round(validScores.reduce((sum, value) => sum + value, 0) / validScores.length) : null;
      const scoreEl = cardEl.querySelector('.score-mitra-val');
      if (scoreEl) {
        scoreEl.textContent = avg !== null ? avg : '—';
      }

      const scoreCard = cardEl.querySelector('.score-card');
      if (scoreCard) {
        let wrapper = scoreCard.querySelector('.per-mahasiswa-wrapper-mitra');
        if (!list.length) {
          if (wrapper) {
            wrapper.remove();
          }
          const dosenCount = window.cardGrades?.[cardId]?.evaluasi_dosen_summary?.count ?? 0;
          if (!dosenCount) {
            cardEl.classList.remove('border-success');
          }
        } else {
          if (!wrapper) {
            wrapper = document.createElement('div');
            wrapper.className = 'mt-2 per-mahasiswa-wrapper per-mahasiswa-wrapper-mitra';
            scoreCard.appendChild(wrapper);
          }

          const rowsHtml = list.map((item, index) => {
            const member = membersMap[String(item.mahasiswa_id)] || {};
            const name = item.mahasiswa_nama || member.nama || 'Mahasiswa';
            const nim = item.mahasiswa_nim || member.nim || '-';
            const nilai = item.nilai_akhir !== null && item.nilai_akhir !== undefined
              ? Number.parseFloat(item.nilai_akhir).toFixed(1)
              : null;
            const grade = item.grade || (nilai !== null ? getGradeFromScore(Number.parseFloat(item.nilai_akhir)) : null);
            const gradeClass = grade ? `badge-${getGradeColor(grade)}` : 'badge-secondary';
            const borderClass = index === 0 ? '' : 'border-top';

            if (nilai === null) {
              return `
                <div class="d-flex align-items-center justify-content-between px-2 py-1 ${borderClass}" style="border-color: var(--border);">
                  <div class="small">
                    <div class="font-weight-bold">${name}</div>
                    <div class="text-muted">${nim}</div>
                  </div>
                  <div class="text-right text-muted small">Belum dinilai</div>
                </div>`;
            }

            return `
              <div class="d-flex align-items-center justify-content-between px-2 py-1 ${borderClass}" style="border-color: var(--border);">
                <div class="small">
                  <div class="font-weight-bold">${name}</div>
                  <div class="text-muted">${nim}</div>
                </div>
                <div class="text-right">
                  <div class="font-weight-bold">${nilai}</div>
                  <span class="badge ${gradeClass}">${grade || '-'}</span>
                </div>
              </div>`;
          }).join('');

          wrapper.innerHTML = `
            <div class="d-flex justify-content-between align-items-center text-muted text-uppercase small">
              <span>Nilai Mitra per Mahasiswa</span>
              <span class="badge badge-light border">${list.length} orang</span>
            </div>
            <div class="mt-2 rounded bg-white" style="border:1px solid var(--border);">
              ${rowsHtml}
            </div>`;
          cardEl.classList.add('border-success');
        }
      }

      window.cardGrades = window.cardGrades || {};
      window.cardGrades[cardId] = window.cardGrades[cardId] || {};
      window.cardGrades[cardId].evaluasi_mitra_summary = { avg, count: list.length };
      window.cardGrades[cardId].evaluasi_mitra_details = list;
      window.cardGrades[cardId].evaluasi_mitra = list.length ? list[list.length - 1] : null;
    };

    const existingEvaluations = {};
    try {
      const response = await fetch(fetchUrl, {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      if (response.ok) {
        const payload = await response.json();
        if (payload?.success && Array.isArray(payload.evaluations)) {
          payload.evaluations.forEach(item => {
            if (!item || typeof item.mahasiswa_id === 'undefined') {
              return;
            }
            existingEvaluations[String(item.mahasiswa_id)] = item;
          });
          syncCardEvaluations(payload.evaluations);
        }
      }
    } catch (error) {
      console.warn('Gagal memuat evaluasi mitra awal', error);
    }

    const buildRow = (member) => {
      const current = existingEvaluations[member.id] || {};
      const valueFor = (kode) => {
        const val = current[kode];
        return (val === 0 || val) ? val : '';
      };

      return `
        <tr data-member="${member.id}" data-evaluation-id="${current.id || ''}">
          <td style="vertical-align: middle; background: #f8f9fa; font-weight: 600;">
            <div>${member.nama}</div>
            <small style="color: #6b7280;">${member.nim}</small>
          </td>
          ${mitraItems.map(item => `
            <td style="text-align: center; vertical-align: middle;">
              <input type="number"
                     class="form-control form-control-sm grade-input-mitra"
                     data-member="${member.id}"
                     data-item="${item.kode}"
                     min="0" max="100"
                     value="${valueFor(item.kode)}"
                     placeholder="-"
                     style="text-align: center;">
            </td>
          `).join('')}
          <td style="text-align: center; vertical-align: middle; font-weight: 600; background: #f8f9fa;">
            <span class="badge badge-info average-badge-mitra">-</span>
          </td>
          <td style="text-align: center; vertical-align: middle; font-weight: 600; background: #e3f2fd;">
            <span class="badge badge-success percentage-badge-mitra">-</span>
          </td>
        </tr>`;
    };

    const modalHtml = `
      <div class="grade-modal-container">
        <div class="text-center mb-4">
          <h4 style="margin: 0; font-size: 1.3rem; font-weight: 700; color: #2c3e50;">${cardTitle}</h4>
          <p style="margin: 0.5rem 0 0 0; color: #6b7280; font-size: 1rem;">Penilaian Mitra per Mahasiswa</p>
        </div>

        <div class="table-responsive" style="max-height: 65vh; overflow-y: auto;">
          <table class="table table-bordered" style="font-size: 0.9rem; margin-bottom: 0;">
            <thead class="thead-info sticky-top">
              <tr>
                <th style="min-width: 180px; background: #17a2b8; color: white; border-color: #138496;">Mahasiswa</th>
                ${mitraItems.map(item => `
                  <th style="min-width: 140px; text-align: center; background: #17a2b8; color: white; border-color: #138496;">
                    <div>${item.nama}</div>
                    <small style="font-weight: normal; opacity: 0.8;">Bobot: ${item.bobot}%</small>
                    <div style="font-size: 0.7rem; opacity: 0.6; margin-top: 2px;">Input 0-100</div>
                  </th>
                `).join('')}
                <th style="min-width: 100px; text-align: center; background: #17a2b8; color: white; border-color: #138496;">Rata-rata</th>
                <th style="min-width: 120px; text-align: center; background: #17a2b8; color: white; border-color: #138496;">
                  Final<br><small style="font-weight: normal; opacity: 0.8;">(Weighted)</small>
                </th>
              </tr>
            </thead>
            <tbody>
              ${members.map(buildRow).join('')}
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4">
          <div class="text-left">
            <small class="text-muted">
              <strong>Keterangan:</strong> Input nilai 0-100 • Sistem menghitung otomatis berdasarkan bobot
            </small>
          </div>
          <div class="text-right">
            <small class="text-muted">Total Mahasiswa: ${members.length}</small>
          </div>
        </div>
      </div>`;

    const calculateRow = (row) => {
      const inputs = row.querySelectorAll('.grade-input-mitra');
      let total = 0;
      let count = 0;
      let weighted = 0;

      inputs.forEach(input => {
        const raw = input.value.trim();
        if (raw === '') {
          return;
        }
        const numeric = Math.max(0, Math.min(100, parseInt(raw, 10) || 0));
        total += numeric;
        count++;
        const kode = input.getAttribute('data-item');
        const weight = weightMap[kode] ?? 0;
        weighted += (numeric * weight / 100);
      });

      row.dataset.averageScore = count > 0 ? (total / count).toFixed(2) : '';
      row.dataset.finalScore = weighted > 0 ? weighted.toFixed(2) : '';

      const avgBadge = row.querySelector('.average-badge-mitra');
      if (avgBadge) {
        avgBadge.textContent = count > 0 ? Math.round(total / count) : '-';
      }

      const finalBadge = row.querySelector('.percentage-badge-mitra');
      if (finalBadge) {
        finalBadge.textContent = weighted > 0 ? Math.round(weighted) : '-';
      }
    };

    Swal.fire({
      html: modalHtml,
      width: '100%',
      showConfirmButton: true,
      confirmButtonText: ' Simpan Nilai',
      confirmButtonColor: '#28a745',
      showCancelButton: true,
      cancelButtonText: ' Batal',
      showCloseButton: true,
      allowOutsideClick: () => !Swal.isLoading(),
      customClass: {
        container: 'grade-mitra-modal',
        popup: 'swal2-popup'
      },
      didOpen: () => {
        const popup = Swal.getPopup();
        popup.querySelectorAll('.grade-input-mitra').forEach(input => {
          const row = input.closest('tr');
          calculateRow(row);
          input.addEventListener('input', () => calculateRow(row));
        });

        if (!document.querySelector(`#${styleId}`)) {
          const style = document.createElement('style');
          style.id = styleId;
          style.textContent = `
            .grade-mitra-modal .swal2-popup { padding: 0; border-radius: 0.5rem; max-width: none; width: 100%; }
            .grade-input-mitra { transition: all 0.2s ease; }
            .grade-input-mitra:focus { border-color: #36b9cc; box-shadow: 0 0 0 0.2rem rgba(54,185,204,0.25); }
            .grade-input-mitra:hover { border-color: #36b9cc; }
            .grade-mitra-modal .table { background: white; }
            .grade-mitra-modal .table th { position: sticky; top: 0; z-index: 10; }
          `;
          document.head.appendChild(style);
        }
      },
      preConfirm: () => {
        const popup = Swal.getPopup();
        const rows = Array.from(popup.querySelectorAll('tbody tr'));
        const items = {};

        rows.forEach(row => calculateRow(row));

        rows.forEach(row => {
          const memberId = row.getAttribute('data-member');
          const rowData = {};
          row.querySelectorAll('.grade-input-mitra').forEach(input => {
            const raw = input.value.trim();
            if (raw === '') {
              return;
            }
            const numeric = Math.max(0, Math.min(100, parseInt(raw, 10) || 0));
            rowData[input.getAttribute('data-item')] = numeric;
          });

          if (Object.keys(rowData).length > 0) {
            items[memberId] = rowData;
          }
        });

        if (! Object.keys(items).length) {
          Swal.showValidationMessage('Isi minimal satu nilai mitra sebelum menyimpan');

          return false;
        }

        const finalScores = rows
          .map(row => parseFloat(row.dataset.finalScore || '0'))
          .filter(value => ! Number.isNaN(value) && value > 0);

        return fetch(gradeUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            sesi_id: {{ $sesi->id }},
            items
          })
        })
          .then(response => {
            if (! response.ok) {
              throw new Error('Gagal menyimpan nilai (HTTP '+response.status+')');
            }

            return response.json();
          })
          .then(data => {
            if (! data?.success) {
              throw new Error(data?.message || 'Gagal menyimpan nilai');
            }

            return { data, finalScores };
          })
          .catch(error => {
            Swal.showValidationMessage(error.message || 'Gagal menyimpan nilai');

            return false;
          });
      }
    }).then(result => {
      if (! result.isConfirmed || ! result.value) {
        return;
      }

      const { data } = result.value;
      swalToast('success', data.message || 'Nilai mitra berhasil disimpan');

      fetch(fetchUrl, {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
        .then(res => res.ok ? res.json() : null)
        .then(payload => {
          if (payload?.success && Array.isArray(payload.evaluations)) {
            syncCardEvaluations(payload.evaluations);
          }
        })
        .catch(error => console.warn('Gagal memuat ulang evaluasi mitra', error));
    });
  };

  // Modal Detail Proyek untuk Evaluasi Dosen - sinkron dengan schema evaluasi_dosen
  window.showProjectDetail = async function(cardId, cardTitle) {
    if (!cardId) {
      return;
    }

    const dosenItems = [
      { kode: 'd_hasil', nama: 'Kualitas Hasil Proyek', bobot: {{ (int)($settings['d_hasil'] ?? 30) }} },
      { kode: 'd_teknis', nama: 'Tingkat Kompleksitas Teknis', bobot: {{ (int)($settings['d_teknis'] ?? 20) }} },
      { kode: 'd_user', nama: 'Kesesuaian dengan Kebutuhan Pengguna', bobot: {{ (int)($settings['d_user'] ?? 15) }} },
      { kode: 'd_efisiensi', nama: 'Efisiensi Waktu dan Biaya', bobot: {{ (int)($settings['d_efisiensi'] ?? 10) }} },
      { kode: 'd_dokpro', nama: 'Dokumentasi dan Profesionalisme', bobot: {{ (int)($settings['d_dokpro'] ?? 15) }} },
      { kode: 'd_inisiatif', nama: 'Kemandirian dan Inisiatif', bobot: {{ (int)($settings['d_inisiatif'] ?? 10) }} }
    ];

    const members = [
      @foreach($members as $m)
        { id: '{{ $m->id }}', nim: '{{ $m->nim }}', nama: '{{ $m->nama }}' },
      @endforeach
    ];
    const membersMap = members.reduce((acc, member) => {
      acc[String(member.id)] = member;

      return acc;
    }, {});

    const totalBobot = dosenItems.reduce((sum, item) => sum + item.bobot, 0);
    const fetchUrl = "{{ route('admin.evaluasi.penilaian-dosen.show-by-project', ['project' => '__ID__']) }}".replace('__ID__', cardId);
    const gradeUrl = "{{ route('admin.evaluasi.project.grade.dosen', ['card' => '__ID__']) }}".replace('__ID__', cardId);

    const syncCardEvaluations = (evaluations) => {
      const cardEl = document.querySelector(`.board-card[data-card-uuid="${cardId}"]`);
      if (!cardEl) {
        return;
      }

      const list = Array.isArray(evaluations) ? evaluations : [];
      const validScores = list
        .map(item => item?.nilai_akhir)
        .filter(value => value !== null && value !== undefined)
        .map(value => parseFloat(value))
        .filter(value => ! Number.isNaN(value));

      const avg = validScores.length ? Math.round(validScores.reduce((sum, value) => sum + value, 0) / validScores.length) : null;
      const titleWrap = cardEl.querySelector('.d-flex.align-items-start.mb-1');
      let badge = titleWrap?.querySelector('.badge.badge-success.badge-pill');

      if (avg !== null) {
        if (! badge && titleWrap) {
          badge = document.createElement('span');
          badge.className = 'badge badge-success badge-pill ml-2';
          titleWrap.appendChild(badge);
        }
        if (badge) {
          const icon = badge.querySelector('i')?.outerHTML || '<i class="fas fa-check mr-1"></i>';
          badge.innerHTML = `${icon}${avg}`;
          badge.style.display = '';
        }
      } else if (badge) {
        badge.remove();
      }

      const scoreEl = cardEl.querySelector('.score-dosen-val');
      if (scoreEl) {
        scoreEl.textContent = avg !== null ? avg : '—';
      }

      const scoreCard = cardEl.querySelector('.score-card');
      if (scoreCard) {
        let wrapper = scoreCard.querySelector('.per-mahasiswa-wrapper-dosen');

        if (!list.length) {
          if (wrapper) {
            wrapper.remove();
          }
          const mitraCount = window.cardGrades?.[cardId]?.evaluasi_mitra_summary?.count ?? 0;
          if (!mitraCount) {
            cardEl.classList.remove('border-success');
          }
        } else {
          if (!wrapper) {
            wrapper = document.createElement('div');
            wrapper.className = 'mt-2 per-mahasiswa-wrapper per-mahasiswa-wrapper-dosen';
            scoreCard.appendChild(wrapper);
          }

          const rowsHtml = list.map((item, index) => {
            const member = membersMap[String(item.mahasiswa_id)] || {};
            const name = item.mahasiswa_nama || member.nama || 'Mahasiswa';
            const nim = item.mahasiswa_nim || member.nim || '-';
            const nilai = item.nilai_akhir !== null && item.nilai_akhir !== undefined
              ? Number.parseFloat(item.nilai_akhir).toFixed(1)
              : null;
            const grade = item.grade || (nilai !== null ? getGradeFromScore(Number.parseFloat(item.nilai_akhir)) : null);
            const gradeClass = grade ? `badge-${getGradeColor(grade)}` : 'badge-secondary';
            const borderClass = index === 0 ? '' : 'border-top';

            if (nilai === null) {
              return `
                <div class="d-flex align-items-center justify-content-between px-2 py-1 ${borderClass}" style="border-color: var(--border);">
                  <div class="small">
                    <div class="font-weight-bold">${name}</div>
                    <div class="text-muted">${nim}</div>
                  </div>
                  <div class="text-right text-muted small">Belum dinilai</div>
                </div>`;
            }

            return `
              <div class="d-flex align-items-center justify-content-between px-2 py-1 ${borderClass}" style="border-color: var(--border);">
                <div class="small">
                  <div class="font-weight-bold">${name}</div>
                  <div class="text-muted">${nim}</div>
                </div>
                <div class="text-right">
                  <div class="font-weight-bold">${nilai}</div>
                  <span class="badge ${gradeClass}">${grade || '-'}</span>
                </div>
              </div>`;
          }).join('');

          wrapper.innerHTML = `
            <div class="d-flex justify-content-between align-items-center text-muted text-uppercase small">
              <span>Nilai Dosen per Mahasiswa</span>
              <span class="badge badge-light border">${list.length} orang</span>
            </div>
            <div class="mt-2 rounded bg-white" style="border:1px solid var(--border);">
              ${rowsHtml}
            </div>`;
          cardEl.classList.add('border-success');
        }
      }

      window.cardGrades = window.cardGrades || {};
      window.cardGrades[cardId] = window.cardGrades[cardId] || {};
      window.cardGrades[cardId].evaluasi_dosen_summary = { avg, count: list.length };
      window.cardGrades[cardId].evaluasi_dosen_details = list;
      window.cardGrades[cardId].evaluasi_dosen = list.length ? list[list.length - 1] : null;
    };

    const existingEvaluations = {};
    try {
      const response = await fetch(fetchUrl, {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      if (response.ok) {
        const payload = await response.json();
        if (payload?.success && Array.isArray(payload.evaluations)) {
          payload.evaluations.forEach(item => {
            if (!item || typeof item.mahasiswa_id === 'undefined') {
              return;
            }
            existingEvaluations[String(item.mahasiswa_id)] = item;
          });
          syncCardEvaluations(payload.evaluations);
        }
      }
    } catch (error) {
      console.warn('Gagal memuat evaluasi dosen awal', error);
    }

    const buildRow = member => {
      const current = existingEvaluations[member.id] || {};
      return `
        <tr data-member="${member.id}" data-evaluation-id="${current.id || ''}">
          <td style="vertical-align: middle; background: #f8f9fa; font-weight: 600;">
            <div>${member.nama}</div>
            <small style="color: #6b7280;">${member.nim}</small>
          </td>
          ${dosenItems.map(item => {
            const raw = current[item.kode];
            const value = (raw === 0 || raw) ? raw : '';
            return `
              <td style="text-align: center; vertical-align: middle;">
                <input type="number"
                       class="form-control form-control-sm grade-input"
                       data-member="${member.id}"
                       data-item="${item.kode}"
                       data-card="${cardId}"
                       min="0" max="100"
                       value="${value}"
                       placeholder="-"
                       style="text-align: center;">
              </td>`;
          }).join('')}
          <td style="text-align: center; vertical-align: middle; font-weight: 600; background: #f8f9fa;">-</td>
          <td style="text-align: center; vertical-align: middle; font-weight: 600; background: #e3f2fd;">-</td>
        </tr>`;
    };

    const modalHtml = `
      <div class="project-detail-modal">
        <div class="text-center mb-4">
          <h4 style="margin: 0; font-size: 1.3rem; font-weight: 700; color: #2c3e50;">${cardTitle}</h4>
          <p style="margin: 0.5rem 0 0 0; color: #6b7280; font-size: 1rem;">Evaluasi Dosen per Mahasiswa</p>
          <small style="color: #28a745; font-weight: 600;">Total Bobot: ${totalBobot}%</small>
        </div>

        <div class="table-responsive" style="max-height: 65vh; overflow-y: auto;">
          <table class="table table-bordered" style="font-size: 0.9rem; margin-bottom: 0;">
            <thead class="thead-dark sticky-top">
              <tr>
                <th style="min-width: 180px; background: #343a40; color: white; border-color: #454d55;">Mahasiswa</th>
                ${dosenItems.map(item => `
                  <th style="min-width: 140px; text-align: center; background: #343a40; color: white; border-color: #454d55;">
                    <div>${item.nama}</div>
                    <small style="font-weight: normal; opacity: 0.8;">Bobot: ${item.bobot}%</small>
                    <div style="font-size: 0.7rem; opacity: 0.6; margin-top: 2px;">Input 1-100</div>
                  </th>
                `).join('')}
                <th style="min-width: 100px; text-align: center; background: #343a40; color: white; border-color: #454d55;">Rata-rata</th>
                <th style="min-width: 120px; text-align: center; background: #343a40; color: white; border-color: #454d55;">
                  Final<br><small style="font-weight: normal; opacity: 0.8;">(Weighted)</small>
                </th>
              </tr>
            </thead>
            <tbody>
              ${members.map(buildRow).join('')}
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4">
          <div class="text-left">
            <small class="text-muted">
              <strong>Keterangan:</strong> Input nilai 1-100 untuk setiap kriteria • Sistem akan menghitung otomatis
            </small>
          </div>
          <div class="text-right">
            <small class="text-muted">Total Mahasiswa: ${members.length}</small>
          </div>
        </div>
      </div>
    `;

    Swal.fire({
      html: modalHtml,
      width: '95%',
      showConfirmButton: true,
      confirmButtonText: 'Simpan Semua Nilai',
      confirmButtonColor: '#28a745',
      showCancelButton: true,
      cancelButtonText: ' Batal',
      showCloseButton: true,
      showLoaderOnConfirm: true,
      allowOutsideClick: () => !Swal.isLoading(),
      customClass: {
        container: 'project-detail-modal',
        popup: 'swal2-popup'
      },
      didOpen: () => {
        document.querySelectorAll('.grade-input').forEach(input => {
          input.addEventListener('input', function() {
            const row = this.closest('tr');
            if (row) {
              calculateRowTotals(row);
            }
          });
        });

        document.querySelectorAll('.project-detail-modal tbody tr').forEach(row => {
          calculateRowTotals(row);
        });
      },
      preConfirm: () => {
        const popup = Swal.getPopup();
        const rows = popup.querySelectorAll('tbody tr');
        const items = {};

        rows.forEach(row => calculateRowTotals(row));

        rows.forEach(row => {
          const memberId = row.getAttribute('data-member');
          const inputs = row.querySelectorAll('.grade-input');
          const rowData = {};

          inputs.forEach(input => {
            const raw = input.value.trim();
            if (raw === '') {
              return;
            }
            const numeric = Math.max(0, Math.min(100, parseInt(raw, 10) || 0));
            rowData[input.getAttribute('data-item')] = numeric;
          });

          if (Object.keys(rowData).length > 0) {
            items[memberId] = rowData;
          }
        });

        if (! Object.keys(items).length) {
          Swal.showValidationMessage('Isi minimal satu nilai sebelum menyimpan');

          return false;
        }

        const finalScores = [];
        rows.forEach(row => {
          const val = parseFloat(row.dataset.finalScore || '0');
          if (! Number.isNaN(val) && val > 0) {
            finalScores.push(val);
          }
        });

        return fetch(gradeUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            sesi_id: {{ $sesi->id }},
            items
          })
        })
          .then(response => {
            if (! response.ok) {
              throw new Error('Gagal menyimpan nilai (HTTP '+response.status+')');
            }

            return response.json();
          })
          .then(data => {
            if (! data?.success) {
              throw new Error(data?.message || 'Gagal menyimpan nilai');
            }

            return { data, finalScores };
          })
          .catch(error => {
            Swal.showValidationMessage(error.message || 'Gagal menyimpan nilai');

            return false;
          });
      }
    }).then(result => {
      if (! result.isConfirmed || ! result.value) {
        return;
      }

      const { data, finalScores } = result.value;
      swalToast('success', data.message || 'Nilai dosen berhasil disimpan');

      const cardEl = document.querySelector(`.board-card[data-card-uuid="${cardId}"]`);
      if (cardEl && Array.isArray(finalScores) && finalScores.length) {
        const total = finalScores.reduce((sum, val) => sum + Number(val), 0);
        const avg = Math.round(total / finalScores.length);

        const scoreEl = cardEl.querySelector('.score-dosen-val');
        if (scoreEl) {
          scoreEl.textContent = avg;
        }

        const badge = cardEl.querySelector('.badge.badge-success.badge-pill');
        if (badge) {
          const icon = badge.querySelector('i')?.outerHTML || '<i class="fas fa-check mr-1"></i>';
          badge.innerHTML = icon + avg;
        }

        cardEl.classList.add('border-success');

        window.cardGrades = window.cardGrades || {};
        window.cardGrades[cardId] = window.cardGrades[cardId] || {};
        window.cardGrades[cardId].dosen = Object.assign({}, window.cardGrades[cardId].dosen, { total: avg });
        window.cardGrades[cardId].evaluasi_dosen = Object.assign({}, window.cardGrades[cardId].evaluasi_dosen, {
          nilai_akhir: avg,
          status: 'submitted'
        });
      }

      fetch(fetchUrl, {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
        .then(res => res.ok ? res.json() : null)
        .then(payload => {
          if (payload?.success && Array.isArray(payload.evaluations)) {
            syncCardEvaluations(payload.evaluations);
          }
        })
        .catch(error => console.warn('Gagal memuat ulang evaluasi dosen', error));
    });

    // Add custom styles
    if (!document.querySelector('#project-detail-modal-styles')) {
      const style = document.createElement('style');
      style.id = 'project-detail-modal-styles';
      style.textContent = `
        .project-detail-modal .swal2-popup {
          padding: 0;
          border-radius: 0.75rem;
          max-height: 90vh;
        }
        .project-detail-modal .table {
          margin-bottom: 0;
        }
        .project-detail-modal .table th,
        .project-detail-modal .table td {
          padding: 0.5rem;
          vertical-align: middle;
        }
        .project-detail-modal .form-control-sm {
          height: 30px;
          font-size: 0.8rem;
        }
        .project-detail-modal .form-control-sm.border-success {
          border: 2px solid #28a745;
          background-color: #f8fff9;
        }
      `;
      document.head.appendChild(style);
    }
  }

  function calculateRowTotals(row) {
    const inputs = row.querySelectorAll('.grade-input');
    const dosenItems = [
      { kode: 'd_hasil', bobot: {{ (int)($settings['d_hasil'] ?? 30) }} },
      { kode: 'd_teknis', bobot: {{ (int)($settings['d_teknis'] ?? 20) }} },
      { kode: 'd_user', bobot: {{ (int)($settings['d_user'] ?? 15) }} },
      { kode: 'd_efisiensi', bobot: {{ (int)($settings['d_efisiensi'] ?? 10) }} },
      { kode: 'd_dokpro', bobot: {{ (int)($settings['d_dokpro'] ?? 15) }} },
      { kode: 'd_inisiatif', bobot: {{ (int)($settings['d_inisiatif'] ?? 10) }} }
    ];

    let total = 0;
    let count = 0;
    let weightedTotal = 0;

    inputs.forEach((input, index) => {
      const value = parseInt(input.value) || 0;
      if (value > 0) {
        total += value;
        count++;
        weightedTotal += (value * dosenItems[index].bobot / 100);
      }
    });

    const avgCell = row.children[inputs.length + 1];
    const finalCell = row.children[inputs.length + 2];

    row.dataset.averageScore = count > 0 ? (total / count).toFixed(2) : '';
    row.dataset.finalScore = weightedTotal > 0 ? weightedTotal.toFixed(2) : '';

    avgCell.innerHTML = count > 0 ? (total / count).toFixed(1) : '-';

    if (weightedTotal > 0) {
      const final = weightedTotal.toFixed(1);
      const grade = getGradeFromScore(weightedTotal);
      finalCell.innerHTML = `${final} <br><small class="badge badge-${getGradeColor(grade)}">${grade}</small>`;
    } else {
      finalCell.innerHTML = '-';
    }
  }

  function getGradeFromScore(score) {
    if (score >= 85) return 'A';
    if (score >= 75) return 'B';
    if (score >= 65) return 'C';
    if (score >= 55) return 'D';
    return 'E';
  }

  function getGradeColor(grade) {
    const colors = {
      'A': 'success',
      'B': 'info',
      'C': 'warning',
      'D': 'danger',
      'E': 'dark'
    };
    return colors[grade] || 'secondary';
  }

  function swalToast(type, message) {
    const Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true,
      didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
      }
    });

    Toast.fire({
      icon: type,
      title: message
    });
  }
})();
</script>
@endpush
