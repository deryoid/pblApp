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

      /* Board Cards */
      .board-card {
        background: #ffffff;
        border-radius: 10px;
        border: 1px solid rgba(9, 30, 66, 0.08);
        border-left: 4px solid #4e73df;
        box-shadow: 0 1px 3px rgba(9, 30, 66, 0.08);
        cursor: grab;
        transition: transform 0.18s cubic-bezier(0.2, 0, 0, 1), box-shadow 0.18s cubic-bezier(0.2, 0, 0, 1);
        position: relative;
        overflow: hidden;
        user-select: none;
      }
      .board-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(9, 30, 66, 0.12);
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
        border-radius: 10px !important;
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
        border-radius: 10px !important;
        z-index: 99999 !important;
      }

      /* Card Completed */
      .card-completed {
        background: linear-gradient(135deg, #ffffff 0%, #f8fbf9 100%) !important;
        border-left: 4px solid #22a06b !important;
      }
      .card-completed::after {
        content: '✓';
        position: absolute;
        top: 4px;
        right: 8px;
        color: #22a06b;
        font-size: 16px;
        font-weight: bold;
        opacity: 0.6;
        pointer-events: none;
      }

      /* Card Content Elements */
      .card-title-full {
        font-size: 0.92rem;
        font-weight: 600;
        line-height: 1.4;
        color: #172b4d;
        white-space: normal;
        word-break: break-word;
        margin-bottom: 0.5rem;
      }

      /* Date Chips */
      .date-chip {
        display: inline-flex;
        align-items: center;
        font-size: 0.72rem;
        font-weight: 600;
        line-height: 1;
        padding: 0.35rem 0.6rem;
        border-radius: 6px;
        background: #e9f2ff;
        color: #0c66e4;
        border: 1px solid transparent;
        white-space: nowrap;
      }
      .date-chip-danger {
        background: #ffebe6;
        color: #c9372c;
      }

      /* Enhancing Badges & Buttons */
      .badge-soft {
        background-color: #ebecf0;
        color: #42526e;
        font-weight: 600;
      }
      .badge-success { background-color: #22a06b; color: white; border: none; }
      .badge-info { background-color: #0c66e4; color: white; border: none; }
      .badge-primary { background-color: #6554c0; color: white; border: none; }
      .badge-warning { background-color: #f5cd47; color: #172b4d; border: none; }
      
      .badge-pill {
        padding: 0.3em 0.7em;
        font-weight: 500;
        border-radius: 4px;
      }

      /* Action Buttons inside Card Header */
      .btn-icon-only {
        width: 28px;
        height: 28px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        background: transparent;
        color: #6b778c;
        border: 1px solid rgba(9, 30, 66, 0.08);
        box-shadow: none;
        transition: all 0.15s ease;
      }
      .btn-icon-only:hover {
        background: #ebecf0;
        color: #172b4d;
      }
      .btn-icon-only.btn-danger:hover { background: #ffebe6; color: #c9372c; }
      .btn-icon-only.btn-info:hover { background: #e9f2ff; color: #0c66e4; }
      .btn-icon-only.btn-primary:hover { background: #e9f2ff; color: #0c66e4; }

      /* Progress Bar */
      .progress {
        height: 8px;
        border-radius: 10px;
        background-color: #ebecf0;
        margin-top: 10px;
        box-shadow: none;
      }
      .progress-bar {
        border-radius: 10px;
        background: #0c66e4;
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
        $map = ['Penelitian'=>'badge-primary','Pengabdian'=>'badge-info','Lomba'=>'badge-warning','PBL x TeFa'=>'badge-success'];
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
        <button id="btnAddList" type="button" class="btn btn-success btn-circle btn-sm mr-2" data-toggle="modal" data-target="#modalAddList" title="Tambah Kolom">
          <i class="fas fa-plus"></i>
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
              @endphp

              <div
                class="card board-card shadow-xs mb-2 hover-raise {{ ($card['status_proyek'] ?? '') === 'Selesai' ? 'card-completed' : '' }}"
                data-card-id="{{ $card['id'] }}"
                data-title="{{ e($card['title']) }}"
                data-title-search="{{ strtolower($card['title']) }}"
                data-card='@json($card, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)'
              >
                <div class="card-body p-2 d-flex flex-column">

                 {{-- HEADER: Status Lock → Aksi (kanan atas) → Judul penuh → Tanggal --}}
                  <div class="d-flex align-items-center mb-1">
                    @if(($card['status_proyek'] ?? '') === 'Selesai')
                      <span class="badge badge-success mr-2" title="Proyek Selesai">
                        <i class="fas fa-check-circle mr-1"></i>Selesai
                      </span>
                    @endif
                    <div class="ml-auto d-flex align-items-center">
                      <button type="button"
                              class="btn btn-primary btn-icon-only btn-sm btn-circle btn-edit-card mr-1"
                              title="Ubah proyek" aria-label="Ubah proyek">
                        <i class="fas fa-edit"></i>
                      </button>
                      {{-- Share Link Penilaian Mitra --}}
                      <button type="button"
                              class="btn btn-info btn-icon-only btn-sm btn-circle mr-1 btn-share-mitra"
                              title="Share Link Penilaian Mitra"
                              data-card-id="{{ $card['id'] }}"
                              data-card-title="{{ e($card['title']) }}"
                              aria-label="Share link penilaian mitra">
                        <i class="fas fa-share-alt" aria-hidden="true"></i>
                      </button>
                      <form method="POST"
                            action="{{ route('proyek.cards.destroy', ['card' => $card['id']]) }}"
                            class="m-0 d-inline">
                        @csrf @method('DELETE')
                        <button type="button"
                                class="btn btn-danger btn-icon-only btn-circle btn-sm btn-delete-card"
                                title="Hapus proyek" aria-label="Hapus proyek">
                          <i class="fas fa-trash"></i>
                        </button>
                      </form>
                    </div>
                  </div>
                  {{-- LABELS & SKEMA --}}
                  @if (!empty($card['labels']) || !empty($card['skema_pbl']))
                    <div class="d-flex align-items-center mb-1">
                      <div class="labels-wrap">
                        @foreach (($card['labels'] ?? []) as $label)
                          <span class="badge badge-pill badge-info mr-1 mb-1">{{ $label }}</span>
                        @endforeach
                      </div>
                      <span class="badge ml-auto {{ schemeBadgeClass($card['skema_pbl'] ?? '') }}">
                        {{ $card['skema_pbl'] ?? '-' }}
                      </span>
                    </div>
                  @endif

                  {{-- KETERKAITAN MATA KULIAH & DOSEN PENGAMPU --}}
                  @if (!empty($card['mata_kuliahs']))
                    <div class="mb-2">
                      @foreach ($card['mata_kuliahs'] as $mk)
                        <div class="badge badge-light border text-primary text-truncate d-block text-left mb-1 py-1 px-2" style="font-size: 0.73rem; white-space: normal;" title="{{ $mk['nama_matkul'] }} (Semester {{ $mk['semester'] }}) - Pengampu: {{ $mk['dosen_pengampu'] ?? 'Dosen Belum Ditentukan' }}">
                          <i class="fas fa-book text-primary mr-1"></i>
                          <strong>{{ $mk['nama_matkul'] }}</strong>
                          @if(!empty($mk['dosen_pengampu']))
                            <div class="text-muted mt-0 small" style="font-size: 0.68rem;">
                              <i class="fas fa-chalkboard-teacher mr-1 text-secondary"></i>{{ $mk['dosen_pengampu'] }} (Sem {{ $mk['semester'] }})
                            </div>
                          @endif
                        </div>
                      @endforeach
                    </div>
                  @endif
                  {{-- Judul penuh (wrap) --}}
                  <div class="card-title-full font-weight-bold mb-1">
                    {{ $card['title'] }}
                  </div>

                  {{-- Tanggal (chip) --}}
                  @if($tglMulai || $tglSelesai)
                    <div class="mb-2">
                      <span class="date-chip {{ $isOverdue ? 'date-chip-danger' : '' }}">
                        <i class="far fa-calendar-alt mr-1"></i>{{ $rentang }}
                      </span>
                    </div>
                  @endif


                

                  {{-- MITRA + KONTAK --}}
                  @if (!empty($card['nama_mitra']))
                    <div class="text-muted x-small mb-1">
                      <i class="fas fa-building mr-1"></i> {{ $card['nama_mitra'] }}
                      @if (!empty($card['kontak_mitra']))
                        <span class="ml-1"><i class="fas fa-phone-alt mr-1"></i>{{ $card['kontak_mitra'] }}</span>
                      @endif
                    </div>
                  @endif

                  {{-- DESKRIPSI --}}
                  @if(!empty($card['description']))
                    <div class="text-muted text-body-2 mb-2 clamp-2">{{ \Illuminate\Support\Str::limit($card['description'], 160) }}</div>
                  @endif

                  {{-- CREATOR / UPDATER --}}
                  <div class="text-muted x-small mb-2">
                    <div class="d-flex align-items-center mb-1">
                      <span class="user-chip" title="{{ $card['created_by_name'] ?? '-' }}">
                        <img class="user-avatar" src="{{ ($card['created_by_avatar'] ?? '') ?: avatarUrl($card['created_by_name'] ?? '-') }}" alt="creator" loading="lazy">
                        <span class="user-name">{{ $card['created_by_name'] ?? '-' }}</span>
                      </span>
                      @if(!empty($card['created_at_human']))<span class="ml-2">{{ $card['created_at_human'] }}</span>@endif
                    </div>
                    @if(!empty($card['has_update']))
                      <div class="d-flex align-items-center">
                        <span class="user-chip" title="{{ $card['updated_by_name'] ?? '-' }}">
                          <img class="user-avatar" src="{{ ($card['updated_by_avatar'] ?? '') ?: avatarUrl($card['updated_by_name'] ?? '-') }}" alt="updator" loading="lazy">
                          <span class="user-name">{{ $card['updated_by_name'] ?? '-' }}</span>
                        </span>
                        @if(!empty($card['updated_at_human']))<span class="ml-2">{{ $card['updated_at_human'] }}</span>@endif
                      </div>
                    @endif
                  </div>

                  {{-- PROGRESS --}}
                  <div class="d-flex align-items-center mb-2">
                    <div class="progress progress-sm flex-grow-1 mr-2">
                      <div class="progress-bar" role="progressbar"
                           style="width: {{ (int)($card['progress'] ?? 0) }}%"
                           aria-valuenow="{{ (int)($card['progress'] ?? 0) }}"
                           aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <span class="small text-muted">{{ (int)($card['progress'] ?? 0) }}%</span>
                  </div>

                  {{-- FOOTER --}}
                  <div class="d-flex align-items-center x-small mt-auto pt-1 border-top-light flex-wrap">
                    <span class="badge {{ statusBadgeClass($card['status_proyek'] ?? '') }}">{{ $card['status_proyek'] ?? 'Proses' }}</span>
                    @if(!empty($card['proposal_url']))
                      <a href="{{ $card['proposal_url'] }}" target="_blank" rel="noopener"
                         class="ml-2 btn btn-outline-danger btn-sm py-0 px-2 font-weight-bold" title="Lihat Berkas Proposal">
                        <i class="fas fa-file-pdf mr-1"></i> Proposal
                      </a>
                    @endif
                    <span class="ml-2 text-muted"><i class="far fa-comment mr-1"></i>{{ $card['comments'] ?? 0 }}</span>
                    @if(!empty($card['link_drive_proyek']))
                      <a href="{{ $card['link_drive_proyek'] }}" target="_blank" rel="noopener"
                         class="ml-auto btn btn-outline-dark btn-sm border btn-drive">
                        <i class="fab fa-google-drive mr-1"></i> Drive
                      </a>
                    @endif
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
  <div class="card shadow-sm">
    <div class="card-body text-center text-muted">
      <p class="mb-2">Belum ada kolom untuk kelompok ini.</p>
      @if(isset($kelompok) && $kelompok)
        <button id="btnAddListEmpty" type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalAddList">
          <i class="fas fa-plus mr-1"></i> Tambah Kolom
        </button>
      @endif
    </div>
  </div>
  @endif
</div>

{{-- Modal tambah kartu --}}
<div class="modal fade" id="modalAddCard" tabindex="-1" role="dialog" aria-hidden="true">
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
<div class="modal fade" id="modalEditCard" tabindex="-1" role="dialog" aria-hidden="true">
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
<div class="modal fade" id="modalAddList" tabindex="-1" role="dialog" aria-hidden="true">
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
<div class="modal fade" id="modalEditList" tabindex="-1" role="dialog" aria-hidden="true">
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

  // Fallback buka modal (BS4/BS5)
  function showModal(id){
    const $el = $('#'+id);
    if (typeof $el.modal === 'function') { $el.modal('show'); return; }
    const el = document.getElementById(id);
    if (window.bootstrap && el) { bootstrap.Modal.getOrCreateInstance(el).show(); }
  }
  $(document).on('click','#btnAddList, #btnAddListEmpty',function(e){
    e.preventDefault(); showModal('modalAddList');
  });

  // Drag antar kartu dengan konfigurasi ultra-smooth
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
    const listId = $(this).data('list-id');
    $('#addCardListId').val(listId);
    $('#modalAddCard').modal('show');
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
      text: 'Kolom harus kosong dari kartu sebelum dapat dihapus.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, hapus',
      cancelButtonText: 'Batal'
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
    Swal.fire({
      icon: 'error',
      title: 'Perhatian',
      html: '{!! implode("<br>", array_map("e", $errors->all())) !!}',
      confirmButtonText: 'Tutup'
    });
  @endif
})();
</script>
@endpush
