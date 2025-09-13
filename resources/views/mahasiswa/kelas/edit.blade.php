@extends('layout.app')

@section('content')
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <h1 class="h3 text-gray-800">Edit Kelas</h1>
    </div>

    <div class="card shadow">
        <div class="card-body">
            {{-- form untuk update --}}
            <form action="{{ route('kelas.update', $kelas->uuid) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="Kelas">Kelas <span class="text-danger">*</span></label>
                    <input type="text" name="kelas" id="kelas" class="form-control @error('kelas') is-invalid @enderror"
                           placeholder="Contoh: 25/26-1" value="{{ old('kelas', $kelas->kelas) }}" required>
                    @error('kelas')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex align-items-center">
                    <button type="submit" class="btn btn-primary btn-sm">
                        Perbarui
                    </button>
                    <a href="{{ route('kelas.index') }}" class="btn btn-secondary btn-sm ml-2">
                        Kembali
                    </a>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection
