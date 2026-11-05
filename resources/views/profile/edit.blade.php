@extends('layout.app')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-900 font-weight-bold">
                <i class="fas fa-user-cog text-primary mr-2"></i>Pengaturan Profil Pengguna
            </h1>
            <p class="text-muted small mb-0">
                Perbarui biodata akun, nomor kontak, foto profil, dan informasi akademik Anda
            </p>
        </div>
        <div class="mt-3 mt-sm-0">
            <a href="{{ route('home') }}" target="_blank" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-globe mr-1"></i> Showcase Proyek SPA
            </a>
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

    @if(session('warning') || ! $user->isProfileComplete())
        <div class="alert alert-warning border-0 shadow-sm rounded-lg mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-2x mr-3 text-warning"></i>
                <div>
                    <h6 class="font-weight-bold mb-1">Syarat Akses Menu: Wajib Memperbarui Profil</h6>
                    <p class="mb-0 small text-dark">
                        {{ session('warning') ?? 'Untuk membuka akses seluruh menu dan fitur PBL (Kelompok, Proyek, Aktivitas, Nilai), Anda wajib melengkapi nomor WhatsApp/HP aktif, data kelas, dan biodata Anda di bawah ini.' }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <!-- Left: Profile Summary Card -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm border-0 rounded-xl text-center p-4">
                <div class="position-relative d-inline-block mx-auto mb-3">
                    <img 
                        src="{{ $user->profile_photo_data_url ?? asset('sbadmin2/img/avatar-default.svg') }}" 
                        alt="Avatar {{ $user->nama_user }}" 
                        width="110"
                        height="110"
                        class="rounded-circle shadow-sm"
                        style="width: 110px; height: 110px; object-fit: cover; border: 4px solid #eff6ff;"
                    >
                    <span class="position-absolute badge badge-success px-2 py-0.5 rounded shadow-sm font-weight-bold" style="bottom: 0; right: 0; font-size: 0.75rem;">
                        <i class="fas fa-check" aria-hidden="true"></i> Aktif
                    </span>
                </div>

                <h2 class="h5 font-weight-bold text-dark mb-1">{{ $user->nama_user }}</h2>
                <p class="text-muted small mb-3">{{ '@' . $user->username }}</p>

                <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                    @php
                        $roleBadge = match($user->role) {
                            'admin' => 'badge-danger',
                            'evaluator' => 'badge-warning',
                            'dosen' => 'badge-info',
                            default => 'badge-primary',
                        };
                    @endphp
                    <span class="badge {{ $roleBadge }} text-uppercase px-3 py-1 font-weight-bold">
                        {{ $user->role }}
                    </span>
                    @if($user->google_id)
                        <span class="badge badge-success px-2 py-1" title="Terhubung dengan Google OAuth">
                            <i class="fab fa-google mr-1"></i> Terverifikasi
                        </span>
                    @endif
                </div>

                <div class="border-top pt-3 text-left">
                    <div class="small text-muted mb-1"><i class="fas fa-envelope text-primary mr-2"></i>{{ $user->email }}</div>
                    <div class="small text-muted mb-1"><i class="fas fa-phone text-success mr-2"></i>{{ $user->no_hp ?? '-' }}</div>
                    @if($user->role === 'mahasiswa' && $mahasiswa)
                        <div class="small text-muted mb-1"><i class="fas fa-id-card text-info mr-2"></i>NIM: <strong>{{ $mahasiswa->nim ?? '-' }}</strong></div>
                        <div class="small text-muted mb-1"><i class="fas fa-calendar-alt text-secondary mr-2"></i>Angkatan: <strong>{{ $mahasiswa->angkatan ?? '-' }}</strong></div>
                        <div class="small text-muted mb-1"><i class="fas fa-chalkboard-teacher text-warning mr-2"></i>Kelas: <strong>{{ $mahasiswa->kelas->kelas ?? 'Belum ditentukan' }}</strong></div>
                        <div class="small text-muted"><i class="fas fa-venus-mars text-primary mr-2"></i>JK: <strong>{{ $mahasiswa->jenis_kelamin_text }}</strong></div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right: Edit Form -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm border-0 rounded-xl">
                <div class="card-header bg-white border-bottom py-3">
                    <h2 class="h6 m-0 font-weight-bold text-dark">
                        <i class="fas fa-user-edit text-primary mr-2" aria-hidden="true"></i>Perbarui Informasi Pribadi
                    </h2>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="nama_user" class="font-weight-bold small text-dark">
                                    Nama Lengkap <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-user text-muted"></i></span>
                                    </div>
                                    <input type="text" name="nama_user" id="nama_user" class="form-control border-left-0 @error('nama_user') is-invalid @enderror" value="{{ old('nama_user', $user->nama_user) }}" required>
                                </div>
                                @error('nama_user') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-group col-md-6 mb-3">
                                <label for="email" class="font-weight-bold small text-dark">
                                    Alamat Email <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-envelope text-muted"></i></span>
                                    </div>
                                    <input type="email" name="email" id="email" class="form-control border-left-0 @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                                </div>
                                @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="no_hp" class="font-weight-bold small text-dark">
                                    Nomor Telepon / WhatsApp <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-phone text-muted"></i></span>
                                    </div>
                                    <input type="text" name="no_hp" id="no_hp" class="form-control border-left-0 @error('no_hp') is-invalid @enderror" value="{{ old('no_hp', $user->no_hp) }}" placeholder="08xxxxxxxxxx" required>
                                </div>
                                @error('no_hp') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-group col-md-6 mb-3">
                                <label for="username" class="font-weight-bold small text-dark">
                                    Nama Pengguna (Username) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-at text-muted"></i></span>
                                    </div>
                                    <input type="text" name="username" id="username" class="form-control border-left-0 @error('username') is-invalid @enderror" value="{{ old('username', $user->username) }}" required>
                                </div>
                                @error('username') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        @if($user->role === 'mahasiswa')
                        <div class="border-top pt-3 mt-2 mb-3">
                            <h3 class="h6 font-weight-bold text-primary mb-3">
                                <i class="fas fa-graduation-cap mr-1" aria-hidden="true"></i> Data Akademik Mahasiswa
                            </h3>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4 mb-3">
                                <label for="angkatan" class="font-weight-bold small text-dark">
                                    Tahun Angkatan
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-calendar-alt text-muted"></i></span>
                                    </div>
                                    <input type="text" name="angkatan" id="angkatan" class="form-control border-left-0 @error('angkatan') is-invalid @enderror" value="{{ old('angkatan', $mahasiswa?->angkatan) }}" placeholder="Contoh: 2024">
                                </div>
                                @error('angkatan') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-group col-md-4 mb-3">
                                <label for="nim" class="font-weight-bold small text-dark">
                                    Nomor Induk Mahasiswa (NIM) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-id-card text-muted"></i></span>
                                    </div>
                                    <input type="text" name="nim" id="nim" class="form-control border-left-0 @error('nim') is-invalid @enderror" value="{{ old('nim', $mahasiswa?->nim) }}" placeholder="Contoh: 230411100001" required>
                                </div>
                                @error('nim') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-group col-md-4 mb-3">
                                <label for="kelas_id" class="font-weight-bold small text-dark">
                                    Kelas Akademik <span class="text-danger">*</span>
                                </label>
                                <select name="kelas_id" id="kelas_id" class="form-control @error('kelas_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($kelasList as $k)
                                        <option value="{{ $k->id }}" {{ old('kelas_id', $mahasiswa?->kelas_id) == $k->id ? 'selected' : '' }}>
                                            {{ $k->kelas }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kelas_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="border-top pt-3 mt-2 mb-3">
                            <h3 class="h6 font-weight-bold text-primary mb-3">
                                <i class="fas fa-id-badge mr-1" aria-hidden="true"></i> Biodata Pribadi Mahasiswa
                            </h3>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4 mb-3">
                                <label for="nik" class="font-weight-bold small text-dark">
                                    NIK (Kependudukan)
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-fingerprint text-muted"></i></span>
                                    </div>
                                    <input type="text" name="nik" id="nik" class="form-control border-left-0 @error('nik') is-invalid @enderror" value="{{ old('nik', $mahasiswa?->nik) }}" placeholder="16 digit NIK">
                                </div>
                                @error('nik') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-group col-md-4 mb-3">
                                <label for="jk" class="font-weight-bold small text-dark">
                                    Jenis Kelamin
                                </label>
                                <select name="jk" id="jk" class="form-control @error('jk') is-invalid @enderror">
                                    <option value="">-- Pilih Jenis Kelamin --</option>
                                    <option value="L" {{ old('jk', $mahasiswa?->jk) == 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                                    <option value="P" {{ old('jk', $mahasiswa?->jk) == 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                                </select>
                                @error('jk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-group col-md-4 mb-3">
                                <label for="agama" class="font-weight-bold small text-dark">
                                    Agama
                                </label>
                                @php
                                    $agamaOptions = ['Islam', 'Kristen Protestan', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Lainnya'];
                                    $currentAgama = old('agama', $mahasiswa?->agama);
                                @endphp
                                <select name="agama" id="agama" class="form-control @error('agama') is-invalid @enderror">
                                    <option value="">-- Pilih Agama --</option>
                                    @foreach($agamaOptions as $opt)
                                        <option value="{{ $opt }}" {{ $currentAgama == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                    @if($currentAgama && !in_array($currentAgama, $agamaOptions))
                                        <option value="{{ $currentAgama }}" selected>{{ $currentAgama }}</option>
                                    @endif
                                </select>
                                @error('agama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4 mb-3">
                                <label for="tempat_lahir" class="font-weight-bold small text-dark">
                                    Tempat Lahir
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-map-marker-alt text-muted"></i></span>
                                    </div>
                                    <input type="text" name="tempat_lahir" id="tempat_lahir" class="form-control border-left-0 @error('tempat_lahir') is-invalid @enderror" value="{{ old('tempat_lahir', $mahasiswa?->tempat_lahir) }}" placeholder="Kota / Kabupaten Lahir">
                                </div>
                                @error('tempat_lahir') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-group col-md-4 mb-3">
                                <label for="tanggal_lahir" class="font-weight-bold small text-dark">
                                    Tanggal Lahir
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-calendar text-muted"></i></span>
                                    </div>
                                    <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="form-control border-left-0 @error('tanggal_lahir') is-invalid @enderror" value="{{ old('tanggal_lahir', $mahasiswa?->tanggal_lahir?->format('Y-m-d')) }}">
                                </div>
                                @error('tanggal_lahir') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-group col-md-4 mb-3">
                                <label for="nama_ibu" class="font-weight-bold small text-dark">
                                    Nama Ibu Kandung
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-female text-muted"></i></span>
                                    </div>
                                    <input type="text" name="nama_ibu" id="nama_ibu" class="form-control border-left-0 @error('nama_ibu') is-invalid @enderror" value="{{ old('nama_ibu', $mahasiswa?->nama_ibu) }}" placeholder="Nama Lengkap Ibu Kandung">
                                </div>
                                @error('nama_ibu') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label for="alamat" class="font-weight-bold small text-dark">
                                Alamat Lengkap / Domisili
                            </label>
                            <textarea name="alamat" id="alamat" rows="2" class="form-control @error('alamat') is-invalid @enderror" placeholder="Jalan, RT/RW, Kelurahan, Kecamatan, Kota/Kabupaten">{{ old('alamat', $mahasiswa?->alamat) }}</textarea>
                            @error('alamat') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        @endif

                        <div class="form-group mb-4">
                            <label for="photo" class="font-weight-bold small text-dark">Unggah Foto Profil Baru</label>
                            <div class="custom-file mb-2">
                                <input type="file" name="photo" id="photo" accept="image/*" class="custom-file-input @error('photo') is-invalid @enderror">
                                <label class="custom-file-label" for="photo">Pilih berkas foto profil...</label>
                            </div>
                            <small class="text-muted d-block">Maksimal 4MB. Format yang didukung: JPEG, PNG, GIF, WebP. Gambar dikompres otomatis untuk efisiensi penyimpanan.</small>
                            @error('photo') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

                            @if($user->profile_photo)
                                <div class="custom-control custom-checkbox mt-3">
                                    <input type="checkbox" name="remove_photo" value="1" id="remove_photo" class="custom-control-input">
                                    <label for="remove_photo" class="custom-control-label small text-danger font-weight-600">
                                        <i class="fas fa-trash-alt mr-1"></i>Hapus foto profil saat ini dan gunakan avatar bawaan
                                    </label>
                                </div>
                            @endif
                        </div>

                        <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center justify-content-sm-end pt-3 border-top">
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="fas fa-save mr-1"></i> Simpan Perubahan Profil
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Update label custom-file-input
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName || 'Pilih berkas...');
    });
</script>
@endpush
@endsection
