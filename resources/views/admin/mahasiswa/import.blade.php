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
                        Delimiter: koma (<code>,</code>), titik koma (<code>;</code>), atau tab.
                        Jika nama mengandung koma/kutip, tidak masalah—parser sudah mendukung enclosure <code>"</code> / <code>'</code>.
                    </small>
                    @error('file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm mr-2">Import</button>
                    <a href="{{ route('mahasiswa.template.csv') }}" class="btn btn-secondary btn-sm mr-2">
                        Download Template CSV
                    </a>
                    <button type="button" id="copyExample" style="background-color: orange; color: black;" class="btn btn-sm">Copy </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Ringkasan Hasil Import --}}
    @if(session('import_stats'))
        @php($s = session('import_stats'))
        <div class="card shadow mt-3">
            <div class="card-header py-2"><strong>Ringkasan Hasil</strong></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <tbody>
                        <tr><th style="width:260px">User baru dibuat</th><td>{{ $s['created_users'] ?? 0 }}</td></tr>
                        <tr><th>Mahasiswa baru dibuat</th><td>{{ $s['created_mhs'] ?? 0 }}</td></tr>
                        <tr><th>Mahasiswa diperbarui</th><td>{{ $s['updated_mhs'] ?? 0 }}</td></tr>
                        <tr><th>Ditautkan (by NIM)</th><td>{{ $s['attached'] ?? 0 }}</td></tr>
                        <tr><th>Dilewati (tidak berubah)</th><td>{{ $s['skipped'] ?? 0 }}</td></tr>
                        <tr class="{{ ($s['conflict'] ?? 0) > 0 ? 'table-warning' : '' }}">
                            <th>Konflik (NIM bentrok)</th><td>{{ $s['conflict'] ?? 0 }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <small class="text-muted">
                    *Konflik: NIM sudah dipakai entri lain. Silakan periksa daftar kesalahan di bawah.
                </small>
            </div>
        </div>
    @endif

    {{-- Daftar Kesalahan --}}
    @if(session('import_errors'))
        <div class="card shadow mt-3">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <strong>Ringkasan Kesalahan ({{ count(session('import_errors')) }})</strong>
                <button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="collapse" data-target="#errorList" aria-expanded="true">
                    Tampilkan/Sembunyikan
                </button>
            </div>
            <div id="errorList" class="collapse show">
                <div class="card-body">
                    <ul class="mb-0 small text-danger">
                        @foreach(session('import_errors') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Contoh CSV --}}
    <div class="card shadow mt-3">
        <div class="card-header py-2">
            <strong>Contoh CSV</strong>
        </div>
        <div class="card-body">
<pre class="mb-2" id="exampleCsv">NIM,Nama
230411100001,Budi Santoso
230411100002,Siti Aminah
230411100003,Agus Saputra
"230411100004","Rizki D'Angelo"
'230411100005','Ayu “Mega” Lestari'
</pre>
            <small class="text-muted d-block">
                Catatan: Nama dengan kutip tunggal/ganda atau “smart quotes” tetap didukung. Jika Excel mengubah tanda kutip, parser akan menormalkan secara otomatis.
            </small>
        </div>
    </div>

</div>

{{-- JS kecil untuk copy contoh --}}
@push('scripts')
<script>
document.getElementById('copyExample')?.addEventListener('click', function () {
    const txt = document.getElementById('exampleCsv')?.innerText || '';
    navigator.clipboard.writeText(txt).then(() => {
        alert('Contoh CSV sudah disalin ke clipboard.');
    });
});
</script>
@endpush
@endsection
