@extends('layout.app')
@section('content')
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 font-weight-bold">Mata Kuliah</h1>
            <p class="text-muted small mb-0">Kelola kurikulum mata kuliah per semester beserta dosen pengampu PBL</p>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap" style="gap: 0.75rem;">
            {{-- Filter Semester --}}
            <div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
                <form class="form-inline" method="GET" action="{{ route('matakuliah.index') }}">
                    <label for="filterSemester" class="mr-2 font-weight-bold small text-muted">Semester:</label>
                    <select name="semester" id="filterSemester" class="form-control form-control-sm mr-2" onchange="this.form.submit()" style="border-radius: 8px;">
                        <option value="">-- Semua Semester --</option>
                        @for($sem = 1; $sem <= 8; $sem++)
                            <option value="{{ $sem }}" {{ (string)$selectedSemester === (string)$sem ? 'selected' : '' }}>
                                Semester {{ $sem }}
                            </option>
                        @endfor
                    </select>
                    @if(!empty($selectedSemester))
                        <a href="{{ route('matakuliah.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </form>

                @if(!empty($selectedSemester))
                    <span class="badge badge-info ml-2">
                        Filter: Semester {{ $selectedSemester }}
                    </span>
                @endif
            </div>

            {{-- Tombol Aksi --}}
            <div class="d-flex align-items-center" style="gap: 0.5rem;">
                <a href="{{ route('matakuliah.import.form') }}" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-file-excel mr-1" aria-hidden="true"></i> Import Excel / CSV
                </a>
                <a href="{{ route('matakuliah.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1" aria-hidden="true"></i> Tambah Mata Kuliah
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover small" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="text-center no-sort" style="width: 45px;">No</th>
                            <th style="width: 110px;">Kode MK</th>
                            <th>Nama Mata Kuliah</th>
                            <th class="text-center" style="width: 100px;">Semester</th>
                            <th class="text-center" style="width: 70px;">SKS</th>
                            <th>Dosen Pengampu</th>
                            <th class="text-center" style="width: 90px;">Status</th>
                            <th class="text-center align-middle no-sort" style="width: 90px;">
                                <i class="fas fa-cogs fa-sm" aria-hidden="true"></i>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $i => $mk)
                        <tr>
                            <td class="text-center align-middle">{{ $loop->iteration }}</td>
                            <td class="align-middle font-weight-bold text-nowrap">
                                <span class="badge badge-light border text-dark">{{ $mk->kode_matkul }}</span>
                            </td>
                            <td class="align-middle font-weight-bold text-dark">
                                {{ $mk->nama_matkul }}
                                @if(!empty($mk->deskripsi))
                                    <div class="text-muted small font-weight-normal text-truncate" style="max-width: 320px;">
                                        {{ $mk->deskripsi }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-center align-middle">
                                <span class="badge badge-primary px-2.5 py-1">
                                    Semester {{ $mk->semester }}
                                </span>
                            </td>
                            <td class="text-center align-middle font-weight-bold">
                                {{ $mk->sks }} SKS
                            </td>
                            <td class="align-middle">
                                @if($mk->dosenPengampu->isNotEmpty())
                                    <div class="d-flex flex-column" style="gap: 0.35rem;">
                                        @foreach($mk->dosenPengampu as $dosen)
                                            <div class="d-flex align-items-center">
                                                <div class="mr-2 text-primary" style="font-size: 0.85rem;">
                                                    <i class="fas fa-chalkboard-teacher"></i>
                                                </div>
                                                <div>
                                                    <span class="font-weight-bold text-dark d-block">{{ $dosen->nama_user }}</span>
                                                    <small class="text-muted" style="font-size: 0.72rem;">{{ $dosen->email ?? $dosen->username }}</small>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @if($mk->dosenPengampu->count() > 1)
                                        <span class="badge badge-light border text-info mt-1" style="font-size: 0.7rem;">
                                            <i class="fas fa-users fa-xs mr-1"></i> Team Teaching ({{ $mk->dosenPengampu->count() }} Dosen)
                                        </span>
                                    @endif
                                @else
                                    <span class="badge badge-warning">
                                        <i class="fas fa-exclamation-circle mr-1"></i> Belum Ditentukan
                                    </span>
                                @endif
                            </td>
                            <td class="text-center align-middle">
                                @if($mk->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center align-middle text-nowrap">
                                <div class="table-action-group">
                                    <a href="{{ route('matakuliah.edit', $mk) }}" class="btn btn-sm btn-outline-success btn-action-icon" title="Ubah Mata Kuliah">
                                        <i class="fas fa-edit fa-sm"></i>
                                    </a>

                                    <form action="{{ route('matakuliah.destroy', $mk) }}" method="POST" class="m-0 p-0 d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-action-icon"
                                            onclick="var form = this.closest('form'); 
                                            Swal.fire({ title: 'Hapus Mata Kuliah?', 
                                            text: 'Apakah Anda yakin ingin menghapus mata kuliah {{ $mk->nama_matkul }}?', 
                                            icon: 'warning', showCancelButton: true, 
                                            confirmButtonText: 'Ya, hapus!', 
                                            cancelButtonText: 'Batal' }).then((result) => { if (result.isConfirmed) form.submit(); });"
                                            title="Hapus Mata Kuliah">
                                            <i class="fas fa-trash fa-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Belum ada data mata kuliah.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
