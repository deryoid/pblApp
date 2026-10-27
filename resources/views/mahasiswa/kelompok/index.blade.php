@extends('layout.app')

@section('title', 'Kelompok PBL Saya - SIMEP Politala')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <!-- Header Halaman -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-900 font-weight-bold">
                <i class="fas fa-users text-primary mr-2"></i>Kelompok PBL Saya
            </h1>
            <p class="text-muted small mb-0">
                Kelola anggota kelompok, kirim undangan rekan mahasiswa, dan pantau status skema kategori proyek.
            </p>
        </div>
        <div class="mt-3 mt-sm-0">
            @if($activePeriode)
                <span class="badge badge-primary px-3 py-2 shadow-sm font-weight-bold">
                    <i class="fas fa-calendar-alt mr-1"></i> Periode: {{ $activePeriode->periode }}
                </span>
            @endif
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-lg" role="alert">
            <i class="fas fa-check-circle mr-2"></i>{{ session('status') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($kelompok)
        <!-- ============================================== -->
        <!-- JIKA MAHASISWA SUDAH MEMILIKI KELOMPOK         -->
        <!-- ============================================== -->
        <div class="row">
            <!-- Informasi Utama Kelompok -->
            <div class="col-lg-5 mb-4">
                <div class="card shadow-sm border-0 rounded-xl h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-dark">
                            <i class="fas fa-id-card text-primary mr-2"></i>Profil Kelompok
                        </h6>
                        @php
                            $skemaClass = match($kelompok->skema_kategori ?? 'Internal') {
                                'Internal' => 'badge-primary',
                                'External' => 'badge-success',
                                'Hibah/Penelitian' => 'badge-warning',
                                'Lomba' => 'badge-danger',
                                default => 'badge-info',
                            };
                        @endphp
                        <span class="badge {{ $skemaClass }} px-3 py-1 font-weight-bold">
                            Skema: {{ $kelompok->skema_kategori ?? 'Internal' }}
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4 text-center">
                            <div class="rounded-circle bg-light text-primary mx-auto d-flex align-items-center justify-content-center mb-3 shadow-sm" style="width:72px;height:72px;font-size:2rem;">
                                <i class="fas fa-users-cog"></i>
                            </div>
                            <h4 class="font-weight-bold text-dark mb-1">{{ $kelompok->nama_kelompok }}</h4>
                            <p class="text-muted small mb-0">{{ $kelompok->periode?->periode ?? 'Periode Aktif' }}</p>
                        </div>

                        <div class="list-group list-group-flush small">
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-top">
                                <span class="text-muted"><i class="fas fa-tags mr-2 text-primary"></i>Skema Kategori:</span>
                                <strong class="text-dark">{{ $kelompok->skema_kategori ?? 'Internal' }}</strong>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span class="text-muted"><i class="fas fa-graduation-cap mr-2 text-info"></i>Semester:</span>
                                <strong class="text-dark">Semester {{ $kelompok->semester ?? 1 }}</strong>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span class="text-muted"><i class="fas fa-user-friends mr-2 text-success"></i>Kapasitas Anggota:</span>
                                <span class="badge {{ $kelompok->isFull() ? 'badge-danger' : 'badge-success' }} px-2 py-1">
                                    {{ $kelompok->mahasiswas->count() }} / {{ $kelompok->max_members ?? 4 }} Mahasiswa
                                </span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span class="text-muted"><i class="fas fa-lock-open mr-2 text-warning"></i>Status Bergabung:</span>
                                <strong class="text-capitalize text-dark">{{ $kelompok->status_bergabung ?? 'Terbuka' }}</strong>
                            </div>
                            @if($kelompok->link_drive)
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                    <span class="text-muted"><i class="fab fa-google-drive mr-2 text-danger"></i>Folder Drive:</span>
                                    <a href="{{ $kelompok->link_drive }}" target="_blank" class="btn btn-xs btn-outline-primary py-1 px-2 rounded-pill">
                                        <i class="fas fa-external-link-alt mr-1"></i> Buka Drive
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daftar Anggota Kelompok -->
            <div class="col-lg-7 mb-4">
                <div class="card shadow-sm border-0 rounded-xl h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-dark">
                            <i class="fas fa-user-check text-success mr-2"></i>Daftar Anggota Tim ({{ $kelompok->mahasiswas->count() }}/{{ $kelompok->max_members ?? 4 }})
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            @foreach($kelompok->mahasiswas as $mhs)
                                @php
                                    $isKetuaMhs = strtolower($mhs->pivot->role ?? '') === 'ketua';
                                    $userMhs = $mhs->user;
                                @endphp
                                <div class="col-12 mb-3">
                                    <div class="p-3 border rounded-xl d-flex align-items-center justify-content-between bg-light">
                                        <div class="d-flex align-items-center">
                                            <img 
                                                src="{{ $userMhs?->profile_photo_data_url ?? asset('sbadmin2/img/undraw_profile.svg') }}" 
                                                alt="Foto" 
                                                class="rounded-circle mr-3 shadow-sm flex-shrink-0" 
                                                style="width: 44px; height: 44px; object-fit: cover; border: 2px solid {{ $isKetuaMhs ? '#2563eb' : '#e2e8f0' }};"
                                            >
                                            <div>
                                                <div class="font-weight-bold text-dark" style="font-size: 0.9375rem;">
                                                    {{ $mhs->nama_mahasiswa ?? $userMhs?->nama_user }}
                                                </div>
                                                <div class="text-muted small">
                                                    NIM: <strong>{{ $mhs->nim ?? '-' }}</strong> &bull; Kelas: {{ $mhs->kelas?->kelas ?? '-' }}
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            @if($isKetuaMhs)
                                                <span class="badge badge-primary px-3 py-1 font-weight-bold">
                                                    <i class="fas fa-crown mr-1 text-warning"></i> Ketua Kelompok
                                                </span>
                                            @else
                                                <span class="badge badge-secondary px-3 py-1">
                                                    Anggota
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section: Kirim Undangan Anggota & Daftar Undangan Keluar -->
        <div class="row">
            <!-- Form Undang Rekan -->
            <div class="col-lg-5 mb-4">
                <div class="card shadow-sm border-0 rounded-xl h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="m-0 font-weight-bold text-dark">
                            <i class="fas fa-paper-plane text-primary mr-2"></i>Undang Rekan ke Kelompok
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        @if($kelompok->isFull())
                            <div class="alert alert-info border-0 shadow-sm rounded-lg mb-0 text-center py-4">
                                <i class="fas fa-check-circle fa-2x mb-2 text-info"></i>
                                <h6 class="font-weight-bold mb-1">Kelompok Sudah Penuh</h6>
                                <p class="small text-muted mb-0">Kelompok Anda telah mencapai kapasitas maksimum {{ $kelompok->max_members }} anggota.</p>
                            </div>
                        @else
                            <form action="{{ route('mahasiswa.kelompok.invite') }}" method="POST">
                                @csrf
                                <div class="form-group mb-3">
                                    <label for="mahasiswa_id" class="font-weight-bold small text-dark">
                                        Pilih Mahasiswa yang Belum Memiliki Kelompok <span class="text-danger">*</span>
                                    </label>
                                    <select name="mahasiswa_id" id="mahasiswa_id" class="form-control select2 @error('mahasiswa_id') is-invalid @enderror" required style="width:100%;">
                                        <option value="">-- Cari Nama atau NIM Mahasiswa --</option>
                                        @foreach($availableStudents as $avail)
                                            <option value="{{ $avail->id }}">
                                                {{ $avail->nama_mahasiswa }} (NIM: {{ $avail->nim }} - {{ $avail->kelas?->kelas ?? 'Kelas -' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('mahasiswa_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    <small class="text-muted d-block mt-1">Hanya mahasiswa yang belum tergabung dalam kelompok aktif yang dapat diundang.</small>
                                </div>

                                <div class="form-group mb-4">
                                    <label for="pesan" class="font-weight-bold small text-dark">Pesan Undangan (Opsional)</label>
                                    <textarea name="pesan" id="pesan" rows="2" class="form-control" placeholder="Contoh: Ayo bergabung ke kelompok kami untuk proyek skema {{ $kelompok->skema_kategori }}!"></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block shadow-sm">
                                    <i class="fas fa-user-plus mr-1"></i> Kirim Undangan Bergabung
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Daftar Undangan Terkirim -->
            <div class="col-lg-7 mb-4">
                <div class="card shadow-sm border-0 rounded-xl h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="m-0 font-weight-bold text-dark">
                            <i class="fas fa-history text-secondary mr-2"></i>Status Undangan yang Telah Dikirim
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        @if($sentInvitations->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Mahasiswa Diundang</th>
                                            <th>Pengundang</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sentInvitations as $inv)
                                            <tr>
                                                <td>
                                                    <div class="font-weight-bold text-dark">{{ $inv->mahasiswa?->nama_mahasiswa ?? '-' }}</div>
                                                    <small class="text-muted">NIM: {{ $inv->mahasiswa?->nim ?? '-' }}</small>
                                                </td>
                                                <td>
                                                    <small class="text-dark font-weight-500">{{ $inv->invitedBy?->nama_user ?? 'Anggota' }}</small>
                                                    <div class="text-muted" style="font-size:0.75rem;">{{ $inv->created_at?->diffForHumans() }}</div>
                                                </td>
                                                <td class="text-center">
                                                    @php
                                                        $statusClass = match($inv->status) {
                                                            'approved' => 'badge-success',
                                                            'rejected' => 'badge-danger',
                                                            'canceled' => 'badge-secondary',
                                                            default => 'badge-warning',
                                                        };
                                                        $statusLabel = match($inv->status) {
                                                            'approved' => 'Diterima',
                                                            'rejected' => 'Ditolak',
                                                            'canceled' => 'Dibatalkan',
                                                            default => 'Menunggu',
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $statusClass }} px-2 py-1">
                                                        {{ $statusLabel }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    @if($inv->status === 'pending')
                                                        <form action="{{ route('mahasiswa.kelompok.invite.cancel', $inv->uuid) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan undangan ini?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger shadow-none" title="Batalkan Undangan">
                                                                <i class="fas fa-times mr-1"></i> Batalkan
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="text-muted small">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-3x mb-2 text-muted" style="opacity:0.3;"></i>
                                <p class="mb-0 small">Belum ada undangan yang dikirimkan ke mahasiswa lain.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    @else
        <!-- ============================================== -->
        <!-- JIKA MAHASISWA BELUM MEMILIKI KELOMPOK         -->
        <!-- ============================================== -->

        <!-- Section 1: Undangan Bergabung yang Masuk -->
        @if($incomingInvitations->count() > 0)
            <div class="card shadow-sm border-0 rounded-xl mb-4 border-left-primary">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-envelope-open-text mr-2"></i>Undangan Masuk untuk Anda ({{ $incomingInvitations->count() }})
                    </h6>
                    <span class="badge badge-warning px-2 py-1">Perlu Tanggapan</span>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small mb-3">
                        Anda telah diundang untuk bergabung ke kelompok PBL berikut. Klik <strong>Terima</strong> untuk resmi bergabung, atau <strong>Tolak</strong> jika ingin memilih kelompok lain.
                    </p>

                    <div class="row">
                        @foreach($incomingInvitations as $invite)
                            <div class="col-md-6 mb-3">
                                <div class="card border rounded-xl shadow-sm p-3 h-100 bg-white">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <h5 class="font-weight-bold text-dark mb-0">{{ $invite->kelompok?->nama_kelompok }}</h5>
                                        <span class="badge badge-primary">
                                            {{ $invite->kelompok?->skema_kategori ?? 'Internal' }}
                                        </span>
                                    </div>
                                    <div class="small text-muted mb-2">
                                        <i class="fas fa-user-edit mr-1"></i> Pengundang: <strong>{{ $invite->invitedBy?->nama_user ?? 'Ketua Kelompok' }}</strong>
                                        &bull; Periode: {{ $invite->kelompok?->periode?->periode ?? '-' }}
                                    </div>
                                    @if($invite->pesan)
                                        <div class="p-2 rounded bg-light small text-dark mb-3 font-italic border">
                                            "{{ $invite->pesan }}"
                                        </div>
                                    @endif

                                    <div class="d-flex align-items-center gap-2 mt-auto pt-2 border-top">
                                        <form action="{{ route('mahasiswa.kelompok.invite.accept', $invite->uuid) }}" method="POST" class="mr-2 flex-grow-1">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-block btn-sm shadow-sm font-weight-bold">
                                                <i class="fas fa-check mr-1"></i> Terima Undangan
                                            </button>
                                        </form>

                                        <form action="{{ route('mahasiswa.kelompok.invite.reject', $invite->uuid) }}" method="POST" onsubmit="return confirm('Yakin ingin menolak undangan ini?');">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger btn-sm shadow-none">
                                                <i class="fas fa-times mr-1"></i> Tolak
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Section 2: Formulir Buat Kelompok Baru -->
        <div class="row">
            <div class="col-lg-7 mx-auto">
                <div class="card shadow-sm border-0 rounded-xl">
                    <div class="card-header bg-white border-bottom py-3 text-center">
                        <div class="rounded-circle bg-light text-primary mx-auto d-flex align-items-center justify-content-center mb-2 shadow-sm" style="width:56px;height:56px;font-size:1.5rem;">
                            <i class="fas fa-plus"></i>
                        </div>
                        <h5 class="font-weight-bold text-dark mb-1">Bentuk Kelompok PBL Baru</h5>
                        <p class="text-muted small mb-0">Belum tergabung dalam kelompok? Buat kelompok baru Anda sekarang dan undang rekan tim.</p>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('mahasiswa.kelompok.store') }}" method="POST">
                            @csrf

                            <div class="form-group mb-3">
                                <label for="nama_kelompok" class="font-weight-bold small text-dark">
                                    Nama Kelompok <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="nama_kelompok" id="nama_kelompok" class="form-control @error('nama_kelompok') is-invalid @enderror" placeholder="Contoh: Kelompok Inovasi AI" value="{{ old('nama_kelompok') }}" required>
                                @error('nama_kelompok') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="skema_kategori" class="font-weight-bold small text-dark">
                                    Skema Kategori Proyek <span class="text-danger">*</span>
                                </label>
                                <select name="skema_kategori" id="skema_kategori" class="form-control @error('skema_kategori') is-invalid @enderror" required>
                                    <option value="">-- Pilih Salah Satu Skema Kategori --</option>
                                    <option value="Internal" {{ old('skema_kategori') === 'Internal' ? 'selected' : '' }}>
                                        Internal (Proyek Riset / Kebutuhan Internal Kampus - Wajib Proposal)
                                    </option>
                                    <option value="External" {{ old('skema_kategori') === 'External' ? 'selected' : '' }}>
                                        External (Mitra Industri / UMKM / Instansi Luar)
                                    </option>
                                    <option value="Hibah/Penelitian" {{ old('skema_kategori') === 'Hibah/Penelitian' ? 'selected' : '' }}>
                                        Hibah / Penelitian (Riset Dosen & Pendanaan Hibah)
                                    </option>
                                    <option value="Lomba" {{ old('skema_kategori') === 'Lomba' ? 'selected' : '' }}>
                                        Lomba (Kompetisi / Gemastik / PKM / Hackathon)
                                    </option>
                                </select>
                                @error('skema_kategori') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-info-circle mr-1"></i>Setiap kelompok wajib memilih 1 skema kategori. Khusus skema <strong>Internal</strong>, wajib mengunggah proposal sebelum memulai pengerjaan proyek.
                                </small>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6 mb-3">
                                    <label for="semester" class="font-weight-bold small text-dark">Semester Akademik</label>
                                    <select name="semester" id="semester" class="form-control">
                                        @for($s = 1; $s <= 8; $s++)
                                            <option value="{{ $s }}" {{ old('semester', 3) == $s ? 'selected' : '' }}>Semester {{ $s }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="form-group col-md-6 mb-3">
                                    <label for="link_drive" class="font-weight-bold small text-dark">Link Folder Google Drive (Opsional)</label>
                                    <input type="url" name="link_drive" id="link_drive" class="form-control @error('link_drive') is-invalid @enderror" placeholder="https://drive.google.com/..." value="{{ old('link_drive') }}">
                                    @error('link_drive') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="pt-3 border-top text-right">
                                <button type="submit" class="btn btn-primary btn-block py-2 font-weight-bold shadow-sm">
                                    <i class="fas fa-plus-circle mr-1"></i> Buat Kelompok & Jadi Ketua
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
