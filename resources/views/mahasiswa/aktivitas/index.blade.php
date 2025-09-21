@extends('layout.app')
@section('content')
@push('styles')
<style>
.user-chip {
  display: inline-flex;
  align-items: center;
  border: 1px solid rgba(0,0,0,.06);
  border-radius: 12px;
  padding: 0 6px 0 2px;
  background: #fff;
  margin-right: 4px;
}
.user-name {
  max-width: 140px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-size: .75rem;
}
.user-avatar {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: 1px solid rgba(0,0,0,.06);
  margin-right: 6px;
  object-fit: cover;
}
</style>
@endpush

@php
if (!function_exists('avatarUrl')) {
  function avatarUrl(?string $name): string {
    $name = trim((string)$name); if ($name === '') $name = 'U';
    $palette = ['4e73df','1cc88a','36b9cc','f6c23e','e74a3b','858796'];
    $idx = abs(crc32($name)) % count($palette); $bg = $palette[$idx]; $enc = urlencode($name);
    return "https://ui-avatars.com/api/?name={$enc}&background={$bg}&color=ffffff&size=64&rounded=true";
  }
}
@endphp
<div class="container-fluid">
  <div class="d-flex align-items-center mb-3 flex-wrap">
    <div class="d-flex align-items-baseline mr-3 mb-2 mb-md-0">
      <h1 class="h3 text-gray-800 mb-0">Aktivitas</h1>
      @if(isset($kelompok) && $kelompok)
        <span class="ml-3 small text-muted">
          <strong>{{ $kelompok->nama_kelompok ?? '-' }}</strong>
          @if(isset($periodeAktif) && $periodeAktif)
            <span class="badge badge-light border ml-2">{{ $periodeAktif->periode }}</span>
          @endif
        </span>
      @endif
    </div>
    <div class="ml-auto d-flex align-items-center">
      @if(isset($kelompok) && $kelompok)
        <button class="btn btn-success btn-circle btn-sm mr-2" data-toggle="modal" data-target="#modalAddList" title="Tambah Kolom">
          <i class="fas fa-plus"></i>
        </button>
      @endif
      <input id="boardSearch" type="text" class="form-control form-control-sm" placeholder="Cari..." style="max-width:220px;">
    </div>
  </div>

  @if(isset($lists) && count($lists) > 0)
  <div class="board-wrapper">
    <div id="board" class="board d-flex">
      @foreach ($lists as $list)
        <div class="board-column" data-col-id="{{ $list['id'] }}">
        {{-- HEAD: Judul, aksi, dan meta --}}
        <div class="board-col-head">
          {{-- Baris 1: Judul + Aksi --}}
          <div class="d-flex align-items-center">
            <h6 class="mb-0 text-uppercase text-dark font-weight-bold truncate">
              {{ $list['title'] }}
            </h6>

            <div class="ml-auto btn-group btn-group-sm" role="group">
              <button class="btn btn-success btn-add-card"
                      data-list-id="{{ $list['id'] }}"
                      data-toggle="modal" data-target="#modalAddCard"
                      title="Tambah Aktivitas">
                <i class="fas fa-plus mr-1"></i> Aktivitas
              </button>

              <button class="btn btn-primary btn-edit-list"
                      data-list-id="{{ $list['id'] }}"
                      data-list-name="{{ $list['title'] }}"
                      data-list-rentang="{{ $list['rentang_tanggal'] }}"
                      data-list-drive="{{ $list['link_drive_logbook'] ?? '' }}"
                      data-list-status="{{ $list['status_evaluasi'] ?? 'Belum Evaluasi' }}"
                      data-toggle="modal" data-target="#modalEditList"
                      title="Ubah kolom">
                <i class="fas fa-edit"></i>
              </button>

              <form method="POST"
                    action="{{ route('aktivitas.lists.destroy', ['list' => $list['id']]) }}"
                    class="m-0">
                @csrf @method('DELETE')
                <button type="button" class="btn btn-danger ml-1 btn-delete-list" title="Hapus kolom">
                  <i class="fas fa-trash"></i>
                </button>
              </form>
            </div>
          </div>
          <hr>
          {{-- Baris 2: Rentang, jumlah, status, logbook --}}
          <div class="d-flex align-items-center small text-muted mt-1 flex-wrap">
            <span class="badge {{ ($list['status_evaluasi'] ?? 'Belum Evaluasi') === 'Sudah Evaluasi' ? 'badge-success' : 'badge-secondary' }}">
              {{ $list['status_evaluasi'] ?? 'Belum Evaluasi' }}
            </span>

            @if(!empty($list['link_drive_logbook']))
              <a href="{{ $list['link_drive_logbook'] }}"
                 target="_blank" rel="noopener"
                 class="btn btn-outline-dark btn-sm border ml-auto mt-1 mt-sm-0">
                <i class="fab fa-google-drive mr-1"></i> Logbook
              </a>
            @endif
            
            <div class="d-inline-flex align-items-center mt-2 mr-2">
              <i class="far fa-calendar-alt mr-1"></i>
              <span class="truncate">{{ $list['rentang_tanggal'] ?: '—' }}</span>
            </div>

            <span class="badge badge-soft mt-2 mr-2" title="Jumlah aktivitas">
              {{ count($list['cards']) }}
            </span>
          </div>
        </div>

          {{-- BODY: Kartu --}}
          <div class="board-list" data-list-id="{{ $list['id'] }}">
            @forelse ($list['cards'] as $card)
              <div class="card board-card mb-2 shadow-xs"
                   data-card-id="{{ $card['id'] }}"
                   data-title-search="{{ strtolower(($card['description'] ?? '') . ' ' . ($card['tanggal_aktivitas'] ?? '')) }}"
                   data-desc="{{ $card['description'] ?? '' }}"
                   data-tanggal="{{ $card['tanggal_aktivitas'] ?? '' }}">
                <div class="card-body py-2">
                  
                  {{-- 1) Tombol aksi di kanan atas --}}
                  <div class="d-flex align-items-center mb-1">
                    <div class="ml-auto d-flex align-items-center">
                      <button type="button" class="btn btn-primary btn-circle btn-icon-only btn-sm btn-edit-card mr-1"
                              title="Ubah" data-toggle="modal" data-target="#modalEditCard">
                        <i class="fas fa-edit"></i>
                      </button>
                      <form method="POST" action="{{ route('aktivitas.cards.destroy', ['card' => $card['id']]) }}" class="m-0 d-inline">
                        @csrf @method('DELETE')
                        <button type="button" class="btn btn-danger btn-circle btn-icon-only btn-sm btn-delete-card" title="Hapus">
                          <i class="fas fa-trash"></i>
                        </button>
                      </form>
                    </div>
                  </div>

                  {{-- 2) Judul/Deskripsi penuh --}}
                  <div class="card-title-full font-weight-bold mb-1">
                    {{ $card['description'] ?? '-' }}
                  </div>

                  {{-- 3) Tanggal di bawah judul --}}
                  <div class="small text-muted mb-2">
                    <i class="far fa-calendar-alt mr-1"></i>
                    {{ $card['tanggal_aktivitas'] ? \Carbon\Carbon::parse($card['tanggal_aktivitas'])->locale('id')->translatedFormat('l, d M Y') : '-' }}
                  </div>

                  <div class="mt-2 d-flex flex-column text-muted x-small">
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
                </div>


                </div>
              </div>
            @empty
              <div class="card card-empty shadow-0">
                <div class="card-body p-3 text-center text-muted small">
                  <i class="far fa-clipboard mr-1"></i> Belum ada aktivitas
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
      <p class="mb-2">Belum ada kolom aktivitas.</p>
      @if(isset($kelompok) && $kelompok)
      <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalAddList">
        <i class="fas fa-plus mr-1"></i> Tambah Kolom
      </button>
      @endif
    </div>
  </div>
  @endif
