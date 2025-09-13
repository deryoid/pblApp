@extends('layout.app')

@section('content')
<div class="container-fluid">

  <div class="d-sm-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 text-gray-800">Ubah Kelompok</h1>
  </div>

  <div class="card shadow">
    <div class="card-body">
      <form action="{{ route('kelompok.update', $kelompok->uuid) }}" method="POST" id="kelompokForm">
        @csrf @method('PUT')

        <div class="form-group">
          <label for="periode_id">Periode <span class="text-danger">*</span></label>
          <select name="periode_id" id="periode_id" class="form-control @error('periode_id') is-invalid @enderror" required>
            @foreach($periodes as $p)
              <option value="{{ $p->id }}" {{ old('periode_id', $kelompok->periode_id)==$p->id?'selected':'' }}>{{ $p->periode }}</option>
            @endforeach
          </select>
          @error('periode_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
          <label for="nama_kelompok">Nama Kelompok <span class="text-danger">*</span></label>
          <input type="text" name="nama_kelompok" id="nama_kelompok"
                 class="form-control @error('nama_kelompok') is-invalid @enderror"
                 value="{{ old('nama_kelompok', $kelompok->nama_kelompok) }}" required>
          @error('nama_kelompok') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
          <label for="ketua_nim">NIM Ketua (opsional)</label>
          <input type="text" name="ketua_nim" id="ketua_nim"
                 class="form-control @error('ketua_nim') is-invalid @enderror"
                 value="{{ old('ketua_nim', optional($kelompok->mahasiswas->firstWhere('pivot.role','Ketua'))->nim) }}">
          @error('ketua_nim') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <hr>

        <h6 class="font-weight-bold">Daftar Anggota</h6>
        <div id="entries">
          @php
            $old = old('entries');
            $rows = $old ?: $kelompok->mahasiswas->map(fn($m)=>['nim'=>$m->nim,'kelas_id'=>$m->pivot->kelas_id])->values()->all();
          @endphp
          @foreach($rows as $i => $row)
            <div class="form-row align-items-end entry-row mb-2">
              <div class="col-md-5">
                <label>Mahasiswa (NIM — Nama)</label>
                <select name="entries[{{ $i }}][nim]" class="form-control select-nim @error('entries.'.$i.'.nim') is-invalid @enderror" required>
                  <option value="">-- Pilih --</option>
                  @foreach($mahasiswas as $m)
                    <option value="{{ $m->nim }}" {{ ($row['nim']??'')==$m->nim?'selected':'' }}>
                      {{ $m->nim }} — {{ $m->nama_mahasiswa }}
                    </option>
                  @endforeach
                </select>
                @error('entries.'.$i.'.nim') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-5">
                <label>Kelas</label>
                <select name="entries[{{ $i }}][kelas_id]" class="form-control @error('entries.'.$i.'.kelas_id') is-invalid @enderror" required>
                  <option value="">-- Pilih Kelas --</option>
                  @foreach($kelasList as $k)
                    <option value="{{ $k->id }}" {{ ($row['kelas_id']??'')==$k->id?'selected':'' }}>{{ $k->kelas }}</option>
                  @endforeach
                </select>
                @error('entries.'.$i.'.kelas_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-2">
                <button type="button" class="btn btn-danger btn-sm remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
              </div>
            </div>
          @endforeach
        </div>

        <button type="button" id="addRow" class="btn btn-info btn-sm mb-3"><i class="fas fa-plus"></i> Tambah Anggota</button>

        <div class="d-flex align-items-center">
          <button type="submit" class="btn btn-primary btn-sm">Perbarui</button>
          <a href="{{ route('kelompok.index') }}" class="btn btn-secondary btn-sm ml-2">Kembali</a>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
  const entries = document.getElementById('entries');
  const addBtn  = document.getElementById('addRow');
  const ketuaInput = document.getElementById('ketua_nim');
  const form = document.getElementById('kelompokForm');

  function reindex(){
    entries.querySelectorAll('.entry-row').forEach((row, idx) => {
      const selects = row.querySelectorAll('select');
      selects[0].name = `entries[${idx}][nim]`;
      selects[1].name = `entries[${idx}][kelas_id]`;
    });
  }

  function addRow(){
    const row = document.createElement('div');
    row.className = 'form-row align-items-end entry-row mb-2';
    row.innerHTML = `
      <div class="col-md-5">
        <label>Mahasiswa (NIM — Nama)</label>
        <select class="form-control select-nim" required>
          <option value="">-- Pilih --</option>
          @foreach($mahasiswas as $m)
            <option value="{{ $m->nim }}">{{ $m->nim }} — {{ $m->nama_mahasiswa }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-5">
        <label>Kelas</label>
        <select class="form-control" required>
          <option value="">-- Pilih Kelas --</option>
          @foreach($kelasList as $k)
            <option value="{{ $k->id }}">{{ $k->kelas }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <button type="button" class="btn btn-danger btn-sm remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
      </div>`;
    entries.appendChild(row);
    reindex();
    row.querySelector('.remove-row').addEventListener('click', ()=>{ row.remove(); reindex(); });

    // cegah duplikasi nim
    const sel = row.querySelector('.select-nim');
    sel.addEventListener('change', function(){
      const v = this.value; if(!v) return;
      const all = [...document.querySelectorAll('select.select-nim')].map(s=>s.value).filter(Boolean);
      if (all.filter(x=>x===v).length > 1) { alert('NIM sudah dipilih di baris lain.'); this.value=''; }
    });
  }

  entries.querySelectorAll('.remove-row').forEach(btn => btn.addEventListener('click', e => { e.currentTarget.closest('.entry-row').remove(); reindex(); }));
  addBtn.addEventListener('click', addRow);

  if (form && ketuaInput) {
    form.addEventListener('submit', function(e){
      const ketua = ketuaInput.value.trim();
      if (!ketua) return;
      const all = [...document.querySelectorAll('select.select-nim')].map(s=>s.value).filter(Boolean);
      if (all.length && !all.includes(ketua)) {
        e.preventDefault();
        alert('NIM Ketua harus salah satu dari daftar anggota.');
        ketuaInput.focus();
      }
    });
  }
});
</script>
@endpush
@endsection
