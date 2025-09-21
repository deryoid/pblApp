@extends('layout.app')
@section('content')
<div class="container-fluid">
  @php
    // Helper kecil untuk badge skema & status (hindari match agar kompatibel)
    if (!function_exists('schemeBadgeClass')) {
      function schemeBadgeClass($skema) {
        $map = [
          'Penelitian' => 'badge-primary',
          'Pengabdian' => 'badge-info',
          'Lomba' => 'badge-warning',
          'PBL x TeFa' => 'badge-success',
        ];
        return $map[$skema] ?? 'badge-secondary';
      }
    }
    if (!function_exists('statusBadgeClass')) {
      function statusBadgeClass($st) {
        $map = [
          'Proses' => 'badge-primary',
          'Dibatalkan' => 'badge-danger',
          'Selesai' => 'badge-success',
        ];
        return $map[$st] ?? 'badge-light';
      }
    }
    if (!function_exists('avatarUrl')) {
      function avatarUrl(?string $name): string {
        $name = trim((string)$name);
        if ($name === '') $name = 'U';
        $palette = ['4e73df','1cc88a','36b9cc','f6c23e','e74a3b','858796'];
        $idx = abs(crc32($name)) % count($palette);
        $bg = $palette[$idx];
        $enc = urlencode($name);
        return "https://ui-avatars.com/api/?name={$enc}&background={$bg}&color=ffffff&size=64&rounded=true";
      }
    }
  @endphp
  @isset($kelompok)
    @if(!$kelompok)
      <div class="alert alert-warning">Anda belum tergabung dalam kelompok. Hubungi admin/dosen pembimbing.</div>
    @endif
  @endisset
  <div class="d-flex align-items-center mb-3 flex-wrap">
    <div class="d-flex align-items-baseline mr-3 mb-2 mb-md-0">
      <h1 class="h3 text-gray-800 mb-0">Proyek</h1>
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
        <button class="btn btn-success btn-circle btn-sm mr-2" data-toggle="modal" data-target="#modalAddList">
          <i class="fas fa-plus "></i>
        </button>
      @endif
      <input id="boardSearch" type="text" class="form-control form-control-sm" placeholder="Cari Kolom...." style="max-width: 220px;">
    </div>
  </div>

  @if(isset($lists) && count($lists) > 0)
  <div class="board-wrapper">
    <div id="board" class="board d-flex">
      @foreach (($lists ?? []) as $list)
        <div class="board-column" data-col-id="{{ $list['id'] }}">
          <div class="d-flex align-items-center mb-2">
            <h6 class="mb-0 text-uppercase text-muted">{{ $list['title'] }}</h6>
            <span class="badge badge-secondary ml-2">{{ count($list['cards']) }}</span>
            <div class="ml-auto d-flex align-items-center btn-group btn-group-sm board-actions" role="group" aria-label="Aksi Kolom">
              <button class="btn btn-info btn-sm btn-edit-list" data-list-id="{{ $list['id'] }}" data-list-name="{{ $list['title'] }}" data-toggle="modal" data-target="#modalEditList" title="Ubah kolom">
                <i class="fas fa-edit"></i>
              </button>
              <form method="POST" action="{{ route('proyek.lists.destroy', ['list' => $list['id']]) }}" class="m-0" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-danger btn-sm btn-delete-list" title="Hapus kolom">
                  <i class="fas fa-trash"></i>
                </button>
              </form>
              <button class="btn btn-success btn-sm btn-add-card" data-list-id="{{ $list['id'] }}" data-toggle="modal" data-target="#modalAddCard" title="Tambah Proyek">
                <i class="fas fa-plus"></i>
              </button>
            </div>
          </div>

          <div class="board-list" data-list-id="{{ $list['id'] }}">
            @forelse ($list['cards'] as $card)
              @php
                $tglMulai = $card['tanggal_mulai'] ?? null;
                // fallback: gunakan 'due' lama jika belum migrasi
                $tglSelesai = $card['tanggal_selesai'] ?? ($card['due'] ?? null);
                $isOverdue = !empty($tglSelesai)
                  && \Carbon\Carbon::parse($tglSelesai)->isPast()
                  && (($card['status_proyek'] ?? '') !== 'Selesai');
                $biayaTotal = (float)($card['biaya_barang'] ?? 0) + (float)($card['biaya_jasa'] ?? 0);
              @endphp
              <div
                class="card board-card shadow-sm mb-2"
                data-card-id="{{ $card['id'] }}"
                data-title="{{ $card['title'] }}"
                data-title-search="{{ strtolower($card['title']) }}"
                data-desc="{{ $card['description'] ?? '' }}"
                data-labels='@json($card['labels'] ?? [])'
                data-progress="{{ $card['progress'] ?? 0 }}"
                data-tmulai="{{ $tglMulai ?? '' }}"
                data-tselesai="{{ $tglSelesai ?? '' }}"
                data-mitra="{{ $card['nama_mitra'] ?? '' }}"
                data-kontak="{{ $card['kontak_mitra'] ?? '' }}"
                data-skema="{{ $card['skema_pbl'] ?? '' }}"
                data-status="{{ $card['status_proyek'] ?? '' }}"
                data-bbar="{{ $card['biaya_barang'] ?? '' }}"
                data-bjas="{{ $card['biaya_jasa'] ?? '' }}"
                data-drive="{{ $card['link_drive_proyek'] ?? '' }}"
                data-kendala="{{ $card['kendala'] ?? '' }}"
                data-catatan="{{ $card['catatan'] ?? '' }}"
              >
                <div class="card-body py-2 d-flex flex-column">
                  {{-- Labels + Skema --}}
                  <div class="mb-1 d-flex align-items-center">
                    @if (!empty($card['labels']))
                      <div class="mr-1">
                        @foreach ($card['labels'] as $label)
                          <span class="badge badge-pill badge-info mr-1">{{ $label }}</span>
                        @endforeach
                      </div>
                    @endif
                    <span class="badge {{ schemeBadgeClass($card['skema_pbl'] ?? '') }} ml-auto">{{ $card['skema_pbl'] ?? '-' }}</span>
                  </div>

                  {{-- Title + Status --}}
                  <div class="d-flex align-items-center mb-1">
                    <div class="font-weight-bold small flex-grow-1">{{ $card['title'] }}</div>
                    
                    <div class="ml-2 d-flex align-items-center">
                      <button type="button" class="btn btn-info btn-circle btn-sm btn-edit-card mr-1" title="Ubah" aria-label="Ubah" data-toggle="modal" data-target="#modalEditCard"><i class="fas fa-edit"></i></button>
                      <form method="POST" action="{{ route('proyek.cards.destroy', ['card' => $card['id']]) }}" class="m-0 d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-danger btn-circle btn-sm btn-delete-card" title="Hapus" aria-label="Hapus"><i class="fas fa-trash"></i></button>
                      </form>
                    </div>
                  </div>

                  {{-- Mitra + Kontak --}}
                  @if (!empty($card['nama_mitra']))
                    <div class="text-muted small mb-1">
                      <i class="fas fa-building mr-1"></i> {{ $card['nama_mitra'] }}
                      {{-- Kontak mitra --}}
                      @if (!empty($card['kontak_mitra']))
                        <span class="ml-1"><i class="fas fa-phone-alt mr-1"></i>{{ $card['kontak_mitra'] }}</span>
                      @endif
                    </div>
                  @endif

                  {{-- Deskripsi --}}
                  @if(!empty($card['description']))
                    <div class="text-muted small mb-2 card-desc">{{ \Illuminate\Support\Str::limit($card['description'], 140) }}</div>
                  @endif

                  {{-- Pembuat & Pengubah (terpisah) + Inisial --}}
                  @php
                    $hasUpdate = !empty($card['has_update']);
                    $creator = trim((string)($card['created_by_name'] ?? ''));
                    $updator = trim((string)($card['updated_by_name'] ?? ''));
                    $initial = function ($name) {
                      if ($name === '') return '';
                      $parts = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY);
                      $ini = '';
                      foreach ($parts as $p) { $ini .= mb_substr($p,0,1); }
                      return mb_strtoupper($ini);
                    };
                  @endphp
                  <div class="text-muted small mb-2">
                    <div class="d-flex align-items-center mb-1">
                      <span class="user-chip" title="{{ $creator }}">
                        <img class="user-avatar" src="{{ ($card['created_by_avatar'] ?? '') ?: avatarUrl($creator) }}" alt="{{ $creator !== '' ? $creator : 'Tanpa Nama' }}" loading="lazy">
                        <span class="user-name">{{ $creator !== '' ? $creator : '-' }}</span>
                      </span>
                      @if(!empty($card['created_at_human']))
                        <span class="ml-2"> {{ $card['created_at_human'] }}</span>
                      @endif
                    </div>
                    @if($hasUpdate)
                      <div class="d-flex align-items-center">
                        <span class="user-chip" title="{{ $updator }}">
                          <img class="user-avatar" src="{{ ($card['updated_by_avatar'] ?? '') ?: avatarUrl($updator) }}" alt="{{ $updator !== '' ? $updator : 'Tanpa Nama' }}" loading="lazy">
                          <span class="user-name">{{ $updator !== '' ? $updator : '-' }}</span>
                        </span>
                        @if(!empty($card['updated_at_human']))
                          <span class="ml-2"> {{ $card['updated_at_human'] }}</span>
                        @endif
                      </div>
                    @endif
                  </div>

                  {{-- Progress --}}
                  <div class="d-flex align-items-center mb-2">
                    <div class="progress progress-sm flex-grow-1 mr-2">
                      <div class="progress-bar" role="progressbar" style="width: {{ (int)($card['progress'] ?? 0) }}%" aria-valuenow="{{ (int)($card['progress'] ?? 0) }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <span class="small text-muted">{{ (int)($card['progress'] ?? 0) }}%</span>
                  </div>

                  {{-- Tanggal & Drive --}}
                  <div class="d-flex align-items-center text-muted small mb-2">
                    @if(!empty($tglMulai) || !empty($tglSelesai))
                      <span class="mr-3 {{ $isOverdue ? 'text-danger' : '' }}">
                        <i class="far fa-calendar-alt mr-1"></i>
                        {{ !empty($tglMulai) ? \Carbon\Carbon::parse($tglMulai)->format('d M') : '?' }} -
                        {{ !empty($tglSelesai) ? \Carbon\Carbon::parse($tglSelesai)->format('d M') : '?' }}
                      </span>
                    @endif
                    @if(!empty($card['link_drive_proyek']))
                      <a href="{{ $card['link_drive_proyek'] }}" target="_blank" rel="noopener" class="ml-auto btn btn-light btn-sm" title="Drive Proyek"><i class="fab fa-google-drive"></i></a>
                    @endif
                  </div>

                  {{-- Counters --}}
                  <div class="d-flex align-items-center text-muted small mt-auto">
                    <span class="ml-auto d-flex align-items-center">
                      <span class="mr-2"><i class="far fa-comment mr-1"></i>{{ $card['comments'] ?? 0 }}</span>
                      <span class="badge {{ statusBadgeClass($card['status_proyek'] ?? '') }} ml-2">{{ $card['status_proyek'] ?? 'Proses' }}</span>
                    </span>
                  </div>
                </div>
              </div>
            @empty
              <div class="text-muted small">Belum ada proyek</div>
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
        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalAddList">
          <i class="fas fa-plus mr-1"></i> Tambah Kolom
        </button>
      @endif
    </div>
  </div>
  @endif