</div>

{{-- Modal Tambah Card --}}
<div class="modal fade" id="modalAddCard" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Tambah Aktivitas</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <form method="POST" action="{{ route('aktivitas.cards.store') }}">
        @csrf
        <div class="modal-body">
          <input type="hidden" name="list" id="addCardListId" value="">
          <div class="form-group">
            <label>Tanggal Aktivitas</label>
            <input name="tanggal_aktivitas" type="date" class="form-control">
          </div>
          <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
          </div>
          <div class="form-group">
            <label>Bukti Kegiatan (Link)</label>
            <input name="bukti_kegiatan" type="url" class="form-control" placeholder="https://...">
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

{{-- Modal Edit Card --}}
<div class="modal fade" id="modalEditCard" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Ubah Aktivitas</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <form id="editCardForm" method="POST" action="#">
        @csrf @method('PUT')
        <div class="modal-body">
          <div class="form-group">
            <label>Tanggal Aktivitas</label>
            <input name="tanggal_aktivitas" id="editTanggal" type="date" class="form-control">
          </div>
          <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="description" id="editDesc" class="form-control" rows="3"></textarea>
          </div>
          <div class="form-group">
            <label>Ganti Bukti (opsional)</label>
            <input name="bukti_kegiatan" type="url" class="form-control" placeholder="https://...">
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

