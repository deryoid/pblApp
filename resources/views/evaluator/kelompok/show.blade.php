@extends('layout.app')

@section('content')
<div class="container-fluid">

  <div class="d-sm-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 text-gray-800">Detail Kelompok</h1>
    <div>
      <a href="{{ route('kelompok.edit', $kelompok->uuid) }}" class="btn btn-success btn-sm">Ubah</a>
      <a href="{{ route('kelompok.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
    </div>
  </div>

  <div class="card shadow mb-4">
    <div class="card-body">
      <dl class="row small">
        <dt class="col-sm-3">Periode</dt>
        <dd class="col-sm-9">{{ $kelompok->periode->periode ?? '-' }}</dd>

        <dt class="col-sm-3">Nama Kelompok</dt>
        <dd class="col-sm-9">{{ $kelompok->nama_kelompok }}</dd>

        <dt class="col-sm-3">Link Drive</dt>
        <dd class="col-sm-9">
          @if(!empty($kelompok->link_drive))
            <div class="d-inline-flex align-items-center">
              <a href="{{ $kelompok->link_drive }}" target="_blank" rel="noopener" class="btn btn-light btn-sm mr-2">
                <i class="fab fa-google-drive mr-1"></i> Buka
              </a>
              <button type="button" class="btn btn-light btn-sm btn-copy-link" data-link="{{ $kelompok->link_drive }}">
                <i class="fas fa-copy mr-1"></i> Salin
              </button>
            </div>
          @else
            <span class="text-muted">-</span>
          @endif
        </dd>
      </dl>

      <h6 class="font-weight-bold">Anggota</h6>
      <div class="table-responsive">
        <table class="table table-bordered small" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>No</th>
              <th>NIM</th>
              <th>Nama Mahasiswa</th>
              <th>Kelas (Pivot)</th>
              <th>Role</th>
            </tr>
          </thead>
          <tbody>
            @forelse($kelompok->mahasiswas as $m)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $m->nim }}</td>
              <td>{{ $m->nama_mahasiswa }}</td>
              <td>{{ $kelasMap[$m->pivot->kelas_id] ?? '-' }}</td>
              <td><span class="badge badge-{{ $m->pivot->role=='Ketua'?'primary':'secondary' }}">{{ $m->pivot->role }}</span></td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center text-muted">Belum ada anggota.</td></tr>
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
