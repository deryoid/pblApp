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
      {{-- Simple action: buka daftar sesi atau buat jadwal --}}
      @if (\Illuminate\Support\Facades\Route::has('admin.evaluasi.sesi.index'))
        <a href="{{ route('admin.evaluasi.sesi.index', ['periode_id' => $periodeId]) }}" class="btn btn-sm btn-secondary">Lihat Sesi</a>
      @endif
      <a href="{{ route('admin.evaluasi.nilai-final', ['periode_id' => $periodeId]) }}" class="btn btn-sm btn-primary">
        <i class="fas fa-chart-bar"></i> Nilai Final
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
              <th>Kelompok</th>
              <th>Anggota</th>
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
                <td>
                  <div class="font-weight-bold">{{ $k->nama_kelompok }}</div>
                  <div class="small-muted truncate">Ketua: {{ $k->ketua_nama ?? '-' }}</div>
                </td>
                <td align="center">
                  {{-- Jumlah anggota --}}
                  <span class="badge badge-primary">{{ (int)($k->mahasiswas_count ?? 0) }}</span>
                </td>
                <td align="center">
                  {{-- Detail --}}
                  <a href="{{ route('admin.evaluasi.kelompok.show', $k->uuid) }}"
                     class="btn  btn-circle btn-primary btn-sm" title="Detail"><i class="fas fa-user-check"></i>
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="3" class="text-center text-muted">Belum ada data.</td>
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
      lengthChange: false,
      pageLength: 20,
      columnDefs: [ { orderable: false, targets: -1 } ]
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
