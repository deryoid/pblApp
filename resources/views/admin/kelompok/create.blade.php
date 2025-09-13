@extends('layout.app')

@section('content')
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <h1 class="h3 text-gray-800">Tambah Kelompok</h1>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('kelompok.store') }}" method="POST" id="kelompokForm">
                @csrf

                {{-- Periode --}}
                <div class="form-group">
                    <label for="periode_id">Periode <span class="text-danger">*</span></label>
                    <select
                        name="periode_id"
                        id="periode_id"
                        class="form-control @error('periode_id') is-invalid @enderror"
                        data-create-url="{{ route('kelompok.create') }}"
                        required
                    >
                        @foreach($periodes as $p)
                            <option value="{{ $p->id }}"
                                {{ (string)old('periode_id', $selectedPeriodeId ?? '') === (string)$p->id ? 'selected' : '' }}>
                                {{ $p->periode }}
                            </option>
                        @endforeach
                    </select>
                    @error('periode_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="form-text text-muted">
                        Daftar mahasiswa di bawah otomatis hanya menampilkan yang belum terdaftar pada periode terpilih.
                    </small>
                </div>

                {{-- Nama Kelompok: auto-generate (hidden) --}}
                <input type="hidden" name="nama_kelompok" value="">

                {{-- NIM Ketua (opsional) --}}
                <div class="form-group">
                    <label for="ketua_nim">NIM Ketua (opsional)</label>
                    <input type="text" name="ketua_nim" id="ketua_nim"
                           class="form-control @error('ketua_nim') is-invalid @enderror"
                           placeholder="Isi salah satu NIM dari daftar anggota"
                           value="{{ old('ketua_nim') }}">
                    @error('ketua_nim') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="form-text text-muted">Jika kosong, baris pertama akan dijadikan Ketua.</small>
                </div>

                <hr>

                <h6 class="font-weight-bold">Daftar Anggota</h6>
                <div id="entries">
                    @php $old = old('entries', [['nim'=>'','kelas_id'=>'']]); @endphp
                    @foreach($old as $i => $row)
                        <div class="form-row align-items-end entry-row mb-2">
                            <div class="col-md-5">
                                <label>Mahasiswa (NIM — Nama)</label>
                                <select name="entries[{{ $i }}][nim]" class="form-control select-nim @error('entries.'.$i.'.nim') is-invalid @enderror" required>
                                    <option value="">-- Pilih --</option>
                                    @forelse($mahasiswas as $m)
                                        <option value="{{ $m->nim }}" {{ ($row['nim']??'')==$m->nim?'selected':'' }}>
                                            {{ $m->nim }} — {{ $m->nama_mahasiswa }}
                                        </option>
                                    @empty
                                        {{-- Jika kosong, biarkan placeholder saja --}}
                                    @endforelse
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
                                <button type="button" class="btn btn-danger btn-sm remove-row" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="button" id="addRow" class="btn btn-info btn-sm mb-3">
                    <i class="fas fa-plus"></i> Tambah Anggota
                </button>

                @if($mahasiswas->isEmpty())
                    <div class="alert alert-warning py-2">
                        Tidak ada mahasiswa yang tersedia untuk periode ini (mungkin sudah tergabung semua).
                    </div>
                @endif

                <div class="d-flex align-items-center">
                    <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                    <a href="{{ route('kelompok.index') }}" class="btn btn-secondary btn-sm ml-2">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>



document.addEventListener('DOMContentLoaded', function(){
  const entries     = document.getElementById('entries');
  const addBtn      = document.getElementById('addRow');
  const ketuaInput  = document.getElementById('ketua_nim');
  const form        = document.getElementById('kelompokForm');
  const periodeSel  = document.getElementById('periode_id');

  // Reload halaman saat periode diganti agar daftar mahasiswa terfilter server-side
  if (periodeSel && periodeSel.dataset.createUrl) {
    periodeSel.addEventListener('change', function(){
      const base = this.dataset.createUrl;
      const url  = base + '?periode_id=' + encodeURIComponent(this.value);
      window.location.href = url;
    });
  }

  function reindex(){
    entries.querySelectorAll('.entry-row').forEach((row, idx) => {
      const selects = row.querySelectorAll('select');
      if (selects[0]) selects[0].name = `entries[${idx}][nim]`;
      if (selects[1]) selects[1].name = `entries[${idx}][kelas_id]`;
    });
  }

  // Sembunyikan/disable opsi NIM yang sudah dipilih di baris lain
  function refreshNimOptions(){
    const selects = Array.from(document.querySelectorAll('select.select-nim'));
    const chosen  = new Set(selects.map(s => s.value).filter(Boolean));
    selects.forEach(sel => {
      const myVal = sel.value;
      sel.querySelectorAll('option').forEach(opt => {
        if (!opt.value) return; // skip placeholder
        const shouldHide = chosen.has(opt.value) && opt.value !== myVal;
        opt.disabled = shouldHide;
        opt.hidden   = shouldHide; // sembunyikan juga di UI
      });
    });
  }

  function bindDuplicateGuard(sel){
    sel.addEventListener('change', function(){
      const v = this.value; if(!v) { refreshNimOptions(); return; }
      const all = [...document.querySelectorAll('select.select-nim')].map(s=>s.value).filter(Boolean);
      if (all.filter(x=>x===v).length > 1) {
        alert('NIM sudah dipilih di baris lain.');
        this.value='';
      }
      refreshNimOptions();
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

    // bind
    const nimSelect = row.querySelector('.select-nim');
    bindDuplicateGuard(nimSelect);
    row.querySelector('.remove-row').addEventListener('click', ()=>{
      row.remove(); reindex(); refreshNimOptions();
    });

    // setelah tambah baris, update opsi
    refreshNimOptions();
  }

  // validasi ketua (UX)
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

  // init awal
  entries.querySelectorAll('.remove-row').forEach(btn => btn.addEventListener('click', e => {
    e.currentTarget.closest('.entry-row').remove(); reindex(); refreshNimOptions();
  }));
  entries.querySelectorAll('select.select-nim').forEach(bindDuplicateGuard);
  addBtn.addEventListener('click', addRow);

  // panggil saat load pertama (agar langsung sembunyikan opsi terpilih dari old input)
  refreshNimOptions();
});

$(document).ready(function() {
  $('.select2').select2();
});
$(document).ready(function() {
  $('.select3').select2();
});

</script>
@endpush
@endsection
