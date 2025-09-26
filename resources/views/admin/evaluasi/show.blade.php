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
      ];
  });

  $absensis   = collect($sesi->absensis ?? []);
  $sesiIndis  = collect($sesi->sesiIndikators ?? []);
  $nilaiDetil = collect($sesi->nilaiDetails ?? []);

  $absByMhs   = $absensis->keyBy('mahasiswa_id');
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
  $wAP            = (int)($settings['w_ap'] ?? 30);
  $wAP_Kehadiran  = (int)($settings['w_ap_kehadiran'] ?? 50);
  $wAP_Presentasi = (int)($settings['w_ap_presentasi'] ?? 50);

  $idKehadiran  = $byKode['m_kehadiran']['id']  ?? null;
  $idPresentasi = $byKode['m_presentasi']['id'] ?? null;
  $apIndicatorsReady = $idKehadiran && $idPresentasi;

  $hadirCount = $members->filter(function($m) use ($absByMhs){
    $st = optional($absByMhs->get($m->id))->status;
    return in_array($st, ['Hadir','Terlambat'], true);
  })->count();
  $total = $members->count();

  $apScores = [];
  $mitraScores = [];
  foreach ($members as $m) {
    $rows = $nilaiByMhs->get($m->id) ?? collect();
    $skKeh = (int)($rows->firstWhere('indikator_id',$idKehadiran)->skor ?? 0);
    $skPrs = (int)($rows->firstWhere('indikator_id',$idPresentasi)->skor ?? 0);
    $apScores[$m->id]    = (int) round($skKeh * $wAP_Kehadiran/100 + $skPrs * $wAP_Presentasi/100);
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

  $nilaiAkhirPerMhs = [];
  foreach ($members as $m) {
    $np = $nilaiProyekPerMhs[$m->id] ?? 0;
    $ap = $apScores[$m->id] ?? 0;
    $nilaiAkhirPerMhs[$m->id] = (int) round($np * $wKelompok/100 + $ap * $wAP/100);
  }

  $avgNilai = (int) round(collect($nilaiAkhirPerMhs)->avg() ?? 0);

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
        <strong>{{ optional($evaluator)->name ?? optional($evaluator)->nama ?? '—' }}</strong>
      </div>
      <div class="text-muted small">
        Jadwal:
        @if(!empty($sesi->jadwal_mulai))
          {{ \Carbon\Carbon::parse($sesi->jadwal_mulai)->locale('id')->translatedFormat('d M Y, H:i') }}
          @if(!empty($sesi->jadwal_selesai)) – {{ \Carbon\Carbon::parse($sesi->jadwal_selesai)->locale('id')->translatedFormat('H:i') }} @endif
        @else — @endif
        @if(!empty($sesi->lokasi)) • {{ $sesi->lokasi }} @endif
      </div>
      <div class="mt-1">
        <span class="badge badge-warning">{{ $sesi->status ?? '—' }}</span>
      </div>
    </div>
  </div>

  {{-- Metrik ringkas --}}
  <div class="row mb-4">
    <div class="col-md-3 mb-2">
      <div class="metric-card d-flex align-items-center">
        <div class="icon-wrap"><i class="fas fa-user-check" aria-hidden="true"></i></div>
        <div>
          <div class="metric-title">Kehadiran</div>
          <div class="metric-value">{{ $hadirCount }}/{{ $total }}</div>
          <div class="metric-sub">{{ (int) round($total ? ($hadirCount*100/$total) : 0) }}%</div>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-2">
      <div class="metric-card d-flex align-items-center">
        <div class="icon-wrap"><i class="fas fa-chart-line" aria-hidden="true"></i></div>
        <div>
          <div class="metric-title">Nilai Rata-rata</div>
          <div class="metric-value">{{ $avgNilai }}</div>
          <div class="metric-sub">{{ kategoriLabel($avgNilai) }}</div>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-2">
      <div class="metric-card d-flex align-items-center">
        <div class="icon-wrap"><i class="fas fa-project-diagram" aria-hidden="true"></i></div>
        <div>
          <div class="metric-title">Nilai Proyek (Kelompok)</div>
          <div class="metric-value">{{ $nilaiProyekKelompok }}</div>
          <div class="metric-sub">Dosen {{ $wDosen }}% • Mitra {{ $wMitra }}%</div>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-2">
      <div class="metric-card d-flex align-items-center">
        <div class="icon-wrap"><i class="fas fa-user-clock" aria-hidden="true"></i></div>
        <div>
          <div class="metric-title">Kontribusi AP</div>
          <div class="metric-value">{{ $wAP }}%</div>
          <div class="metric-sub">Kehadiran {{ $wAP_Kehadiran }}% • Presentasi {{ $wAP_Presentasi }}%</div>
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
                  @endphp

                  <div class="card board-card shadow-xs mb-2 hover-raise"
                       data-card-id="{{ $card->id }}"
                       data-card-uuid="{{ $card->uuid }}"
                       data-title="{{ $card->title }}">
                    <div class="card-body p-2 d-flex flex-column">

                      {{-- Judul --}}
                      <div class="card-title-full font-weight-bold mb-1">{{ $card->title }}</div>

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
                      @php $dTot = optional($cgRow)->total; $mTot = optional($cgRowM)->total; @endphp
                      <div class="score-card mb-2 p-2" style="border:1px solid var(--border);border-radius:.5rem;background:var(--bg-soft)">
                        <div class="d-flex justify-content-between">
                          <div class="mr-2">
                            <i class="fas fa-user-graduate mr-1" aria-hidden="true"></i>
                            Dosen: <span class="font-weight-bold score-dosen-val">{{ $dTot !== null ? (int)$dTot : '—' }}</span>
                          </div>
                          <div>
                            <i class="fas fa-handshake mr-1" aria-hidden="true"></i>
                            Mitra: <span class="font-weight-bold score-mitra-val">{{ $mTot !== null ? (int)$mTot : '—' }}</span>
                          </div>
                        </div>
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
                                  class="btn btn-outline-primary"
                                  title="Nilai Dosen"
                                  onclick="gradeDosen('{{ $card->uuid }}','{{ addslashes($card->title) }}')">
                            <i class="fas fa-user-graduate" aria-hidden="true"></i>
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

  // Edit Absensi
  window.editAbsensi = function(mahasiswaId, nim, nama, current, catatan){
    const options = ['Hadir','Terlambat','Sakit','Dispensasi','Alpa'];
    const selectHtml = `<select id="swStatus" class="swal2-select">${options.map(o=>`<option ${o===current?'selected':''}>${o}</option>`).join('')}</select>`;
    const noteHtml = `<input id="swKet" class="swal2-input" placeholder="Catatan" value="${(catatan||'').replace(/\"/g,'&quot;')}">`;
    Swal.fire({
      title: `Absensi ${nim}`, html: `<div class="text-left">Nama: <strong>${nama}</strong></div>${selectHtml}${noteHtml}`,
      focusConfirm:false, showCancelButton:true, confirmButtonText:'Simpan'
    }).then((res)=>{
      if(!res.isConfirmed) return;
      const status = document.getElementById('swStatus').value;
      const ket = document.getElementById('swKet').value;
      $.post("{{ route('admin.evaluasi.absensi.save', $sesi->id) }}", {mahasiswa_id:mahasiswaId,status:status,keterangan:ket,_token:'{{ csrf_token() }}'})
        .done(function(r){ r.success ? (swalToast('success','Absensi disimpan'), location.reload()) : Swal.fire('Gagal','Tidak dapat menyimpan absensi','error'); })
        .fail(()=> Swal.fire('Gagal','Tidak dapat menyimpan absensi','error'));
    });
  };

  // Edit AP (dua input angka agar presisi)
  window.editAP = function(mahasiswaId, nim, nama, vKeh, vPre){
    const html = `
      <div class="text-left mb-2">Nama: <strong>${nama}</strong></div>
      <label class="d-block text-left">Kehadiran</label>
      <input id="apKeh" type="number" min="0" max="100" value="${vKeh}" class="swal2-input" style="width:140px">
      <label class="d-block text-left mt-2">Presentasi</label>
      <input id="apPre" type="number" min="0" max="100" value="${vPre}" class="swal2-input" style="width:140px">
    `;
    Swal.fire({title:`AP ${nim}`, html, focusConfirm:false, showCancelButton:true, confirmButtonText:'Simpan'})
      .then((res)=>{
        if(!res.isConfirmed) return;
        const keh = parseInt(document.getElementById('apKeh').value,10)||0;
        const pre = parseInt(document.getElementById('apPre').value,10)||0;
        $.post("{{ route('admin.evaluasi.ap.save', $sesi->id) }}", {mahasiswa_id:mahasiswaId, kehadiran:keh, presentasi:pre, _token:'{{ csrf_token() }}'})
          .done(function(r){
            if(r.success){ swalToast('success','AP disimpan'); location.reload(); }
            else { Swal.fire('Gagal', r.message || 'Tidak dapat menyimpan AP', 'error'); }
          })
          .fail(function(xhr){
            const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Tidak dapat menyimpan AP';
            Swal.fire('Gagal', msg, 'error');
          });
      });
  };

  // Edit Presentasi saja
  window.editPresentasi = function(mahasiswaId, nim, nama, vKeh, vPre){
    const html = `
      <div class="text-left mb-2">Nama: <strong>${nama}</strong></div>
      <label class="d-block text-left mt-2">Presentasi</label>
      <input id="apPre2" type="number" min="0" max="100" value="${vPre}" class="swal2-input" style="width:140px">
    `;
    Swal.fire({title:`Keaktifan Presentasi ${nim}`, html, focusConfirm:false, showCancelButton:true, confirmButtonText:'Simpan'})
      .then((res)=>{
        if(!res.isConfirmed) return;
        const pre = parseInt(document.getElementById('apPre2').value,10)||0;
        $.post("{{ route('admin.evaluasi.ap.save', $sesi->id) }}", {mahasiswa_id:mahasiswaId, kehadiran:(vKeh||0), presentasi:pre, _token:'{{ csrf_token() }}'})
          .done(function(r){
            if(r.success){ swalToast('success','Keaktifan disimpan'); location.reload(); }
            else { Swal.fire('Gagal', r.message || 'Tidak dapat menyimpan', 'error'); }
          })
          .fail(function(xhr){
            const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Tidak dapat menyimpan';
            Swal.fire('Gagal', msg, 'error');
          });
      });
  };

  // Grade Dosen per proyek
  window.gradeDosen = function(cardId, title){
    if(!cardId || !title) return;
    const items = ['d_hasil','d_teknis','d_user','d_efisiensi','d_dokpro','d_inisiatif'];
    const labels = {
      d_hasil:'Kualitas Hasil Proyek', d_teknis:'Kompleksitas Teknis', d_user:'Kesesuaian Pengguna',
      d_efisiensi:'Efisiensi Waktu & Biaya', d_dokpro:'Dokumentasi & Profesionalisme', d_inisiatif:'Kemandirian & Inisiatif'
    };
    const saved = (window.cardGrades?.[cardId]?.dosen) || null;
    const nilai = (saved && saved.nilai) ? saved.nilai : {};
    let html='';
    items.forEach(k=>{
      const val = parseInt((nilai && nilai[k] != null) ? nilai[k] : 0, 10) || 0;
      html += `<div class="text-left mt-2"><label class="d-block">${labels[k]}</label>`+
              `<input id="num-${k}" type="number" min="0" max="100" value="${val}" class="swal2-input" style="width:140px"></div>`;
    });
    Swal.fire({title:`Nilai Dosen — ${title}`, html, showCancelButton:true, confirmButtonText:'Simpan'}).then(res=>{
      if(!res.isConfirmed) return;
      const payload={}; items.forEach(k=> payload[k] = parseInt(document.getElementById('num-'+k).value||'0',10)||0 );
      $.post("{{ route('admin.evaluasi.project.grade.dosen', ['card'=>'__ID__']) }}".replace('__ID__', cardId), {sesi_id: {{ $sesi->id }}, items: payload, _token:'{{ csrf_token() }}'})
        .done(function(r){
          if(r.success){
            swalToast('success','Nilai dosen disimpan');
            try{
              window.cardGrades = window.cardGrades || {};
              window.cardGrades[cardId] = window.cardGrades[cardId] || {};
              window.cardGrades[cardId].dosen = window.cardGrades[cardId].dosen || {};
              window.cardGrades[cardId].dosen.nilai = payload;
              if (typeof r.total !== 'undefined') {
                window.cardGrades[cardId].dosen.total = r.total;
                const wrap = document.querySelector('.board-card[data-card-uuid="'+cardId+'"]');
                if (wrap){
                  const el = wrap.querySelector('.score-dosen-val') || wrap.querySelector('.badge.badge-primary.ml-1');
                  if (el) el.textContent = r.total;
                }
              }
            }catch(e){}
          } else {
            Swal.fire('Gagal','Tidak dapat menyimpan','error');
          }
        })
        .fail(()=> Swal.fire('Gagal','Tidak dapat menyimpan','error'));
    });
  };

  // Grade Mitra per proyek
  window.gradeMitra = function(cardId, title){
    if(!cardId || !title) return;
    const saved = (window.cardGrades?.[cardId]?.mitra) || null;
    const n = (saved && saved.nilai) ? saved.nilai : {};
    const vK = parseInt((n && n.kehadiran != null) ? n.kehadiran : 0, 10) || 0;
    const vP = parseInt((n && n.presentasi != null) ? n.presentasi : 0, 10) || 0;
    const html = `
      <label class="d-block text-left">Kehadiran</label>
      <input id="num-mke" type="number" min="0" max="100" value="${vK}" class="swal2-input" style="width:140px">
      <label class="d-block text-left mt-2">Presentasi</label>
      <input id="num-mpr" type="number" min="0" max="100" value="${vP}" class="swal2-input" style="width:140px">
    `;
    Swal.fire({title:`Nilai Mitra — ${title}`, html, showCancelButton:true, confirmButtonText:'Simpan'}).then(res=>{
      if(!res.isConfirmed) return;
      const keh = parseInt(document.getElementById('num-mke').value||'0',10)||0;
      const pre = parseInt(document.getElementById('num-mpr').value||'0',10)||0;
      $.post("{{ route('admin.evaluasi.project.grade.mitra', ['card'=>'__ID__']) }}".replace('__ID__', cardId), {sesi_id: {{ $sesi->id }}, kehadiran:keh, presentasi:pre, _token:'{{ csrf_token() }}'})
        .done(function(r){
          if(r.success){
            swalToast('success','Nilai mitra disimpan');
            try{
              window.cardGrades = window.cardGrades || {};
              window.cardGrades[cardId] = window.cardGrades[cardId] || {};
              window.cardGrades[cardId].mitra = window.cardGrades[cardId].mitra || {};
              window.cardGrades[cardId].mitra.nilai = { kehadiran:keh, presentasi:pre };
              if (typeof r.total !== 'undefined') {
                window.cardGrades[cardId].mitra.total = r.total;
                const wrap = document.querySelector('.board-card[data-card-uuid="'+cardId+'"]');
                if (wrap){
                  const el = wrap.querySelector('.score-mitra-val') || wrap.querySelector('.badge.badge-info.ml-1');
                  if (el) el.textContent = r.total;
                }
              }
            }catch(e){}
          } else {
            Swal.fire('Gagal','Tidak dapat menyimpan','error');
          }
        })
        .fail(()=> Swal.fire('Gagal','Tidak dapat menyimpan','error'));
    });
  };
})();
</script>
@endpush
