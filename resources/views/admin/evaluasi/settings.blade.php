@extends('layout.app')
@section('content')
<div class="container-fluid">
  <div class="d-flex align-items-center mb-3">
    <h1 class="h3 text-gray-800 mb-0">Pengaturan Evaluasi</h1>
  </div>

  @if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="row">
    <div class="col-md-6">
      <div class="card shadow-sm mb-4">
        <div class="card-header">Bobot Set Penilai Proyek</div>
        <div class="card-body">
          <form method="POST" action="{{ route('admin.evaluasi.settings.save') }}">
            @csrf

            {{-- ===== Set Dosen/Mitra ===== --}}
            <div class="form-group">
              <label>Bobot Dosen (%)</label>
              <input type="number" min="0" max="100" class="form-control @error('w_dosen') is-invalid @enderror"
                     name="w_dosen"
                     value="{{ old('w_dosen', (int)($settings['w_dosen'] ?? 80)) }}" required>
              <small class="text-muted">Sisa bobot untuk Mitra akan otomatis menjadi 100 - Dosen.</small>
              @error('w_dosen') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
              <label>Bobot Mitra (%)</label>
              <input type="number" min="0" max="100" class="form-control @error('w_mitra') is-invalid @enderror"
                     name="w_mitra"
                     value="{{ old('w_mitra', (int)($settings['w_mitra'] ?? 20)) }}" required>
              @error('w_mitra') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <hr>

            {{-- ===== Sub-Indikator Dosen ===== --}}
            <div class="form-group">
              <label class="d-block">Bobot Sub-Indikator Dosen (total 100)</label>

              <div class="form-row">
                <div class="col-8"><small>Kualitas Hasil Proyek</small></div>
                <div class="col-4">
                  <input type="number" min="0" max="100" class="form-control @error('d_hasil') is-invalid @enderror"
                         name="d_hasil"
                         value="{{ old('d_hasil', (int)($settings['d_hasil'] ?? 30)) }}" required>
                  @error('d_hasil') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>

              <div class="form-row mt-2">
                <div class="col-8"><small>Tingkat Kompleksitas Teknis</small></div>
                <div class="col-4">
                  <input type="number" min="0" max="100" class="form-control @error('d_teknis') is-invalid @enderror"
                         name="d_teknis"
                         value="{{ old('d_teknis', (int)($settings['d_teknis'] ?? 20)) }}" required>
                  @error('d_teknis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>

              <div class="form-row mt-2">
                <div class="col-8"><small>Kesesuaian dengan Kebutuhan Pengguna</small></div>
                <div class="col-4">
                  <input type="number" min="0" max="100" class="form-control @error('d_user') is-invalid @enderror"
                         name="d_user"
                         value="{{ old('d_user', (int)($settings['d_user'] ?? 15)) }}" required>
                  @error('d_user') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>

              <div class="form-row mt-2">
                <div class="col-8"><small>Efisiensi Waktu dan Biaya</small></div>
                <div class="col-4">
                  <input type="number" min="0" max="100" class="form-control @error('d_efisiensi') is-invalid @enderror"
                         name="d_efisiensi"
                         value="{{ old('d_efisiensi', (int)($settings['d_efisiensi'] ?? 10)) }}" required>
                  @error('d_efisiensi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>

              <div class="form-row mt-2">
                <div class="col-8"><small>Dokumentasi dan Profesionalisme</small></div>
                <div class="col-4">
                  <input type="number" min="0" max="100" class="form-control @error('d_dokpro') is-invalid @enderror"
                         name="d_dokpro"
                         value="{{ old('d_dokpro', (int)($settings['d_dokpro'] ?? 15)) }}" required>
                  @error('d_dokpro') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>

              <div class="form-row mt-2">
                <div class="col-8"><small>Kemandirian dan Inisiatif</small></div>
                <div class="col-4">
                  <input type="number" min="0" max="100" class="form-control @error('d_inisiatif') is-invalid @enderror"
                         name="d_inisiatif"
                         value="{{ old('d_inisiatif', (int)($settings['d_inisiatif'] ?? 10)) }}" required>
                  @error('d_inisiatif') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>

              <small class="text-muted">Jika total tidak 100, indikator terakhir akan disesuaikan otomatis.</small>
            </div>

            {{-- ===== Sub-Indikator Mitra ===== --}}
            <div class="form-group">
              <label class="d-block">Bobot Sub-Indikator Mitra (total 100)</label>

              <div class="form-row">
                <div class="col-8"><small>Kehadiran</small></div>
                <div class="col-4">
                  <input type="number" min="0" max="100" class="form-control @error('m_kehadiran') is-invalid @enderror"
                         name="m_kehadiran"
                         value="{{ old('m_kehadiran', (int)($settings['m_kehadiran'] ?? 50)) }}" required>
                  @error('m_kehadiran') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>

              <div class="form-row mt-2">
                <div class="col-8"><small>Presentasi</small></div>
                <div class="col-4">
                  <input type="number" min="0" max="100" class="form-control @error('m_presentasi') is-invalid @enderror"
                         name="m_presentasi"
                         value="{{ old('m_presentasi', (int)($settings['m_presentasi'] ?? 50)) }}" required>
                  @error('m_presentasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>

              <small class="text-muted">Jika total tidak 100, Presentasi akan disesuaikan otomatis.</small>
            </div>

            <hr>

            {{-- ===== Distribusi Nilai Akhir (Kelompok vs AP) ===== --}}
            <div class="form-group">
              <label>Distribusi Nilai Akhir per Mahasiswa</label>
              <div class="form-row">
                <div class="col">
                  <label class="small mb-1">Nilai Proyek (Mhs) %</label>
                  <input type="number" min="0" max="100" class="form-control @error('w_kelompok') is-invalid @enderror"
                         name="w_kelompok"
                         value="{{ old('w_kelompok', (int)($settings['w_kelompok'] ?? 70)) }}" required>
                  @error('w_kelompok') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col">
                  <label class="small mb-1">Aktivitas Partisipatif (AP) %</label>
                  <input type="number" min="0" max="100" class="form-control @error('w_ap') is-invalid @enderror"
                         name="w_ap"
                         value="{{ old('w_ap', (int)($settings['w_ap'] ?? 30)) }}" required>
                  @error('w_ap') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>
              <small class="text-muted">Pastikan jumlah = 100.</small>
            </div>

            <hr>

            {{-- ===== Bobot Komponen AP ===== --}}
            <div class="form-group">
              <label>Bobot Komponen AP</label>
              <div class="form-row">
                <div class="col">
                  <label class="small mb-1">Kehadiran %</label>
                  <input type="number" min="0" max="100" class="form-control @error('w_ap_kehadiran') is-invalid @enderror"
                         name="w_ap_kehadiran"
                         value="{{ old('w_ap_kehadiran', (int)($settings['w_ap_kehadiran'] ?? 50)) }}" required>
                  @error('w_ap_kehadiran') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col">
                  <label class="small mb-1">Presentasi %</label>
                  <input type="number" min="0" max="100" class="form-control @error('w_ap_presentasi') is-invalid @enderror"
                         name="w_ap_presentasi"
                         value="{{ old('w_ap_presentasi', (int)($settings['w_ap_presentasi'] ?? 50)) }}" required>
                  @error('w_ap_presentasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>
              <small class="text-muted">Pastikan jumlah = 100.</small>
            </div>

            <div class="text-right">
              <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
