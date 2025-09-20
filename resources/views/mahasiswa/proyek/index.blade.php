@extends('layout.app')
@section('content')
<div class="container-fluid">
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
              <button class="btn btn-outline-primary btn-sm btn-edit-list" data-list-id="{{ $list['id'] }}" data-list-name="{{ $list['title'] }}" data-toggle="modal" data-target="#modalEditList" title="Ubah kolom">
                <i class="fas fa-edit"></i>
              </button>
              <form method="POST" action="{{ route('proyek.lists.destroy', ['list' => $list['id']]) }}" class="m-0" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-outline-danger btn-sm btn-delete-list" title="Hapus kolom">
                  <i class="fas fa-trash"></i>
                </button>
              </form>
              <button class="btn btn-outline-success btn-sm btn-add-card" data-list-id="{{ $list['id'] }}" data-toggle="modal" data-target="#modalAddCard" title="Tambah Proyek">
                <i class="fas fa-plus"></i>
              </button>
            </div>
          </div>

          <div class="board-list" data-list-id="{{ $list['id'] }}">
            @forelse ($list['cards'] as $card)
              <div class="card board-card shadow-sm mb-2" data-card-id="{{ $card['id'] }}" data-title="{{ $card['title'] }}" data-title-search="{{ strtolower($card['title']) }}" data-desc="{{ $card['description'] ?? '' }}" data-labels='@json($card['labels'] ?? [])' data-due="{{ $card['due'] ?? '' }}" data-progress="{{ $card['progress'] ?? 0 }}">
                <div class="card-body py-2">
                  @if (!empty($card['labels']))
                    <div class="mb-1">
                      @foreach ($card['labels'] as $label)
                        <span class="badge badge-pill badge-info mr-1">{{ $label }}</span>
                      @endforeach
                    </div>
                  @endif

                <div class="d-flex align-items-start mb-1">
                  <div class="font-weight-bold small flex-grow-1">{{ $card['title'] }}</div>
                  <div class="ml-2">
                    <button type="button" class="btn btn-link btn-sm p-0 text-secondary btn-edit-card" title="Ubah" data-toggle="modal" data-target="#modalEditCard"><i class="fas fa-edit"></i></button>
                    <form method="POST" action="{{ route('proyek.cards.destroy', ['card' => $card['id']]) }}" style="display:inline;">
                      @csrf
                      @method('DELETE')
                      <button type="button" class="btn btn-link btn-sm p-0 text-danger btn-delete-card" title="Hapus"><i class="fas fa-trash"></i></button>
                    </form>
                  </div>
                </div>

                @if(!empty($card['description']))
                  <div class="text-muted small mb-2">{{ \Illuminate\Support\Str::limit($card['description'], 120) }}</div>
                @endif

                  @isset($card['progress'])
                    <div class="d-flex align-items-center mb-2">
                      <div class="progress progress-sm flex-grow-1 mr-2">
                        <div class="progress-bar" role="progressbar" style="width: {{ $card['progress'] }}%" aria-valuenow="{{ $card['progress'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                      <span class="small text-muted">{{ $card['progress'] }}%</span>
                    </div>
                  @endisset

                  <div class="d-flex align-items-center text-muted small">
                    @if(!empty($card['due']))
                      @php $overdue = \Carbon\Carbon::parse($card['due'])->isPast(); @endphp
                      <span class="mr-2 {{ $overdue ? 'text-danger' : '' }}">
                        <i class="far fa-clock mr-1"></i>{{ \Carbon\Carbon::parse($card['due'])->format('d M') }}
                      </span>
                    @endif

                    @if(!empty($card['members']))
                      <div class="d-flex align-items-center mr-2">
                        @foreach ($card['members'] as $initial)
                          <span class="avatar-initial" title="{{ $initial }}">{{ $initial }}</span>
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

{{-- Modal tambah kartu sederhana (stub) --}}
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
            <input name="title" type="text" class="form-control" placeholder="Judul kartu" required>
          </div>
          <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Deskripsi singkat"></textarea>
          </div>
          <div class="form-group">
            <label>Label (pisahkan koma)</label>
            <input name="labels" type="text" class="form-control" placeholder="Contoh: Backend,API">
          </div>
          <div class="form-group mb-0">
            <label>Tenggat</label>
            <input name="due_date" type="date" class="form-control">
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

{{-- Modal edit kartu --}}
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
            <label>Tenggat</label>
            <input name="due_date" id="editDue" type="date" class="form-control">
          </div>
          <div class="form-group mb-0">
            <label>Progress</label>
            <input name="progress" id="editProgress" type="number" min="0" max="100" class="form-control">
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
  .avatar-initial {
    display: inline-flex; align-items: center; justify-content: center;
    width: 24px; height: 24px; border-radius: 50%;
    background: #f1f2f6; color: #4e73df; font-size: .75rem; font-weight: 700;
    border: 1px solid rgba(0,0,0,.05); margin-right: 4px;
  }
  /* Align action buttons neatly */
  .board-actions .btn { padding: .2rem .45rem; line-height: 1; }
  .board-actions .btn i { font-size: .85rem; }
  .board-column .d-flex.align-items-center { min-height: 34px; }
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
      const due = card.data('due')||'';
      const progress = card.data('progress')||0;

      $('#editTitle').val(title);
      $('#editDescription').val(desc);
      $('#editLabels').val(labels);
      $('#editDue').val(due);
      $('#editProgress').val(progress);
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
