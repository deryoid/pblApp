@extends('layout.app')
@section('content')
<div class="container-fluid">

    <h1 class="h3 mb-2 text-gray-800">Kelompok</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <form class="form-inline" method="GET" action="{{ route('kelompok.index') }}">
                    <select name="periode_id" class="form-control form-control-sm mr-2">
                        <option value="">Semua Periode</option>
                        @isset($periodes)
                            @foreach($periodes as $p)
                                @php
                                    // Default ke periode aktif jika $qPeriode kosong
                                    $selectedId = $qPeriode ?? ($activePeriode->id ?? null);
                                @endphp
                                <option value="{{ $p->id }}"
                                    {{ (string)$selectedId === (string)$p->id ? 'selected' : '' }}>
                                    {{ $p->periode }}
                                </option>
                            @endforeach
                        @endisset
                    </select>
                    <button class="btn btn-secondary btn-sm mr-2" type="submit"><i class="fas fa-search"></i></button>

                </form>

                @isset($activePeriode)
                    <span class="badge badge-info ml-3">
                        Periode Aktif: {{ $activePeriode->periode }}
                    </span>
                @endisset
            </div>

            <a href="{{ route('kelompok.create') }}" class="btn btn-primary btn-icon-split btn-sm">
                <span class="icon text-primary-50"><i class="fas fa-plus"></i></span>
                <span class="text">Tambah</span>
            </a>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered small" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Periode</th>
                            <th>Nama Kelompok</th>
                            <th class="text-center">Drive</th>
                            <th>Jumlah Anggota</th>
                            <th class="text-center align-middle"><i class="fas fa-cogs fa-sm"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $k)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $k->periode->periode ?? '-' }}</td>
                            <td>{{ $k->nama_kelompok }}</td>
                            <td class="text-center align-middle">
                                @if(!empty($k->link_drive))
                                  <div class="d-inline-flex align-items-center">
                                    <a href="{{ $k->link_drive }}" target="_blank" rel="noopener" class="btn btn-light btn-sm mr-1" title="Buka Drive">
                                      <i class="fab fa-google-drive"></i>
                                    </a>
                                    <button type="button" class="btn btn-light btn-sm btn-copy-link" data-link="{{ $k->link_drive }}" title="Salin Link">
                                      <i class="fas fa-copy"></i>
                                    </button>
                                  </div>
                                @else
                                  <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $k->mahasiswas_count ?? $k->mahasiswas->count() }}</td>
                            <td class="text-center align-middle">
                                <a href="{{ route('kelompok.show', $k->uuid) }}" class="btn btn-info btn-circle btn-sm" title="Detail">
                                    <i class="fas fa-eye fa-sm"></i>
                                </a>
                                <a href="{{ route('kelompok.edit', $k->uuid) }}" class="btn btn-success btn-circle btn-sm" title="Ubah">
                                    <i class="fas fa-edit fa-sm"></i>
                                </a>
                                <form action="{{ route('kelompok.destroy', $k->uuid) }}" method="POST" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-danger btn-circle btn-sm" title="Hapus"
                                        onclick="var f=this.closest('form'); Swal.fire({
                                            title:'Hapus?',
                                            text:'Apakah Anda yakin ingin menghapus data ini?',
                                            icon:'warning', showCancelButton:true,
                                            confirmButtonText:'Ya, hapus!', cancelButtonText:'Batal'
                                        }).then((r)=>{ if(r.isConfirmed) f.submit(); });">
                                        <i class="fas fa-trash fa-sm"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted">Belum ada data.</td></tr>
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
            Swal.fire({toast:true, position:'top', icon:'success', title:'Link disalin', showConfirmButton:false, timer:1200});
          });
        } else {
          const ta = document.createElement('textarea');
          ta.value = link; document.body.appendChild(ta); ta.select();
          try { document.execCommand('copy'); Swal.fire({toast:true, position:'top', icon:'success', title:'Link disalin', showConfirmButton:false, timer:1200}); } finally { document.body.removeChild(ta); }
        }
      });
    });
  });
  </script>
@endpush
@endsection
