@extends('layout.app')
@section('content')
@push('styles')
<style>
  .metric-card{border:1px solid #eef1f5;border-radius:.75rem;padding:1rem}
  .metric-value{font-size:1.35rem;font-weight:700}
  .small-muted{font-size:.85rem;color:#6c757d}
  .table td{vertical-align:middle}
  .truncate{max-width:240px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .chip{display:inline-flex;align-items:center;border:1px solid #eef1f5;border-radius:999px;padding:.15rem .5rem;font-size:.75rem}
  .chip-success{background:#ecfdf5;color:#047857;border-color:#d1fae5}
  .chip-warning{background:#fff7ed;color:#b45309;border-color:#ffedd5}
  .chip-info{background:#eff6ff;color:#1d4ed8;border-color:#dbeafe}
  .chip-secondary{background:#f3f4f6;color:#374151;border-color:#e5e7eb}
  .chip-danger{background:#fef2f2;color:#b91c1c;border-color:#fee2e2}
  .bg-ap{background:#d9ead3}
  .bg-hp{background:#d9ead3}
  .bg-dosen{background:#cfe2f3}
  .bg-mitra{background:#cfe2f3}
  .bg-na{background:#234d20;color:#fff}
  .bg-na a, .bg-na small{color:#fff}
</style>
@endpush

@php
  // --------- Data & helper dari DB ---------
  // $kelompok, $periode, $sesi, $evaluator, $anggota, $settings dikirim dari controller

  // Normalisasi anggota -> objek {id,nim,nama}
  $members = collect($anggota ?? [])->map(function($m){
      $arr = is_array($m) ? (object)$m : $m;
      return (object)[
        'id'   => $arr->id   ?? ($arr->mahasiswa_id ?? null),
        'nim'  => $arr->nim  ?? null,
        'nama' => $arr->nama ?? ($arr->nama_mahasiswa ?? null),
      ];
  });

  // Relasi yang sudah di-eager-load di controller:
  $absensis   = collect($sesi->absensis ?? []);
  $sesiIndis  = collect($sesi->sesiIndikators ?? []);   // tiap item punya ->indikator (kode,nama), ->bobot, ->skor (set dosen)
  $nilaiDetil = collect($sesi->nilaiDetails ?? []);     // nilai per-mahasiswa per-indikator

  // Peta absensi per mahasiswa
  $absByMhs   = $absensis->keyBy('mahasiswa_id');

  // Peta nilai per mahasiswa
  $nilaiByMhs = $nilaiDetil->groupBy('mahasiswa_id');

  // Peta indikator sesi: kode => [id,bobot,skor,nama]
  $byKode = $sesiIndis->mapWithKeys(function($si){
    $kode = optional($si->indikator)->kode;
    return $kode ? [
      $kode => [
        'id'    => $si->indikator_id,
        'bobot' => (int)$si->bobot,
        'skor'  => (int)($si->skor ?? 0),      // skor set DOSEN per-indikator (di sesi)
        'nama'  => $si->indikator->nama ?? $kode,
      ]
    ] : [];
  });

  // Settings
  $wDosen          = (int)($settings['w_dosen']          ?? 80);
  $wMitra          = (int)($settings['w_mitra']          ?? 20);
  $wKelompok       = (int)($settings['w_kelompok']       ?? 70);
  $wAP             = (int)($settings['w_ap']             ?? 30);
  $wAP_Kehadiran   = (int)($settings['w_ap_kehadiran']   ?? 50);
  $wAP_Presentasi  = (int)($settings['w_ap_presentasi']  ?? 50);

  // Ambil id indikator yg dipakai untuk AP/Mitra
  $idKehadiran   = $byKode['m_kehadiran']['id']   ?? null;
  $idPresentasi  = $byKode['m_presentasi']['id']  ?? null;

  // Hitung rekap ringkas (hadir = Hadir/Terlambat dari evaluasi_absensi)
  $hadirCount = $members->filter(function($m) use ($absByMhs){
      $st = optional($absByMhs->get($m->id))->status;
      return in_array($st, ['Hadir','Terlambat'], true);
  })->count();
  $total = $members->count();

  // Skor AP & Mitra per mahasiswa (diambil dari evaluasi_nilai_detail untuk m_kehadiran/m_presentasi)
  $apScores = [];       // AP (w_ap_kehadiran/presentasi)
  $mitraScores = [];    // Mitra (w_mitra: komposisi di luar)

  foreach ($members as $m) {
      $rows = $nilaiByMhs->get($m->id) ?? collect();

      $skKeh = $rows->firstWhere('indikator_id', $idKehadiran)->skor ?? 0;
      $skPrs = $rows->firstWhere('indikator_id', $idPresentasi)->skor ?? 0;

      $apScores[$m->id]     = (int) round($skKeh * $wAP_Kehadiran/100 + $skPrs * $wAP_Presentasi/100);
      // Mitra-score per mahasiswa (pakai 50/50 default; bisa juga pakai pengaturan yang sama)
      $mitraScores[$m->id]  = (int) round(($skKeh + $skPrs) / 2);
  }

  // Skor set DOSEN (kelompok) = sum(skor * bobot)/100 dari evaluasi_sesi_indikator
  $skorDosen = (int) round($sesiIndis->reduce(function($carry, $si){
      $sk = (int)($si->skor ?? 0);   $bb = (int)($si->bobot ?? 0);
      return $carry + ($sk * $bb);
  }, 0) / 100);

  // Nilai Proyek per Mahasiswa = Dosen*wDosen + Mitra*wMitra
  $nilaiProyekPerMhs = [];
  foreach ($members as $m) {
      $nilaiProyekPerMhs[$m->id] = (int) round($skorDosen * $wDosen/100 + ($mitraScores[$m->id] ?? 0) * $wMitra/100);
  }
  $nilaiProyekKelompok = (int) round(collect($nilaiProyekPerMhs)->avg() ?? 0);

  // Nilai akhir per mahasiswa = Kelompok*wKelompok + AP*wAP
  $nilaiAkhirPerMhs = [];
  foreach ($members as $m) {
      $np = $nilaiProyekPerMhs[$m->id] ?? 0;
      $ap = $apScores[$m->id] ?? 0;
      $nilaiAkhirPerMhs[$m->id] = (int) round($np * $wKelompok/100 + $ap * $wAP/100);
  }

  // Rata-rata sederhana (dipakai di kartu "Nilai Rata-rata")
  $avgNilai = (int) round(collect($nilaiAkhirPerMhs)->avg() ?? 0);

  // Kategori label helper
  function kategoriLabel($n){
      if ($n>=81) return 'Sangat Baik';
      if ($n>=61) return 'Baik';
      if ($n>=41) return 'Cukup';
      if ($n>=21) return 'Kurang';
      return 'Sangat Kurang';
  }
@endphp

<div class="container-fluid">
  <div class="d-flex align-items-center mb-3 flex-wrap">
    <div class="mr-3">
      <h1 class="h3 text-gray-800 mb-0">Detail Evaluasi</h1>
      <div class="small-muted">
        <span class="mr-2"><i class="fas fa-users mr-1"></i>{{ $kelompok->nama_kelompok ?? '—' }}</span>
        <span class="badge badge-light border">{{ $periode->periode ?? '—' }}</span>
      </div>
    </div>
    <div class="ml-auto text-right">
      <div class="small-muted">Evaluator:
        <strong>{{ optional($evaluator)->name ?? optional($evaluator)->nama ?? '—' }}</strong>
      </div>
      <div class="small-muted">
        Jadwal:
        @if(!empty($sesi->jadwal_mulai))
          {{ \Carbon\Carbon::parse($sesi->jadwal_mulai)->locale('id')->translatedFormat('d M Y, H:i') }}
          @if(!empty($sesi->jadwal_selesai))
            &ndash; {{ \Carbon\Carbon::parse($sesi->jadwal_selesai)->locale('id')->translatedFormat('H:i') }}
          @endif
        @else
          —
        @endif
        @if(!empty($sesi->lokasi)) • {{ $sesi->lokasi }} @endif
      </div>
      <div class="mt-1">
        <span class="badge badge-warning">{{ $sesi->status ?? '—' }}</span>
      </div>
    </div>
  </div>

  {{-- Metrik ringkas --}}
  <div class="row mb-4">
    <div class="col-md-3 mb-2">
      <div class="metric-card">
        <div class="small-muted">Kehadiran</div>
        <div class="metric-value">{{ $hadirCount }}/{{ $total }}</div>
        <div class="small-muted">{{ (int) round($total ? ($hadirCount*100/$total) : 0) }}%</div>
      </div>
    </div>
    <div class="col-md-3 mb-2">
      <div class="metric-card">
        <div class="small-muted">Nilai Rata-rata</div>
        <div class="metric-value">{{ $avgNilai }}</div>
        <div class="small-muted">Rata-rata nilai akhir mahasiswa</div>
      </div>
    </div>
    <div class="col-md-3 mb-2">
      <div class="metric-card">
        <div class="small-muted">Nilai Proyek (Kelompok)</div>
        <div class="metric-value">{{ $nilaiProyekKelompok }}</div>
        <div class="small-muted">= Dosen×{{ $wDosen }}% + Mitra×{{ $wMitra }}%</div>
      </div>
    </div>
    <div class="col-md-3 mb-2">
      <div class="metric-card">
        <div class="small-muted">Kontribusi AP</div>
        <div class="metric-value">{{ $wAP }}%</div>
        <div class="small-muted">Kehadiran {{ $wAP_Kehadiran }}% • Presentasi {{ $wAP_Presentasi }}%</div>
      </div>
    </div>
  </div>

  <ul class="nav nav-tabs" id="evalTabs" role="tablist">
    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-ringkasan" role="tab">Ringkasan</a></li>
    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-absensi" role="tab">Absensi</a></li>
    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-ap" role="tab">AP</a></li>
    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-penilaian" role="tab">Penilaian</a></li>
    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-proyek-nilai" role="tab">Nilai Proyek</a></li>
    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-rekap" role="tab">Rekap</a></li>
  </ul>

  <div class="tab-content border-left border-right border-bottom p-3">
    {{-- Ringkasan --}}
    <div class="tab-pane fade show active" id="tab-ringkasan" role="tabpanel">
      <p class="small-muted mb-3">Ringkasan metrik dihitung dari tabel evaluasi (<code>evaluasi_absensi</code>, <code>evaluasi_sesi_indikator</code>, <code>evaluasi_nilai_detail</code>) dan pengaturan.</p>
      <ul class="mb-0">
        <li>Kehadiran: <strong>{{ $hadirCount }}/{{ $total }}</strong> ({{ (int) round($total ? ($hadirCount*100/$total) : 0) }}%)</li>
        <li>Nilai proyek (kelompok): <strong>{{ $nilaiProyekKelompok }}</strong> (Dosen {{ $wDosen }}% • Mitra {{ $wMitra }}%)</li>
        <li>Nilai akhir (rata-rata mahasiswa): <strong>{{ $avgNilai }}</strong></li>
      </ul>
    </div>

    {{-- Absensi --}}
    <div class="tab-pane fade" id="tab-absensi" role="tabpanel">
      <div class="small-muted mb-2">Absensi dari tabel <code>evaluasi_absensi</code> per mahasiswa.</div>
      <div class="table-responsive">
        <table class="table table-sm table-hover">
          <thead class="thead-light"><tr>
            <th>NIM</th><th>Nama</th><th>Status</th><th>Waktu</th><th>Catatan</th>
          </tr></thead>
          <tbody>
            @forelse ($members as $m)
              @php $ab = $absByMhs->get($m->id); @endphp
              <tr>
                <td>{{ $m->nim }}</td>
                <td class="truncate" title="{{ $m->nama }}">{{ $m->nama }}</td>
                <td>
                  @php
                    $st = $ab->status ?? '—';
                    $cls = ['Hadir'=>'badge-success','Terlambat'=>'badge-warning','Sakit'=>'badge-info','Dispensasi'=>'badge-info','Alpa'=>'badge-secondary'][$st] ?? 'badge-light';
                  @endphp
                  <span class="badge {{ $cls }}">{{ $st }}</span>
                </td>
                <td>
                  @if(!empty($ab?->waktu_absen))
                    {{ \Carbon\Carbon::parse($ab->waktu_absen)->locale('id')->translatedFormat('d M Y, H:i') }}
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>
                <td class="truncate" title="{{ $ab->keterangan ?? '—' }}">{{ $ab->keterangan ?? '—' }}</td>
              </tr>
            @empty
              <tr><td colspan="5" class="text-center text-muted">Belum ada anggota.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- AP (Kehadiran & Presentasi dari nilai per-mahasiswa) --}}
    <div class="tab-pane fade" id="tab-ap" role="tabpanel">
      <div class="small-muted mb-2">
        AP = Kehadiran × {{ $wAP_Kehadiran }}% + Presentasi × {{ $wAP_Presentasi }}% (dari <code>evaluasi_nilai_detail</code> & indikator <code>m_kehadiran</code>/<code>m_presentasi</code>)
      </div>
      <div class="table-responsive">
        <table class="table table-sm table-hover">
          <thead class="thead-light">
            <tr>
              <th>NIM</th><th>Nama</th>
              <th>Kehadiran</th><th>Presentasi</th><th>AP</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($members as $m)
              @php
                $rows = $nilaiByMhs->get($m->id) ?? collect();
                $vKeh = (int) ($rows->firstWhere('indikator_id', $idKehadiran)->skor ?? 0);
                $vPre = (int) ($rows->firstWhere('indikator_id', $idPresentasi)->skor ?? 0);
                $ap   = (int) ($apScores[$m->id] ?? 0);
              @endphp
              <tr>
                <td>{{ $m->nim }}</td>
                <td class="truncate" title="{{ $m->nama }}">{{ $m->nama }}</td>
                <td>{{ $vKeh }}</td>
                <td>{{ $vPre }}</td>
                <td><span class="badge badge-primary">{{ $ap }}</span></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    {{-- Penilaian (konfigurasi indikator sesi & nilai mitra per-mhs) --}}
    <div class="tab-pane fade" id="tab-penilaian" role="tabpanel">
      <div class="d-flex align-items-center mb-3">
        <div class="small-muted">Indikator sesi (dari <code>evaluasi_sesi_indikator</code>)</div>
        <div class="ml-auto small-muted">Total bobot: {{ $sesiIndis->sum('bobot') }}%</div>
      </div>
      <div class="row">
        @forelse ($sesiIndis as $si)
          <div class="col-md-4 mb-2">
            <div class="metric-card">
              <div class="d-flex align-items-center">
                <div>
                  <div class="font-weight-bold">{{ $si->indikator->nama ?? $si->indikator->kode ?? 'Indikator' }}</div>
                  <div class="small-muted">Kode: {{ $si->indikator->kode ?? '-' }}</div>
                </div>
                <div class="ml-auto">
                  <span class="badge badge-primary">Bobot {{ (int)$si->bobot }}%</span>
                </div>
              </div>
              <div class="mt-2">
                <div class="d-flex align-items-center">
                  <input type="range" min="0" max="100" value="{{ (int)($si->skor ?? 0) }}" class="custom-range" disabled>
                  <span class="ml-2 small" style="width:38px;text-align:right">{{ (int)($si->skor ?? 0) }}</span>
                </div>
                <div class="small-muted mt-1">{{ $si->komentar ?? '' }}</div>
              </div>
            </div>
          </div>
        @empty
          <div class="col-12"><div class="small-muted">Belum ada indikator di sesi ini.</div></div>
        @endforelse
      </div>

      <hr>
      <div class="small-muted mb-2">Nilai Mitra per Mahasiswa (dari <code>evaluasi_nilai_detail</code>)</div>
      <div class="table-responsive">
        <table class="table table-sm table-hover">
          <thead class="thead-light"><tr>
            <th>NIM</th><th>Nama</th>
            <th style="width:150px">Kehadiran</th>
            <th style="width:150px">Presentasi</th>
            <th>Skor Mitra</th>
          </tr></thead>
          <tbody>
            @foreach ($members as $m)
              @php
                $rows = $nilaiByMhs->get($m->id) ?? collect();
                $v1 = (int) ($rows->firstWhere('indikator_id', $idKehadiran)->skor ?? 0);
                $v2 = (int) ($rows->firstWhere('indikator_id', $idPresentasi)->skor ?? 0);
                $sv = (int) ($mitraScores[$m->id] ?? 0);
              @endphp
              <tr>
                <td>{{ $m->nim }}</td>
                <td class="truncate" title="{{ $m->nama }}">{{ $m->nama }}</td>
                <td>
                  <div class="d-flex align-items-center">
                    <input type="range" min="0" max="100" value="{{ $v1 }}" class="custom-range" disabled>
                    <span class="ml-2 small" style="width:38px;text-align:right">{{ $v1 }}</span>
                  </div>
                </td>
                <td>
                  <div class="d-flex align-items-center">
                    <input type="range" min="0" max="100" value="{{ $v2 }}" class="custom-range" disabled>
                    <span class="ml-2 small" style="width:38px;text-align:right">{{ $v2 }}</span>
                  </div>
                </td>
                <td><strong>{{ $sv }}</strong></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    {{-- Nilai Proyek + Distribusi ke Mahasiswa --}}
    <div class="tab-pane fade" id="tab-proyek-nilai" role="tabpanel">
      <div class="row mb-3">
        <div class="col-md-4 mb-2">
          <div class="metric-card">
            <div class="small-muted">Skor Set Dosen</div>
            <div class="metric-value">{{ $skorDosen }}</div>
            <div class="small-muted">Bobot set {{ $wDosen }}%</div>
          </div>
        </div>
        <div class="col-md-4 mb-2">
          <div class="metric-card">
            <div class="small-muted">Skor Set Mitra (Rata-rata)</div>
            <div class="metric-value">{{ (int) round(collect($mitraScores)->avg() ?? 0) }}</div>
            <div class="small-muted">Bobot set {{ $wMitra }}%</div>
          </div>
        </div>
        <div class="col-md-4 mb-2">
          <div class="metric-card">
            <div class="small-muted">Nilai Proyek (Kelompok)</div>
            <div class="metric-value">{{ $nilaiProyekKelompok }}</div>
            <div class="small-muted">= Dosen×{{ $wDosen }}% + Mitra×{{ $wMitra }}%</div>
          </div>
        </div>
      </div>

      <div class="small-muted mb-2">Distribusi ke Mahasiswa: {{ $wKelompok }}% Nilai Proyek (Mhs) + {{ $wAP }}% AP</div>
      <div class="table-responsive">
        <table class="table table-sm table-hover">
          <thead class="thead-light"><tr>
            <th>NIM</th><th>Nama</th><th>AP</th><th>Nilai Proyek (Mhs)</th><th>Nilai Akhir (Mhs)</th>
          </tr></thead>
          <tbody>
            @foreach ($members as $m)
              <tr>
                <td>{{ $m->nim }}</td>
                <td class="truncate" title="{{ $m->nama }}">{{ $m->nama }}</td>
                <td><span class="badge badge-primary">{{ $apScores[$m->id] ?? 0 }}</span></td>
                <td>{{ $nilaiProyekPerMhs[$m->id] ?? 0 }}</td>
                <td><strong>{{ $nilaiAkhirPerMhs[$m->id] ?? 0 }}</strong></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    {{-- Rekap --}}
    <div class="tab-pane fade" id="tab-rekap" role="tabpanel">
      <div class="table-responsive">
        <table class="table table-sm table-bordered">
          <thead>
            <tr>
              <th colspan="2" class="text-center bg-ap">Aktifitas Partisipatif</th>
              <th class="text-center bg-na">NA Aktivitas Partisipatif</th>
              <th colspan="8" class="text-center bg-hp">Hasil Proyek</th>
              <th class="text-center bg-na">NA Hasil Proyek</th>
            </tr>
            <tr>
              <th class="text-center bg-ap">Kehadiran</th>
              <th class="text-center bg-ap">Presentator</th>
              <th class="text-center bg-na">&nbsp;</th>
              <th colspan="6" class="text-center bg-dosen">Dosen <small>({{ $wDosen }}%)</small></th>
              <th colspan="2" class="text-center bg-mitra">Mitra <small>({{ $wMitra }}%)</small></th>
              <th class="text-center bg-na">&nbsp;</th>
            </tr>
            <tr>
              <th class="text-center">&nbsp;</th>
              <th class="text-center">&nbsp;</th>
              <th class="text-center bg-na">&nbsp;</th>
              <th class="text-center">Kualitas Hasil Proyek<br><small>{{ $byKode['d_hasil']['bobot'] ?? 30 }}%</small></th>
              <th class="text-center">Tingkat Kompleksitas Teknis<br><small>{{ $byKode['d_teknis']['bobot'] ?? 20 }}%</small></th>
              <th class="text-center">Kesesuaian dgn Kebutuhan Pengguna<br><small>{{ $byKode['d_user']['bobot'] ?? 15 }}%</small></th>
              <th class="text-center">Efisiensi Waktu & Biaya<br><small>{{ $byKode['d_efisiensi']['bobot'] ?? 10 }}%</small></th>
              <th class="text-center">Dokumentasi & Profesionalisme<br><small>{{ $byKode['d_dokpro']['bobot'] ?? 15 }}%</small></th>
              <th class="text-center">Kemandirian & Inisiatif<br><small>{{ $byKode['d_inisiatif']['bobot'] ?? 10 }}%</small></th>
              <th class="text-center">Kehadiran<br><small>50%</small></th>
              <th class="text-center">Presentasi<br><small>50%</small></th>
              <th class="text-center bg-na">&nbsp;</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($members as $m)
              @php
                $rows = $nilaiByMhs->get($m->id) ?? collect();
                $mKeh = (int) ($rows->firstWhere('indikator_id',$idKehadiran)->skor ?? 0);
                $mPre = (int) ($rows->firstWhere('indikator_id',$idPresentasi)->skor ?? 0);

                // skor dosen per kelompok (pakai yang sama untuk semua baris)
                $dHasil = (int)($byKode['d_hasil']['skor'] ?? 0);
                $dTek   = (int)($byKode['d_teknis']['skor'] ?? 0);
                $dUser  = (int)($byKode['d_user']['skor'] ?? 0);
                $dEfi   = (int)($byKode['d_efisiensi']['skor'] ?? 0);
                $dDok   = (int)($byKode['d_dokpro']['skor'] ?? 0);
                $dIni   = (int)($byKode['d_inisiatif']['skor'] ?? 0);

                $naProyekMhs = (int) ($nilaiProyekPerMhs[$m->id] ?? 0);
                $apFinal     = (int) ($apScores[$m->id] ?? 0);
              @endphp
              <tr>
                <td class="text-center">{{ $mKeh }}</td>
                <td class="text-center">{{ $mPre }}</td>
                <td class="text-center">@if($apFinal>0)<span class="badge badge-success">{{ $apFinal }}</span>@else <span class="badge badge-secondary">Belum dinilai</span>@endif</td>

                <td class="text-center">{{ $dHasil }}</td>
                <td class="text-center">{{ $dTek }}</td>
                <td class="text-center">{{ $dUser }}</td>
                <td class="text-center">{{ $dEfi }}</td>
                <td class="text-center">{{ $dDok }}</td>
                <td class="text-center">{{ $dIni }}</td>

                <td class="text-center">{{ $mKeh }}</td>
                <td class="text-center">{{ $mPre }}</td>

                <td class="text-center">
                  @if($naProyekMhs>0)
                    <span class="badge badge-primary">{{ $naProyekMhs }}</span>
                  @else
                    <span class="badge badge-secondary">Belum dinilai</span>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>
@endsection
