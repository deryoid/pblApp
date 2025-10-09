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
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover" id="kunjunganMitraTable" style="width: 100%;">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Tanggal</th>
                                                    <th>Periode</th>
                                                    <th>Kelompok</th>
                                                    <th>Perusahaan</th>
                                                    <th>Alamat</th>
                                                    <th>Status</th>
                                                    <th>Diinput Oleh</th>
                                                    <th>Bukti</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Data akan dimuat melalui AJAX -->
                                            </tbody>
                                        </table>
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
                    // Initialize DataTable
                    var table = $('#kunjunganMitraTable').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: '{{ route("mahasiswa.kunjungan.data") }}',
                            type: 'GET',
                            error: function(xhr, error, thrown) {
                                console.error('DataTables AJAX Error:', {
                                    status: xhr.status,
                                    error: error,
                                    thrown: thrown,
                                    response: xhr.responseText
                                });
                                $('#kunjunganMitraTable tbody').html(
                                    '<tr><td colspan="8" class="text-center text-danger">' +
                                    '<i class="fas fa-exclamation-triangle"></i> ' +
                                    'Gagal memuat data. Silakan refresh halaman.' +
                                    '</td></tr>'
                                );
                            }
                        },
                        columns: [
                            { data: 'tanggal_kunjungan', name: 'tanggal_kunjungan' },
                            { data: 'periode_nama', name: 'p.periode' },
                            { data: 'kelompok_nama', name: 'k.nama_kelompok' },
                            { data: 'perusahaan', name: 'perusahaan' },
                            { data: 'alamat', name: 'alamat' },
                            { data: 'status_kunjungan', name: 'status_kunjungan' },
                            { data: 'diinput_oleh', name: 'u.nama_user' },
                            { data: 'bukti', name: 'bukti_kunjungan', orderable: false, searchable: false }
                        ],
                        order: [[0, 'desc']], // Sort by date descending by default
                        pageLength: 25,
                        responsive: true,
                        language: {
                            "processing": "Sedang memuat semua data kunjungan...",
                            "lengthMenu": "Tampilkan _MENU_ data per halaman",
                            "zeroRecords": "Tidak ada data kunjungan mitra di database",
                            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ total kunjungan (SEMUA DATA)",
                            "infoEmpty": "Tidak ada data kunjungan di database",
                            "infoFiltered": "(difilter dari _MAX_ total data)",
                            "search": "Cari di semua data:",
                            "paginate": {
                                "first": "Pertama",
                                "last": "Terakhir",
                                "next": "Selanjutnya",
                                "previous": "Sebelumnya"
                            }
                        }
                    });
                });

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
