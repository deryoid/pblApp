@extends('layout.app')

@section('content')
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <h1 class="h3 text-gray-800">Tambah Mahasiswa</h1>
    </div>

    <div class="card shadow">
        <div class="card-body">
            {{-- form untuk simpan --}}
            <form action="{{ route('mahasiswa.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="nim">NIM <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        id="nim"
                        name="nim"
                        class="form-control @error('nim') is-invalid @enderror"
                        placeholder="Contoh: 230411100001"
                        value="{{ old('nim') }}"
                        required
                    >
                    @error('nim')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="nama_mahasiswa">Nama Mahasiswa <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        id="nama_mahasiswa"
                        name="nama_mahasiswa"
                        class="form-control @error('nama_mahasiswa') is-invalid @enderror"
                        placeholder="Nama lengkap"
                        value="{{ old('nama_mahasiswa') }}"
                        required
                    >
                    @error('nama_mahasiswa')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex align-items-center">
                    <button type="submit" class="btn btn-primary btn-sm">
                        Simpan
                    </button>
                    <a href="{{ route('mahasiswa.index') }}" class="btn btn-secondary btn-sm ml-2">
                        Kembali
                    </a>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection
