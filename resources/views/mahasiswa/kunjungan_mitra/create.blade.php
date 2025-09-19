@extends('layout.app')

@section('content')
<div class="container-fluid">
    <h1 class="h4 text-gray-800 mb-3">Tambah Kunjungan Mitra</h1>
    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('mahasiswa.kunjungan.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Periode <span class="text-danger">*</span></label>
                        <select name="periode_id" class="form-control @error('periode_id') is-invalid @enderror" required>
                            <option value="">Pilih Periode</option>
                            @foreach($periodes as $p)
                                <option value="{{ $p->id }}" {{ old('periode_id')==$p->id? 'selected':'' }}>{{ $p->periode }} ({{ $p->status_periode ?? '' }})</option>
                            @endforeach
                        </select>
                        @error('periode_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label>Kelompok <span class="text-danger">*</span></label>
                        <select name="kelompok_id" class="form-control @error('kelompok_id') is-invalid @enderror" required>
                            <option value="">Pilih Kelompok Anda</option>
                            @foreach($kelompokOptions as $opt)
                                <option value="{{ $opt->kelompok_id }}" {{ old('kelompok_id')==$opt->kelompok_id? 'selected':'' }}>
                                    {{ $opt->nama_kelompok }} - {{ $opt->nama_periode }}
                                </option>
                            @endforeach
                        </select>
                        @error('kelompok_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Perusahaan <span class="text-danger">*</span></label>
                        <input type="text" name="perusahaan" class="form-control @error('perusahaan') is-invalid @enderror" value="{{ old('perusahaan') }}" required>
                        @error('perusahaan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label>Alamat <span class="text-danger">*</span></label>
                        <input type="text" name="alamat" class="form-control @error('alamat') is-invalid @enderror" value="{{ old('alamat') }}" required>
                        @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Tanggal Kunjungan <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_kunjungan" class="form-control @error('tanggal_kunjungan') is-invalid @enderror" value="{{ old('tanggal_kunjungan') }}" required>
                        @error('tanggal_kunjungan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label>Status <span class="text-danger">*</span></label>
                        <select name="status_kunjungan" class="form-control @error('status_kunjungan') is-invalid @enderror" required>
                            @php $st = old('status_kunjungan','Sudah dikunjungi'); @endphp
                            <option value="Sudah dikunjungi" {{ $st=='Sudah dikunjungi'?'selected':'' }}>Sudah dikunjungi</option>
                            <option value="Proses Pembicaraan" {{ $st=='Proses Pembicaraan'?'selected':'' }}>Proses Pembicaraan</option>
                            <option value="Tidak ada tanggapan" {{ $st=='Tidak ada tanggapan'?'selected':'' }}>Tidak ada tanggapan</option>
                            <option value="Ditolak" {{ $st=='Ditolak'?'selected':'' }}>Ditolak</option>
                        </select>
                        @error('status_kunjungan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Bukti Kunjungan (Foto)</label>
                    <input type="file" name="bukti" class="form-control-file @error('bukti') is-invalid @enderror" accept="image/*">
                    <small class="text-muted">Maks 6MB. Disimpan di database (BLOB).</small>
                    @error('bukti') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div>
                    <button class="btn btn-primary btn-sm" type="submit">Simpan</button>
                    <a class="btn btn-secondary btn-sm" href="{{ route('mahasiswa.kunjungan.index') }}">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
