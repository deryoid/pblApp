@extends('layout.app')

@section('content')
<div class="container-fluid">
  <div class="d-flex align-items-center mb-3">
    <h1 class="h3 text-gray-800 mb-0">Pengaturan Persentase Penilaian</h1>
  </div>

  <div class="row">
    <div class="col-lg-8 col-xl-7">
      <form method="post" action="{{ route('admin.evaluasi.settings.save') }}" class="card shadow-sm mb-4">
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
              <label>Efisiensi Waktu dan Biaya</label>
              <input type="number" class="form-control" name="d_efisiensi" min="0" max="100" value="{{ $settings['d_efisiensi'] ?? 10 }}">
            </div>
            <div class="form-group col-sm-6">
              <label>Dokumentasi dan Profesionalisme</label>
              <input type="number" class="form-control" name="d_dokpro" min="0" max="100" value="{{ $settings['d_dokpro'] ?? 15 }}">
            </div>
            <div class="form-group col-sm-6">
              <label>Kemandirian dan Inisiatif</label>
              <input type="number" class="form-control" name="d_inisiatif" min="0" max="100" value="{{ $settings['d_inisiatif'] ?? 10 }}">
            </div>
          </div>

          <hr>
          <h6 class="text-muted mb-3">Indikator Mitra (jumlah = 100%)</h6>
          <div class="form-row">
            <div class="form-group col-sm-6">
              <label>Komunikasi dan Sikap di Lapangan</label>
              <input type="number" class="form-control" name="m_kehadiran" min="0" max="100" value="{{ $settings['m_kehadiran'] ?? 50 }}">
            </div>
            <div class="form-group col-sm-6">
              <label>Hasil Pekerjaan</label>
              <input type="number" class="form-control" name="m_presentasi" min="0" max="100" value="{{ $settings['m_presentasi'] ?? 50 }}">
            </div>
          </div>

          <hr>
          <h6 class="text-muted mb-3">Komponen AP (jumlah = 100%)</h6>
          <div class="form-row">
            <div class="form-group col-sm-6">
              <label>AP: Kehadiran</label>
              <input type="number" class="form-control" name="w_ap_kehadiran" min="0" max="100" value="{{ $settings['w_ap_kehadiran'] ?? 50 }}">
            </div>
            <div class="form-group col-sm-6">
              <label>AP: Presentasi</label>
              <input type="number" class="form-control" name="w_ap_presentasi" min="0" max="100" value="{{ $settings['w_ap_presentasi'] ?? 50 }}">
            </div>
          </div>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
          <div class="small text-muted" id="js-hints"></div>
          <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
        </div>
      </form>
    </div>
    <div class="col-lg-4 col-xl-5">
      {{-- Petunjuk --}}
      <div class="card shadow-sm mb-4">
        <div class="card-body">
          <h6 class="text-muted mb-3">Petunjuk</h6>
          <p class="small">Halaman ini digunakan untuk mengatur persentase penilaian evaluasi kinerja mahasiswa pada kegiatan PBL.</p>
          <ul class="small pl-3">
            <li>Pastikan setiap kelompok indikator (Dosen, Mitra, AP, Bobot) berjumlah 100%.</li>
            <li>Indikator Dosen terdiri dari 6 aspek penilaian.</li>
            <li>Indikator Mitra terdiri dari 2 aspek penilaian.</li>
            <li>AP (Aktifitas Partisipatif) terdiri dari kehadiran dan presentasi.</li>
            <li>Bobot Agregat menentukan kontribusi penilaian Dosen dan Mitra terhadap nilai akhir.</li>
            <li>Setelah mengubah pengaturan, klik "Simpan Pengaturan".</li>
            <li>Lihat panel “Review dari Database” di bawah untuk nilai yang tersimpan.</li>
          </ul>
        </div>
      </div>

      {{-- Review dari Database --}}
      <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span>Review dari Database</span>
          <span class="small text-muted">tersimpan</span>
        </div>
        <div class="card-body small">
          @php
            $get = fn($k,$def=0)=> (int)($settings[$k] ?? $def);

            $sumDosen = $get('d_hasil') + $get('d_teknis') + $get('d_user') + $get('d_efisiensi') + $get('d_dokpro') + $get('d_inisiatif');
            $sumMitra = $get('m_kehadiran') + $get('m_presentasi');
            $sumAP    = $get('w_ap_kehadiran') + $get('w_ap_presentasi');
            $sumDM    = $get('w_dosen') + $get('w_mitra');
            $sumGA    = $get('w_kelompok') + $get('w_ap');

            $badge = fn($ok)=> $ok ? 'badge-success' : 'badge-danger';
          @endphp

          {{-- Dosen --}}
          <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center">
              <strong>Indikator Dosen</strong>
              <span class="badge {{ $badge($sumDosen===100) }}">{{ $sumDosen }}%</span>
            </div>
            <ul class="list-unstyled mb-1 mt-2">
              <li>Kualitas Hasil Proyek <span class="float-right">{{ $get('d_hasil') }}%</span></li>
              <li>Kompleksitas Teknis <span class="float-right">{{ $get('d_teknis') }}%</span></li>
              <li>Kesesuaian Pengguna <span class="float-right">{{ $get('d_user') }}%</span></li>
              <li>Efisiensi Waktu/Biaya <span class="float-right">{{ $get('d_efisiensi') }}%</span></li>
              <li>Dokumentasi & Profesionalisme <span class="float-right">{{ $get('d_dokpro') }}%</span></li>
              <li>Kemandirian & Inisiatif <span class="float-right">{{ $get('d_inisiatif') }}%</span></li>
            </ul>
          </div>

          {{-- Mitra --}}
          <div class="mb-3 border-top pt-2">
            <div class="d-flex justify-content-between align-items-center">
              <strong>Indikator Mitra</strong>
              <span class="badge {{ $badge($sumMitra===100) }}">{{ $sumMitra }}%</span>
            </div>
            <ul class="list-unstyled mb-1 mt-2">
              <li>Komunikasi & Sikap <span class="float-right">{{ $get('m_kehadiran') }}%</span></li>
              <li>Hasil Pekerjaan <span class="float-right">{{ $get('m_presentasi') }}%</span></li>
            </ul>
          </div>

          {{-- AP --}}
          <div class="mb-3 border-top pt-2">
            <div class="d-flex justify-content-between align-items-center">
              <strong>Komponen AP</strong>
              <span class="badge {{ $badge($sumAP===100) }}">{{ $sumAP }}%</span>
            </div>
            <ul class="list-unstyled mb-1 mt-2">
              <li>Kehadiran <span class="float-right">{{ $get('w_ap_kehadiran') }}%</span></li>
              <li>Presentasi <span class="float-right">{{ $get('w_ap_presentasi') }}%</span></li>
            </ul>
          </div>

          {{-- Agregat --}}
          <div class="mb-1 border-top pt-2">
            <div class="d-flex justify-content-between align-items-center">
              <strong>Bobot Agregat</strong>
            </div>
            <ul class="list-unstyled mb-1 mt-2">
              <li>Dosen + Mitra
                <span class="float-right">
                  {{ $get('w_dosen') }}% + {{ $get('w_mitra') }}%
                  <span class="badge ml-2 {{ $badge($sumDM===100) }}">{{ $sumDM }}%</span>
                </span>
              </li>
              <li>Hasil Proyek + AP
                <span class="float-right">
                  {{ $get('w_kelompok') }}% + {{ $get('w_ap') }}%
                  <span class="badge ml-2 {{ $badge($sumGA===100) }}">{{ $sumGA }}%</span>
                </span>
              </li>
            </ul>
          </div>

          {{-- (Opsional) Nilai Partisipatif Dinamis dari tabel nilai_partisipatif --}}
          @isset($partItems)
            @php $sumPart = (int) $partItems->sum('value'); @endphp
            <div class="border-top pt-2 mt-2">
              <div class="d-flex justify-content-between align-items-center">
                <strong>Nilai Partisipatif (dinamis)</strong>
                <span class="badge {{ $badge($sumPart===100) }}">{{ $sumPart }}%</span>
              </div>
              <ul class="list-unstyled mb-0 mt-2">
                @forelse($partItems as $pi)
                  <li>{{ strtoupper($pi->key) }} <span class="float-right">{{ (int)$pi->value }}%</span></li>
                @empty
                  <li class="text-muted">Belum ada item.</li>
                @endforelse
              </ul>
            </div>
          @endisset
        </div>
      </div>
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function(){
    const $ = (sel) => document.querySelector(sel);
    const val = (name) => parseInt((document.querySelector(`[name="${name}"]`)?.value || '0'), 10) || 0;
    const hints = $('#js-hints');
    function calc(){
      const sumD = val('d_hasil')+val('d_teknis')+val('d_user')+val('d_efisiensi')+val('d_dokpro')+val('d_inisiatif');
      const sumM = val('m_kehadiran')+val('m_presentasi');
      const sumPA= val('w_ap_kehadiran')+val('w_ap_presentasi');
      const sumPM= val('w_dosen')+val('w_mitra');
      const sumGA= val('w_kelompok')+val('w_ap');
      const notes = [];
      if (sumD !== 100) notes.push(`Indikator Dosen = ${sumD}%`);
      if (sumM !== 100) notes.push(`Indikator Mitra = ${sumM}%`);
      if (sumPA !== 100) notes.push(`AP (Kehadiran+Presentasi) = ${sumPA}%`);
      if (sumPM !== 100) notes.push(`Bobot Dosen+Mitra = ${sumPM}%`);
      if (sumGA !== 100) notes.push(`Proyek+AP = ${sumGA}%`);
      hints.textContent = notes.length ? ('Periksa penjumlahan: ' + notes.join(' � ')) : 'Semua penjumlahan = 100%';
      hints.className = 'small ' + (notes.length ? 'text-danger' : 'text-success');
    }
    document.querySelectorAll('input[type=number]').forEach(el => el.addEventListener('input', calc));
    calc();
  });
</script>
@endpush