</div>

{{-- Modal tambah kartu (disesuaikan skema revisi) --}}
<div class="modal fade" id="modalAddCard" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Proyek</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <form method="POST" action="{{ route('proyek.cards.store') }}">
        @csrf
        <div class="modal-body">
          <input type="hidden" name="list" id="addCardListId" value="">
          <div class="form-group">
            <label>Judul</label>
            <input name="title" type="text" class="form-control" placeholder="Judul" required>
          </div>
          <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Deskripsi singkat"></textarea>
          </div>
          <div class="form-group">
            <label>Label (pisahkan koma)</label>
            <input name="labels" type="text" class="form-control" placeholder="Contoh: Penelitian, AI dsb">
          </div>
          <div class="form-group">
            <label>Kontak Mitra (telp/email)</label>
            <input name="kontak_mitra" type="text" class="form-control" placeholder="Contoh: +62-812-xxxx-xxxx atau email">
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Tanggal Mulai</label>
              <input name="tanggal_mulai" type="date" class="form-control">
            </div>
            <div class="form-group col-md-6">
              <label>Tanggal Selesai</label>
              <input name="tanggal_selesai" type="date" class="form-control">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Skema PBL</label>
              <select name="skema_pbl" class="form-control">
                <option value="">- Pilih -</option>
                <option>Penelitian</option>
                <option>Pengabdian</option>
                <option>Lomba</option>
                <option>PBL x TeFa</option>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label>Status</label>
              <select name="status_proyek" class="form-control">
                <option>Proses</option>
                <option>Dibatalkan</option>
                <option>Selesai</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label>Nama Mitra</label>
            <input name="nama_mitra" type="text" class="form-control" placeholder="Nama perusahaan/mitra">
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Biaya Barang</label>
              <input name="biaya_barang" type="number" step="0.01" min="0" class="form-control" value="0">
            </div>
            <div class="form-group col-md-6">
              <label>Biaya Jasa</label>
              <input name="biaya_jasa" type="number" step="0.01" min="0" class="form-control" value="0">
            </div>
          </div>
          <div class="form-group">
            <label>Link Drive Proyek</label>
            <input name="link_drive_proyek" type="url" class="form-control" placeholder="https://...">
          </div>
          <div class="form-group">
            <label>Kendala</label>
            <textarea name="kendala" class="form-control" rows="2"></textarea>
          </div>
          <div class="form-group mb-0">
            <label>Catatan</label>
            <textarea name="catatan" class="form-control" rows="2"></textarea>
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

