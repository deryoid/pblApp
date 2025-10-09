<div class="table-responsive">
    @if($kunjungans->count() > 0)
        <table class="table table-bordered table-hover">
            <thead class="thead-light">
                <tr>
                    <th>Tanggal</th>
                    <th>Periode</th>
                    <th>Kelompok</th>
                    <th>Perusahaan</th>
                    <th>Alamat</th>
                    <th>Status</th>
                    <th>Diinput Oleh</th>
                    <th>Bukti</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kunjungans as $kunjungan)
                    <tr>
                        <td>
                            <span class="badge badge-info">
                                {{ date('d M Y', strtotime($kunjungan->tanggal_kunjungan)) }}
                            </span>
                        </td>
                        <td>
                            @if($kunjungan->periode_nama)
                                <span class="badge badge-secondary">{{ htmlspecialchars($kunjungan->periode_nama) }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($kunjungan->kelompok_nama)
                                <span class="badge badge-primary">{{ htmlspecialchars($kunjungan->kelompok_nama) }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <strong>{{ htmlspecialchars($kunjungan->perusahaan) }}</strong>
                        </td>
                        <td>
                            <small class="text-muted">{{ htmlspecialchars(substr($kunjungan->alamat, 0, 80)) }}</small>
                        </td>
                        <td>
                            @php
                                $statusBadge = [
                                    'Sudah dikunjungi' => 'success',
                                    'Proses Pembicaraan' => 'info',
                                    'Tidak ada tanggapan' => 'warning',
                                    'Ditolak' => 'danger',
                                ];
                                $badgeClass = $statusBadge[$kunjungan->status_kunjungan] ?? 'secondary';
                            @endphp
                            <span class="badge badge-{{ $badgeClass }}">
                                {{ htmlspecialchars($kunjungan->status_kunjungan) }}
                            </span>
                        </td>
                        <td>
                            {{ $kunjungan->user_nama ? htmlspecialchars($kunjungan->user_nama) : '-' }}
                        </td>
                        <td>
                            @if($kunjungan->bukti_kunjungan)
                                <button class="btn btn-sm btn-success" onclick="showBukti('{{ $kunjungan->id }}')" title="Lihat Bukti">
                                    <i class="fas fa-image"></i>
                                </button>
                            @else
                                <span class="text-muted" title="Tidak ada bukti">
                                    <i class="fas fa-times-circle"></i>
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            Tidak ada data kunjungan mitra
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination Links -->
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted">
                Menampilkan {{ $kunjungans->firstItem() }} sampai {{ $kunjungans->lastItem() }} dari {{ $kunjungans->total() }} total kunjungan
            </div>
            {{ $kunjungans->links('pagination::bootstrap-4') }}
        </div>
    @else
        <div class="text-center py-4">
            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
            <p class="text-muted">Tidak ada data kunjungan mitra di database</p>
        </div>
    @endif
</div>