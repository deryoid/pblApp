@extends('layout.app')
@section('content')
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Bank Data Dosen</h1>
            <p class="text-secondary small mb-0 font-weight-500">Kelola master bank data dosen pengampu dan penilai PBL (NIP, Nama, Nomor Telp, Keahlian).</p>
        </div>
        <div class="mt-2 mt-sm-0 d-flex flex-wrap" style="gap: 0.5rem;">
            <div class="badge badge-light border px-3 py-2 text-dark font-weight-600">
                <i class="fas fa-chalkboard-teacher mr-1 text-primary" aria-hidden="true"></i> Total Dosen: {{ $data->count() }}
            </div>
            <div class="badge-status-linked">
                <i class="fas fa-check-circle mr-1" aria-hidden="true"></i> Terhubung: {{ $data->whereNotNull('user_id')->count() }}
            </div>
        </div>
    </div>

    <div class="card shadow-sm border border-gray-200 mb-4">
        <div class="card-header py-3 bg-white border-bottom">
            <div class="d-flex align-items-center justify-content-between flex-wrap w-100" style="gap: 0.75rem;">
                {{-- Kiri: Tambah Dosen --}}
                <div>
                    <a href="{{ route('dosen.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1" aria-hidden="true"></i> Tambah Dosen
                    </a>
                </div>

                {{-- Kanan: Import + Sinkron --}}
                <div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
                    {{-- Import Excel / CSV --}}
                    <a href="{{ route('dosen.import.form') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel mr-1" aria-hidden="true"></i> Import Excel / CSV
                    </a>

                    {{-- Sinkronkan Akun --}}
                    <form action="{{ route('dosen.sync') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm" title="Sinkronkan akun dosen dari tabel pengguna" aria-label="Sinkronkan akun dosen dari tabel pengguna">
                            <i class="fas fa-sync-alt mr-1" aria-hidden="true"></i> Sinkronkan Akun Dosen
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover small" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="text-center no-sort" style="width: 45px;">No</th>
                            <th style="width: 120px;">NIP</th>
                            <th>Nama Dosen</th>
                            <th style="width: 140px;">Nomor Telp / WA</th>
                            <th>Keahlian / Spesialisasi</th>
                            <th>Akun Pengguna</th>
                            <th class="text-center" style="width: 110px;">Status Akun</th>
                            <th class="text-center no-sort" style="width: 100px;">Atur Sandi</th>
                            <th class="text-center align-middle no-sort" style="width: 100px;">
                                <i class="fas fa-cogs fa-sm" aria-hidden="true"></i>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $i => $d)
                        @php $u = $d->user; @endphp
                        <tr>
                            <td class="text-center align-middle">{{ $loop->iteration }}</td>
                            <td class="align-middle font-weight-bold text-nowrap text-primary">
                                {{ $d->nip ?? '-' }}
                            </td>
                            <td class="align-middle font-weight-500">
                                <div class="text-dark font-weight-bold">{{ $d->nama_dosen }}</div>
                            </td>
                            <td class="align-middle text-nowrap">
                                @if($d->no_hp || $u?->no_hp)
                                    <span class="text-success font-weight-500">
                                        <i class="fas fa-phone mr-1" aria-hidden="true"></i>{{ $d->no_hp ?: $u->no_hp }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="align-middle">
                                @if($d->keahlian)
                                    <span class="badge badge-light border text-dark font-weight-normal px-2 py-1">
                                        <i class="fas fa-tools mr-1 text-primary" aria-hidden="true"></i>{{ $d->keahlian }}
                                    </span>
                                @else
                                    <span class="text-muted small"><em>Belum diisi</em></span>
                                @endif
                            </td>
                            <td class="align-middle">
                                @if($u)
                                    <div class="font-weight-bold text-dark" style="font-size: 0.85rem;">{{ $u->username }}</div>
                                    <div class="text-secondary font-weight-500" style="font-size: 0.775rem;">{{ $u->email }}</div>
                                @else
                                    <span class="text-muted small"><em>Belum terhubung</em></span>
                                @endif
                            </td>

                            {{-- STATUS AKUN --}}
                            <td class="text-center align-middle text-nowrap">
                                @if($u)
                                    <span class="badge-status-linked">
                                        <i class="fas fa-check-circle mr-1" aria-hidden="true"></i> Terhubung
                                    </span>
                                @else
                                    <span class="badge-status-unlinked">
                                        <i class="fas fa-clock mr-1" aria-hidden="true"></i> Belum Mendaftar
                                    </span>
                                @endif
                            </td>

                            {{-- Reset Password --}}
                            <td class="text-center align-middle text-nowrap">
                                @if($u)
                                <form action="{{ route('user.reset-password', $u) }}" method="POST" class="m-0 d-inline">
                                    @csrf
                                    <button type="button" class="btn btn-xs btn-outline-warning text-nowrap"
                                        onclick="var form = this.closest('form');
                                        Swal.fire({
                                            title: 'Reset Kata Sandi Dosen?',
                                            text: 'Kata sandi akun dosen {{ $d->nama_dosen }} akan diatur ulang menjadi default ({{ $u->username }}@pbl).',
                                            icon: 'warning',
                                            showCancelButton: true,
                                            confirmButtonText: 'Ya, Reset',
                                            cancelButtonText: 'Batal'
                                        }).then((result) => { if (result.isConfirmed) form.submit(); });"
                                        aria-label="Reset kata sandi akun dosen {{ $d->nama_dosen }}" title="Reset Password Akun">
                                        <i class="fas fa-key mr-1" aria-hidden="true"></i> Reset
                                    </button>
                                </form>
                                @else
                                <span class="text-muted small">-</span>
                                @endif
                            </td>

                            {{-- AKSI --}}
                            <td class="text-center align-middle text-nowrap">
                                <div class="table-action-group">
                                    <a href="{{ route('dosen.edit', $d) }}" class="btn btn-sm btn-outline-success btn-action-icon" aria-label="Ubah data dosen {{ $d->nama_dosen }}" title="Ubah Data Dosen">
                                        <i class="fas fa-edit fa-sm" aria-hidden="true"></i>
                                    </a>

                                    <form action="{{ route('dosen.destroy', $d) }}" method="POST" class="m-0 p-0 d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-action-icon"
                                            onclick="var form = this.closest('form');
                                            Swal.fire({
                                                title: 'Hapus Dosen?',
                                                html: 'Data dosen <strong>{{ $d->nama_dosen }}</strong> akan dihapus dari Bank Data.',
                                                icon: 'warning',
                                                showCancelButton: true,
                                                confirmButtonText: 'Ya, hapus!',
                                                cancelButtonText: 'Batal'
                                            }).then((result) => { if (result.isConfirmed) form.submit(); });"
                                            aria-label="Hapus data dosen {{ $d->nama_dosen }}"
                                            title="Hapus Data Dosen">
                                            <i class="fas fa-trash fa-sm" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <div class="py-3">
                                    <i class="fas fa-chalkboard-teacher fa-3x text-gray-300 mb-3" aria-hidden="true"></i>
                                    <h2 class="font-weight-bold text-gray-700 h6 mb-1">Belum Ada Data Dosen</h2>
                                    <p class="small text-muted mb-3">Mulai tambahkan master dosen atau sinkronkan dengan akun dosen yang telah terdaftar.</p>
                                    <div class="d-inline-flex flex-wrap" style="gap: 0.5rem;">
                                        <a href="{{ route('dosen.create') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus mr-1" aria-hidden="true"></i> Tambah Dosen
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
