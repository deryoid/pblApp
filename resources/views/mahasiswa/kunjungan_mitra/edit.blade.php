@extends('layout.app')

@section('content')
<div class="container-fluid">
    <h1 class="h4 text-gray-800 mb-3">Edit Kunjungan Mitra</h1>
    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('mahasiswa.kunjungan.update', $kunjungan) }}" method="POST" enctype="multipart/form-data" id="editForm">
                @csrf
                @method('PUT')
                {{-- Hidden field untuk optimistic locking --}}
                <input type="hidden" name="updated_at" value="{{ $kunjungan->updated_at?->toIso8601String() ?? $kunjungan->updated_at }}">

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Periode <span class="text-danger">*</span></label>
                        <select name="periode_id" class="form-control @error('periode_id') is-invalid @enderror" required>
                            @foreach($periodes as $p)
                                <option value="{{ $p->id }}" {{ old('periode_id', $kunjungan->periode_id)==$p->id? 'selected':'' }}>{{ $p->periode }} ({{ $p->status_periode ?? '' }})</option>
                            @endforeach
                        </select>
                        @error('periode_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label>Kelompok <span class="text-danger">*</span></label>
                        <select name="kelompok_id" class="form-control @error('kelompok_id') is-invalid @enderror" required>
                            @foreach($kelompokOptions as $opt)
                                <option value="{{ $opt->kelompok_id }}" {{ old('kelompok_id', $kunjungan->kelompok_id)==$opt->kelompok_id? 'selected':'' }}>
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
                        <input type="text" name="perusahaan" class="form-control @error('perusahaan') is-invalid @enderror" value="{{ old('perusahaan', $kunjungan->perusahaan) }}" required>
                        @error('perusahaan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label>Alamat <span class="text-danger">*</span></label>
                        <input type="text" name="alamat" class="form-control @error('alamat') is-invalid @enderror" value="{{ old('alamat', $kunjungan->alamat) }}" required>
                        @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Tanggal Kunjungan <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_kunjungan" class="form-control @error('tanggal_kunjungan') is-invalid @enderror" value="{{ old('tanggal_kunjungan', $kunjungan->tanggal_kunjungan) }}" required>
                        @error('tanggal_kunjungan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label>Status <span class="text-danger">*</span></label>
                        @php $st = old('status_kunjungan', $kunjungan->status_kunjungan); @endphp
                        <select name="status_kunjungan" class="form-control @error('status_kunjungan') is-invalid @enderror" required>
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
                    @if($kunjungan->bukti_data_url)
                        <div class="mb-2">
                            <img src="{{ $kunjungan->bukti_data_url }}" alt="Bukti" style="max-height:120px;object-fit:cover;border-radius:4px;">
                        </div>
                        <div class="form-check mb-2">
                            <input type="checkbox" name="remove_bukti" value="1" id="remove_bukti" class="form-check-input">
                            <label for="remove_bukti" class="form-check-label">Hapus bukti saat simpan</label>
                        </div>
                    @endif
                    <input type="file" name="bukti" class="form-control-file @error('bukti') is-invalid @enderror" accept="image/*">
                    <small class="text-muted">Maks 5MB. Format: JPEG, PNG, GIF, WebP. Maksimal 50 megapixels. Gambar akan dikompres otomatis ke 500KB.</small>
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