{{-- Modal Tambah List --}}
<div class="modal fade" id="modalAddList" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Tambah Kolom</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <form method="POST" action="{{ route('aktivitas.lists.store') }}">
        @csrf
        <div class="modal-body">
          <div class="form-group">
            <label>Nama Kolom (mis. Minggu 1)</label>
            <input name="name" type="text" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Rentang Tanggal</label>
            <input name="rentang_tanggal" type="text" class="form-control" placeholder="Contoh : 01 Jan 2024 - 07 Jan 2024">
          </div>
          <div class="form-group">
            <label>Link Drive Logbook</label>
            <input name="link_drive_logbook" type="url" class="form-control" placeholder="https://...">
          </div>
          <div class="form-group">
            <label>Status Evaluasi</label>
            <select name="status_evaluasi" class="form-control">
              <option>Belum Evaluasi</option>
              <option>Sudah Evaluasi</option>
            </select>
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

{{-- Modal Edit List --}}
<div class="modal fade" id="modalEditList" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Ubah Kolom</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <form id="editListForm" method="POST" action="#">
        @csrf @method('PUT')
        <div class="modal-body">
          <div class="form-group">
            <label>Nama Kolom</label>
            <input name="name" id="editListName" type="text" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Rentang Tanggal</label>
            <input name="rentang_tanggal" id="editListRentang" type="text" class="form-control">
          </div>
          <div class="form-group">
            <label>Link Drive Logbook</label>
            <input name="link_drive_logbook" id="editListDrive" type="url" class="form-control">
          </div>
          <div class="form-group">
            <label>Status Evaluasi</label>
            <select name="status_evaluasi" id="editListStatus" class="form-control">
              <option>Belum Evaluasi</option>
              <option>Sudah Evaluasi</option>
            </select>
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
@endsection