{{-- Modal edit kartu (disesuaikan skema revisi) --}}
<div class="modal fade" id="modalEditCard" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Ubah Proyek</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <form id="editCardForm" method="POST" action="#">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="form-group">
            <label>Judul</label>
            <input name="title" id="editTitle" type="text" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="description" id="editDescription" class="form-control" rows="3"></textarea>
          </div>
          <div class="form-group">
            <label>Label (pisahkan koma)</label>
            <input name="labels" id="editLabels" type="text" class="form-control">
          </div>
          <div class="form-group">
            <label>Kontak Mitra (telp/email)</label>
            <input name="kontak_mitra" id="editKontak" type="text" class="form-control">
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Tanggal Mulai</label>
              <input name="tanggal_mulai" id="editStart" type="date" class="form-control">
            </div>
            <div class="form-group col-md-6">
              <label>Tanggal Selesai</label>
              <input name="tanggal_selesai" id="editEnd" type="date" class="form-control">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Skema PBL</label>
              <select name="skema_pbl" id="editSkema" class="form-control">
                <option value="">- Pilih -</option>
                <option>Penelitian</option>
                <option>Pengabdian</option>
                <option>Lomba</option>
                <option>PBL x TeFa</option>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label>Status</label>
              <select name="status_proyek" id="editStatus" class="form-control">
                <option>Proses</option>
                <option>Dibatalkan</option>
                <option>Selesai</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label>Nama Mitra</label>
            <input name="nama_mitra" id="editMitra" type="text" class="form-control">
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Biaya Barang</label>
              <input name="biaya_barang" id="editBiayaBarang" type="number" step="0.01" min="0" class="form-control">
            </div>
            <div class="form-group col-md-6">
              <label>Biaya Jasa</label>
              <input name="biaya_jasa" id="editBiayaJasa" type="number" step="0.01" min="0" class="form-control">
            </div>
          </div>
          <div class="form-group">
            <label>Link Drive Proyek</label>
            <input name="link_drive_proyek" id="editDrive" type="url" class="form-control" placeholder="https://...">
          </div>
          <div class="form-group mb-0">
            <label>Progress</label>
            <input name="progress" id="editProgress" type="number" min="0" max="100" class="form-control">
          </div>
          <div class="form-group mt-2">
            <label>Kendala</label>
            <textarea name="kendala" id="editKendala" class="form-control" rows="2"></textarea>
          </div>
          <div class="form-group">
            <label>Catatan</label>
            <textarea name="catatan" id="editCatatan" class="form-control" rows="2"></textarea>
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

