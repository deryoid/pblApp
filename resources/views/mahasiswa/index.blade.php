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
                                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-users mr-2"></i>Kelompok Saya</h6>
                                    @if($kelompokSaya)
                                        <span class="badge badge-info">{{ $kelompokSaya->nama_kelompok }}</span>
                                    @endif
                                </div>
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

                    {{-- Content Row --}}   
                </div>
                @endsection