@push('styles')
<style>
  .board-wrapper { overflow-x:auto; overflow-y:hidden; }
  .board { min-height:70vh; padding-bottom:.5rem; }
  .board-column { width:300px; min-width:300px; margin-right:1rem; display:flex; flex-direction:column; }
  .board-list { min-height:20px; }

  .board-col-head{
    position:sticky; top:0; z-index:2;
    background:#fff; border:1px solid #eef1f5; border-radius:.75rem; padding:.7rem .7rem;
    box-shadow:0 2px 6px rgba(0,0,0,.03); margin-bottom:.5rem;
  }

  .board-card { border-left:3px solid #1cc88a; cursor:grab; }
  .board-card.sortable-chosen { opacity:.8; }
  .board-card.sortable-ghost { border:1px dashed #1cc88a; background:#f8f9fc; }

  .badge-soft{ background:#eef2ff; color:#4e73df; }
  .truncate{ max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .x-small{ font-size:.75rem; }
  .text-body-2{ font-size:.9rem; line-height:1.35; }
  .clamp-3{ display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; }
  .shadow-xs{ box-shadow:0 1px 3px rgba(0,0,0,.06); }

  /* Judul penuh pada kartu */
  .card-title-full{ font-size:.95rem; line-height:1.3; }

</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function(){
  const reorderUrl = "{{ route('aktivitas.reorder') }}";
  const reorderListsUrl = "{{ route('aktivitas.lists.reorder') }}";

  // Drag kartu
  document.querySelectorAll('.board-list').forEach(function(listEl){
    new Sortable(listEl, {
      group: 'board', animation: 150,
      ghostClass: 'sortable-ghost', chosenClass: 'sortable-chosen', dragClass: 'sortable-drag',
      onEnd: function(evt){
        const cardEl = evt.item;
        const cardId = cardEl.getAttribute('data-card-id');
        const toList  = evt.to.getAttribute('data-list-id');
        const newIndex = evt.newIndex;
        $.post(reorderUrl, { card_id: cardId, to_list: toList, position: newIndex, _token: '{{ csrf_token() }}' })
          .fail(()=> Swal.fire('Gagal','Tidak dapat menyimpan urutan','error'));
      }
    });
  });

  // Drag kolom
  const boardEl = document.getElementById('board');
  if (boardEl) {
    new Sortable(boardEl, {
      group:'board-columns', animation:150, handle:'.board-col-head',
      onEnd: function(){
        const ids = Array.from(boardEl.querySelectorAll('.board-column')).map(col => col.getAttribute('data-col-id'));
        $.post(reorderListsUrl, { list_ids: ids, _token: '{{ csrf_token() }}' })
          .fail(()=> Swal.fire('Gagal','Tidak dapat menyimpan urutan kolom','error'));
      }
    });
  }

  // Pencarian
  const search = document.getElementById('boardSearch');
  if (search) {
    search.addEventListener('input', function(){
      const q = this.value.trim().toLowerCase();
      document.querySelectorAll('.board-card').forEach(function(card){
        const hay = (card.getAttribute('data-title-search') || '').toLowerCase();
        card.style.display = hay.includes(q) ? '' : 'none';
      });
    });
  }

  // Tambah card -> isi list id
  $(document).on('click', '.btn-add-card', function(){
    $('#addCardListId').val($(this).data('list-id'));
  });

  // Hapus card
  $(document).on('click', '.btn-delete-card', function(){
    const form = $(this).closest('form');
    Swal.fire({ title:'Hapus Aktivitas?', icon:'warning', showCancelButton:true, confirmButtonText:'Ya, hapus' })
      .then(r=>{ if(r.isConfirmed) form.submit(); });
  });

  // Hapus list
  $(document).on('click', '.btn-delete-list', function(){
    const form = $(this).closest('form');
    Swal.fire({ title:'Hapus kolom?', text:'Kolom harus kosong.', icon:'warning', showCancelButton:true, confirmButtonText:'Ya, hapus' })
      .then(r=>{ if(r.isConfirmed) form.submit(); });
  });

  // Edit card -> isi modal
  $(document).on('click', '.btn-edit-card', function(){
    const card = $(this).closest('.board-card');
    const id   = card.data('card-id');
    const tgl  = card.data('tanggal')||'';
    const desc = card.data('desc')||'';
    $('#editTanggal').val(tgl);
    $('#editDesc').val(desc);
    $('#editCardForm').attr('action', "{{ url('mahasiswa/aktivitas/cards') }}/"+id);
  });

  // Edit list -> isi modal
  $(document).on('click', '.btn-edit-list', function(){
    const id      = $(this).data('list-id');
    const name    = $(this).data('list-name')    || '';
    const rentang = $(this).data('list-rentang') || '';
    const drive   = $(this).data('list-drive')   || '';
    const status  = $(this).data('list-status')  || 'Belum Evaluasi';

    $('#editListName').val(name);
    $('#editListRentang').val(rentang);
    $('#editListDrive').val(drive);
    $('#editListStatus').val(status);

    $('#editListForm').attr('action', "{{ url('mahasiswa/aktivitas/lists') }}/"+id);
  });
})();
</script>
@endpush
