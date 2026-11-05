@extends('layout.app')
@section('content')
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="h3 text-gray-800">Import Bank Data Mahasiswa (Excel & CSV)</h1>
            <p class="text-muted small mb-0">Import data mahasiswa dari berkas Excel (<code>.xlsx</code>, <code>.xls</code>) atau <code>.csv</code>. Akun pengguna akan otomatis disesuaikan jika sudah ada, atau siap diklaim oleh mahasiswa saat mendaftar.</p>
        </div>
        <a href="{{ route('mahasiswa.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
    </div>

    <div class="card shadow-sm border border-gray-200">
        <div class="card-body">
            <form action="{{ route('mahasiswa.import') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label for="file">Pilih Berkas Excel / CSV <span class="text-danger">*</span></label>
                    <input type="file" name="file" id="file"
                           class="form-control @error('file') is-invalid @enderror"
                           accept=".xlsx,.xls,.csv,.txt" required>
                    <small class="form-text text-muted">
                        Format kolom header: <strong>Angkatan, NIM, Nama Mahasiswa, NIK, JK, Nama Ibu, Agama, Kelas, Tempat Lahir, Tanggal Lahir, Alamat, No. HP, Email Kampus</strong>.
                        Mendukung file Microsoft Excel (<code>.xlsx</code>, <code>.xls</code>) dan CSV dengan delimiter koma (<code>,</code>) atau titik koma (<code>;</code>).
                    </small>
                    @error('file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-file-import mr-1"></i> Mulai Import
                    </button>
                    <a href="{{ route('mahasiswa.template.excel') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel mr-1"></i> Download Template Excel (.xlsx)
                    </a>
                    <a href="{{ route('mahasiswa.template.csv') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-file-csv mr-1"></i> Download Template CSV
                    </a>
                    <button type="button" id="copyExample" class="btn btn-warning btn-sm">
                        <i class="fas fa-copy mr-1"></i> Salin Header CSV
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Ringkasan Hasil Import --}}
    @if(session('import_stats'))
        @php($s = session('import_stats'))
        <div class="card shadow-sm border border-gray-200 mt-3">
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
        <div class="card shadow-sm border border-gray-200 mt-3">
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

    {{-- Format & Contoh Kolom --}}
    <div class="card shadow-sm border border-gray-200 mt-3">
        <div class="card-header py-2">
            <strong>Format Kolom & Contoh Data</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive mb-3">
                <table class="table table-bordered table-sm small mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Angkatan</th>
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th>NIK</th>
                            <th>JK</th>
                            <th>Nama Ibu</th>
                            <th>Agama</th>
                            <th>Kelas</th>
                            <th>Tempat Lahir</th>
                            <th>Tanggal Lahir</th>
                            <th>Alamat</th>
                            <th>No. HP</th>
                            <th>Email Kampus</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>2024</td>
                            <td>C030324001</td>
                            <td>Budi Santoso</td>
                            <td>6301012005040001</td>
                            <td>L</td>
                            <td>Siti Aminah</td>
                            <td>Islam</td>
                            <td>2A</td>
                            <td>Pelaihari</td>
                            <td>2004-05-12</td>
                            <td>Jl. A. Yani KM 6</td>
                            <td>081234567890</td>
                            <td>c030324001@politala.ac.id</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <pre class="mb-2 p-2 bg-light rounded small" id="exampleCsv">Angkatan,NIM,Nama Mahasiswa,NIK,JK,Nama Ibu,Agama,Kelas,Tempat Lahir,Tanggal Lahir,Alamat,No. HP,Email Kampus
2024,C030324001,Budi Santoso,6301012005040001,L,Siti Aminah,Islam,2A,Pelaihari,2004-05-12,"Jl. A. Yani KM 6, Pelaihari",081234567890,c030324001@politala.ac.id
2024,C030324002,Ayu Lestari,6301016508040002,P,Nurul Hidayah,Islam,2B,Banjarmasin,2004-08-25,"Jl. Gatot Subroto No. 15",081398765432,c030324002@politala.ac.id</pre>
            <small class="text-muted d-block">
                Catatan: Anda dapat mengunggah berkas Excel (<code>.xlsx</code> / <code>.xls</code>) secara langsung atau berkas CSV. Sistem akan membaca tanggal lahir baik dalam format teks (<code>YYYY-MM-DD</code> atau <code>DD/MM/YYYY</code>) maupun format date/serial bawaan Excel.
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
