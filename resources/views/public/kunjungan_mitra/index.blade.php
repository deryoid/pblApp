@extends('layout.public')

@section('title', 'Data Kunjungan Mitra - SIMEP Politala')

@section('content')
<div class="container-fluid px-0">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-900 font-weight-bold">
                <i class="fas fa-building text-primary mr-2" aria-hidden="true"></i>
                Data Kunjungan Mitra Semua Kelompok
            </h1>
            <p class="text-muted small mb-0">
                <i class="fas fa-info-circle mr-1"></i>
                Menampilkan log aktivitas dan dokumentasi verifikasi kunjungan lapangan mahasiswa ke mitra industri
            </p>
        </div>
        <div class="mt-3 mt-sm-0">
            <a href="{{ route('home') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-th-large mr-1"></i> Showcase Proyek SPA
            </a>
        </div>
    </div>

    <!-- Kunjungan Mitra Card -->
    <div class="card shadow-sm border-0 rounded-xl overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-dark">
                <i class="fas fa-list text-primary mr-2"></i>Daftar Riwayat Kunjungan Industri
            </h6>
        </div>
        <div class="card-body p-4">
            <!-- Search Form -->
            <div class="row mb-4">
                <div class="col-lg-6 col-md-8">
                    <form id="searchForm" class="d-flex align-items-stretch gap-2">
                        <div class="input-group flex-grow-1">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                            </div>
                            <input type="text" name="search" class="form-control border-left-0" placeholder="Cari perusahaan, kelompok, atau topik..." value="{{ request('search') }}">
                        </div>
                        <button type="submit" class="btn btn-primary ml-2 px-3 flex-shrink-0">
                            <i class="fas fa-search mr-1 d-none d-sm-inline"></i>Cari
                        </button>
                        @if(request('search'))
                            <a href="{{ request()->url() }}" class="btn btn-outline-secondary ml-1 flex-shrink-0" title="Reset Pencarian">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Loading indicator -->
            <div id="loadingIndicator" class="text-center py-5" style="display: none;">
                <i class="fas fa-spinner fa-spin fa-2x text-primary mb-2"></i>
                <p class="text-muted small mb-0">Memuat data kunjungan mitra...</p>
            </div>

            <!-- Table Container -->
            <div id="kunjunganTableContainer">
                <!-- Data akan dimuat via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Modal Bukti Kunjungan -->
<div class="modal fade" id="buktiKunjunganModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg rounded-xl overflow-hidden">
            <div class="modal-header bg-slate text-white py-3 px-4" style="background:#0f172a; color:#ffffff;">
                <h5 class="modal-title font-weight-bold" style="font-size: 1.1rem;">
                    <i class="fas fa-camera mr-2 text-primary"></i>Bukti Dokumentasi Kunjungan
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.8;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center p-4">
                <div id="buktiContent">
                    <!-- Content akan dimuat melalui AJAX -->
                </div>
            </div>
            <div class="modal-footer bg-light px-4 py-3">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Tutup</button>
                <a id="downloadBukti" href="#" download class="btn btn-primary btn-sm" style="display:none;">
                    <i class="fas fa-download mr-1"></i>Unduh Bukti
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
    url = url || '{{ route("public.kunjungan.data") }}';

    var searchValue = $('input[name="search"]').val();
    if (searchValue) {
        var separator = url.includes('?') ? '&' : '?';
        url += separator + 'search=' + encodeURIComponent(searchValue);
    }

    $('#loadingIndicator').show();
    $('#kunjunganTableContainer').hide();

    $.get(url, function(response) {
        if (response.error) {
            $('#kunjunganTableContainer').html(
                '<div class="alert alert-danger">' + response.message + '</div>'
            );
        } else {
            $('#kunjunganTableContainer').html(response);
        }
    })
    .fail(function() {
        $('#kunjunganTableContainer').html(
            '<div class="alert alert-danger">' +
            '<i class="fas fa-exclamation-triangle mr-2"></i>' +
            'Gagal memuat data kunjungan. Silakan refresh halaman.' +
            '</div>'
        );
    })
    .always(function() {
        $('#loadingIndicator').hide();
        $('#kunjunganTableContainer').show();
    });
}

function showBukti(kunjunganId) {
    $('#buktiContent').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary mb-2"></i><p class="text-muted small">Memuat dokumen...</p></div>');
    $('#downloadBukti').hide();
    $('#buktiKunjunganModal').modal('show');

    $.get(`/kunjungan-mitra/${kunjunganId}/bukti`, function(response) {
        if(response.success && response.bukti_data_url) {
            let html = `
                <div class="mb-3 text-left">
                    <h6 class="font-weight-bold text-dark mb-1">Perusahaan / Instansi:</h6>
                    <p class="text-primary font-weight-600 mb-0">${response.perusahaan || '-'}</p>
                </div>
                <div class="p-2 border rounded-lg bg-light d-inline-block">
                    <img src="${response.bukti_data_url}" alt="Bukti Kunjungan" class="img-fluid rounded" style="max-height: 480px;">
                </div>
                <div class="mt-3">
                    <span class="badge badge-info">${response.mime_type || 'image/jpeg'}</span>
                </div>
            `;
            $('#buktiContent').html(html);

            if(response.bukti_data_url) {
                $('#downloadBukti').attr('href', response.bukti_data_url);
                $('#downloadBukti').attr('download', `bukti_kunjungan_${kunjunganId}.jpg`);
                $('#downloadBukti').show();
            }
        } else {
            $('#buktiContent').html('<div class="alert alert-warning mb-0"><i class="fas fa-exclamation-circle mr-2"></i>Bukti dokumentasi kunjungan tidak tersedia untuk log ini.</div>');
        }
    }).fail(function() {
        $('#buktiContent').html('<div class="alert alert-danger mb-0"><i class="fas fa-times-circle mr-2"></i>Terjadi kesalahan saat memuat berkas bukti.</div>');
    });
}
</script>
@endpush
@endsection
