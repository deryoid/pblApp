@extends('layout.app')
@section('content')
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <h1 class="h3 text-gray-800">Import Mahasiswa (CSV)</h1>
        <a href="{{ route('mahasiswa.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('mahasiswa.import') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label for="file">Pilih File CSV <span class="text-danger">*</span></label>
                    <input type="file" name="file" id="file"
                           class="form-control @error('file') is-invalid @enderror"
                           accept=".csv,.txt" required>
                    <small class="form-text text-muted">
                        Format kolom: <strong>NIM, Nama</strong> (boleh ada header).
                        Delimiter yang didukung: koma (<code>,</code>), titik koma (<code>;</code>), tab.
                    </small>
                    @error('file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary btn-sm">
                    Import
                </button>
            </form>
        </div>
    </div>

    @if(session('import_errors'))
        <div class="card shadow mt-3">
            <div class="card-header py-2">
                <strong>Ringkasan Kesalahan</strong>
            </div>
            <div class="card-body">
                <ul class="mb-0 small text-danger">
                    @foreach(session('import_errors') as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="card shadow mt-3">
        <div class="card-header py-2">
            <strong>Contoh CSV</strong>
        </div>
        <div class="card-body">
        <pre class="mb-0">
NIM,Nama
230411100001,Budi Santoso
230411100002,Siti Aminah
230411100003,Agus Saputra
        </pre>
        </div>
    </div>

</div>
@endsection
