@extends('layout.app')

@section('content')
<div class="container-fluid">
  <h1 class="h4 mb-3">Atur Jadwal Evaluasi</h1>

  <div class="card">
    <div class="card-body">
      <div class="mb-3 small text-muted">
        <div><strong>Kelompok:</strong> {{ $kelompok->nama_kelompok }}</div>
        <div><strong>Periode:</strong> {{ $periode->periode }}</div>
      </div>

      <form method="POST" action="{{ route('admin.evaluasi.schedule.save', $sesi->id) }}">
        @csrf @method('PATCH')

        <div class="form-group">
          <label>Tanggal & Waktu Mulai</label>
          <input type="datetime-local" name="jadwal_mulai" class="form-control @error('jadwal_mulai') is-invalid @enderror"
                 value="{{ old('jadwal_mulai', optional($sesi->jadwal_mulai)->format('Y-m-d\TH:i')) }}" required>
          @error('jadwal_mulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
          <label>Tanggal & Waktu Selesai <small class="text-muted">(opsional)</small></label>
          <input type="datetime-local" name="jadwal_selesai" class="form-control @error('jadwal_selesai') is-invalid @enderror"
                 value="{{ old('jadwal_selesai', optional($sesi->jadwal_selesai)->format('Y-m-d\TH:i')) }}">
          @error('jadwal_selesai') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
          <label>Lokasi <small class="text-muted">(opsional)</small></label>
          <input type="text" name="lokasi" class="form-control @error('lokasi') is-invalid @enderror"
                 value="{{ old('lokasi', $sesi->lokasi) }}" maxlength="150">
          @error('lokasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
          <label>Evaluator <small class="text-muted">(opsional)</small></label>
          <select name="evaluator_id" class="form-control @error('evaluator_id') is-invalid @enderror">
            <option value="">— Pilih evaluator —</option>
            @foreach ($evaluators as $u)
              <option value="{{ $u->id }}" {{ (int)old('evaluator_id', $sesi->evaluator_id) === (int)$u->id ? 'selected' : '' }}>
                {{ $u->nama_user ?? $u->name ?? $u->username }}
              </option>
            @endforeach
          </select>
          @error('evaluator_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="d-flex">
          <a href="{{ route('admin.evaluasi.index', ['periode_id' => $periode->id]) }}" class="btn btn-light mr-2">Kembali</a>
          <button class="btn btn-primary">Simpan Jadwal</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
