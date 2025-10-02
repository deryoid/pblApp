@extends('layout.app')

@section('content')
<div class="container-fluid">
  <div class="d-flex align-items-center mb-3">
    <h1 class="h3 text-gray-800 mb-0">Pengaturan Persentase Penilaian</h1>
    <a href="{{ route('evaluator.evaluasi.index') }}" class="btn btn-secondary btn-sm ml-auto">
      <i class="fas fa-arrow-left"></i> Kembali
    </a>
  </div>

  <div class="row">
    <div class="col-lg-8 col-xl-7">
      <form method="post" action="{{ route('evaluator.evaluasi.settings.save') }}" class="card shadow-sm mb-4">
        @csrf
        <div class="card-body">
          <h6 class="text-muted mb-3">Bobot Agregat</h6>
          <div class="form-row">
            <div class="form-group col-6">
              <label>Bobot Dosen (%)</label>
              <input type="number" class="form-control" name="w_dosen" min="0" max="100" value="{{ $settings['w_dosen'] ?? 80 }}">
            </div>
            <div class="form-group col-6">
              <label>Bobot Mitra (%)</label>
              <input type="number" class="form-control" name="w_mitra" min="0" max="100" value="{{ $settings['w_mitra'] ?? 20 }}">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group col-6">
              <label>Bobot Hasil Proyek (%)</label>
              <input type="number" class="form-control" name="w_kelompok" min="0" max="100" value="{{ $settings['w_kelompok'] ?? 70 }}">
            </div>
            <div class="form-group col-6">
              <label>Bobot Aktifitas Partisipatif (%)</label>
              <input type="number" class="form-control" name="w_ap" min="0" max="100" value="{{ $settings['w_ap'] ?? 30 }}">
            </div>
          </div>

          <hr>
          <h6 class="text-muted mb-3">Indikator Dosen (jumlah = 100%)</h6>
          <div class="form-row">
            <div class="form-group col-sm-6">
              <label>Kualitas Hasil Proyek</label>
              <input type="number" class="form-control" name="d_hasil" min="0" max="100" value="{{ $settings['d_hasil'] ?? 30 }}">
            </div>
            <div class="form-group col-sm-6">
              <label>Tingkat Kompleksitas Teknis</label>
              <input type="number" class="form-control" name="d_teknis" min="0" max="100" value="{{ $settings['d_teknis'] ?? 20 }}">
            </div>
            <div class="form-group col-sm-6">
              <label>Kesesuaian dengan Kebutuhan Pengguna</label>
              <input type="number" class="form-control" name="d_user" min="0" max="100" value="{{ $settings['d_user'] ?? 15 }}">
            </div>
            <div class="form-group col-sm-6">
              <label>Proses Evaluasi</label>
              <input type="number" class="form-control" name="d_efisiensi" min="0" max="100" value="{{ $settings['d_efisiensi'] ?? 10 }}">
            </div>
            <div class="form-group col-sm-6">
              <label>Format Laporan</label>
              <input type="number" class="form-control" name="d_dokpro" min="0" max="100" value="{{ $settings['d_dokpro'] ?? 15 }}">
            </div>
            <div class="form-group col-sm-6">
              <label>Inisiatif & Inovasi</label>
              <input type="number" class="form-control" name="d_inisiatif" min="0" max="100" value="{{ $settings['d_inisiatif'] ?? 10 }}">
            </div>
          </div>

          <hr>
          <h6 class="text-muted mb-3">Indikator Mitra (jumlah = 100%)</h6>
          <div class="form-row">
            <div class="form-group col-6">
              <label>Kehadiran & Disiplin</label>
              <input type="number" class="form-control" name="m_kehadiran" min="0" max="100" value="{{ $settings['m_kehadiran'] ?? 50 }}">
            </div>
            <div class="form-group col-6">
              <label>Kualitas Presentasi</label>
              <input type="number" class="form-control" name="m_presentasi" min="0" max="100" value="{{ $settings['m_presentasi'] ?? 50 }}">
            </div>
          </div>

          <hr>
          <h6 class="text-muted mb-3">Aktifitas Partisipatif (jumlah = 100%)</h6>
          <div class="form-row">
            <div class="form-group col-6">
              <label>Kehadiran</label>
              <input type="number" class="form-control" name="w_ap_kehadiran" min="0" max="100" value="{{ $settings['w_ap_kehadiran'] ?? 50 }}">
            </div>
            <div class="form-group col-6">
              <label>Presentasi</label>
              <input type="number" class="form-control" name="w_ap_presentasi" min="0" max="100" value="{{ $settings['w_ap_presentasi'] ?? 50 }}">
            </div>
          </div>

          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Simpan Pengaturan
          </button>
        </div>
      </form>
    </div>

    <div class="col-lg-4 col-xl-5">
      <div class="card shadow-sm mb-4">
        <div class="card-body">
          <h6 class="text-muted mb-3">Rumus Perhitungan</h6>

          <div class="mb-4">
            <strong>Nilai Final Mahasiswa:</strong>
            <div class="mt-2 p-3 bg-light rounded">
              <p class="mb-1">Final = (Hasil Proyek × {{ $settings['w_kelompok'] ?? 70 }}%) + (AP × {{ $settings['w_ap'] ?? 30 }}%)</p>
            </div>
          </div>

          <div class="mb-4">
            <strong>Hasil Proyek:</strong>
            <div class="mt-2 p-3 bg-light rounded">
              <p class="mb-1">Proyek = (Nilai Dosen × {{ $settings['w_dosen'] ?? 80 }}%) + (Nilai Mitra × {{ $settings['w_mitra'] ?? 20 }}%)</p>
            </div>
          </div>

          <div class="mb-4">
            <strong>Aktifitas Partisipatif:</strong>
            <div class="mt-2 p-3 bg-light rounded">
              <p class="mb-1">AP = (Kehadiran × {{ $settings['w_ap_kehadiran'] ?? 50 }}%) + (Presentasi × {{ $settings['w_ap_presentasi'] ?? 50 }}%)</p>
            </div>
          </div>

          <div class="mb-3">
            <strong>Grade Nilai:</strong>
            <div class="mt-2 p-3 bg-light rounded">
              <p class="mb-1">A : 85 - 100</p>
              <p class="mb-1">B : 75 - 84</p>
              <p class="mb-1">C : 65 - 74</p>
              <p class="mb-1">D : 55 - 64</p>
              <p class="mb-1">E : 0 - 54</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-calculate totals
    function calculateTotals() {
        // Dosen indicators
        var dHasil = parseInt($('input[name="d_hasil"]').val()) || 0;
        var dTeknis = parseInt($('input[name="d_teknis"]').val()) || 0;
        var dUser = parseInt($('input[name="d_user"]').val()) || 0;
        var dEfisiensi = parseInt($('input[name="d_efisiensi"]').val()) || 0;
        var dDokpro = parseInt($('input[name="d_dokpro"]').val()) || 0;
        var dInisiatif = parseInt($('input[name="d_inisiatif"]').val()) || 0;
        var dTotal = dHasil + dTeknis + dUser + dEfisiensi + dDokpro + dInisiatif;

        // Mitra indicators
        var mKehadiran = parseInt($('input[name="m_kehadiran"]').val()) || 0;
        var mPresentasi = parseInt($('input[name="m_presentasi"]').val()) || 0;
        var mTotal = mKehadiran + mPresentasi;

        // AP indicators
        var wApKehadiran = parseInt($('input[name="w_ap_kehadiran"]').val()) || 0;
        var wApPresentasi = parseInt($('input[name="w_ap_presentasi"]').val()) || 0;
        var wApTotal = wApKehadiran + wApPresentasi;

        // Aggregate weights
        var wKelompok = parseInt($('input[name="w_kelompok"]').val()) || 0;
        var wAp = parseInt($('input[name="w_ap"]').val()) || 0;
        var aggregateTotal = wKelompok + wAp;

        // Update validation messages
        $('.validation-message').remove();

        if (dTotal !== 100) {
            $('input[name="d_inisiatif"]').after('<div class="validation-message text-danger small">Total: ' + dTotal + '% (harus 100%)</div>');
        }

        if (mTotal !== 100) {
            $('input[name="m_presentasi"]').after('<div class="validation-message text-danger small">Total: ' + mTotal + '% (harus 100%)</div>');
        }

        if (wApTotal !== 100) {
            $('input[name="w_ap_presentasi"]').after('<div class="validation-message text-danger small">Total: ' + wApTotal + '% (harus 100%)</div>');
        }

        if (aggregateTotal !== 100) {
            $('input[name="w_ap"]').after('<div class="validation-message text-danger small">Total: ' + aggregateTotal + '% (harus 100%)</div>');
        }
    }

    // Calculate on load and change
    calculateTotals();
    $('input[type="number"]').on('input change', calculateTotals);
});
</script>
@endpush
@endsection