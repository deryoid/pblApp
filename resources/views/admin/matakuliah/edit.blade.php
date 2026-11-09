@extends('layout.app')

@php
    $mataKuliah = $mataKuliah ?? $matakuliah ?? null;
@endphp

@section('content')
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 font-weight-bold">Edit Mata Kuliah</h1>
            <p class="text-muted small mb-0">Perbarui data kurikulum mata kuliah, semester, dan dosen pengampu</p>
        </div>
        <a href="{{ route('matakuliah.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    <div class="card shadow mb-4" style="max-width: 800px;">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edit Mata Kuliah: {{ $mataKuliah->nama_matkul }}</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('matakuliah.update', $mataKuliah) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="kode_matkul">Kode Mata Kuliah <span class="text-danger">*</span></label>
                        <input type="text" name="kode_matkul" id="kode_matkul"
                               class="form-control @error('kode_matkul') is-invalid @enderror"
                               placeholder="Contoh: MK101 / TRKJ201"
                               value="{{ old('kode_matkul', $mataKuliah->kode_matkul) }}" required>
                        @error('kode_matkul')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-md-6">
                        <label for="nama_matkul">Nama Mata Kuliah <span class="text-danger">*</span></label>
                        <input type="text" name="nama_matkul" id="nama_matkul"
                               class="form-control @error('nama_matkul') is-invalid @enderror"
                               placeholder="Contoh: Pemrograman Web Lanjut"
                               value="{{ old('nama_matkul', $mataKuliah->nama_matkul) }}" required>
                        @error('nama_matkul')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="semester">Semester <span class="text-danger">*</span></label>
                        <select name="semester" id="semester" class="form-control @error('semester') is-invalid @enderror" required>
                            <option value="">-- Pilih Semester --</option>
                            @for($s = 1; $s <= 8; $s++)
                                <option value="{{ $s }}" {{ old('semester', $mataKuliah->semester) == $s ? 'selected' : '' }}>
                                    Semester {{ $s }}
                                </option>
                            @endfor
                        </select>
                        @error('semester')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-md-6">
                        <label for="sks">Bobot SKS <span class="text-danger">*</span></label>
                        <input type="number" name="sks" id="sks" min="1" max="8"
                               class="form-control @error('sks') is-invalid @enderror"
                               value="{{ old('sks', $mataKuliah->sks) }}" required>
                        @error('sks')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="dosen_ids">
                        <i class="fas fa-chalkboard-teacher text-primary mr-1"></i> Dosen Pengampu Kuliah
                        <span class="badge badge-info ml-1">Bisa Lebih dari 1 Dosen (Team Teaching)</span>
                    </label>
                    @php
                        $selectedDosenIds = old('dosen_ids', $mataKuliah->dosenPengampu->pluck('id')->toArray());
                    @endphp
                    <select name="dosen_ids[]" id="dosen_ids" class="form-control select2 @error('dosen_ids') is-invalid @enderror" multiple="multiple" style="width: 100%;">
                        @foreach($dosenList as $dosen)
                            <option value="{{ $dosen->id }}" {{ in_array($dosen->id, $selectedDosenIds) ? 'selected' : '' }}>
                                {{ $dosen->nama_user }} ({{ ucfirst($dosen->role) }}) - {{ $dosen->email ?? $dosen->username }}
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Mata kuliah dapat diampu oleh 2 atau lebih dosen (team teaching). Satu dosen juga dapat mengampu beberapa mata kuliah berbeda.</small>
                    @error('dosen_ids')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi / Ruang Lingkup Materi</label>
                    <textarea name="deskripsi" id="deskripsi" rows="3"
                              class="form-control @error('deskripsi') is-invalid @enderror"
                              placeholder="Penjelasan capaian pembelajaran atau materi mata kuliah...">{{ old('deskripsi', $mataKuliah->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                               {{ old('is_active', $mataKuliah->is_active) ? 'checked' : '' }}>
                        <label class="custom-control-label font-weight-bold" for="is_active">Status Mata Kuliah Aktif</label>
                    </div>
                    <small class="text-muted">Mata kuliah aktif dapat dipilih oleh mahasiswa pada pengerjaan proyek PBL.</small>
                </div>

                <div class="d-flex align-items-center mt-4" style="gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-save mr-1"></i> Perbarui Mata Kuliah
                    </button>
                    <a href="{{ route('matakuliah.index') }}" class="btn btn-secondary btn-sm">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#dosen_ids').select2({
            placeholder: '-- Pilih Satu atau Beberapa Dosen Pengampu --',
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endpush
