@extends('layout.app')

@section('content')
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <h1 class="h3 text-gray-800">Edit Pengguna</h1>
    </div>

    <div class="card shadow">
        <div class="card-body">
            {{-- form untuk update user --}}
            <form action="{{ route('user.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="nama_user">Nama User <span class="text-danger">*</span></label>
                        <input type="text" name="nama_user" id="nama_user"
                               class="form-control @error('nama_user') is-invalid @enderror"
                               value="{{ old('nama_user', $user->nama_user) }}" required>
                        @error('nama_user')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-md-6">
                        <label for="email">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="no_hp">Nomor HP/WA <span class="text-danger">*</span></label>
                        <input type="text" name="no_hp" id="no_hp"
                               class="form-control @error('no_hp') is-invalid @enderror"
                               value="{{ old('no_hp', $user->no_hp) }}" required>
                        @error('no_hp')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-md-6">
                        <label for="username">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" id="username"
                               class="form-control @error('username') is-invalid @enderror"
                               value="{{ old('username', $user->username) }}" required>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="password">Password <small class="text-muted">(kosongkan jika tidak diubah)</small></label>
                        <div class="input-group">
                            <input type="password" name="password" id="password"
                                   class="form-control @error('password') is-invalid @enderror">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Toggle password visibility">Lihat</button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="password_confirmation">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="role">Role <span class="text-danger">*</span></label>
                        <select name="role" id="role" class="form-control @error('role') is-invalid @enderror" required>
                            <option value="">Pilih Role</option>
                            <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="evaluator" {{ old('role', $user->role) == 'evaluator' ? 'selected' : '' }}>Evaluator</option>
                            <option value="mahasiswa" {{ old('role', $user->role) == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group d-flex align-items-end">
                    <div>
                        <button type="submit" class="btn btn-primary btn-sm">
                            Perbarui
                        </button>
                        <a href="{{ route('user.index') }}" class="btn btn-secondary btn-sm ml-2">
                            Kembali
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const p = document.getElementById('password');
    const btn = document.getElementById('togglePassword');
    if (!p || !btn) return;
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        if (p.type === 'password') {
            p.type = 'text';
            btn.textContent = 'Sembunyikan';
        } else {
            p.type = 'password';
            btn.textContent = 'Lihat';
        }
    });
});
</script>
@endsection
