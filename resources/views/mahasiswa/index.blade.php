@extends('layout.app')
@section('content')
                <div class="container-fluid">
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-bolt text-warning mr-2" aria-hidden="true"></i>
                            Selamat Datang Ranger Junior <b>{{ Auth::user()->nama_user }}</b>
                        </h1>
                        {{-- <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                                class="fas fa-download fa-sm text-white-50"></i> Generate Report</a> --}}
                    </div>

                    {{-- Info Periode Aktif & Kelompok Saya --}}
                    @php
                        $mhs = \App\Models\Mahasiswa::where('user_id', Auth::id())->first();
                        $periodeAktif = \App\Models\Periode::where('status_periode','Aktif')->orderByDesc('created_at')->first();
                        $kelompokSaya = null;
                        $anggotaKelompok = collect();
                        $labelPeriode = $periodeAktif? $periodeAktif->periode : null;
                        if ($mhs) {
                            if ($periodeAktif) {
                                $kelompokSaya = $mhs->kelompoks()->wherePivot('periode_id', $periodeAktif->id)->first();
                                if ($kelompokSaya) {
                                    $anggotaKelompok = $kelompokSaya->mahasiswas()
                                        ->wherePivot('periode_id', $periodeAktif->id)
                                        ->with('user')
                                        ->get();
                                }
                            }
                            // fallback: pakai keanggotaan terbaru jika tidak ada di periode aktif
                            if (!$kelompokSaya) {
                                $kelompokSaya = $mhs->kelompoks()->latest('kelompok_mahasiswa.created_at')->first();
                                if ($kelompokSaya) {
                                    $anggotaKelompok = $kelompokSaya->mahasiswas()->with('user')->get();
                                }
                            }
                        }
                    @endphp

                    <div class="row mb-3">
                        <div class="col-lg-4 mb-3">
                            <div class="card border-left-primary shadow h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-calendar-check fa-2x text-primary mr-3"></i>
                                        <div>
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Periode Aktif</div>
                                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                                {{ $labelPeriode ?? 'Tidak ada periode aktif' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8 mb-3">
                            <div class="card shadow h-100">
                                @if($kelompokSaya)
                                <div class="card-header py-3 d-flex align-items-right justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-users mr-2"></i>{{ $kelompokSaya->nama_kelompok }} </h6>                                    
                                       <a href="{{ $kelompokSaya->link_drive }}" target="blank" class="badge badge-secondary" title="Klik untuk copy"><i class="fas fa-copy mr-2"></i>Drive Kelompok</a>
                                </div>
                                @endif
                                <div class="card-body p-2">
                                    @if($kelompokSaya)
                                        <ul class="list-group list-group-flush">
                                            @forelse($anggotaKelompok as $am)
                                                <li class="list-group-item d-flex align-items-center">
                                                    <img
                                                        src="{{ ($am->user && $am->user->profile_photo_data_url) ? $am->user->profile_photo_data_url : asset('sbadmin2/img/undraw_profile.svg') }}"
                                                        alt="Foto {{ $am->nama_mahasiswa }}"
                                                        class="rounded-circle mr-2"
                                                        style="width:32px;height:32px;object-fit:cover;"
                                                    >
                                                    <span>{{ $am->nama_mahasiswa }}</span>
                                                    @php $r = strtolower($am->pivot->role ?? ''); @endphp
                                                    @if($r === 'ketua')
                                                        <span class="badge badge-primary ml-auto">Ketua</span>
                                                    @endif
                                                </li>
                                            @empty
                                                <li class="list-group-item text-muted">Belum ada anggota.</li>
                                            @endforelse
                                        </ul>
                                    @else
                                        <div class="text-muted px-3 py-2">Belum tergabung dalam kelompok.</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- End Info Periode Aktif & Kelompok Saya --}}

                    {{-- Tabel Kunjungan Mitra --}}
                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-building mr-2"></i>Kunjungan Mitra Semua Kelompok (SEMUA DATA)
                                    </h6>
                                    <a href="{{ route('mahasiswa.kunjungan.index') }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-plus mr-1"></i>Tambah Kunjungan
                                    </a>
                                </div>
                                <div class="card-body">
                                    <!-- Search Form -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <form id="searchForm" class="d-flex">
                                                <input type="text" name="search" class="form-control me-2" placeholder="Cari kunjungan..." value="{{ request('search') }}">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-search"></i> Cari
                                                </button>
                                                @if(request('search'))
                                                    <a href="{{ request()->url() }}" class="btn btn-secondary ms-2">
                                                        <i class="fas fa-times"></i> Reset
                                                    </a>
                                                @endif
                                            </form>
                                        </div>
                                        <div class="col-md-6 text-end">
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle"></i>
                                                Menampilkan semua data kunjungan mitra ({{ \App\Models\KunjunganMitra::count() }} total data)
                                            </small>
                                        </div>
                                    </div>

                                    <!-- Loading indicator -->
                                    <div id="loadingIndicator" class="text-center py-4" style="display: none;">
                                        <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                                        <p class="mt-2">Memuat data...</p>
                                    </div>

                                    <!-- Table Container -->
                                    <div id="kunjunganTableContainer">
                                        @include('mahasiswa.partials.kunjungan_table', ['kunjungans' => $kunjungans ?? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10)])
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Content Row --}}
                </div>

    
                {{-- Modal Bukti Kunjungan --}}
                <div class="modal fade" id="buktiKunjunganModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Bukti Kunjungan</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body text-center">
                                <div id="buktiContent">
                                    <!-- Content akan dimuat melalui AJAX -->
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                <a id="downloadBukti" href="#" download class="btn btn-primary" style="display:none;">
                                    <i class="fas fa-download mr-1"></i>Download Bukti
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                @push('scripts')
                <script>
                $(document).ready(function() {
                    // Handle search form submission
                    $('#searchForm').on('submit', function(e) {
                        e.preventDefault();
                        loadKunjunganData();
                    });

                    // Handle pagination links
                    $(document).on('click', '.pagination a', function(e) {
                        e.preventDefault();
                        var url = $(this).attr('href');
                        loadKunjunganData(url);
                    });

                    // Load initial data
                    loadKunjunganData();
                });

                function loadKunjunganData(url) {
                    url = url || '{{ route("mahasiswa.kunjungan.data") }}';

                    // Get search value
                    var searchValue = $('input[name="search"]').val();

                    // Add search parameter to URL
                    if (searchValue) {
                        var separator = url.includes('?') ? '&' : '?';
                        url += separator + 'search=' + encodeURIComponent(searchValue);
                    }

                    // Show loading
                    $('#loadingIndicator').show();
                    $('#kunjunganTableContainer').hide();

                    // Load data via AJAX
                    $.get(url, function(response) {
                        if (response.error) {
                            $('#kunjunganTableContainer').html(
                                '<div class="alert alert-danger">' + response.message + '</div>'
                            );
                        } else {
                            $('#kunjunganTableContainer').html(response);
                        }
                    })
                    .fail(function(xhr) {
                        $('#kunjunganTableContainer').html(
                            '<div class="alert alert-danger">' +
                            '<i class="fas fa-exclamation-triangle"></i> ' +
                            'Gagal memuat data. Silakan refresh halaman.' +
                            '</div>'
                        );
                    })
                    .always(function() {
                        $('#loadingIndicator').hide();
                        $('#kunjunganTableContainer').show();
                    });
                }

                function showBukti(kunjunganId) {
                    // Loading state
                    $('#buktiContent').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Memuat bukti...</div>');
                    $('#downloadBukti').hide();
                    $('#buktiKunjunganModal').modal('show');

                    // Fetch bukti via AJAX
                    $.get(`/mahasiswa/kunjungan/${kunjunganId}/bukti`, function(response) {
                        if(response.success && response.bukti_data_url) {
                            let html = `
                                <div class="mb-3">
                                    <h6 class="text-primary">Bukti Kunjungan</h6>
                                    <p class="text-muted">Perusahaan: ${response.perusahaan || '-'}</p>
                                </div>
                                <img src="${response.bukti_data_url}" alt="Bukti Kunjungan" class="img-fluid" style="max-height: 500px;">
                                <div class="mt-3">
                                    <small class="text-muted">Format: ${response.mime_type || '-'}</small>
                                </div>
                            `;
                            $('#buktiContent').html(html);

                            // Setup download link
                            if(response.bukti_data_url) {
                                $('#downloadBukti').attr('href', response.bukti_data_url);
                                $('#downloadBukti').attr('download', `bukti_kunjungan_${kunjunganId}.jpg`);
                                $('#downloadBukti').show();
                            }
                        } else {
                            $('#buktiContent').html('<div class="alert alert-warning">Bukti kunjungan tidak tersedia</div>');
                        }
                    }).fail(function() {
                        $('#buktiContent').html('<div class="alert alert-danger">Terjadi kesalahan saat memuat bukti</div>');
                    });
                }
                </script>
                @endpush
                @endsection
