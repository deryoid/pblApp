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
    </div>
  </div>

  @php
    function statusClass($s){
      return match($s) {
        'Belum dijadwalkan' => 'badge-secondary',
        'Terjadwal'         => 'badge-info',
        'Berlangsung'       => 'badge-warning',
        'Selesai'           => 'badge-success',
        'Dibatalkan'        => 'badge-danger',
        default             => 'badge-light',
      };
    }
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
              <th style="width:40px;"><input type="checkbox" id="chk-all"></th>
              <th>Kelompok</th>
              <th>Anggota</th>
              <th>Jadwal</th>
              <th>Lokasi</th>
              <th>Evaluator</th>
              <th>Status</th>
              <th>Mingguan (AP)</th>
              <th style="width:220px; text-align: center;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($kelompoks as $k)
              @php
                $sesi          = $sesiMap[$k->id] ?? null;
                $jadwal        = $sesi?->jadwal_mulai;
                $evaluatorNama = $sesi?->evaluator?->name;
                $status        = $sesi?->status ?? 'Belum dijadwalkan';
                $searchStr     = strtolower(
                                  trim(
                                    (string)($k->nama_kelompok ?? '').' '.
                                    (string)($evaluatorNama ?? '').' '.
                                    (string)($k->ketua_nama ?? '')
                                  )
                                );
              @endphp
              <tr data-search="{{ $searchStr }}">
                <td><input type="checkbox" class="chk-kelompok" value="{{ $k->id }}"></td>
                <td>
                  <div class="font-weight-bold">{{ $k->nama_kelompok }}</div>
                  <div class="small-muted truncate">Ketua: {{ $k->ketua_nama ?? '-' }}</div>
                </td>
                <td align="center">
                  {{-- Jumlah anggota --}}
                  <span class="badge badge-primary">{{ (int)($k->mahasiswas_count ?? 0) }}</span>
                </td>
                <td>
                  @if($jadwal)
                    {{ \Carbon\Carbon::parse($jadwal)->locale('id')->translatedFormat('d M Y, H:i') }}
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td>{{ $sesi?->lokasi ?? '-' }}</td>
                <td>{{ $evaluatorNama ?? '-' }}</td>
                <td>
                  <span class="badge status-badge {{ statusClass($status) }}">{{ $status }}</span>
                </td>
                <td>
                  @php $wk = $weeklyMap[$k->id] ?? ['eval_count'=>0,'avg_kehadiran'=>0,'avg_keaktifan'=>0]; @endphp
                  <div class="small-muted">
                    Sesi: <strong>{{ $wk['eval_count'] }}</strong><br>
                    Kehadiran: <strong>{{ $wk['avg_kehadiran'] }}%</strong><br>
                    Keaktifan: <strong>{{ $wk['avg_keaktifan'] }}%</strong>
                  </div>
                </td>
                <td align="center">
                  {{-- Detail --}}
                  <a href="{{ route('admin.evaluasi.kelompok.show', $k->uuid) }}"
                     class="btn  btn-circle btn-primary btn-sm" title="Detail"><i class="fas fa-user-check"></i>
                  </a>
                  <div class="btn-group btn-group-sm" role="group">

                    {{-- Mulai (hanya jika ada sesi & belum/terjadwal) --}}
                    <form action="{{ $sesi ? route('admin.evaluasi.start', $sesi->id) : '#' }}"
                          method="POST" class="ml-1 frm-start">
                      @csrf @method('PATCH')
                      <button type="submit" class="btn btn-circle btn-success btn-sm"
                        {{ !$sesi || in_array($status,['Berlangsung','Selesai','Dibatalkan']) ? 'disabled' : '' }}>
                        <i class="fas fa-check-circle"></i>
                      </button>
                    </form>

                    {{-- Selesai (hanya jika sedang berlangsung) --}}
                    <form action="{{ $sesi ? route('admin.evaluasi.finish', $sesi->id) : '#' }}"
                          method="POST" class="ml-1 frm-finish">
                      @csrf @method('PATCH')
                      <button type="submit" class="btn btn-circle btn-dark btn-sm"
                        {{ !$sesi || $status!=='Berlangsung' ? 'disabled' : '' }}>
                        <i class="fas fa-times-circle"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center text-muted">Belum ada data.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-3">
          {{ $kelompoks->links() }}
          <button id="btn-bulk-schedule" class="btn btn-sm btn-primary ml-2">Jadwalkan Terpilih</button>
      </div>
    </div>
  </div>
</div>

  <!-- Bulk schedule modal -->
  <div class="modal fade" id="bulkScheduleModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Jadwalkan Terpilih</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          @include('admin.evaluasi.sesi-schedule-bulk', ['selected_ids' => []])
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
      columnDefs: [ { orderable: false, targets: 0 }, { orderable: false, targets: -1 } ]
    });
  } catch (e) {
    // DataTables not loaded; fallback to no client-side plugin
  }

  // Select all checkbox
  const chkAll = document.getElementById('chk-all');
  if (chkAll) {
    chkAll.addEventListener('change', function(){
      const checked = this.checked;
      document.querySelectorAll('.chk-kelompok').forEach(cb => cb.checked = checked);
    });
  }

  // SweetAlert confirm for start/finish
  $(document).on('submit','.frm-start', function(e){
    e.preventDefault(); const form = this;
    Swal.fire({title:'Mulai sesi evaluasi?', icon:'question', showCancelButton:true, confirmButtonText:'Mulai'})
      .then(r=>{ if(r.isConfirmed) form.submit(); });
  });
  $(document).on('submit','.frm-finish', function(e){
    e.preventDefault(); const form = this;
    Swal.fire({title:'Akhiri sesi evaluasi?', icon:'warning', showCancelButton:true, confirmButtonText:'Akhiri'})
      .then(r=>{ if(r.isConfirmed) form.submit(); });
  });

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
      // Show modal
      $('#bulkScheduleModal').modal('show');
    });
  }
})();
</script>
@endpush
@endsection
