<div class="table-responsive">
    @if($kunjungans->count() > 0)
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th style="white-space: nowrap;">Tanggal</th>
                    <th style="white-space: nowrap;">Periode</th>
                    <th style="white-space: nowrap;">Kelompok</th>
                    <th style="min-width: 180px;">Perusahaan</th>
                    <th style="min-width: 200px;">Alamat</th>
                    <th class="text-center" style="white-space: nowrap;">Status</th>
                    <th style="white-space: nowrap;">Diinput Oleh</th>
                    <th class="text-center" style="white-space: nowrap;">Bukti</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kunjungans as $kunjungan)
                    <tr>
                        <td style="white-space: nowrap;">
                            <span class="badge badge-info">
                                {{ date('d M Y', strtotime($kunjungan->tanggal_kunjungan)) }}
                            </span>
                        </td>
                        <td style="white-space: nowrap;">
                            @if($kunjungan->periode_nama)
                                <span class="badge badge-secondary">{{ htmlspecialchars($kunjungan->periode_nama) }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td style="white-space: nowrap;">
                            @if($kunjungan->kelompok_nama)
                                <span class="badge badge-primary">{{ htmlspecialchars($kunjungan->kelompok_nama) }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <strong class="text-dark d-block text-wrap-neat">{{ htmlspecialchars($kunjungan->perusahaan) }}</strong>
                        </td>
                        <td>
                            <small class="text-muted d-block text-wrap-neat" style="line-height: 1.4;">{{ htmlspecialchars(substr($kunjungan->alamat, 0, 100)) }}</small>
                        </td>
                        <td class="text-center" style="white-space: nowrap;">
                            @php
                                $statusBadge = [
                                    'Sudah dikunjungi' => 'success',
                                    'Proses Pembicaraan' => 'info',
                                    'Tidak ada tanggapan' => 'warning',
                                    'Ditolak' => 'danger',
                                ];
                                $badgeClass = $statusBadge[$kunjungan->status_kunjungan] ?? 'secondary';
                            @endphp
                            <span class="badge badge-{{ $badgeClass }} px-2 py-1">
                                {{ htmlspecialchars($kunjungan->status_kunjungan) }}
                            </span>
                        </td>
                        <td style="white-space: nowrap;">
                            <span class="small font-weight-600 text-dark">{{ $kunjungan->user_nama ? htmlspecialchars($kunjungan->user_nama) : '-' }}</span>
                        </td>
                        <td class="text-center" style="white-space: nowrap;">
                            <button class="btn btn-sm btn-outline-success shadow-none rounded-pill px-3" onclick="showBukti('{{ $kunjungan->id }}')" title="Lihat Foto Bukti">
                                <i class="fas fa-image mr-1"></i> Foto
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            Tidak ada data kunjungan mitra
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination Links -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 mt-4 pt-3 border-top">
            <div class="text-muted small mb-2 mb-sm-0">
                Menampilkan {{ $kunjungans->firstItem() }} sampai {{ $kunjungans->lastItem() }} dari {{ $kunjungans->total() }} total kunjungan
            </div>
            <div>
                {{ $kunjungans->links('pagination::bootstrap-4') }}
            </div>
        </div>
    @else
        <div class="text-center py-5">
            <i class="fas fa-inbox fa-3x text-muted mb-3" style="opacity: 0.4;"></i>
            <h6 class="text-dark font-weight-bold">Tidak Ada Data Kunjungan Mitra</h6>
            <p class="text-muted small mb-0">Belum ada riwayat kunjungan industri yang dicatat dalam sistem.</p>
        </div>
    @endif
</div>