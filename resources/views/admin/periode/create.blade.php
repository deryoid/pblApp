@extends('layout.app')

@section('content')
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <h1 class="h3 text-gray-800">Tambah Periode</h1>
    </div>

    <div class="card shadow">
        <div class="card-body">
            {{-- form untuk simpan --}}
            <form action="{{ route('periode.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="periode">Periode <span class="text-danger">*</span></label>
                    <input type="text" name="periode" id="periode" class="form-control @error('periode') is-invalid @enderror"
                           placeholder="Contoh: 25/26-1" value="{{ old('periode') }}" required>
                    @error('periode')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Status Periode <span class="text-danger">*</span></label>

                    <div class="form-check">
                        <input class="form-check-input @error('status_periode') is-invalid @enderror"
                            type="radio"
                            name="status_periode"
                            id="status_aktif"
                            value="Aktif"
                            {{ old('status_periode', $periode->status_periode ?? 'Aktif') == 'Aktif' ? 'checked' : '' }}>
                        <label class="form-check-label" for="status_aktif">
                            Aktif
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input @error('status_periode') is-invalid @enderror"
                            type="radio"
                            name="status_periode"
                            id="status_tidak_aktif"
                            value="Tidak Aktif"
                            {{ old('status_periode', $periode->status_periode ?? 'Aktif') == 'Tidak Aktif' ? 'checked' : '' }}>
                        <label class="form-check-label" for="status_tidak_aktif">
                            Tidak Aktif
                        </label>
                    </div>

                    @error('status_periode')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>



                <div class="d-flex align-items-center">
                    <button type="submit" class="btn btn-primary btn-sm">
                        Simpan
                    </button>
                    <a href="{{ route('periode.index') }}" class="btn btn-secondary btn-sm ml-2">
                        Kembali
                    </a>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection
