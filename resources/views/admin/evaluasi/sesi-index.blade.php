@extends('layout.app')
@section('content')
<div class="container-fluid">
  <div class="d-flex mb-3">
    <h1 class="h4 mb-0">Sesi Evaluasi</h1>
    <div class="ml-auto d-flex">
      <form class="form-inline" method="GET">
  <select name="periode_id" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
          @foreach($periodes as $p)
          <option value="{{ $p->id }}" {{ (int)$periodeId===(int)$p->id?'selected':'' }}>
            {{ $p->periode }} {{ $p->status_periode==='Aktif'?'(Aktif)':'' }}
          </option>
          @endforeach
        </select>
        <input name="q" value="{{ $q }}" class="form-control form-control-sm mr-2" placeholder="Cari kelompok/evaluator">
        <button class="btn btn-sm btn-primary">Terapkan</button>
      </form>
      @if (\Illuminate\Support\Facades\Route::has('admin.evaluasi.schedule.bulk'))
        <a class="btn btn-sm btn-success ml-2" href="{{ route('admin.evaluasi.schedule.bulk',['periode_id'=>$periodeId]) }}">Jadwalkan Massal</a>
      @else
        <a class="btn btn-sm btn-success ml-2 disabled" href="#" title="Route tidak tersedia">Jadwalkan Massal</a>
      @endif
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-sm table-hover mb-0">
        <thead class="thead-light">
          <tr>
            <th>Kelompok</th>
            <th>Evaluator</th>
            <th>Mulai</th>
            <th>Selesai</th>
            <th>Status</th>
            <th style="width:260px">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($sesi as $s)
            <tr>
              <td>{{ $s->kelompok->nama_kelompok }}</td>
              <td>{{ $s->evaluator->name ?? '—' }}</td>
              <td>{{ optional($s->jadwal_mulai)->format('d M Y H:i') ?? '—' }}</td>
              <td>{{ optional($s->jadwal_selesai)->format('d M Y H:i') ?? '—' }}</td>
              <td><span class="badge badge-{{ [
                  'Belum dijadwalkan'=>'secondary',
                  'Terjadwal'=>'info',
                  'Berlangsung'=>'warning',
                  'Selesai'=>'success',
                  'Dibatalkan'=>'danger'
                ][$s->status] ?? 'light' }}">{{ $s->status }}</span></td>
              <td class="d-flex">
                <a class="btn btn-outline-primary btn-sm mr-1" href="{{ route('admin.evaluasi.schedule.form', $s->kelompok->uuid) }}">Jadwalkan</a>
                <form method="POST" action="{{ route('admin.evaluasi.start',$s->id) }}" class="mr-1">@csrf @method('PATCH')
                  <button class="btn btn-outline-success btn-sm" {{ in_array($s->status,['Berlangsung','Selesai','Dibatalkan'])?'disabled':'' }}>Mulai</button>
                </form>
                <form method="POST" action="{{ route('admin.evaluasi.finish',$s->id) }}">@csrf @method('PATCH')
                  <button class="btn btn-outline-dark btn-sm" {{ $s->status!=='Berlangsung'?'disabled':'' }}>Selesai</button>
                </form>
                <a class="btn btn-outline-secondary btn-sm ml-1" href="{{ route('admin.evaluasi.kelompok.show',$s->kelompok->uuid) }}">Detail</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted">Tidak ada sesi.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-body py-2">
      {{ $sesi->links() }}
    </div>
  </div>
</div>
@endsection
