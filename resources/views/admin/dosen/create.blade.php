@extends('layout.app')

@section('content')
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <h1 class="h3 text-gray-800 font-weight-bold">Tambah Dosen ke Bank Data</h1>
        <a href="{{ route('dosen.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    <div class="card shadow-sm border border-gray-200">
        <div class="card-body">
            <form action="{{ route('dosen.store') }}" method="POST">
                @csrf

                <h2 class="h6 font-weight-bold text-primary mb-3">
                    <i class="fas fa-chalkboard-teacher mr-1" aria-hidden="true"></i> Informasi Dosen
                </h2>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="nip" class="font-weight-bold small">NIP (Nomor Induk Pegawai)</label>
                        <input
                            type="text"
                            id="nip"
                            name="nip"
                            class="form-control @error('nip') is-invalid @enderror"
                            placeholder="Contoh: 198501012010121001"
                            value="{{ old('nip') }}"
                        >
                        @error('nip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="form-text text-muted">Dapat dikosongkan jika dosen belum memiliki NIP resmi.</small>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="nama_dosen" class="font-weight-bold small">Nama Dosen & Gelar <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            id="nama_dosen"
                            name="nama_dosen"
                            class="form-control @error('nama_dosen') is-invalid @enderror"
                            placeholder="Contoh: Dr. Ahmad Fauzi, M.Kom."
                            value="{{ old('nama_dosen') }}"
                            required
                        >
                        @error('nama_dosen') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="no_hp" class="font-weight-bold small">Nomor Telepon / WhatsApp</label>
                        <input
                            type="text"
                            id="no_hp"
                            name="no_hp"
                            class="form-control @error('no_hp') is-invalid @enderror"
                            placeholder="Contoh: 081234567890"
                            value="{{ old('no_hp') }}"
                        >
                        @error('no_hp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group col-md-6">
                        <label for="email" class="font-weight-bold small">Alamat Email (Opsional)</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            placeholder="Contoh: dosen@politala.ac.id"
                            value="{{ old('email') }}"
                        >
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label for="keahlian" class="font-weight-bold small">Bidang Keahlian / Spesialisasi</label>
                    <textarea
                        id="keahlian"
                        name="keahlian"
                        rows="3"
                        class="form-control @error('keahlian') is-invalid @enderror"
                        placeholder="Contoh: Jaringan Komputer, Keamanan Siber, Rekayasa Perangkat Lunak, IoT"
                    >{{ old('keahlian') }}</textarea>
                    @error('keahlian') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="form-text text-muted">Tuliskan kompetensi atau bidang kepakaran dosen untuk pertimbangan penugasan proyek/evaluasi PBL.</small>
                </div>

                <hr class="my-4">

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="create_user" name="create_user" value="1" {{ old('create_user') ? 'checked' : '' }}>
                        <label class="custom-control-label font-weight-bold" for="create_user">Buatkan akun login pengguna langsung (Role: Dosen)</label>
                    </div>
                    <small class="form-text text-muted">Akun login akan dibuatkan dengan username & sandi awal berdasarkan NIP/Email.</small>
                </div>

                <div class="d-flex align-items-center pt-2">
                    <button type="submit" class="btn btn-primary btn-sm px-3">
                        <i class="fas fa-save mr-1"></i> Simpan Data Dosen
                    </button>
                    <a href="{{ route('dosen.index') }}" class="btn btn-secondary btn-sm ml-2">
                        Batal
                    </a>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection
