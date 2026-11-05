@extends('layout.app')
@section('content')
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="h3 text-gray-800 font-weight-bold">Import Bank Data Dosen (Excel & CSV)</h1>
            <p class="text-secondary small mb-0 font-weight-500">
                Import data master dosen pengampu dan penilai PBL dari berkas Excel (<code>.xlsx</code>, <code>.xls</code>) atau <code>.csv</code>. Akun pengguna dosen akan otomatis disinkronkan.
            </p>
        </div>
        <a href="{{ route('dosen.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1" aria-hidden="true"></i> Kembali ke Daftar Dosen
        </a>
    </div>

    <div class="card shadow-sm border border-gray-200">
        <div class="card-body">
            <form action="{{ route('dosen.import') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label for="file" class="font-weight-bold text-dark">Pilih Berkas Excel / CSV <span class="text-danger">*</span></label>
                    <input type="file" name="file" id="file"
                           class="form-control @error('file') is-invalid @enderror"
                           accept=".xlsx,.xls,.csv,.txt" required>
                    <small class="form-text text-muted">
                        Format kolom header: <strong>NIP, Nama Dosen, Nomor Telp, Keahlian</strong>.
                        Mendukung file Microsoft Excel (<code>.xlsx</code>, <code>.xls</code>) dan CSV dengan delimiter koma (<code>,</code>) atau titik koma (<code>;</code>).
                    </small>
                    @error('file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-file-import mr-1" aria-hidden="true"></i> Mulai Import
                    </button>
                    <a href="{{ route('dosen.template.excel') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel mr-1" aria-hidden="true"></i> Unduh Template Excel (.xlsx)
                    </a>
                    <a href="{{ route('dosen.template.csv') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-file-csv mr-1" aria-hidden="true"></i> Unduh Template CSV
                    </a>
                    <button type="button" id="copyDosenHeader" class="btn btn-warning btn-sm">
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
                        <tr><th style="width:260px">Dosen baru dibuat</th><td>{{ $s['created_dosen'] ?? 0 }}</td></tr>
                        <tr><th>Dosen diperbarui</th><td>{{ $s['updated_dosen'] ?? 0 }}</td></tr>
                        <tr><th>Ditautkan ke akun pengguna</th><td>{{ $s['attached'] ?? 0 }}</td></tr>
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
                <button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="collapse" data-target="#errorListDosen" aria-expanded="true">
                    Tampilkan / Sembunyikan
                </button>
            </div>
            <div id="errorListDosen" class="collapse show">
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
            <strong>Format Kolom & Contoh Data Dosen</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive mb-3">
                <table class="table table-bordered table-sm small mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>NIP</th>
                            <th>Nama Dosen</th>
                            <th>Nomor Telp</th>
                            <th>Keahlian</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>198501012010121001</td>
                            <td>Dr. Ahmad Fauzi, M.Kom.</td>
                            <td>081234567890</td>
                            <td>Rekayasa Perangkat Lunak, Cloud Computing</td>
                        </tr>
                        <tr>
                            <td>199003152018032002</td>
                            <td>Nurul Hidayati, S.Kom., M.T.</td>
                            <td>085678901234</td>
                            <td>Jaringan Komputer, Keamanan Siber, IoT</td>
                        </tr>
                        <tr>
                            <td><em>(boleh kosong)</em></td>
                            <td>Rahmat Hidayat, M.Kom.</td>
                            <td>081987654321</td>
                            <td>Kecerdasan Buatan, Data Science</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="small text-muted">
                <strong>Catatan:</strong>
                <ul class="mb-0 pl-3">
                    <li>Kolom <strong>Nama Dosen</strong> wajib diisi.</li>
                    <li>Kolom <strong>NIP</strong> dapat dikosongkan bila dosen belum memiliki NIP resmi.</li>
                    <li>Nomor telepon otomatis dinormalisasi jika awalan <code>0</code> terpotong oleh Excel.</li>
                    <li>Jika NIP atau nama cocok dengan akun pengguna yang memiliki role dosen/evaluator, data akan ditautkan secara otomatis.</li>
                </ul>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    document.getElementById('copyDosenHeader')?.addEventListener('click', function () {
        const headerText = "NIP,Nama Dosen,Nomor Telp,Keahlian";
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
