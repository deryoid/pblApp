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
          {{ optional($periodeAktif)->periode ?? '-' }}
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

  @php
  @endphp

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
              <th class="text-center" style="width: 45px;">No</th>
              <th>Kelompok</th>
              <th>Anggota Tim</th>
              <th>Progress Evaluasi</th>
              <th style="width:80px; text-align: center;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($kelompoks as $k)
              @php
                $searchStr     = strtolower(
                                  trim(
                                    (string)($k->nama_kelompok ?? '').' '.
                                    (string)($k->ketua_nama ?? '')
                                  )
                                );
              @endphp
              <tr data-search="{{ $searchStr }}">
                <td class="text-center align-middle font-weight-bold text-muted">
                  {{ $kelompoks->firstItem() + $loop->index }}
                </td>
                <td class="align-middle">
                  <div class="font-weight-bold text-dark">{{ $k->nama_kelompok }}</div>
                  <div class="small-muted">Ketua: <span class="font-weight-500 text-dark">{{ $k->ketua_nama ?? '-' }}</span></div>
                </td>
                <td class="align-middle">
                  @if($k->mahasiswas && $k->mahasiswas->count())
                    <ul class="list-unstyled mb-0 small" style="line-height: 1.45;">
                      @foreach($k->mahasiswas as $m)
                        <li class="py-0.5">
                          <span class="badge badge-light border text-nowrap mr-1">{{ $m->nim }}</span>
                          <span class="font-weight-500">{{ $m->nama }}</span>
                        </li>
                      @endforeach
                    </ul>
                  @else
                    <span class="text-muted small"><em>Belum ada anggota</em></span>
                  @endif
                </td>
                <td class="align-middle">
                  <div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
                    <div class="activity-boxes">
                      {!! $k->activity_boxes !!}
                    </div>
                    <span class="badge badge-light border text-dark font-weight-bold ml-1">
                      {{ $k->evaluated_activities_count }}/{{ $k->total_activities }} Sesi
                    </span>
                  </div>
                </td>
                <td class="text-center align-middle text-nowrap">
                  <div class="table-action-group">
                    <a href="{{ route('evaluator.evaluasi.showKelompok', $k->uuid) }}"
                       class="btn btn-sm btn-outline-primary btn-action-icon" title="Detail Evaluasi"><i class="fas fa-user-check fa-sm"></i>
                    </a>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center text-muted py-4">Belum ada data evaluasi.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
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
      lengthChange: true,
      pageLength: 100, // Increased to match server-side pagination
      lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
      columnDefs: [ { orderable: false, targets: -1 } ],
      language: {
        "lengthMenu": "Tampilkan _MENU_ data per halaman",
        "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
        "search": "Cari:",
        "paginate": {
          "first": "Pertama",
          "last": "Terakhir",
          "next": "Selanjutnya",
          "previous": "Sebelumnya"
        }
      }
    });
  } catch (e) {
    // DataTables not loaded; fallback to no client-side plugin
  }



  // Bulk schedule button
  const btnBulk = document.getElementById('btn-bulk-schedule');
  if (btnBulk) {
    btnBulk.addEventListener('click', function(){
      const selected = Array.from(document.querySelectorAll('.chk-kelompok:checked')).map(n => n.value);
      if (selected.length === 0) { Swal.fire('Info','Pilih minimal satu kelompok','info'); return; }
      // Populate hidden inputs in modal form
      const container = document.getElementById('bulk-selected-inputs');
      container.innerHTML = '';
      selected.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'selected_ids[]';
        input.value = id;
        container.appendChild(input);
      });
    });
  }
})();
</script>
@endpush
@endsection
