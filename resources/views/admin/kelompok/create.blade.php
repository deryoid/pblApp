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

                {{-- Nama Kelompok (opsional) --}}
                <div class="form-group">
                    <label for="nama_kelompok">Nama Kelompok (opsional)</label>
                    <input
                        type="text"
                        name="nama_kelompok"
                        id="nama_kelompok"
                        class="form-control @error('nama_kelompok') is-invalid @enderror"
                        placeholder='Kosongkan untuk otomatis (Kelompok 1, 2, 3, ...)'
                        value="{{ old('nama_kelompok') }}"
                    >
                    @error('nama_kelompok') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Link Drive (opsional) --}}
                <div class="form-group">
                    <label for="link_drive">Link Drive</label>
                    <input
                        type="url"
                        name="link_drive"
                        id="link_drive"
                        class="form-control @error('link_drive') is-invalid @enderror"
                        placeholder="https://drive.google.com/..."
                        value="{{ old('link_drive') }}"
                    >
                    @error('link_drive') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="form-text text-muted">Sertakan tautan Google Drive untuk dokumen kelompok.</small>
                </div>

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
                                    @endforelse
                                </select>
                                @error('entries.'.$i.'.nim') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-5">
                                <label>Kelas</label>
                                <select name="entries[{{ $i }}][kelas_id]" class="form-control select-kelas @error('entries.'.$i.'.kelas_id') is-invalid @enderror" required>
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

  // ==== Select2 (opsional) ====
  // Matcher yang menyembunyikan NIM yang sudah dipilih di baris lain.
  window.__chosenNims = new Set();
  function select2Matcher(params, data) {
    if (!data.id) return null;                          // skip group
    const val = (data.element && data.element.value) ? data.element.value : data.id;
    // Sembunyikan jika sudah dipilih di baris lain (kecuali opsi yang sedang terpilih di select ini)
    const isSelectedHere = data.element && data.element.selected;
    if (window.__chosenNims.has(val) && !isSelectedHere) return null;

    // Pencarian bawaan
    if (!params.term) return data;
    const term = params.term.toLowerCase();
    const text = (data.text || '').toLowerCase();
    return text.indexOf(term) > -1 ? data : null;
  }

  function initSelect2(scope){
    if (!(window.jQuery && jQuery().select2)) return;
    $(scope).find('.select-nim').each(function(){
      const $el = $(this);
      if ($el.data('select2')) $el.select2('destroy');
      $el.select2({ width: '100%', placeholder: '-- Pilih --', matcher: select2Matcher });
    });
    $(scope).find('.select-kelas').each(function(){
      const $el = $(this);
      if ($el.data('select2')) $el.select2('destroy');
      $el.select2({ width: '100%', placeholder: '-- Pilih Kelas --' });
    });
  }

  // Inisialisasi Select2 pertama kali (jika ada lib-nya)
  initSelect2(document);

  // Reload halaman saat periode diganti (filter server-side)
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

  // Kumpulkan NIM yang sudah dipilih & update tampilan semua select
  function collectChosen(){
    const selects = Array.from(document.querySelectorAll('select.select-nim'));
    window.__chosenNims = new Set(selects.map(s => s.value).filter(Boolean));
  }

  // Fallback native (tanpa Select2): sembunyikan & disable option yang sudah dipilih di baris lain
  function refreshNativeOptions(){
    const selects = Array.from(document.querySelectorAll('select.select-nim'));
    selects.forEach(sel => {
      const myVal = sel.value;
      sel.querySelectorAll('option').forEach(opt => {
        if (!opt.value) return; // placeholder
        const shouldHide = window.__chosenNims.has(opt.value) && opt.value !== myVal;
        opt.disabled = shouldHide;
        opt.hidden   = shouldHide;
      });
    });
  }

  // Sinkronkan ke Select2 (matcher pakai window.__chosenNims, cukup trigger change)
  function refreshAllSelects(){
    collectChosen();
    refreshNativeOptions();
    if (window.jQuery && jQuery().select2) {
      $('.select-nim').each(function(){ $(this).trigger('change.select2'); });
    }
  }

  function bindDuplicateGuard(sel){
    sel.addEventListener('change', function(){
      const v = this.value;
      if(!v){ refreshAllSelects(); return; }
      const all = [...document.querySelectorAll('select.select-nim')].map(s=>s.value).filter(Boolean);
      if (all.filter(x=>x===v).length > 1) {
        alert('NIM sudah dipilih di baris lain.');
        this.value='';
        if (window.jQuery && jQuery().select2) $(this).val('').trigger('change');
      }
      refreshAllSelects();
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
        <select class="form-control select-kelas" required>
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

    // Init Select2 di baris baru
    initSelect2(row);

    const nimSelect = row.querySelector('.select-nim');
    bindDuplicateGuard(nimSelect);

    row.querySelector('.remove-row').addEventListener('click', ()=>{
      row.remove(); reindex(); refreshAllSelects();
    });

    refreshAllSelects();
  }

  // validasi ketua (harus salah satu dari daftar anggota)
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
    e.currentTarget.closest('.entry-row').remove(); reindex(); refreshAllSelects();
  }));
  entries.querySelectorAll('select.select-nim').forEach(bindDuplicateGuard);
  addBtn.addEventListener('click', addRow);

  // panggil saat load pertama (agar langsung menyembunyikan opsi dari old input)
  refreshAllSelects();
});
</script>
@endpush
@endsection