{{-- Modal edit kolom/list --}}
<div class="modal fade" id="modalEditList" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Ubah Kolom</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <form id="editListForm" method="POST" action="#">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="form-group">
            <label>Nama Kolom</label>
            <input name="name" id="editListName" type="text" class="form-control" required>
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

{{-- Modal buat/ubah proyek dihapus: tidak ada entitas Project Board --}}

@push('styles')
<style>
  .board-wrapper { overflow-x: auto; overflow-y: hidden; }
  .board { min-height: 70vh; padding-bottom: .5rem; }
  .board-column { width: 280px; min-width: 280px; margin-right: 1rem; }
  .board-column:last-child { margin-right: 0; }
  .board-list { min-height: 20px; }
  .board-card { border-left: 3px solid #4e73df; cursor: grab; }
  .board-card.sortable-chosen { opacity: .8; }
  .board-card.sortable-ghost { border: 1px dashed #4e73df; background: #f8f9fc; }
  .progress.progress-sm { height: 6px; }
  /* members UI removed */
  /* Align action buttons neatly */
  .board-actions .btn { padding: .2rem .45rem; line-height: 1; }
  .board-actions .btn i { font-size: .85rem; }
  .board-column .d-flex.align-items-center { min-height: 34px; }
  .card-desc { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
  /* Card action buttons use btn-circle globally */
  /* Small avatar initial + name chip for creator/updater */
  .user-chip { display:inline-flex; align-items:center; border:1px solid rgba(0,0,0,.06); border-radius:12px; padding:0 6px 0 2px; background:#fff; }
  .user-name { max-width:140px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
  .user-avatar { width:20px; height:20px; border-radius:50%; border:1px solid rgba(0,0,0,.06); margin-right:6px; object-fit:cover; }
</style>
@endpush

@push('scripts')
<!-- SortableJS untuk drag-n-drop -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
  (function() {
    // Inisialisasi Drag & Drop antar kartu
    const lists = document.querySelectorAll('.board-list');
    const reorderUrl = "{{ route('proyek.reorder') }}";
    const reorderListsUrl = "{{ route('proyek.lists.reorder') }}";
    lists.forEach(function(listEl){
      new Sortable(listEl, {
        group: 'board',
        animation: 150,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        dragClass: 'sortable-drag',
        onEnd: function(evt) {
          const cardEl = evt.item;
          const cardId = cardEl.getAttribute('data-card-id');
          const toList = evt.to.getAttribute('data-list-id');
          const fromList = evt.from.getAttribute('data-list-id');
          const newIndex = evt.newIndex;

          // Placeholder: tampilkan notifikasi perubahan urutan/status
          console.log('Moved', { cardId, fromList, toList, newIndex });

          // Kirim perubahan ke backend via AJAX (stub persistence)
          $.post(reorderUrl, { card_id: cardId, to_list: toList, position: newIndex, _token: '{{ csrf_token() }}' })
            .done(() => console.log('Sukses simpan urutan'))
            .fail(() => Swal.fire('Gagal', 'Tidak dapat menyimpan urutan', 'error'));
        }
      });
    });

    // Reorder kolom/list
    const boardEl = document.getElementById('board');
    if (boardEl) {
      new Sortable(boardEl, {
        group: 'board-columns',
        animation: 150,
        handle: '.d-flex.align-items-center',
        onEnd: function() {
          const ids = Array.from(boardEl.querySelectorAll('.board-column')).map(col => col.getAttribute('data-col-id'));
          $.post(reorderListsUrl, { list_ids: ids, _token: '{{ csrf_token() }}' })
            .done(() => console.log('Sukses simpan urutan kolom'))
            .fail(() => Swal.fire('Gagal', 'Tidak dapat menyimpan urutan kolom', 'error'));
        }
      });
    }

    // Pencarian sederhana di client
    const search = document.getElementById('boardSearch');
    if (search) {
      search.addEventListener('input', function() {
        const q = this.value.trim().toLowerCase();
        document.querySelectorAll('.board-card').forEach(function(card){
          const title = card.getAttribute('data-title-search') || '';
          card.style.display = title.includes(q) ? '' : 'none';
        });
      });
    }

    // Set list target saat klik tambah
    $(document).on('click', '.btn-add-card', function(){
      const listUuid = $(this).data('list-id');
      $('#addCardListId').val(listUuid);
    });

    // Hapus kartu (konfirmasi)
    $(document).on('click', '.btn-delete-card', function(){
      const form = $(this).closest('form');
      Swal.fire({ title: 'Hapus Proyek?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, hapus' })
        .then(r=>{ if(r.isConfirmed) form.submit(); });
    });

    // Hapus kolom (konfirmasi)
    $(document).on('click', '.btn-delete-list', function(){
      const form = $(this).closest('form');
      Swal.fire({ title: 'Hapus kolom?', text: 'Kolom harus kosong.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, hapus' })
        .then(r=>{ if(r.isConfirmed) form.submit(); });
    });

    // Edit kartu: isi modal dari data-card
    $(document).on('click', '.btn-edit-card', function(){
      const card = $(this).closest('.board-card');
      const id = card.data('card-id');
      const title = card.data('title');
      const desc = card.data('desc') || '';
      const labels = (card.data('labels')||[]).join(', ');
      const start = card.data('tmulai')||'';
      const end = card.data('tselesai')||'';
      const progress = card.data('progress')||0;
      const skema = card.data('skema')||'';
      const status = card.data('status')||'';
      const mitra = card.data('mitra')||'';
      const kontak = card.data('kontak')||'';
      const bbar = card.data('bbar')||'';
      const bjas = card.data('bjas')||'';
      const drive = card.data('drive')||'';
      const kendala = card.data('kendala')||'';
      const catatan = card.data('catatan')||'';

      $('#editTitle').val(title);
      $('#editDescription').val(desc);
      $('#editLabels').val(labels);
      $('#editStart').val(start);
      $('#editEnd').val(end);
      $('#editProgress').val(progress);
      $('#editSkema').val(skema);
      $('#editStatus').val(status||'Proses');
      $('#editMitra').val(mitra);
      $('#editKontak').val(kontak);
      $('#editBiayaBarang').val(bbar);
      $('#editBiayaJasa').val(bjas);
      $('#editDrive').val(drive);
      $('#editKendala').val(kendala);
      $('#editCatatan').val(catatan);
      $('#editCardForm').attr('action', "{{ url('mahasiswa/proyek/cards') }}/"+id);
    });

    // Edit kolom: isi modal dari data-list
    $(document).on('click', '.btn-edit-list', function(){
      const id = $(this).data('list-id');
      const name = $(this).data('list-name') || '';
      $('#editListName').val(name);
      $('#editListForm').attr('action', "{{ url('mahasiswa/proyek/lists') }}/"+id);
    });
  })();
</script>
@endpush
@endsection

{{-- Modal tambah kolom/list --}}
<div class="modal fade" id="modalAddList" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Kolom</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <form method="POST" action="{{ route('proyek.lists.store') }}">
        @csrf
        <div class="modal-body">
          <div class="form-group">
            <label>Nama Kolom</label>
            <input name="name" type="text" class="form-control" required>
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
