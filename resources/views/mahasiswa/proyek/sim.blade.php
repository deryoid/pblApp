@extends('layout.app')
@section('content')
<div class="container-fluid">
  <div class="d-flex align-items-center mb-3 flex-wrap">
    <div class="d-flex align-items-baseline mr-3 mb-2 mb-md-0">
      <h1 class="h3 text-gray-800 mb-0">Simulasi Proyek</h1>
      <span class="ml-3 small text-muted">Preview kartu dengan skema revisi</span>
    </div>
  </div>

  @php
    // Data simulasi list + cards (menggunakan skema revisi)
    $lists = [
      [
        'id' => 'list-backlog', 'title' => 'Backlog',
        'cards' => [
          [
            'id' => 'c-1001',
            'title' => 'Integrasi API Mitra X',
            'description' => 'Menyusun kontrak API, autentikasi, dan endpoint dasar.',
            'labels' => ['Backend','Prioritas'],
            'nama_mitra' => 'PT Satu Maju',
            'skema_pbl' => 'Penelitian',
            'tanggal_mulai' => '2025-09-20',
            'tanggal_selesai' => now()->addDays(10)->toDateString(),
            'biaya_barang' => 1500000,
            'biaya_jasa' => 0,
            'progress' => 10,
            'kendala' => 'Menunggu akses sandbox dari mitra.',
            'catatan' => 'Butuh rapat kickoff pekan ini.',
            'status_proyek' => 'Proses',
            'link_drive_proyek' => 'https://drive.google.com/sim-proyek-1',
            'members' => ['Andi Nugraha','Rika Kusuma'],
            'comments' => 2,
            'attachments' => 1,
          ],
        ],
      ],
      [
        'id' => 'list-progress', 'title' => 'Dalam Proses',
        'cards' => [
          [
            'id' => 'c-1002',
            'title' => 'Dashboard Monitoring IoT',
            'description' => 'Visualisasi data sensor dan kontrol perangkat.',
            'labels' => ['Frontend','IoT'],
            'nama_mitra' => 'CV Dua Karya',
            'skema_pbl' => 'PBL x TeFa',
            'tanggal_mulai' => now()->subDays(5)->toDateString(),
            'tanggal_selesai' => now()->addDays(3)->toDateString(),
            'biaya_barang' => 500000,
            'biaya_jasa' => 1000000,
            'progress' => 65,
            'kendala' => null,
            'catatan' => 'Perlu validasi desain dengan mitra.',
            'status_proyek' => 'Proses',
            'link_drive_proyek' => null,
            'members' => ['Eka Sholeha'],
            'comments' => 5,
            'attachments' => 2,
          ],
        ],
      ],
      [
        'id' => 'list-done', 'title' => 'Selesai',
        'cards' => [
          [
            'id' => 'c-1003',
            'title' => 'Otomasi Laporan Harian',
            'description' => 'Template dan generator laporan PDF terjadwal.',
            'labels' => ['Automation'],
            'nama_mitra' => 'UD Tiga Sejahtera',
            'skema_pbl' => 'Pengabdian',
            'tanggal_mulai' => now()->subDays(20)->toDateString(),
            'tanggal_selesai' => now()->subDays(1)->toDateString(),
            'biaya_barang' => 0,
            'biaya_jasa' => 750000,
            'progress' => 100,
            'kendala' => null,
            'catatan' => 'Sudah diserahterimakan.',
            'status_proyek' => 'Selesai',
            'link_drive_proyek' => 'https://drive.google.com/sim-proyek-3',
            'members' => ['Dewi Indra'],
            'comments' => 1,
            'attachments' => 0,
          ],
        ],
      ],
    ];

    function schemeBadgeClass($skema) {
      return match ($skema) {
        'Penelitian' => 'badge-primary',
        'Pengabdian' => 'badge-info',
        'Lomba' => 'badge-warning',
        'PBL x TeFa' => 'badge-success',
        default => 'badge-secondary',
      };
    }
    function statusBadgeClass($st) {
      return match ($st) {
        'Proses' => 'badge-primary',
        'Dibatalkan' => 'badge-danger',
        'Selesai' => 'badge-success',
        default => 'badge-light',
      };
    }
  @endphp

  <div class="board-wrapper">
    <div class="board d-flex">
      @foreach ($lists as $list)
        <div class="board-column" data-col-id="{{ $list['id'] }}">
          <div class="d-flex align-items-center mb-2">
            <h6 class="mb-0 text-uppercase text-muted">{{ $list['title'] }}</h6>
            <span class="badge badge-secondary ml-2">{{ count($list['cards']) }}</span>
          </div>
          <div class="board-list" data-list-id="{{ $list['id'] }}">
            @forelse ($list['cards'] as $card)
              @php $isOverdue = !empty($card['tanggal_selesai']) && \Carbon\Carbon::parse($card['tanggal_selesai'])->isPast() && ($card['status_proyek'] ?? '')!=='Selesai'; @endphp
              <div class="card board-card shadow-sm mb-2" data-card-id="{{ $card['id'] }}">
                <div class="card-body py-2 d-flex flex-column">
                  {{-- Labels --}}
                  <div class="mb-1 d-flex align-items-center">
                    @if (!empty($card['labels']))
                      @foreach ($card['labels'] as $label)
                        <span class="badge badge-pill badge-info mr-1">{{ $label }}</span>
                      @endforeach
                    @endif
                    <span class="badge {{ schemeBadgeClass($card['skema_pbl'] ?? '') }} ml-auto">{{ $card['skema_pbl'] ?? '-' }}</span>
                  </div>

                  {{-- Title + Status --}}
                  <div class="d-flex align-items-center mb-1">
                    <div class="font-weight-bold small flex-grow-1">{{ $card['title'] }}</div>
                    <span class="badge {{ statusBadgeClass($card['status_proyek'] ?? '') }} ml-2">{{ $card['status_proyek'] ?? 'Proses' }}</span>
                  </div>

                  {{-- Mitra --}}
                  @if (!empty($card['nama_mitra']))
                    <div class="text-muted small mb-1"><i class="fas fa-building mr-1"></i> {{ $card['nama_mitra'] }}</div>
                  @endif

                  {{-- Deskripsi --}}
                  @if (!empty($card['description']))
                    <div class="text-muted small mb-2 card-desc">{{ \Illuminate\Support\Str::limit($card['description'], 140) }}</div>
                  @endif

                  {{-- Progress --}}
                  <div class="d-flex align-items-center mb-2">
                    <div class="progress progress-sm flex-grow-1 mr-2">
                      <div class="progress-bar" role="progressbar" style="width: {{ (int)($card['progress'] ?? 0) }}%" aria-valuenow="{{ (int)($card['progress'] ?? 0) }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <span class="small text-muted">{{ (int)($card['progress'] ?? 0) }}%</span>
                  </div>

                  {{-- Tanggal & Biaya --}}
                  <div class="d-flex align-items-center text-muted small mb-2">
                    @if(!empty($card['tanggal_mulai']) || !empty($card['tanggal_selesai']))
                      <span class="mr-3 {{ $isOverdue ? 'text-danger' : '' }}">
                        <i class="far fa-calendar-alt mr-1"></i>
                        {{ $card['tanggal_mulai'] ? \Carbon\Carbon::parse($card['tanggal_mulai'])->format('d M') : '?' }}
                        —
                        {{ $card['tanggal_selesai'] ? \Carbon\Carbon::parse($card['tanggal_selesai'])->format('d M') : '?' }}
                      </span>
                    @endif
                    @php $biayaTotal = (float)($card['biaya_barang'] ?? 0) + (float)($card['biaya_jasa'] ?? 0); @endphp
                    @if($biayaTotal > 0)
                      <span class="mr-3"><i class="fas fa-money-bill-wave mr-1"></i>Rp {{ number_format($biayaTotal,0,',','.') }}</span>
                    @endif
                    @if(!empty($card['link_drive_proyek']))
                      <a href="{{ $card['link_drive_proyek'] }}" target="_blank" rel="noopener" class="ml-auto btn btn-light btn-sm" title="Drive Proyek"><i class="fab fa-google-drive"></i></a>
                    @endif
                  </div>

                  {{-- Members / Kendala / Counters --}}
                  <div class="d-flex align-items-center text-muted small mt-auto">
                    @if(!empty($card['members']))
                      <div class="d-flex align-items-center mr-2">
                        @foreach ($card['members'] as $nm)
                          @php $ini = collect(explode(' ', $nm))->map(fn($p)=>mb_substr($p,0,1))->join(''); @endphp
                          <span class="avatar-initial" title="{{ $nm }}">{{ mb_strtoupper($ini) }}</span>
                        @endforeach
                      </div>
                    @endif
                    <span class="ml-auto d-flex align-items-center">
                      <span class="mr-2"><i class="far fa-comment mr-1"></i>{{ $card['comments'] ?? 0 }}</span>
                      <span><i class="fas fa-paperclip mr-1"></i>{{ $card['attachments'] ?? 0 }}</span>
                    </span>
                  </div>
                </div>
              </div>
            @empty
              <div class="text-muted small">Belum ada kartu</div>
            @endforelse
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>

@push('styles')
<style>
  .board-wrapper { overflow-x: auto; overflow-y: hidden; }
  .board { min-height: 70vh; padding-bottom: .5rem; }
  .board-column { width: 300px; min-width: 300px; margin-right: 1rem; }
  .board-column:last-child { margin-right: 0; }
  .board-list { min-height: 20px; }
  .board-card { border-left: 3px solid #4e73df; height: 230px; overflow: hidden; }
  .progress.progress-sm { height: 6px; }
  .avatar-initial { display:inline-flex; align-items:center; justify-content:center; width: 24px; height:24px; border-radius:50%; background:#f1f2f6; color:#4e73df; font-size:.75rem; font-weight:700; border:1px solid rgba(0,0,0,.05); margin-right:4px; }
  .card-desc { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>
@endpush
@endsection
