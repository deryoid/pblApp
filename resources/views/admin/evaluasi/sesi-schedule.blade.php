@extends('layout.app')
@section('content')
<div class="container-fluid">
  <h1 class="h4 mb-3">Jadwalkan Sesi</h1>

  <form method="POST" action="{{ route('admin.evaluasi.schedule.save',$sesi->id) }}">
    @csrf @method('PATCH')
    <div class="form-row">
      <div class="form-group col-md-4">
        <label>Evaluator</label>
        <select name="evaluator_id" class="form-control">
          <option value="">— Pilih —</option>
          @foreach($evaluators as $u)
            <option value="{{ $u->id }}" {{ $sesi->evaluator_id==$u->id?'selected':'' }}>{{ $u->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-md-4">
        <label>Tanggal</label>
        <input type="date" name="mulai_tanggal" class="form-control"
               value="{{ optional($sesi->jadwal_mulai)->format('Y-m-d') }}">
      </div>
      <div class="form-group col-md-2">
        <label>Jam Mulai</label>
        <input type="time" name="mulai_jam" class="form-control"
               value="{{ optional($sesi->jadwal_mulai)->format('H:i') }}">
      </div>
      <div class="form-group col-md-2">
        <label>Jam Selesai</label>
        <input type="time" name="selesai_jam" class="form-control"
               value="{{ optional($sesi->jadwal_selesai)->format('H:i') }}">
      </div>
    </div>

    <div class="form-group">
      <label>Lokasi</label>
      <input type="text" name="lokasi" class="form-control" value="{{ $sesi->lokasi }}">
    </div>

    <div class="d-flex">
      <button class="btn btn-primary">Simpan Jadwal</button>
  <a href="{{ route('admin.evaluasi.index',['periode_id'=>$sesi->periode_id]) }}" class="btn btn-light ml-2">Kembali</a>
    </div>
  </form>
</div>
@endsection
