@extends('layout.app')
@section('content')
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Kelompok PBL</h1>
            <p class="text-secondary small mb-0 font-weight-500">Daftar kelompok mahasiswa PBL yang aktif pada periode evaluasi.</p>
        </div>
        @isset($activePeriode)
            <div class="badge badge-info px-3 py-2">
                <i class="fas fa-calendar-check mr-1"></i> Periode Aktif: {{ $activePeriode->periode }}
            </div>
        @endisset
    </div>

    <div class="card shadow-sm border border-gray-200 mb-4">
        <div class="card-header py-3 bg-white border-bottom d-flex justify-content-between align-items-center flex-wrap" style="gap: 0.75rem;">
            <div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
                <form class="form-inline" method="GET" action="{{ route('evaluator.kelompok.index') }}">
                    <label class="mr-2 small font-weight-bold text-muted">Periode:</label>
                    <select name="periode_id" class="form-control form-control-sm mr-2" onchange="this.form.submit()" style="border-radius: 8px;">
                        <option value="">Semua Periode</option>
                        @isset($periodes)
                            @foreach($periodes as $p)
                                @php
                                    $selectedId = $qPeriode ?? ($activePeriode->id ?? null);
                                @endphp
                                <option value="{{ $p->id }}"
                                    {{ (string)$selectedId === (string)$p->id ? 'selected' : '' }}>
                                    {{ $p->periode }}
                                </option>
                            @endforeach
                        @endisset
                    </select>
                </form>
            </div>

            <div class="badge badge-light border px-3 py-2 text-dark font-weight-600">
                <i class="fas fa-users mr-1 text-primary"></i> Total: {{ $kelompoks->count() }} Kelompok
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover small" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="text-center no-sort" style="width: 45px;">No</th>
                            <th style="width: 140px;">Periode</th>
                            <th>Nama Kelompok</th>
                            <th class="text-center no-sort" style="width: 90px;">Drive</th>
                            <th>Daftar Anggota Tim</th>
                            <th class="text-center" style="width: 70px;">Jumlah</th>
                            <th class="text-center align-middle no-sort" style="width: 60px;">
                                <i class="fas fa-cogs fa-sm" aria-hidden="true"></i>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kelompoks as $k)
                        <tr>
                            <td class="text-center align-middle">{{ $loop->iteration }}</td>
                            <td class="align-middle text-nowrap font-weight-500">{{ $k->periode->periode ?? '-' }}</td>
                            <td class="align-middle font-weight-bold text-dark">{{ $k->nama_kelompok }}</td>
                            <td class="text-center align-middle">
                                @if(!empty($k->link_drive))
                                  <div class="d-inline-flex align-items-center" style="gap: 0.25rem;">
                                    <a href="{{ $k->link_drive }}" target="_blank" rel="noopener" class="btn btn-xs btn-outline-primary btn-action-icon" title="Buka Google Drive">
                                      <i class="fab fa-google-drive"></i>
                                    </a>
                                    <button type="button" class="btn btn-xs btn-outline-secondary btn-action-icon btn-copy-link" data-link="{{ $k->link_drive }}" title="Salin Link Drive">
                                      <i class="fas fa-copy"></i>
                                    </button>
                                  </div>
                                @else
                                  <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="align-middle">
                                @if($k->mahasiswas && $k->mahasiswas->count() > 0)
                                    <ul class="list-unstyled mb-0 small" style="line-height: 1.5;">
                                        @foreach($k->mahasiswas as $mhs)
                                            <li class="py-0.5">
                                                <span class="badge badge-light border text-nowrap mr-1">{{ $mhs->nim }}</span>
                                                <span class="font-weight-500">{{ $mhs->nama_mahasiswa }}</span>
                                                @if($mhs->pivot->role === 'Ketua')
                                                    <span class="badge badge-primary ml-1">Ketua</span>
                                                @endif
                                                @if($mhs->kelas)
                                                    <small class="text-muted ml-1">({{ $mhs->kelas->kelas }})</small>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-muted small"><em>Belum ada anggota</em></span>
                                @endif
                            </td>
                            <td class="text-center align-middle font-weight-bold">
                                <span class="badge badge-light border text-dark font-weight-bold px-2 py-1">
                                    {{ $k->mahasiswas_count ?? $k->mahasiswas->count() }} Org
                                </span>
                            </td>
                            <td class="text-center align-middle text-nowrap">
                                <div class="table-action-group">
                                    <a href="{{ route('evaluator.kelompok.show', $k->uuid) }}" class="btn btn-sm btn-outline-info btn-action-icon" title="Detail Kelompok">
                                        <i class="fas fa-eye fa-sm"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data kelompok.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.btn-copy-link').forEach(function(btn){
      btn.addEventListener('click', function(){
        const link = this.getAttribute('data-link') || '';
        if (!link) return;
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(link).then(()=>{
            Swal.fire({toast:true, position:'top', icon:'success', title:'Link Drive disalin', showConfirmButton:false, timer:1500});
          });
        } else {
          const ta = document.createElement('textarea');
          ta.value = link; document.body.appendChild(ta); ta.select();
          try { document.execCommand('copy'); Swal.fire({toast:true, position:'top', icon:'success', title:'Link Drive disalin', showConfirmButton:false, timer:1500}); } finally { document.body.removeChild(ta); }
        }
      });
    });
  });
</script>
@endpush
@endsection
