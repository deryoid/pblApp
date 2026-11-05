@extends('layout.app')

@php
    $kelasList = $kelasList ?? \App\Models\Kelas::orderBy('kelas')->get(['id', 'kelas']);
    $agamaOptions = ['Islam', 'Kristen Protestan', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Lainnya'];
@endphp

@section('content')
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <h1 class="h3 text-gray-800 font-weight-bold">Tambah Mahasiswa ke Bank Data</h1>
        <a href="{{ route('mahasiswa.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    <div class="card shadow-sm border border-gray-200">
        <div class="card-body">
            <form action="{{ route('mahasiswa.store') }}" method="POST">
                @csrf

                <h2 class="h6 font-weight-bold text-primary mb-3">
                    <i class="fas fa-graduation-cap mr-1" aria-hidden="true"></i> Informasi Utama & Akademik
                </h2>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="angkatan" class="font-weight-bold small">Tahun Angkatan</label>
                        <input
                            type="text"
                            id="angkatan"
                            name="angkatan"
                            class="form-control @error('angkatan') is-invalid @enderror"
                            placeholder="Contoh: 2024"
                            value="{{ old('angkatan') }}"
                        >
                        @error('angkatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group col-md-4">
                        <label for="nim" class="font-weight-bold small">NIM <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            id="nim"
                            name="nim"
                            class="form-control @error('nim') is-invalid @enderror"
                            placeholder="Contoh: C030324001"
                            value="{{ old('nim') }}"
                            required
                        >
                        @error('nim') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group col-md-4">
                        <label for="nama_mahasiswa" class="font-weight-bold small">Nama Mahasiswa <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            id="nama_mahasiswa"
                            name="nama_mahasiswa"
                            class="form-control @error('nama_mahasiswa') is-invalid @enderror"
                            placeholder="Nama lengkap sesuai identitas"
                            value="{{ old('nama_mahasiswa') }}"
                            required
                        >
                        @error('nama_mahasiswa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="kelas_id" class="font-weight-bold small">Kelas</label>
                        <select name="kelas_id" id="kelas_id" class="form-control @error('kelas_id') is-invalid @enderror">
                            <option value="">-- Pilih Kelas (Opsional) --</option>
                            @foreach($kelasList as $k)
                                <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                                    {{ $k->kelas }}
                                </option>
                            @endforeach
                        </select>
                        @error('kelas_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group col-md-4">
                        <label for="no_hp" class="font-weight-bold small">Nomor HP / WhatsApp</label>
                        <input
                            type="text"
                            id="no_hp"
                            name="no_hp"
                            class="form-control @error('no_hp') is-invalid @enderror"
                            placeholder="08xxxxxxxxxx"
                            value="{{ old('no_hp') }}"
                        >
                        @error('no_hp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group col-md-4">
                        <label for="email_kampus" class="font-weight-bold small">Email Kampus</label>
                        <input
                            type="email"
                            id="email_kampus"
                            name="email_kampus"
                            class="form-control @error('email_kampus') is-invalid @enderror"
                            placeholder="contoh@politala.ac.id"
                            value="{{ old('email_kampus') }}"
                        >
                        @error('email_kampus') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <hr class="my-4">
                <h2 class="h6 font-weight-bold text-primary mb-3">
                    <i class="fas fa-id-card mr-1" aria-hidden="true"></i> Biodata Lengkap Mahasiswa
                </h2>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="nik" class="font-weight-bold small">NIK (16 Digit)</label>
                        <input
                            type="text"
                            id="nik"
                            name="nik"
                            class="form-control @error('nik') is-invalid @enderror"
                            placeholder="630101xxxxxxxxxx"
                            value="{{ old('nik') }}"
                        >
                        @error('nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group col-md-4">
                        <label for="jk" class="font-weight-bold small">Jenis Kelamin</label>
                        <select name="jk" id="jk" class="form-control @error('jk') is-invalid @enderror">
                            <option value="">-- Pilih Jenis Kelamin --</option>
                            <option value="L" {{ old('jk') == 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                            <option value="P" {{ old('jk') == 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                        </select>
                        @error('jk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group col-md-4">
                        <label for="agama" class="font-weight-bold small">Agama</label>
                        <select name="agama" id="agama" class="form-control @error('agama') is-invalid @enderror">
                            <option value="">-- Pilih Agama --</option>
                            @foreach($agamaOptions as $opt)
                                <option value="{{ $opt }}" {{ old('agama') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                        @error('agama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="tempat_lahir" class="font-weight-bold small">Tempat Lahir</label>
                        <input
                            type="text"
                            id="tempat_lahir"
                            name="tempat_lahir"
                            class="form-control @error('tempat_lahir') is-invalid @enderror"
                            placeholder="Kota / Kabupaten"
                            value="{{ old('tempat_lahir') }}"
                        >
                        @error('tempat_lahir') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group col-md-4">
                        <label for="tanggal_lahir" class="font-weight-bold small">Tanggal Lahir</label>
                        <input
                            type="date"
                            id="tanggal_lahir"
                            name="tanggal_lahir"
                            class="form-control @error('tanggal_lahir') is-invalid @enderror"
                            value="{{ old('tanggal_lahir') }}"
                        >
                        @error('tanggal_lahir') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group col-md-4">
                        <label for="nama_ibu" class="font-weight-bold small">Nama Ibu Kandung</label>
                        <input
                            type="text"
                            id="nama_ibu"
                            name="nama_ibu"
                            class="form-control @error('nama_ibu') is-invalid @enderror"
                            placeholder="Nama lengkap ibu kandung"
                            value="{{ old('nama_ibu') }}"
                        >
                        @error('nama_ibu') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="alamat" class="font-weight-bold small">Alamat Lengkap / Domisili</label>
                    <textarea
                        id="alamat"
                        name="alamat"
                        rows="2"
                        class="form-control @error('alamat') is-invalid @enderror"
                        placeholder="Alamat domisili mahasiswa"
                    >{{ old('alamat') }}</textarea>
                    @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <hr class="my-4">

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="create_user" name="create_user" value="1" {{ old('create_user') ? 'checked' : '' }}>
                        <label class="custom-control-label font-weight-bold" for="create_user">Buatkan akun login pengguna langsung (Username & Sandi Awal = NIM)</label>
                    </div>
                    <small class="form-text text-muted">Jika tidak dicentang, data disimpan di Bank Data Mahasiswa dan mahasiswa dapat mendaftar mandiri via Google OAuth / verifikasi NIM.</small>
                </div>

                <div class="d-flex align-items-center pt-2">
                    <button type="submit" class="btn btn-primary btn-sm px-3">
                        <i class="fas fa-save mr-1"></i> Simpan Data Mahasiswa
                    </button>
                    <a href="{{ route('mahasiswa.index') }}" class="btn btn-secondary btn-sm ml-2">
                        Batal
                    </a>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection
