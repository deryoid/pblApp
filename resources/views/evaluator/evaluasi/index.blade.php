@extends('layout.app')
@section('content')
@push('styles')
<style>
  .badge-soft { background:#eef2ff; color:#4e73df; }
  .status-badge { font-weight:600; }
  .table thead th { white-space: nowrap; }
  .table td { vertical-align: middle; }
  .small-muted { font-size:.85rem; color:#6c757d; }
  .truncate { max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .activity-box {
    display: inline-block;
    width: 12px;
    height: 12px;
    margin: 0 1px;
    border-radius: 2px;
  }
  .activity-box.evaluated {
    background-color: #28a745;
  }
  .activity-box.unevaluated {
    background-color: #343a40;
  }
</style>
@endpush

<div class="container-fluid">
  {{-- Header + Filter --}}
  <div class="d-flex align-items-center mb-3 flex-wrap">
    <div class="d-flex align-items-baseline mr-3 mb-2 mb-md-0">
      <h1 class="h3 text-gray-800 mb-0">Evaluasi</h1>
      <span class="ml-3 small text-muted">
        <strong>Periode:</strong>
        <span class="badge badge-light border">
          {{ optional($periodeAktif)->periode ?? '—' }}
        </span>
      </span>
    </div>

    <div class="ml-auto d-flex align-items-center">
      <a href="{{ route('evaluator.evaluasi.nilai-final', ['periode_id' => $periodeId]) }}" class="btn btn-sm btn-primary">
        <i class="fas fa-chart-bar"></i> Nilai Final
      </a>
      <a href="{{ route('evaluator.evaluasi.settings') }}" class="btn btn-sm btn-secondary ml-2">
        <i class="fas fa-cog"></i> Pengaturan
      </a>
    </div>
  </div>

  {{-- Filter --}}
  <div class="row mb-3">
    <div class="col-md-4">
      <form method="GET" action="{{ route('evaluator.evaluasi.index') }}">
        <div class="input-group">
          <input type="text" name="q" class="form-control" placeholder="Cari nama kelompok atau ketua..." value="{{ request('q') }}">
          <select name="periode_id" class="form-control" style="max-width:150px;">
            <option value="">Semua Periode</option>
            @foreach($periodes as $p)
              <option value="{{ $p->id }}" {{ $periodeId == $p->id ? 'selected' : '' }}>{{ $p->periode }}</option>
            @endforeach
          </select>
          <button class="btn btn-outline-secondary" type="submit">
            <i class="fas fa-search"></i>
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <div class="d-flex align-items-center mb-2">
        <div class="small-muted">
          Menampilkan {{ $kelompoks->firstItem() ?? 0 }}–{{ $kelompoks->lastItem() ?? 0 }} dari {{ $kelompoks->total() }} kelompok
          @if(request('q')) • kata kunci: <strong>{{ request('q') }}</strong> @endif
        </div>
        <div class="ml-auto">
          <span class="badge badge-soft">Total Kelompok: {{ $kelompoks->total() }}</span>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-sm table-hover" id="tbl-evaluasi">
          <thead class="thead-light">
            <tr>
              <th>Kelompok</th>
              <th>Anggota</th>
              <th>Evaluasi</th>
              <th>Kehadiran</th>
              <th>Keaktifan</th>
              <th style="width:80px; text-align: center;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($kelompoks as $k)
              @php
                $searchStr     = strtolower(
                                  trim(
                                    (string)($k->nama_kelompok ?? '').' '.
                                    (string)($k->mahasiswas->firstWhere('pivot.role', 'ketua')?->nama_mahasiswa ?? '')
                                  )
                                );
                $weeklyData = $weeklyMap[$k->id] ?? [];
              @endphp
              <tr data-search="{{ $searchStr }}">
                <td>
                  <div class="font-weight-bold">{{ $k->nama_kelompok }}</div>
                  <div class="small-muted truncate">Ketua: {{ $k->mahasiswas->firstWhere('pivot.role', 'ketua')?->nama_mahasiswa ?? '-' }}</div>
                </td>
                <td align="center">
                  {{-- Jumlah anggota --}}
                      @if($k->mahasiswas && $k->mahasiswas->count())
                        <div class="text-left">
                          @foreach($k->mahasiswas as $m)
                            <span class="d-block small">{{ $loop->iteration }}. {{ $m->nama_mahasiswa }}</span>
                          @endforeach
                        </div>
                      @else
                        <span class="text-muted small">—</span>
                      @endif
                </td>
                <td>
                  <div class="d-flex align-items-center">
                    <span class="badge mr-2">
                      Evaluasi :
                    </span>
                    <div class="activity-boxes">
                      {{-- Menampilkan aktivitas boxes --}}
                      @php
                      $evalCount = $weeklyData['eval_count'] ?? 0;
                      $avgKehadiran = $weeklyData['avg_kehadiran'] ?? 0;
                      $avgKeaktifan = $weeklyData['avg_keaktifan'] ?? 0;
                      @endphp
                      @for($i = 1; $i <= max($evalCount, 1); $i++)
                        <div class="activity-box {{ $i <= $evalCount ? 'evaluated' : 'unevaluated' }}"></div>
                      @endfor
                    </div>
                    <span class="small-muted ml-2">
                      {{ $evalCount }} sesi
                    </span>
                  </div>
                </td>
                <td>
                  <div class="text-center">
                    <div class="progress" style="height: 8px;">
                      <div class="progress-bar bg-{{ $avgKehadiran >= 75 ? 'success' : ($avgKehadiran >= 50 ? 'warning' : 'danger') }}"
                           style="width: {{ $avgKehadiran }}%"
                           title="{{ $avgKehadiran }}%">
                      </div>
                    </div>
                    <small class="text-muted">{{ $avgKehadiran }}%</small>
                  </div>
                </td>
                <td>
                  <div class="text-center">
                    <div class="progress" style="height: 8px;">
                      <div class="progress-bar bg-{{ $avgKeaktifan >= 75 ? 'success' : ($avgKeaktifan >= 50 ? 'warning' : 'danger') }}"
                           style="width: {{ $avgKeaktifan }}%"
                           title="{{ $avgKeaktifan }}%">
                      </div>
                    </div>
                    <small class="text-muted">{{ $avgKeaktifan }}%</small>
                  </div>
                </td>
                <td align="center">
                  {{-- Detail --}}
                  <a href="{{ route('evaluator.evaluasi.showKelompok', $k->uuid) }}"
                     class="btn btn-circle btn-primary btn-sm" title="Detail"><i class="fas fa-user-check"></i>
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center text-muted">Belum ada data.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Pagination --}}
      <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted">
          {{ $kelompoks->links() }}
        </div>
      </div>

    </div>
  </div>
</div>


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function(){
  // Pencarian client-side tambahan (opsional)
  const tbl = document.getElementById('tbl-evaluasi');
  if (!tbl) return;
  // Initialize DataTables if available
  let dataTable = null;
  try {
    dataTable = $(tbl).DataTable({
      paging: true,
      info: true,
      searching: true,
      ordering: true,
      lengthChange: false,
      pageLength: 20,
      columnDefs: [ { orderable: false, targets: -1 } ]
    });
  } catch (e) {
    // DataTables not loaded; fallback to no client-side plugin
  }
})();
</script>
@endpush
@endsection