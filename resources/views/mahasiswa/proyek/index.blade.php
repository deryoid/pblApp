@extends('layout.app')
@section('content')
@push('styles')
    <style>
      @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

      /* Typography & Global adjustments */
      .container-fluid, .board-wrapper {
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
      }

      /* Kanban Board Container */
      .board-wrapper {
        overflow-x: auto;
        overflow-y: hidden;
        padding-bottom: 20px;
        -ms-overflow-style: none;
        scrollbar-width: none;
      }
      .board-wrapper::-webkit-scrollbar {
        display: none;
      }
      .board {
        display: inline-flex;
        align-items: flex-start;
        gap: 1.5rem;
        padding: 0.5rem 0.2rem;
        min-height: 70vh;
      }

      /* Board Columns (Lists) */
      .board-column {
        background: #f4f5f7;
        border-radius: 12px;
        width: 320px;
        min-width: 320px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        max-height: calc(100vh - 160px);
        border: 1px solid #ebecf0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        transition: transform 0.2s cubic-bezier(0.2, 0, 0, 1), opacity 0.2s ease;
      }

      /* Column Header */
      .board-col-head {
        padding: 1rem 1.2rem;
        background: #f4f5f7;
        border-bottom: 1px solid rgba(9, 30, 66, 0.08);
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
        position: sticky;
        top: 0;
        z-index: 10;
        cursor: grab;
      }
      .board-col-head:active {
        cursor: grabbing;
      }
      .board-col-head h6 {
        font-weight: 600;
        color: #172b4d;
        font-size: 0.95rem;
      }

      /* The scrollable list area */
      .board-list {
        padding: 0.8rem;
        overflow-y: auto;
        overflow-x: hidden;
        flex: 1;
        min-height: 120px;
      }
      .board-list::-webkit-scrollbar {
        width: 6px;
      }
      .board-list::-webkit-scrollbar-thumb {
        background: #c1c7d0;
        border-radius: 10px;
      }

      /* Board Cards (Modern & Clear) */
      .board-card {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        border-left: 4px solid #3b82f6; /* default blue */
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.02);
        cursor: grab;
        transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.2s ease;
        position: relative;
        overflow: hidden;
        user-select: none;
        margin-bottom: 0.85rem !important;
      }
      .board-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px -3px rgba(15, 23, 42, 0.08), 0 4px 6px -2px rgba(15, 23, 42, 0.04);
        border-color: #cbd5e1;
      }
      .board-card:active {
        cursor: grabbing;
      }

      /* Sortable drag state */
      .board-card.sortable-chosen {
        opacity: 0.95;
        cursor: grabbing !important;
        transform: scale(1.02);
        box-shadow: 0 10px 25px -5px rgba(78, 115, 223, 0.25);
        z-index: 1000;
      }
      .board-card.sortable-ghost {
        opacity: 0.35 !important;
        background: rgba(78, 115, 223, 0.08) !important;
        border: 2px dashed #4e73df !important;
        border-radius: 12px !important;
        box-shadow: none !important;
        transform: scale(0.98);
      }
      .board-card.sortable-drag,
      .sortable-fallback {
        opacity: 1 !important;
        cursor: grabbing !important;
        transform: rotate(2deg) scale(1.03) !important;
        box-shadow: 0 16px 30px rgba(78, 115, 223, 0.3) !important;
        border-left: 4px solid #4e73df !important;
        border-radius: 12px !important;
        z-index: 99999 !important;
      }

      /* Dynamic Skema Left Borders */
      .board-card.skema-border-eksternal {
        border-left-color: #10b981 !important;
      }
      .board-card.skema-border-tefa {
        border-left-color: #8b5cf6 !important;
      }
      .board-card.skema-border-internal {
        border-left-color: #3b82f6 !important;
      }
      .board-card.skema-border-lomba {
        border-left-color: #ef4444 !important;
      }
      .board-card.skema-border-penelitian {
        border-left-color: #f59e0b !important;
      }

      /* Card Completed State */
      .board-card.card-completed {
        background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%) !important;
        border-left: 4px solid #10b981 !important;
        border-color: #bbf7d0;
      }

      /* Card Top Row */
      .card-top-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 0.55rem;
      }

      .card-top-badges {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.35rem;
      }

      /* Skema Pill Badges */
      .card-skema-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.25rem 0.55rem;
        border-radius: 6px;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        line-height: 1;
      }
      .card-skema-pill.pill-eksternal {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
      }
      .card-skema-pill.pill-tefa {
        background: #f5f3ff;
        color: #5b21b6;
        border: 1px solid #ddd6fe;
      }
      .card-skema-pill.pill-internal {
        background: #eff6ff;
        color: #1e40af;
        border: 1px solid #bfdbfe;
      }
      .card-skema-pill.pill-lomba {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
      }
      .card-skema-pill.pill-other {
        background: #f8fafc;
        color: #475569;
        border: 1px solid #e2e8f0;
      }

      /* Card Status Selesai Ribbon */
      .card-done-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.68rem;
        font-weight: 700;
        padding: 0.22rem 0.5rem;
        border-radius: 6px;
        background: #10b981;
        color: #ffffff;
      }

      /* Card Header Actions */
      .card-actions-toolbar {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
      }
      .btn-card-action {
        width: 26px;
        height: 26px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        background: #f8fafc;
        color: #64748b;
        border: 1px solid #e2e8f0;
        font-size: 0.75rem;
        transition: all 0.15s ease;
        cursor: pointer;
      }
      .btn-card-action:hover {
        background: #ffffff;
        color: #0f172a;
        border-color: #cbd5e1;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
      }
      .btn-card-action.btn-action-edit:hover {
        background: #eff6ff;
        color: #2563eb;
        border-color: #bfdbfe;
      }
      .btn-card-action.btn-action-share:hover {
        background: #ecfeff;
        color: #0891b2;
        border-color: #a5f3fc;
      }
      .btn-card-action.btn-action-delete:hover {
        background: #fef2f2;
        color: #dc2626;
        border-color: #fecaca;
      }

      /* Card Project Title */
      .card-title-hero {
        font-size: 0.95rem;
        font-weight: 700;
        line-height: 1.4;
        color: #0f172a;
        margin-bottom: 0.45rem;
        word-break: break-word;
        transition: color 0.15s ease;
        cursor: pointer;
      }
      .card-title-hero:hover {
        color: #2563eb;
      }

      /* Timeline Date Badge */
      .card-timeline-row {
        margin-bottom: 0.55rem;
      }
      .card-timeline-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.28rem 0.6rem;
        border-radius: 6px;
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
      }
      .card-timeline-chip.timeline-overdue {
        background: #fef2f2;
        color: #b91c1c;
        border-color: #fecaca;
      }
      .overdue-pill {
        background: #ef4444;
        color: #ffffff;
        padding: 0.1rem 0.35rem;
        border-radius: 4px;
        font-size: 0.65rem;
        font-weight: 700;
        margin-left: 0.25rem;
      }

      /* Mitra Industri Box */
      .card-mitra-box {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.35rem 0.6rem;
        margin-bottom: 0.55rem;
        font-size: 0.75rem;
        color: #334155;
      }
      .card-mitra-box i {
        color: #0284c7;
        font-size: 0.85rem;
        flex-shrink: 0;
      }
      .mitra-name {
        font-weight: 700;
        color: #0f172a;
      }
      .mitra-contact {
        color: #64748b;
        margin-left: 0.35rem;
        font-size: 0.7rem;
      }

      /* Mata Kuliah Section */
      .card-matkul-section {
        background: #fafafa;
        border: 1px solid #f0f0f0;
        border-radius: 8px;
        padding: 0.45rem 0.6rem;
        margin-bottom: 0.55rem;
      }
      .card-matkul-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.7rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 0.35rem;
      }
      .card-matkul-item {
        font-size: 0.73rem;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 0.25rem 0.5rem;
        margin-bottom: 0.25rem;
        line-height: 1.35;
      }
      .card-matkul-item:last-child {
        margin-bottom: 0;
      }
      .matkul-name {
        font-weight: 600;
        color: #1e40af;
      }
      .matkul-dosen {
        font-size: 0.68rem;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 0.25rem;
        margin-top: 1px;
      }
      .btn-toggle-matkul {
        border: none;
        background: none;
        color: #2563eb;
        font-size: 0.68rem;
        font-weight: 600;
        cursor: pointer;
        padding: 0;
        margin-top: 0.2rem;
        display: inline-block;
      }
      .btn-toggle-matkul:hover {
        text-decoration: underline;
      }

      /* Card Description */
      .card-desc-snippet {
        font-size: 0.8rem;
        line-height: 1.45;
        color: #475569;
        margin-bottom: 0.55rem;
      }

      /* Modern Progress Bar */
      .card-progress-box {
        margin-bottom: 0.6rem;
      }
      .progress-label-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.72rem;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 0.25rem;
      }
      .progress-bar-track {
        height: 6px;
        background: #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
      }
      .progress-bar-fill {
        height: 100%;
        border-radius: 10px;
        transition: width 0.3s ease;
      }
      .fill-green { background: #10b981; }
      .fill-blue { background: #3b82f6; }
      .fill-amber { background: #f59e0b; }
      .fill-gray { background: #94a3b8; }
      .text-green { color: #059669 !important; }
      .text-blue { color: #2563eb !important; }
      .text-amber { color: #d97706 !important; }
      .text-gray { color: #64748b !important; }

      /* Card Footer Row */
      .card-footer-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.4rem;
        padding-top: 0.5rem;
        border-top: 1px solid #f1f5f9;
        font-size: 0.75rem;
        flex-wrap: wrap;
      }
      .card-footer-left {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        min-width: 0;
      }
      .card-footer-right {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        margin-left: auto;
      }

      /* Status Badges */
      .card-status-pill {
        display: inline-flex;
        align-items: center;
        font-size: 0.68rem;
        font-weight: 700;
        padding: 0.2rem 0.5rem;
        border-radius: 6px;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        line-height: 1;
      }
      .card-status-pill.status-selesai {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
      }
      .card-status-pill.status-proses {
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
      }
      .card-status-pill.status-dibatalkan {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
      }

      /* Creator Mini Chip */
      .creator-mini-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.7rem;
        color: #64748b;
        max-width: 120px;
      }
      .avatar-nano {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
      }
      .creator-mini-name {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      /* File Link Buttons */
      .btn-card-link-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.2rem 0.45rem;
        border-radius: 5px;
        font-size: 0.7rem;
        font-weight: 600;
        text-decoration: none !important;
        transition: all 0.15s ease;
        line-height: 1;
      }
      .btn-card-link-badge.link-pdf {
        background: #fff1f2;
        color: #e11d48;
        border: 1px solid #fecdd3;
      }
      .btn-card-link-badge.link-pdf:hover {
        background: #ffe4e6;
        color: #be123c;
      }
      .btn-card-link-badge.link-drive {
        background: #f0fdf4;
        color: #16a34a;
        border: 1px solid #bbf7d0;
      }
      .btn-card-link-badge.link-drive:hover {
        background: #dcfce7;
        color: #15803d;
      }

      /* Comment Count */
      .card-comment-count {
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
        color: #94a3b8;
        font-size: 0.72rem;
        font-weight: 600;
      }
      
      /* Global Additions */
      .btn-add-card, .btn-edit-list {
        font-size: 0.75rem;
        font-weight: 500;
        padding: 0.3rem 0.6rem;
        border-radius: 6px;
        box-shadow: none;
      }
      .truncate {
        max-width: 150px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }
      .x-small { font-size: .75rem; }
      .text-body-2 { font-size: .88rem; line-height: 1.35; }
      .clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
      }
      .user-chip {
        display: inline-flex;
        align-items: center;
        border: 1px solid rgba(0,0,0,.06);
        border-radius: 12px;
        padding: 0 6px 0 2px;
        background: #fff;
      }
      .user-name {
        max-width: 140px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }
      .user-avatar {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        margin-right: 6px;
        object-fit: cover;
      }
      .border-top-light { border-top: 1px solid #f1f2f4; }
      .btn-drive { padding: .18rem .5rem; }
      .labels-wrap { display: flex; flex-wrap: wrap; }
    </style>
@endpush
<div class="container-fluid">
  @if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm d-flex flex-column p-3 mb-4 rounded-xl alert-dismissible fade show" role="alert">
      <div class="d-flex align-items-center mb-1">
        <i class="fas fa-exclamation-circle text-danger mr-2"></i>
        <strong class="text-danger">Terjadi kesalahan pada formulir proyek:</strong>
      </div>
      <ul class="mb-0 pl-4 text-dark small">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  @endif
  @php
    if (!function_exists('schemeBadgeClass')) {
      function schemeBadgeClass($skema) {
        $map = [
          'Internal' => 'badge-primary',
          'External' => 'badge-success',
          'Eksternal' => 'badge-success',
          'Hibah/Penelitian' => 'badge-warning',
          'Lomba' => 'badge-danger',
          'Teaching Factory' => 'badge-info',
          'TeFa' => 'badge-info',
          'Penelitian' => 'badge-primary',
          'Pengabdian' => 'badge-info',
          'PBL x TeFa' => 'badge-success',
        ];
        return $map[$skema] ?? 'badge-secondary';
      }
    }
    if (!function_exists('statusBadgeClass')) {
      function statusBadgeClass($st) {
        $map = ['Proses'=>'badge-primary','Dibatalkan'=>'badge-danger','Selesai'=>'badge-success'];
        return $map[$st] ?? 'badge-light';
      }
    }
    if (!function_exists('avatarUrl')) {
      function avatarUrl(?string $name): string {
        $name = trim((string)$name); if ($name === '') $name = 'U';
        $palette = ['4e73df','1cc88a','36b9cc','f6c23e','e74a3b','858796'];
        $idx = abs(crc32($name)) % count($palette); $bg = $palette[$idx]; $enc = urlencode($name);
        return "https://ui-avatars.com/api/?name={$enc}&background={$bg}&color=ffffff&size=64&rounded=true";
      }
    }
  @endphp

  @if(!isset($kelompok) || !$kelompok)
    <div class="alert alert-warning border-0 shadow-sm d-flex flex-column flex-md-row align-items-md-center justify-content-between p-3 mb-4 rounded-xl">
      <div class="mb-2 mb-md-0">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        Anda belum memiliki kelompok pada periode PBL {{ $periodeAktif?->periode ?? 'terbaru' }}. Kelompok PBL diulang setiap periode baru. Silakan bentuk kelompok baru atau terima undangan bergabung terlebih dahulu.
      </div>
      <a href="{{ route('mahasiswa.kelompok.index') }}" class="btn btn-sm btn-primary shadow-sm font-weight-bold flex-shrink-0">
        <i class="fas fa-users mr-1"></i> Kelola Kelompok
      </a>
    </div>
  @endif

  <div class="d-flex align-items-center mb-3 flex-wrap">
    <div class="d-flex align-items-baseline mr-3 mb-2 mb-md-0">
      <h1 class="h3 text-gray-800 mb-0">Proyek/Kegiatan</h1>
      @if(isset($kelompok) && $kelompok)
        <span class="ml-3 small text-muted">
          <strong>{{ $kelompok->nama_kelompok }}</strong>
          @if(isset($periodeAktif) && $periodeAktif)
            <span class="badge badge-light border ml-2">{{ $periodeAktif->periode }}</span>
          @endif
        </span>
      @endif
    </div>
    <div class="ml-auto d-flex align-items-center">
      @if(isset($kelompok) && $kelompok)
        <button type="button" class="btn btn-primary btn-sm shadow-sm mr-2 btn-add-card" data-list-id="{{ $lists[0]['id'] ?? '' }}" data-toggle="modal" data-target="#modalAddCard" title="Tambah Proyek">
          <i class="fas fa-plus mr-1"></i> Tambah Proyek
        </button>
        <button id="btnAddList" type="button" class="btn btn-outline-success btn-sm shadow-sm mr-2" data-toggle="modal" data-target="#modalAddList" title="Tambah Kolom">
          <i class="fas fa-columns mr-1"></i> Tambah Kolom
        </button>
      @endif
      <input id="boardSearch" type="text" class="form-control form-control-sm" placeholder="Cari kartu…" style="max-width: 220px;">
    </div>
  </div>

  @if(isset($lists) && count($lists) > 0)
  <div class="board-wrapper">
    <div id="board" class="board d-flex">
      @foreach (($lists ?? []) as $list)
        <div class="board-column" data-col-id="{{ $list['id'] }}">
          {{-- HEAD kolom --}}
          <div class="board-col-head">
            <div class="d-flex align-items-center">
              <h6 class="mb-0 text-uppercase text-dark font-weight-bold truncate">{{ $list['title'] }}</h6>
              <span class="badge badge-soft ml-2" title="Jumlah proyek">{{ count($list['cards'] ?? []) }}</span>

              <div class="ml-auto btn-group btn-group-sm" role="group" aria-label="Aksi Kolom">
                <button class="btn btn-success btn-add-card"
                        data-list-id="{{ $list['id'] }}"
                        data-toggle="modal"
                        data-target="#modalAddCard"
                        title="Tambah Proyek">
                  <i class="fas fa-plus mr-1"></i> Proyek/Kegiatan
                </button>

                <button class="btn btn-primary btn-edit-list"
                        data-list-id="{{ $list['id'] }}"
                        data-list-name="{{ $list['title'] }}"
                        data-toggle="modal"
                        data-target="#modalEditList"
                        title="Ubah kolom">
                  <i class="fas fa-edit"></i>
                </button>

                <form method="POST" action="{{ route('proyek.lists.destroy', ['list' => $list['id']]) }}" class="m-0">
                  @csrf @method('DELETE')
                  <button type="button" class="btn btn-danger btn-delete-list ml-2" title="Hapus kolom">
                    <i class="fas fa-trash"></i>
                  </button>
                </form>
              </div>
            </div>
          </div>

          {{-- LIST kartu --}}
          <div class="board-list" data-list-id="{{ $list['id'] }}">
            @forelse (($list['cards'] ?? []) as $card)
              @php
                $tglMulai   = $card['tanggal_mulai']  ?? null;
                $tglSelesai = $card['tanggal_selesai']?? ($card['due'] ?? null);
                $isOverdue  = !empty($tglSelesai) && \Carbon\Carbon::parse($tglSelesai)->isPast() && (($card['status_proyek'] ?? '') !== 'Selesai');
                $rentang    = trim(
                                ( $tglMulai   ? \Carbon\Carbon::parse($tglMulai)->format('d M Y') : '?' )
                                .' – '.
                                ( $tglSelesai ? \Carbon\Carbon::parse($tglSelesai)->format('d M Y') : '?' )
                              );
                
                $skemaName = $card['skema_pbl'] ?? 'Internal';
                $skemaLower = strtolower($skemaName);
                if (str_contains($skemaLower, 'tefa') || str_contains($skemaLower, 'factory')) {
                    $skemaBorder = 'skema-border-tefa';
                    $skemaPill = 'pill-tefa';
                    $skemaIcon = 'fa-industry';
                } elseif (str_contains($skemaLower, 'eksternal') || str_contains($skemaLower, 'external')) {
                    $skemaBorder = 'skema-border-eksternal';
                    $skemaPill = 'pill-eksternal';
                    $skemaIcon = 'fa-handshake';
                } elseif (str_contains($skemaLower, 'lomba')) {
                    $skemaBorder = 'skema-border-lomba';
                    $skemaPill = 'pill-lomba';
                    $skemaIcon = 'fa-trophy';
                } elseif (str_contains($skemaLower, 'hibah') || str_contains($skemaLower, 'penelitian')) {
                    $skemaBorder = 'skema-border-penelitian';
                    $skemaPill = 'pill-other';
                    $skemaIcon = 'fa-microscope';
                } else {
                    $skemaBorder = 'skema-border-internal';
                    $skemaPill = 'pill-internal';
                    $skemaIcon = 'fa-laptop-code';
                }

                $progress = (int)($card['progress'] ?? 0);
                if ($progress >= 100 || ($card['status_proyek'] ?? '') === 'Selesai') {
                    $progressColor = 'green';
                } elseif ($progress >= 50) {
                    $progressColor = 'blue';
                } elseif ($progress > 0) {
                    $progressColor = 'amber';
                } else {
                    $progressColor = 'gray';
                }
                $isSelesai = ($card['status_proyek'] ?? '') === 'Selesai';
              @endphp

              <div
                class="card board-card {{ $skemaBorder }} {{ $isSelesai ? 'card-completed' : '' }}"
                data-card-id="{{ $card['id'] }}"
                data-title="{{ e($card['title']) }}"
                data-title-search="{{ strtolower($card['title']) }}"
                data-desc="{{ strtolower($card['description'] ?? '') }}"
                data-card='@json($card, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)'
              >
                <div class="card-body p-3 d-flex flex-column">

                  {{-- 1. ROW ATAS: Skema Badge + Status Selesai + Actions Toolbar --}}
                  <div class="card-top-row">
                    <div class="card-top-badges">
                      <span class="card-skema-pill {{ $skemaPill }}" title="Skema Proyek: {{ $skemaName }}">
                        <i class="fas {{ $skemaIcon }}"></i> {{ $skemaName }}
                      </span>
                      @if($isSelesai)
                        <span class="card-done-badge" title="Proyek Telah Selesai">
                          <i class="fas fa-check"></i> Selesai
                        </span>
                      @endif
                    </div>

                    {{-- Quick Action Buttons --}}
                    <div class="card-actions-toolbar">
                      <button type="button"
                              class="btn-card-action btn-action-edit btn-edit-card"
                              title="Ubah & Lengkapi Data Proyek" aria-label="Ubah proyek">
                        <i class="fas fa-pen"></i>
                      </button>
                      <button type="button"
                              class="btn-card-action btn-action-share btn-share-mitra"
                              title="Bagikan Link Penilaian Mitra"
                              data-card-id="{{ $card['id'] }}"
                              data-card-title="{{ e($card['title']) }}"
                              aria-label="Share link penilaian mitra">
                        <i class="fas fa-share-alt"></i>
                      </button>
                      <form method="POST"
                            action="{{ route('proyek.cards.destroy', ['card' => $card['id']]) }}"
                            class="m-0 d-inline">
                        @csrf @method('DELETE')
                        <button type="button"
                                class="btn-card-action btn-action-delete btn-delete-card"
                                title="Hapus Proyek Ini" aria-label="Hapus proyek">
                          <i class="fas fa-trash-alt"></i>
                        </button>
                      </form>
                    </div>
                  </div>

                  {{-- 2. JUDUL PROYEK (Hero Element) --}}
                  <div class="card-title-hero btn-edit-card" title="Klik untuk melihat atau mengedit proyek">
                    {{ $card['title'] }}
                  </div>

                  {{-- 3. JADWAL / TIMELINE PELAKSANAAN --}}
                  @if($tglMulai || $tglSelesai)
                    <div class="card-timeline-row">
                      <span class="card-timeline-chip {{ $isOverdue ? 'timeline-overdue' : '' }}" title="Periode Pelaksanaan: {{ $rentang }}">
                        <i class="far fa-calendar-alt"></i>
                        <span>{{ $rentang }}</span>
                        @if($isOverdue)
                          <span class="overdue-pill">Terlewat</span>
                        @endif
                      </span>
                    </div>
                  @endif

                  {{-- 4. MITRA INDUSTRI (Jika ada) --}}
                  @if (!empty($card['nama_mitra']))
                    <div class="card-mitra-box" title="Mitra Industri / Partner">
                      <i class="fas fa-building"></i>
                      <div class="text-truncate">
                        <span class="mitra-name">{{ $card['nama_mitra'] }}</span>
                        @if (!empty($card['kontak_mitra']))
                          <span class="mitra-contact">&bull; <i class="fas fa-phone-alt"></i> {{ $card['kontak_mitra'] }}</span>
                        @endif
                      </div>
                    </div>
                  @endif

                  {{-- 5. KETERKAITAN MATA KULIAH & DOSEN PENGAMPU --}}
                  @if (!empty($card['mata_kuliahs']))
                    <div class="card-matkul-section">
                      <div class="card-matkul-header">
                        <span><i class="fas fa-book-reader mr-1 text-primary"></i> Mata Kuliah Terkait ({{ count($card['mata_kuliahs']) }})</span>
                      </div>
                      <div class="card-matkul-list">
                        @foreach ($card['mata_kuliahs'] as $index => $mk)
                          <div class="card-matkul-item {{ $index >= 2 ? 'matkul-item-extra' : '' }}" style="{{ $index >= 2 ? 'display:none;' : '' }}" title="{{ $mk['nama_matkul'] }} (Semester {{ $mk['semester'] }}) - Pengampu: {{ $mk['dosen_pengampu'] ?? 'Belum Ditentukan' }}">
                            <div class="matkul-name text-truncate">
                              <i class="fas fa-book mr-1 text-primary"></i>
                              <strong>{{ $mk['nama_matkul'] }}</strong>
                              <span class="text-muted small">(Sem {{ $mk['semester'] }})</span>
                            </div>
                            @if(!empty($mk['dosen_pengampu']))
                              <div class="matkul-dosen text-truncate">
                                <i class="fas fa-chalkboard-teacher text-secondary"></i>
                                <span>{{ $mk['dosen_pengampu'] }}</span>
                              </div>
                            @endif
                          </div>
                        @endforeach
                        @if(count($card['mata_kuliahs']) > 2)
                          <button type="button" class="btn-toggle-matkul" onclick="const p = $(this).closest('.card-matkul-section'); p.find('.matkul-item-extra').slideToggle(120); const s = $(this).find('span'); s.text(s.text().includes('+') ? 'Tampilkan lebih ringkas' : '+{{ count($card['mata_kuliahs']) - 2 }} matkul lainnya');">
                            <span>+{{ count($card['mata_kuliahs']) - 2 }} matkul lainnya</span>
                          </button>
                        @endif
                      </div>
                    </div>
                  @endif

                  {{-- 6. DESKRIPSI SINGKAT (Jika ada) --}}
                  @if(!empty($card['description']))
                    <div class="card-desc-snippet clamp-2" title="{{ $card['description'] }}">
                      {{ \Illuminate\Support\Str::limit($card['description'], 140) }}
                    </div>
                  @endif

                  {{-- 7. PROGRESS BAR --}}
                  <div class="card-progress-box">
                    <div class="progress-label-row">
                      <span><i class="fas fa-chart-line mr-1"></i> Progres</span>
                      <span class="font-weight-700 text-{{ $progressColor }}">{{ $progress }}%</span>
                    </div>
                    <div class="progress-bar-track">
                      <div class="progress-bar-fill fill-{{ $progressColor }}" style="width: {{ $progress }}%"></div>
                    </div>
                  </div>

                  {{-- 8. LABELS / TOPIK TAGS (Jika ada) --}}
                  @if(!empty($card['labels']))
                    <div class="d-flex flex-wrap gap-1 mb-2">
                      @foreach ($card['labels'] as $label)
                        <span class="badge badge-light border text-secondary" style="font-size: 0.68rem; padding: 0.2rem 0.45rem;">
                          #{{ $label }}
                        </span>
                      @endforeach
                    </div>
                  @endif

                  {{-- 9. FOOTER: Status + Creator + Berkas Links + Komentar --}}
                  <div class="card-footer-row mt-auto">
                    <div class="card-footer-left">
                      <span class="card-status-pill status-{{ strtolower($card['status_proyek'] ?? 'proses') }}">
                        {{ $card['status_proyek'] ?? 'Proses' }}
                      </span>
                      @if(!empty($card['created_by_name']))
                        <div class="creator-mini-chip" title="Dibuat oleh {{ $card['created_by_name'] }} {{ $card['created_at_human'] ? '('.$card['created_at_human'].')' : '' }}">
                          <img class="avatar-nano" src="{{ ($card['created_by_avatar'] ?? '') ?: avatarUrl($card['created_by_name']) }}" alt="Avatar" loading="lazy">
                          <span class="creator-mini-name">{{ \Illuminate\Support\Str::limit($card['created_by_name'], 12) }}</span>
                        </div>
                      @endif
                    </div>

                    <div class="card-footer-right">
                      @if(!empty($card['proposal_url']))
                        <a href="{{ $card['proposal_url'] }}" target="_blank" rel="noopener"
                           class="btn-card-link-badge link-pdf" title="Lihat Berkas Proposal PDF">
                          <i class="fas fa-file-pdf"></i> Proposal
                        </a>
                      @endif
                      @if(!empty($card['link_drive_proyek']))
                        <a href="{{ $card['link_drive_proyek'] }}" target="_blank" rel="noopener"
                           class="btn-card-link-badge link-drive" title="Buka Berkas Luaran di Google Drive">
                          <i class="fab fa-google-drive"></i> Drive
                        </a>
                      @endif
                      @if(!empty($card['comments']) && $card['comments'] > 0)
                        <span class="card-comment-count" title="{{ $card['comments'] }} Komentar">
                          <i class="far fa-comment-dots"></i> {{ $card['comments'] }}
                        </span>
                      @endif
                    </div>
                  </div>

                </div>
              </div>
            @empty
              <div class="card card-empty shadow-0">
                <div class="card-body p-3 text-center text-muted small">
                  <i class="far fa-clipboard mr-1"></i> Belum ada proyek/kegiatan
                </div>
              </div>
            @endforelse
          </div>
        </div>
      @endforeach
    </div>
  </div>
  @else
  <div class="card shadow-sm border-0 rounded-xl">
    <div class="card-body text-center py-5">
      <div class="mb-3">
        <i class="fas fa-clipboard-list text-muted fa-3x"></i>
      </div>
      <h5 class="text-gray-800 font-weight-bold mb-2">Belum ada kolom untuk kelompok ini.</h5>
      <p class="text-muted small mb-4" style="max-width: 500px; margin: auto;">
        Mulai buat proyek kelompok Anda dengan menginisialisasi kolom standar Kanban atau menambahkan proyek/kolom pertama secara mandiri.
      </p>
      @if(isset($kelompok) && $kelompok)
        <div class="d-flex align-items-center justify-content-center flex-wrap gap-2">
          <button type="button" class="btn btn-primary btn-sm shadow-sm mr-2 btn-add-card" data-list-id="" data-toggle="modal" data-target="#modalAddCard">
            <i class="fas fa-plus mr-1"></i> Tambah Proyek Pertama
          </button>
          <button id="btnAddListEmpty" type="button" class="btn btn-success btn-sm shadow-sm mr-2" data-toggle="modal" data-target="#modalAddList">
            <i class="fas fa-columns mr-1"></i> Tambah Kolom Kustom
          </button>
          <form method="POST" action="{{ route('proyek.lists.quick-setup') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-primary btn-sm shadow-sm">
              <i class="fas fa-magic mr-1"></i> Gunakan Kolom Standar PBL
            </button>
          </form>
        </div>
      @endif
    </div>
  </div>
  @endif
</div>

{{-- Modal tambah kartu --}}
<div class="modal fade" id="modalAddCard" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Tambah Proyek/Kegiatan</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <form method="POST" action="{{ route('proyek.cards.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <input type="hidden" name="list" id="addCardListId" value="{{ old('list') }}">
          <div class="form-group">
            <label>Judul Proyek <span class="text-danger">*</span></label>
            <input name="title" type="text" class="form-control" value="{{ old('title') }}" placeholder="Contoh: Implementasi Sistem Monitoring IOT" required>
          </div>
          <div class="form-group">
            <label>Deskripsi Proyek</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Jelaskan ruang lingkup luaran proyek...">{{ old('description') }}</textarea>
          </div>
          <div class="form-group">
            <label>Label (pisahkan koma)</label>
            <input name="labels" type="text" class="form-control" value="{{ old('labels') }}" placeholder="Internal, Riset, IoT">
          </div>
          <div class="form-group">
            <label>Kontak Mitra (jika External)</label>
            <input name="kontak_mitra" type="text" class="form-control" value="{{ old('kontak_mitra') }}" placeholder="08xxxxxxxx">
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Tanggal Mulai</label>
              <input name="tanggal_mulai" type="date" class="form-control" value="{{ old('tanggal_mulai') }}">
            </div>
            <div class="form-group col-md-6">
              <label>Tanggal Selesai</label>
              <input name="tanggal_selesai" type="date" class="form-control" value="{{ old('tanggal_selesai') }}">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Skema Kategori Proyek <span class="text-danger">*</span></label>
              <select name="skema_pbl" id="addSkema" class="form-control" required>
                @php $currentSkema = old('skema_pbl', $kelompok->skema_kategori ?? 'Internal'); @endphp
                <option value="Internal" {{ $currentSkema === 'Internal' ? 'selected' : '' }}>Internal (Wajib Proposal)</option>
                <option value="External" {{ $currentSkema === 'External' ? 'selected' : '' }}>External (Mitra Industri)</option>
                <option value="Hibah/Penelitian" {{ $currentSkema === 'Hibah/Penelitian' ? 'selected' : '' }}>Hibah/Penelitian</option>
                <option value="Lomba" {{ $currentSkema === 'Lomba' ? 'selected' : '' }}>Lomba</option>
                <option value="Teaching Factory" {{ $currentSkema === 'Teaching Factory' ? 'selected' : '' }}>Teaching Factory</option>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label>Status</label>
              <select name="status_proyek" class="form-control">
                <option value="Proses" {{ old('status_proyek') === 'Proses' ? 'selected' : '' }}>Proses</option>
                <option value="Dibatalkan" {{ old('status_proyek') === 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
              </select>
            </div>
          </div>

          {{-- Keterkaitan Mata Kuliah --}}
          <div class="form-group">
            <label class="d-flex align-items-center justify-content-between mb-1">
              <span><i class="fas fa-book-reader text-primary mr-1"></i> Keterkaitan Mata Kuliah & Dosen Pengampu</span>
              <span id="addMatkulBadgeNote" class="badge badge-primary">Semester {{ $kelompokSemester ?? 1 }}</span>
            </label>
            <select name="mata_kuliah_ids[]" id="addMataKuliah" class="form-control select2-matkul" multiple="multiple" style="width: 100%;">
              @foreach(($mataKuliahList ?? []) as $mk)
                <option value="{{ $mk->id }}" data-semester="{{ $mk->semester }}">
                  [Sem {{ $mk->semester }}] {{ $mk->kode_matkul }} - {{ $mk->nama_matkul }} ({{ $mk->dosenPengampu->pluck('nama_user')->join(', ') ?: 'Dosen Belum Ditentukan' }})
                </option>
              @endforeach
            </select>
            <small id="addMatkulHelp" class="form-text text-muted">
              <i class="fas fa-info-circle mr-1 text-primary"></i> <span>Proyek Internal dikaitkan dengan mata kuliah <strong>Semester {{ $kelompokSemester ?? 1 }}</strong>.</span>
            </small>
          </div>

          {{-- Unggah Berkas Proposal --}}
          <div class="form-group p-3 border rounded bg-light">
            <label class="font-weight-bold mb-1 text-dark">
              <i class="fas fa-file-upload mr-1 text-primary"></i> Berkas Proposal Proyek
              <span class="badge badge-warning ml-1">Wajib jika Proyek Internal</span>
            </label>
            <input type="file" name="proposal_file" class="form-control-file" accept=".pdf,.doc,.docx">
            <small class="form-text text-muted">
              Sebelum memulai proyek internal, kelompok wajib mengunggah proposal (format PDF/DOC/DOCX, maks. 10MB).
            </small>
          </div>

          <div class="form-group">
            <label>Nama Mitra (Opsional untuk Internal)</label>
            <input name="nama_mitra" type="text" class="form-control" value="{{ old('nama_mitra') }}" placeholder="Nama instansi/mitra...">
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Biaya Barang</label>
              <input name="biaya_barang" type="number" step="0.01" min="0" class="form-control" value="{{ old('biaya_barang', 0) }}">
            </div>
            <div class="form-group col-md-6">
              <label>Biaya Jasa</label>
              <input name="biaya_jasa" type="number" step="0.01" min="0" class="form-control" value="{{ old('biaya_jasa', 0) }}">
            </div>
          </div>
          <div class="form-group">
            <label>Link Folder Google Drive</label>
            <input name="link_drive_proyek" type="text" class="form-control" value="{{ old('link_drive_proyek') }}" placeholder="https://drive.google.com/...">
          </div>
          <div class="form-group">
            <label>Kendala</label>
            <textarea name="kendala" class="form-control" rows="2" placeholder="Kendala pengerjaan...">{{ old('kendala') }}</textarea>
          </div>
          <div class="form-group mb-0">
            <label>Catatan Dosen</label>
            <textarea name="catatan" class="form-control" rows="2" readonly></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary font-weight-bold">Simpan Proyek</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Modal edit kartu --}}
<div class="modal fade" id="modalEditCard" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Ubah Proyek/Kegiatan</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <form id="editCardForm" method="POST" action="#" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="modal-body">
          <div class="form-group"><label>Judul Proyek <span class="text-danger">*</span></label><input name="title" id="editTitle" type="text" class="form-control" required></div>
          <div class="form-group"><label>Deskripsi Proyek</label><textarea name="description" id="editDescription" class="form-control" rows="3"></textarea></div>
          <div class="form-group"><label>Label</label><input name="labels" id="editLabels" type="text" class="form-control"></div>
          <div class="form-group"><label>Kontak Mitra</label><input name="kontak_mitra" id="editKontak" type="text" class="form-control"></div>
          <div class="form-row">
            <div class="form-group col-md-6"><label>Tanggal Mulai</label><input name="tanggal_mulai" id="editStart" type="date" class="form-control"></div>
            <div class="form-group col-md-6"><label>Tanggal Selesai</label><input name="tanggal_selesai" id="editEnd" type="date" class="form-control"></div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Skema Kategori Proyek <span class="text-danger">*</span></label>
              <select name="skema_pbl" id="editSkema" class="form-control" required>
                <option value="Internal">Internal (Wajib Proposal)</option>
                <option value="External">External (Mitra Industri)</option>
                <option value="Hibah/Penelitian">Hibah/Penelitian</option>
                <option value="Lomba">Lomba</option>
                <option value="Teaching Factory">Teaching Factory</option>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label>Status</label>
              <select name="status_proyek" id="editStatus" class="form-control">
                <option value="Proses">Proses</option>
                <option value="Dibatalkan">Dibatalkan</option>
                <option value="Selesai">Selesai</option>
              </select>
            </div>
          </div>

          {{-- Keterkaitan Mata Kuliah --}}
          <div class="form-group">
            <label class="d-flex align-items-center justify-content-between mb-1">
              <span><i class="fas fa-book-reader text-primary mr-1"></i> Keterkaitan Mata Kuliah & Dosen Pengampu</span>
              <span id="editMatkulBadgeNote" class="badge badge-primary">Semester {{ $kelompokSemester ?? 1 }}</span>
            </label>
            <select name="mata_kuliah_ids[]" id="editMataKuliah" class="form-control select2-matkul" multiple="multiple" style="width: 100%;">
              @foreach(($mataKuliahList ?? []) as $mk)
                <option value="{{ $mk->id }}" data-semester="{{ $mk->semester }}">
                  [Sem {{ $mk->semester }}] {{ $mk->kode_matkul }} - {{ $mk->nama_matkul }} ({{ $mk->dosenPengampu->pluck('nama_user')->join(', ') ?: 'Dosen Belum Ditentukan' }})
                </option>
              @endforeach
            </select>
            <small id="editMatkulHelp" class="form-text text-muted">
              <i class="fas fa-info-circle mr-1 text-primary"></i> <span>Proyek Internal dikaitkan dengan mata kuliah <strong>Semester {{ $kelompokSemester ?? 1 }}</strong>.</span>
            </small>
          </div>

          {{-- Unggah Berkas Proposal --}}
          <div class="form-group p-3 border rounded bg-light">
            <label class="font-weight-bold mb-1 text-dark">
              <i class="fas fa-file-upload mr-1 text-primary"></i> Berkas Proposal Proyek
              <span class="badge badge-warning ml-1">Wajib jika Proyek Internal</span>
            </label>
            <input type="file" name="proposal_file" class="form-control-file" accept=".pdf,.doc,.docx">
            <small class="form-text text-muted">Unggah berkas baru untuk mengganti proposal sebelumnya (PDF/DOC/DOCX - Maks. 10MB).</small>
            <div id="editProposalWrapper" class="mt-2" style="display:none;">
              <span class="badge badge-success px-2 py-1"><i class="fas fa-file-pdf mr-1"></i> Proposal Terunggah</span>
              <a id="editProposalLink" href="#" target="_blank" class="btn btn-xs btn-outline-primary ml-2 py-1 px-2 rounded-pill font-weight-bold">
                <i class="fas fa-download mr-1"></i> Unduh / Buka Proposal
              </a>
            </div>
          </div>
          <div class="form-group"><label>Nama Mitra</label><input name="nama_mitra" id="editMitra" type="text" class="form-control"></div>
          <div class="form-row">
            <div class="form-group col-md-6"><label>Biaya Barang</label><input name="biaya_barang" id="editBiayaBarang" type="number" step="0.01" min="0" class="form-control"></div>
            <div class="form-group col-md-6"><label>Biaya Jasa</label><input name="biaya_jasa" id="editBiayaJasa" type="number" step="0.01" min="0" class="form-control"></div>
          </div>
          <div class="form-group"><label>Link Drive</label><input name="link_drive_proyek" id="editDrive" type="url" class="form-control" placeholder="https://..."></div>
          <div class="form-group"><label>Progress</label><input name="progress" id="editProgress" type="number" min="0" max="100" class="form-control"></div>
          <div class="form-group">
            <label>Kendala</label>
            <textarea name="kendala" id="editKendala" class="form-control" rows="2"></textarea>
          </div>
          <div class="form-group">
            <label>Catatan</label>
            <textarea name="catatan" id="editCatatan" class="form-control" rows="2" readonly></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Modal tambah kolom --}}
<div class="modal fade" id="modalAddList" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Tambah Kolom</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <form method="POST" action="{{ route('proyek.lists.store') }}">
        @csrf
        <div class="modal-body">
          <div class="form-group"><label>Nama Kolom (Mis: Proyek 1)</label><input name="name" type="text" class="form-control" required></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Modal edit kolom --}}
<div class="modal fade" id="modalEditList" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Ubah Kolom</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <form id="editListForm" method="POST" action="#">
        @csrf @method('PUT')
        <div class="modal-body">
          <div class="form-group"><label>Nama Kolom</label><input name="name" id="editListName" type="text" class="form-control" required></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function(){
  const reorderUrl = "{{ route('proyek.reorder') }}";
  const reorderListsUrl = "{{ route('proyek.lists.reorder') }}";

  // Fallback buka modal (BS4/BS5) jika dipanggil programatik
  function showModal(id){
    const $el = $('#'+id);
    if (typeof $el.modal === 'function') { $el.modal('show'); return; }
    const el = document.getElementById(id);
    if (window.bootstrap && el) { bootstrap.Modal.getOrCreateInstance(el).show(); }
  }

  // Inisialisasi drag-and-drop kartu & kolom jika Sortable tersedia
  if (typeof Sortable !== 'undefined') {
    document.querySelectorAll('.board-list').forEach(function(listEl){
      new Sortable(listEl, {
        group:'board',
        animation: 200,
        easing: 'cubic-bezier(0.2, 0, 0, 1)',
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        dragClass: 'sortable-drag',
        fallbackClass: 'sortable-fallback',
        forceFallback: true,
        fallbackOnBody: true,
        fallbackTolerance: 4,
        swapThreshold: 0.65,
        invertSwap: true,
        direction: 'vertical',
        emptyInsertThreshold: 20,
        bubbleScroll: true,
        scroll: true,
        scrollSensitivity: 80,
        scrollSpeed: 15,
        delay: 0,
        delayOnTouchOnly: false,
        onEnd:function(evt){
          const cardId = evt.item.getAttribute('data-card-id');
          const toList = evt.to.getAttribute('data-list-id');
          const newIndex = evt.newIndex;
          $.post(reorderUrl, {card_id:cardId,to_list:toList,position:newIndex,_token:'{{ csrf_token() }}'})
           .fail(()=> Swal.fire('Gagal','Tidak dapat menyimpan urutan','error'));
        }
      });
    });

    // Drag urutkan kolom (handle: head) dengan konfigurasi ultra-smooth
    const boardEl = document.getElementById('board');
    if (boardEl){
      new Sortable(boardEl, {
        group:'board-columns',
        animation: 220,
        easing: 'cubic-bezier(0.2, 0, 0, 1)',
        handle:'.board-col-head',
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        dragClass: 'sortable-drag',
        fallbackClass: 'sortable-fallback',
        forceFallback: true,
        fallbackOnBody: true,
        fallbackTolerance: 5,
        swapThreshold: 0.65,
        invertSwap: true,
        direction: 'horizontal',
        bubbleScroll: true,
        scroll: true,
        scrollSensitivity: 100,
        scrollSpeed: 20,
        onEnd:function(){
          const ids = Array.from(boardEl.querySelectorAll('.board-column')).map(col=>col.getAttribute('data-col-id'));
          $.post(reorderListsUrl, {list_ids:ids,_token:'{{ csrf_token() }}'})
           .fail(()=> Swal.fire('Gagal','Tidak dapat menyimpan urutan kolom','error'));
        }
      });
    }
  }

  // Cari
  const search = document.getElementById('boardSearch');
  if (search){
    search.addEventListener('input', function(){
      const q = this.value.trim().toLowerCase();
      document.querySelectorAll('.board-card').forEach(function(card){
        const cardData = $(card).data('card') || {};
        const title = (cardData.title || card.getAttribute('data-title-search') || '').toLowerCase();
        const desc = (cardData.description || card.getAttribute('data-desc') || '').toLowerCase();
        const hay = title + ' ' + desc;
        card.style.display = hay.includes(q) ? '' : 'none';
      });
    });
  }

  const kelompokSemester = {{ (int) ($kelompokSemester ?? 1) }};

  function updateMatkulFilter(selectSkemaEl, selectMatkulEl, helpEl, badgeEl) {
    const skema = $(selectSkemaEl).val() || 'Internal';
    const isInternal = (skema.toLowerCase() === 'internal');

    if (isInternal) {
      $(badgeEl).removeClass('badge-success badge-info').addClass('badge-primary').text('Semester ' + kelompokSemester);
      $(helpEl).html('<i class="fas fa-info-circle mr-1 text-primary"></i> Proyek Internal dikaitkan dengan mata kuliah <strong>Semester ' + kelompokSemester + '</strong>.');

      // Di Proyek Internal, filter hanya mata kuliah semester kelompok
      $(selectMatkulEl).find('option').each(function() {
        const sem = parseInt($(this).data('semester'), 10);
        if (sem === kelompokSemester) {
          $(this).prop('disabled', false).show();
        } else {
          $(this).prop('disabled', true).prop('selected', false).hide();
        }
      });
    } else {
      $(badgeEl).removeClass('badge-primary badge-info').addClass('badge-success').text('Bebas (Semua Semester)');
      $(helpEl).html('<i class="fas fa-check-circle mr-1 text-success"></i> Proyek <strong>' + skema + '</strong> bebas memilih mata kuliah dari semua semester.');

      // Di Proyek External/Hibah/Lomba, tampilkan semua mata kuliah
      $(selectMatkulEl).find('option').prop('disabled', false).show();
    }

    if (window.jQuery && jQuery().select2) {
      if ($(selectMatkulEl).hasClass('select2-hidden-accessible')) {
        $(selectMatkulEl).select2('destroy');
      }
      $(selectMatkulEl).select2({
        dropdownParent: $(selectMatkulEl).closest('.modal'),
        placeholder: '-- Pilih Mata Kuliah Terkait --',
        allowClear: true,
        width: '100%'
      });
    }
  }

  // Event listener skema change di Modal Add & Edit
  $(document).on('change', '#addSkema', function() {
    updateMatkulFilter(this, '#addMataKuliah', '#addMatkulHelp', '#addMatkulBadgeNote');
  });

  $(document).on('change', '#editSkema', function() {
    updateMatkulFilter(this, '#editMataKuliah', '#editMatkulHelp', '#editMatkulBadgeNote');
  });

  // Tambah kartu: isi list id
  $(document).on('click','.btn-add-card',function(){
    const listId = $(this).data('list-id') || '';
    $('#addCardListId').val(listId);
  });

  $('#modalAddCard').on('shown.bs.modal', function () {
    if (!$('#addCardListId').val()) {
      const firstCol = $('.board-column').first().data('col-id');
      if (firstCol) $('#addCardListId').val(firstCol);
    }
    updateMatkulFilter('#addSkema', '#addMataKuliah', '#addMatkulHelp', '#addMatkulBadgeNote');
  });

  // Hapus kartu
  $(document).on('click','.btn-delete-card',function(){
    const form = $(this).closest('form');
    Swal.fire({
      title: 'Hapus Proyek?',
      text: 'Data proyek dan relasinya akan dihapus.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, hapus',
      cancelButtonText: 'Batal'
    }).then(r=>{ if(r.isConfirmed) form.submit(); });
  });

  // Hapus kolom
  $(document).on('click','.btn-delete-list',function(){
    const form = $(this).closest('form');
    Swal.fire({
      title: 'Hapus kolom?',
      text: 'Semua kartu proyek di dalam kolom ini juga akan ikut dihapus.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, hapus',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#e02424'
    }).then(r=>{ if(r.isConfirmed) form.submit(); });
  });

  // Edit kartu: isi modal
  $(document).on('click','.btn-edit-card',function(e){
    e.preventDefault();
    const cardEl = $(this).closest('.board-card');
    const card = cardEl.data('card') || {};
    const id = card.id || cardEl.data('card-id');

    $('#editTitle').val(card.title || cardEl.data('title') || '');
    $('#editDescription').val(card.description || '');
    $('#editLabels').val((card.labels || []).join(', '));
    $('#editStart').val(card.tanggal_mulai || '');
    $('#editEnd').val(card.tanggal_selesai || '');
    $('#editProgress').val(card.progress ?? 0);
    const skemaVal = card.skema_pbl || 'Internal';
    $('#editSkema').val(skemaVal);
    $('#editStatus').val(card.status_proyek || 'Proses');
    $('#editMitra').val(card.nama_mitra || '');
    $('#editKontak').val(card.kontak_mitra || '');
    $('#editBiayaBarang').val(card.biaya_barang ?? 0);
    $('#editBiayaJasa').val(card.biaya_jasa ?? 0);
    $('#editDrive').val(card.link_drive_proyek || '');
    $('#editKendala').val(card.kendala || '');
    $('#editCatatan').val(card.catatan || '');

    // Update filter skema terlebih dahulu
    updateMatkulFilter('#editSkema', '#editMataKuliah', '#editMatkulHelp', '#editMatkulBadgeNote');

    // Set nilai mata kuliah yang sudah dipilih
    const matkulIds = (card.mata_kuliah_ids || []).map(String);
    $('#editMataKuliah').val(matkulIds).trigger('change');

    const proposalUrl = card.proposal_url;
    if (proposalUrl) {
      $('#editProposalWrapper').show();
      $('#editProposalLink').attr('href', proposalUrl);
    } else {
      $('#editProposalWrapper').hide();
      $('#editProposalLink').attr('href', '#');
    }

    $('#editCardForm').attr('action', "{{ url('mahasiswa/proyek/cards') }}/" + id);
    showModal('modalEditCard');
  });

  $('#modalEditCard').on('shown.bs.modal', function () {
    updateMatkulFilter('#editSkema', '#editMataKuliah', '#editMatkulHelp', '#editMatkulBadgeNote');
  });

  // Edit kolom
  $(document).on('click','.btn-edit-list',function(){
    const id = $(this).data('list-id');
    $('#editListName').val($(this).data('list-name')||'');
    $('#editListForm').attr('action', "{{ url('mahasiswa/proyek/lists') }}/"+id);
  });

  // Share Link Penilaian Mitra
  $(document).on('click', '.btn-share-mitra', function(e) {
    e.preventDefault();
    const cardEl = $(this).closest('.board-card');
    const cardData = cardEl.data('card') || {};
    const cardId = $(this).data('card-id') || cardData.id;
    const cardTitle = $(this).data('card-title') || cardData.title || 'Proyek';
    window.sharePenilaianMitra(cardId, cardTitle);
  });

  window.sharePenilaianMitra = function(cardId, cardTitle) {
    const baseUrl = window.location.origin;
    const shareUrl = `${baseUrl}/penilaian-mitra/${cardId}`;

    Swal.fire({
      title: 'Share Link Penilaian Mitra',
      html: `
        <div class="text-left">
          <p class="mb-3">Bagikan link ini kepada mitra untuk menilai proyek:</p>
          <div class="form-group mb-3">
            <label class="small text-muted font-weight-bold">Proyek:</label>
            <div class="font-weight-bold text-dark">${cardTitle}</div>
          </div>
          <div class="form-group mb-3">
            <label class="small text-muted font-weight-bold">Link Penilaian Mitra:</label>
            <div class="input-group">
              <input type="text" id="shareLinkInput" class="form-control" value="${shareUrl}" readonly>
              <div class="input-group-append">
                <button type="button" class="btn btn-primary" onclick="copyShareLink()">
                  <i class="fas fa-copy"></i> Salin
                </button>
              </div>
            </div>
          </div>
          <div class="alert alert-info small mb-0">
            <i class="fas fa-info-circle mr-1"></i>
            Mitra dapat langsung mengakses link ini tanpa login untuk mengisi rubrik penilaian proyek.
          </div>
        </div>
      `,
      showCancelButton: true,
      showConfirmButton: false,
      cancelButtonText: 'Tutup',
      width: '600px'
    });
  };

  // Copy link to clipboard
  window.copyShareLink = function() {
    const input = document.getElementById('shareLinkInput');
    if (!input) return;
    input.select();
    input.setSelectionRange(0, 99999);

    try {
      document.execCommand('copy');
      const originalValue = input.value;
      input.value = 'Tersalin!';
      input.classList.add('bg-success', 'text-white');

      setTimeout(() => {
        input.value = originalValue;
        input.classList.remove('bg-success', 'text-white');
      }, 2000);

      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: 'success',
          title: 'Berhasil!',
          text: 'Link penilaian berhasil disalin ke clipboard.',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true
        });
      }
    } catch (err) {
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: 'error',
          title: 'Gagal!',
          text: 'Gagal menyalin link. Silakan salin manual.',
        });
      }
    }
  };

  @if($errors->any())
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'error',
        title: 'Perhatian',
        html: '{!! implode("<br>", array_map("e", $errors->all())) !!}',
        confirmButtonText: 'Tutup'
      });
    }
  @endif
})();
</script>
@endpush
