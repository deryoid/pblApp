@extends('layout.app')
@section('content')
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="h3 text-gray-800 font-weight-bold">Import Data Mata Kuliah (Excel & CSV)</h1>
            <p class="text-secondary small mb-0 font-weight-500">
                Import data kurikulum mata kuliah dari berkas Excel (<code>.xlsx</code>, <code>.xls</code>) atau <code>.csv</code>. Dosen pengampu dapat langsung ditautkan berdasarkan NIP atau nama.
            </p>
        </div>
        <a href="{{ route('matakuliah.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1" aria-hidden="true"></i> Kembali ke Daftar Mata Kuliah
        </a>
    </div>

    <div class="card shadow-sm border border-gray-200">
        <div class="card-body">
            <form action="{{ route('matakuliah.import') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label for="file" class="font-weight-bold text-dark">Pilih Berkas Excel / CSV <span class="text-danger">*</span></label>
                    <input type="file" name="file" id="file"
                           class="form-control @error('file') is-invalid @enderror"
                           accept=".xlsx,.xls,.csv,.txt" required>
                    <small class="form-text text-muted">
                        Format kolom header: <strong>Kode MK, Nama Mata Kuliah, Semester, SKS, Dosen Pengampu, Deskripsi</strong>.
                        Mendukung berkas Microsoft Excel (<code>.xlsx</code>, <code>.xls</code>) dan CSV dengan pemisah koma (<code>,</code>) atau titik koma (<code>;</code>).
                    </small>
                    @error('file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-file-import mr-1" aria-hidden="true"></i> Mulai Import
                    </button>
                    <a href="{{ route('matakuliah.template.excel') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel mr-1" aria-hidden="true"></i> Unduh Template Excel (.xlsx)
                    </a>
                    <a href="{{ route('matakuliah.template.csv') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-file-csv mr-1" aria-hidden="true"></i> Unduh Template CSV
                    </a>
                    <button type="button" id="copyMatkulHeader" class="btn btn-warning btn-sm">
                        <i class="fas fa-copy mr-1" aria-hidden="true"></i> Salin Header CSV
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Ringkasan Hasil Import --}}
    @if(session('import_stats'))
        @php($s = session('import_stats'))
        <div class="card shadow-sm border border-gray-200 mt-3">
            <div class="card-header py-2 bg-white"><strong>Ringkasan Hasil Import</strong></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <tbody>
                        <tr><th style="width:260px">Mata kuliah baru dibuat</th><td>{{ $s['created_matkul'] ?? 0 }}</td></tr>
                        <tr><th>Mata kuliah diperbarui</th><td>{{ $s['updated_matkul'] ?? 0 }}</td></tr>
                        <tr><th>Dosen pengampu disinkronkan</th><td>{{ $s['synced_dosen'] ?? 0 }}</td></tr>
                        <tr><th>Dilewati (tidak ada perubahan)</th><td>{{ $s['skipped'] ?? 0 }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Daftar Kesalahan --}}
    @if(session('import_errors'))
        <div class="card shadow-sm border border-gray-200 mt-3">
            <div class="card-header py-2 d-flex justify-content-between align-items-center bg-white">
                <strong class="text-danger">Catatan Kesalahan Baris ({{ count(session('import_errors')) }})</strong>
                <button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="collapse" data-target="#errorListMatkul" aria-expanded="true">
                    Tampilkan / Sembunyikan
                </button>
            </div>
            <div id="errorListMatkul" class="collapse show">
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
        <div class="card-header py-2 bg-white">
            <strong>Format Kolom &amp; Contoh Data Mata Kuliah</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive mb-3">
                <table class="table table-bordered table-sm small mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Kode MK</th>
                            <th>Nama Mata Kuliah</th>
                            <th>Semester</th>
                            <th>SKS</th>
                            <th>Dosen Pengampu</th>
                            <th>Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>TRKJ101</code></td>
                            <td>Jaringan Komputer Dasar</td>
                            <td>1</td>
                            <td>3</td>
                            <td>198501012010121001; Nurul Hidayati</td>
                            <td>Pengenalan konsep OSI model, routing dasar, dan LAN.</td>
                        </tr>
                        <tr>
                            <td><code>TRKJ203</code></td>
                            <td>Pemrograman Berbasis Objek</td>
                            <td>2</td>
                            <td>3</td>
                            <td>Dr. Ahmad Fauzi</td>
                            <td>Penerapan OOP dan arsitektur aplikasi modular.</td>
                        </tr>
                        <tr>
                            <td><code>TRKJ305</code></td>
                            <td>Keamanan Jaringan &amp; Kriptografi</td>
                            <td>3</td>
                            <td>3</td>
                            <td><em>(boleh kosong)</em></td>
                            <td>Analisis celah keamanan, firewall, enkripsi data.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="small text-muted">
                <strong>Catatan:</strong>
                <ul class="mb-0 pl-3">
                    <li>Kolom <strong>Kode MK</strong> dan <strong>Nama Mata Kuliah</strong> wajib diisi.</li>
                    <li>Jika <strong>Kode MK</strong> sudah ada di database, data mata kuliah akan diperbarui secara otomatis.</li>
                    <li>Kolom <strong>Semester</strong> berupa angka 1 sampai 8 (default: 1 jika kosong atau di luar rentang).</li>
                    <li>Kolom <strong>SKS</strong> berupa angka 1 sampai 12 (default: 2 jika kosong).</li>
                    <li>Kolom <strong>Dosen Pengampu</strong> dapat diisi NIP atau nama dosen. Jika pengampu lebih dari satu (team teaching), pisahkan dengan tanda titik koma (<code>;</code>) atau koma (<code>,</code>). Sistem akan otomatis menautkan ke akun dosen terkait.</li>
                </ul>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    document.getElementById('copyMatkulHeader')?.addEventListener('click', function () {
        const headerText = "Kode MK,Nama Mata Kuliah,Semester,SKS,Dosen Pengampu,Deskripsi";
        navigator.clipboard.writeText(headerText).then(function() {
            Swal.fire({
                toast: true,
                position: 'top',
                icon: 'success',
                title: 'Header CSV berhasil disalin',
                showConfirmButton: false,
                timer: 1500
            });
        });
    });
</script>
@endpush
@endsection
