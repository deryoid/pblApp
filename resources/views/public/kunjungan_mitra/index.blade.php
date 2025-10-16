@extends('layout.public')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-building text-primary mr-2" aria-hidden="true"></i>
            Data Kunjungan Mitra Semua Kelompok
        </h1>
        <small class="text-muted">
            <i class="fas fa-info-circle mr-1"></i>
            Menampilkan semua data kunjungan mitra dari semua kelompok
        </small>
    </div>

    <!-- Kunjungan Mitra Card -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list mr-2"></i>Daftar Kunjungan Mitra
            </h6>
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
                        <i class="fas fa-building mr-1"></i>
                        Total kunjungan dari semua kelompok
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
                <!-- Data akan dimuat via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Modal Bukti Kunjungan -->
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
    url = url || '{{ route("public.kunjungan.data") }}';

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
    $.get(`/kunjungan-mitra/${kunjunganId}/bukti`, function(response) {
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