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
    $kehadiran = $rows->firstWhere('indikator_id', $idKehadiran);
    $presentasi = $rows->firstWhere('indikator_id', $idPresentasi);
    $mitraScores[$m->id] = [
      'kehadiran' => (int)($kehadiran->skor ?? 0),
      'presentasi' => (int)($presentasi->skor ?? 0),
    ];
  }

  $wApKehadiran = (int)($settings['w_ap_kehadiran'] ?? 50);
  $wApPresentasi = (int)($settings['w_ap_presentasi'] ?? 50);
@endphp

<div class="container-fluid">
  <!-- Header -->
  <div class="d-flex align-items-center mb-4">
    <div>
      <h1 class="h3 text-gray-800 mb-1">Evaluasi Kelompok</h1>
      <div class="d-flex align-items-center text-muted small">
        <span>{{ $kelompok->nama_kelompok }}</span>
        <span class="mx-2">•</span>
        <span>{{ $periode->periode }}</span>
        <span class="mx-2">•</span>
        <span>Evaluator: {{ optional($evaluator)->nama_user ?? '-' }}</span>
      </div>
    </div>
    <div class="ml-auto">
      <a href="{{ route('evaluator.evaluasi.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Kembali
      </a>
      <a href="{{ route('evaluator.evaluasi.projectTimeline', $kelompok->uuid) }}" class="btn btn-info btn-sm ml-2">
        <i class="fas fa-clock"></i> Timeline
      </a>
      <a href="{{ route('evaluator.evaluasi.projectExport', $kelompok->uuid) }}" class="btn btn-success btn-sm ml-2">
        <i class="fas fa-file-excel"></i> Export
      </a>
    </div>
  </div>

  <!-- Metrics -->
  <div class="row mb-4">
    <div class="col-md-3">
      <div class="metric-card hover-raise">
        <div class="d-flex align-items-center">
          <div class="icon-wrap bg-primary text-white">
            <i class="fas fa-users"></i>
          </div>
          <div>
            <div class="metric-title">Total Anggota</div>
            <div class="metric-value">{{ $members->count() }}</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="metric-card hover-raise">
        <div class="d-flex align-items-center">
          <div class="icon-wrap bg-success text-white">
            <i class="fas fa-clipboard-check"></i>
          </div>
          <div>
            <div class="metric-title">Sudah Dievaluasi</div>
            <div class="metric-value">{{ $studentEvalStats['evaluated'] }}</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="metric-card hover-raise">
        <div class="d-flex align-items-center">
          <div class="icon-wrap bg-info text-white">
            <i class="fas fa-tasks"></i>
          </div>
          <div>
            <div class="metric-title">Total Proyek</div>
            <div class="metric-value">{{ $proyek_total_cards }}</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="metric-card hover-raise">
        <div class="d-flex align-items-center">
          <div class="icon-wrap bg-warning text-white">
            <i class="fas fa-calendar-check"></i>
          </div>
          <div>
            <div class="metric-title">Total Aktivitas</div>
            <div class="metric-value">{{ $aktivitas_total }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Anggota Kelompok -->
  <div class="card-section mb-4">
    <div class="card-header">
      <h5 class="section-title mb-0">
        <i class="fas fa-users text-primary"></i>
        Anggota Kelompok
      </h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th style="width:40px">No</th>
              <th>NIM</th>
              <th>Nama</th>
              <th>Kelas</th>
              <th>Status Evaluasi</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($members as $idx => $m)
              <tr>
                <td>{{ $idx + 1 }}</td>
                <td><span class="font-mono">{{ $m->nim }}</span></td>
                <td>{{ $m->nama }}</td>
                <td>{{ $m->kelas }}</td>
                <td>
                  @if(isset($studentEvaluations[$m->id]))
                    <span class="badge badge-success">Sudah Dievaluasi</span>
                  @else
                    <span class="badge badge-warning">Belum Dievaluasi</span>
                  @endif
                </td>
                <td>
                  <button class="btn btn-sm btn-primary" onclick="openEvaluationModal({{ $m->id }}, '{{ $m->nama }}')">
                    <i class="fas fa-clipboard-check"></i> Evaluasi
                  </button>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Project Board -->
  @if($proyekLists && $proyekLists->count() > 0)
  <div class="card-section mb-4">
    <div class="card-header">
      <h5 class="section-title mb-0">
        <i class="fas fa-project-diagram text-primary"></i>
        Board Proyek
        <small class="text-muted ml-2">({{ $proyek_total_cards }} kartu)</small>
      </h5>
    </div>
    <div class="card-body p-3">
      <div class="board-wrapper">
        <div class="board" id="project-board">
          @foreach($proyekLists as $list)
          <div class="board-column" data-list-id="{{ $list->id }}">
            <div class="board-col-head">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <strong>{{ $list->name }}</strong>
                  <div class="text-muted small">{{ $list->cards->count() }} kartu</div>
                </div>
                @if($list->cards->count() > 0)
                  <div class="dropdown">
                    <button class="btn btn-sm btn-light" data-toggle="dropdown">
                      <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                      <a class="dropdown-item" href="#" onclick="sortCards({{ $list->id }}, 'due_date')">
                        <i class="fas fa-sort-amount-down"></i> Urutkan Due Date
                      </a>
                    </div>
                  </div>
                @endif
              </div>
            </div>
            <div class="board-list" data-list-id="{{ $list->id }}">
              @foreach($list->cards as $card)
                @php
                  $cardGrade = $cardGradesMap[$card->id] ?? [];
                  $dosenScore = $cardGrade['evaluasi_dosen_summary']['avg'] ?? 0;
                  $mitraScore = $cardGrade['evaluasi_mitra_summary']['avg'] ?? 0;
                  $finalScore = $dosenScore * 0.8 + $mitraScore * 0.2;
                  $grade = $finalScore >= 85 ? 'A' : ($finalScore >= 75 ? 'B' : ($finalScore >= 65 ? 'C' : ($finalScore >= 55 ? 'D' : 'E')));
                @endphp
                <div class="card board-card hover-raise mb-2" data-card-id="{{ $card->id }}"
                     @if($finalScore > 0) class="board-card border-success" @endif>
                  <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                      <h6 class="card-title mb-0 card-title-full">{{ $card->title }}</h6>
                      @if($finalScore > 0)
                        <span class="badge badge-{{ $grade == 'A' ? 'success' : ($grade == 'B' ? 'info' : ($grade == 'C' ? 'warning' : 'danger')) }}">
                          {{ $grade }}
                        </span>
                      @endif
                    </div>

                    @if($card->description)
                    <p class="card-text small text-muted clamp-2 mb-2">{{ $card->description }}</p>
                    @endif

                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <div class="d-flex align-items-center">
                        @if($card->labels && count($card->labels) > 0)
                          @foreach($card->labels as $label)
                            <span class="badge badge-light badge-pill mr-1">{{ $label }}</span>
                          @endforeach
                        @endif
                      </div>
                      <small class="text-muted">#{{ $card->id }}</small>
                    </div>

                    @if($card->due_date)
                    <div class="mb-2">
                      <span class="date-chip {{ $card->due_date->isPast() ? 'date-chip-danger' : '' }}">
                        <i class="fas fa-calendar-alt"></i>
                        {{ $card->due_date->format('d M Y') }}
                      </span>
                    </div>
                    @endif

                    <div class="progress mb-2" style="height: 6px;">
                      <div class="progress-bar bg-{{ $card->progress == 100 ? 'success' : 'primary' }}"
                           style="width: {{ $card->progress }}%"></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                      <small class="text-muted">Progress: {{ $card->progress }}%</small>
                      @if($finalScore > 0)
                        <small class="text-muted">Nilai: {{ round($finalScore) }}</small>
                      @endif
                    </div>

                    <div class="mt-2 d-flex justify-content-between">
                      <button class="btn btn-xs btn-outline-primary" onclick="viewProject({{ $card->id }})">
                        <i class="fas fa-eye"></i>
                      </button>
                      <button class="btn btn-xs btn-outline-success" onclick="evaluateProject({{ $card->id }})">
                        <i class="fas fa-clipboard-check"></i>
                      </button>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
  @endif

  <!-- Aktivitas -->
  @if($aktivitasLists && $aktivitasLists->count() > 0)
  <div class="card-section">
    <div class="card-header">
      <h5 class="section-title mb-0">
        <i class="fas fa-tasks text-primary"></i>
        Aktivitas
        <small class="text-muted ml-2">({{ $aktivitas_total }} kegiatan)</small>
      </h5>
    </div>
    <div class="card-body p-3">
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Aktivitas</th>
              <th>Status</th>
              <th>Progress</th>
              <th>Due Date</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($aktivitasLists as $list)
              @foreach($list->cards as $card)
                <tr>
                  <td>
                    <strong>{{ $card->title }}</strong>
                    @if($card->description)
                      <br><small class="text-muted">{{ Str::limit($card->description, 80) }}</small>
                    @endif
                  </td>
                  <td>
                    <span class="badge badge-{{ $card->status == 'Selesai' ? 'success' : ($card->status == 'Proses' ? 'primary' : 'secondary') }}">
                      {{ $card->status }}
                    </span>
                  </td>
                  <td>
                    <div class="progress" style="height: 6px; width: 100px;">
                      <div class="progress-bar bg-{{ $card->progress == 100 ? 'success' : 'primary' }}"
                           style="width: {{ $card->progress }}%"></div>
                    </div>
                    <small>{{ $card->progress }}%</small>
                  </td>
                  <td>
                    @if($card->due_date)
                      {{ $card->due_date->format('d M Y') }}
                    @else
                      -
                    @endif
                  </td>
                  <td>
                    <button class="btn btn-sm btn-outline-info" onclick="evaluateActivity({{ $card->id }})">
                      <i class="fas fa-clipboard-check"></i> Evaluasi
                    </button>
                  </td>
                </tr>
              @endforeach
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
  @endif
</div>

<!-- Evaluation Modal -->
<div class="modal fade" id="evaluationModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Evaluasi Mahasiswa</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <div id="evaluationContent">
          <!-- Content will be loaded here -->
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
// Global variables
let currentSesiId = {{ $sesi->id }};

function openEvaluationModal(mahasiswaId, mahasiswaNama) {
  $('#evaluationModal .modal-title').text('Evaluasi: ' + mahasiswaNama);

  // Load evaluation form via AJAX
  $.get(`/evaluator/evaluasi/get-evaluation-form/${mahasiswaId}?sesi_id=${currentSesiId}`, function(data) {
    $('#evaluationContent').html(data);
    $('#evaluationModal').modal('show');
  });
}

function viewProject(cardId) {
  $.get(`/evaluator/evaluasi/project/${cardId}`, function(data) {
    if(data.success) {
      alert('Project Details:\n\nTitle: ' + data.project.title +
            '\nDescription: ' + (data.project.description || 'Tidak ada') +
            '\nProgress: ' + data.project.progress + '%' +
            '\nStatus: ' + data.project.status);
    }
  });
}

function evaluateProject(cardId) {
  window.location.href = `/evaluator/evaluasi/project/${cardId}/evaluate`;
}

function evaluateActivity(cardId) {
  window.location.href = `/evaluator/evaluasi/activity/${cardId}/evaluate`;
}

function sortCards(listId, sortBy) {
  // Implement sorting logic here
  console.log('Sorting cards in list', listId, 'by', sortBy);
}

// Initialize sortable if available
$(document).ready(function() {
  if(typeof Sortable !== 'undefined') {
    $('.board-list').each(function() {
      new Sortable(this, {
        group: 'project-cards',
        animation: 150,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        onEnd: function(evt) {
          const cardId = evt.item.dataset.cardId;
          const newListId = evt.to.dataset.listId;
          const newPosition = evt.newIndex;

          // Update via AJAX
          $.post('/evaluator/evaluasi/reorder-card', {
            card_id: cardId,
            to_list: newListId,
            position: newPosition,
            _token: '{{ csrf_token() }}'
          }).done(function(response) {
            if(response.status !== 'ok') {
              console.error('Failed to reorder card');
            }
          });
        }
      });
    });
  }
});
</script>
@endpush
@endsection