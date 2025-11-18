@extends('layout.app')
@section('content')
@push('style')
    <style>
      .card-title-full{
        font-size:.95rem;
        line-height:1.3;
        /* full width + wrap rapi */
        white-space:normal;
        word-break:break-word;
      }
      .date-chip{
        display:inline-flex;
        align-items:center;
        font-size:.75rem;
        line-height:1;
        padding:.28rem .5rem;
        border-radius:999px;
        background:#f1f5ff;
        color:#2743d3;
        border:1px solid #e5e9ff;
        white-space:nowrap;
      }
      .date-chip-danger{
        background:#fff2f2;
        color:#b42318;
        border-color:#ffd9d7;
      }

    </style>
@endpush
<div class="container-fluid">
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
                  <i class="fas fa-plus mr-1"></i> Proyek
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
                data-kendala="{{ e($card['kendala'] ?? '') }}"
                data-catatan="{{ e($card['catatan'] ?? '') }}"
                              >
                <div class="card-body p-2 d-flex flex-column">

                 {{-- HEADER: Status Lock → Aksi (kanan atas) → Judul penuh → Tanggal --}}
                  <div class="d-flex align-items-center mb-1">
                    @if(($card['status_proyek'] ?? '') === 'Selesai')
                      <span class="badge badge-success mr-2" title="Proyek selesai - tidak dapat diubah">
                        <i class="fas fa-lock mr-1"></i>Selesai
                      </span>
                    @endif
                    <div class="ml-auto d-flex align-items-center">
                      <button type="button"
                              class="btn btn-primary btn-icon-only btn-sm btn-circle btn-edit-card mr-1"
                              title="Ubah" data-toggle="modal" data-target="#modalEditCard" aria-label="Ubah proyek">
                        <i class="fas fa-edit"></i>
                      </button>
                      {{-- Share Link Penilaian Mitra --}}
                      <button type="button"
                              class="btn btn-info btn-icon-only btn-sm btn-circle mr-1"
                              title="Share Link Penilaian Mitra"
                              onclick="sharePenilaianMitra('{{ $card['id'] }}','{{ addslashes($card['title']) }}')">
                        <i class="fas fa-share-alt" aria-hidden="true"></i>
                      </button>
                      <form method="POST"
                            action="{{ route('proyek.cards.destroy', ['card' => $card['id']]) }}"
                            class="m-0 d-inline">
                        @csrf @method('DELETE')
                        <button type="button"
                                class="btn btn-danger btn-icon-only btn-circle btn-sm btn-delete-card"
                                title="Hapus" aria-label="Hapus proyek">
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
                  <div class="d-flex align-items-center x-small mt-auto pt-1 border-top-light">
                    <span class="badge {{ statusBadgeClass($card['status_proyek'] ?? '') }}">{{ $card['status_proyek'] ?? 'Proses' }}</span>
                    <span class="ml-3 text-muted"><i class="far fa-comment mr-1"></i>{{ $card['comments'] ?? 0 }}</span>
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
                  <i class="far fa-clipboard mr-1"></i> Belum ada proyek
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
      <div class="modal-header"><h5 class="modal-title">Tambah Proyek</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <form method="POST" action="{{ route('proyek.cards.store') }}">
        @csrf
        <div class="modal-body">
          <input type="hidden" name="list" id="addCardListId" value="">
          <div class="form-group"><label>Judul</label><input name="title" type="text" class="form-control" required></div>
          <div class="form-group"><label>Deskripsi</label><textarea name="description" class="form-control" rows="3"></textarea></div>
          <div class="form-group"><label>Label (pisahkan koma)</label><input name="labels" type="text" class="form-control" placeholder="AI, IoT"></div>
          <div class="form-group"><label>Kontak Mitra</label><input name="kontak_mitra" type="text" class="form-control"></div>
          <div class="form-row">
            <div class="form-group col-md-6"><label>Tanggal Mulai</label><input name="tanggal_mulai" type="date" class="form-control"></div>
            <div class="form-group col-md-6"><label>Tanggal Selesai</label><input name="tanggal_selesai" type="date" class="form-control"></div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Skema PBL</label>
              <select name="skema_pbl" class="form-control">
                <option value="">- Pilih -</option><option>Penelitian</option><option>Pengabdian</option><option>Lomba</option><option>PBL x TeFa</option>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label>Status</label>
              <select name="status_proyek" class="form-control"><option>Proses</option><option>Dibatalkan</option></select>
            </div>
          </div>
          <div class="form-group"><label>Nama Mitra</label><input name="nama_mitra" type="text" class="form-control"></div>
          <div class="form-row">
            <div class="form-group col-md-6"><label>Biaya Barang</label><input name="biaya_barang" type="number" step="0.01" min="0" class="form-control" value="0"></div>
            <div class="form-group col-md-6"><label>Biaya Jasa</label><input name="biaya_jasa" type="number" step="0.01" min="0" class="form-control" value="0"></div>
          </div>
          <div class="form-group"><label>Link Drive Proyek</label><input name="link_drive_proyek" type="url" class="form-control" placeholder="https://..."></div>
          <div class="form-group"><label>Kendala</label><textarea name="kendala" class="form-control" rows="2"></textarea></div>
          <div class="form-group mb-0"><label>Catatan</label><textarea name="catatan" class="form-control" rows="2" readonly></textarea></div>
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
      <div class="modal-header"><h5 class="modal-title">Ubah Proyek</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <form id="editCardForm" method="POST" action="#">
        @csrf @method('PUT')
        <div class="modal-body">
          <div class="form-group"><label>Judul</label><input name="title" id="editTitle" type="text" class="form-control" required></div>
          <div class="form-group"><label>Deskripsi</label><textarea name="description" id="editDescription" class="form-control" rows="3"></textarea></div>
          <div class="form-group"><label>Label</label><input name="labels" id="editLabels" type="text" class="form-control"></div>
          <div class="form-group"><label>Kontak Mitra</label><input name="kontak_mitra" id="editKontak" type="text" class="form-control"></div>
          <div class="form-row">
            <div class="form-group col-md-6"><label>Tanggal Mulai</label><input name="tanggal_mulai" id="editStart" type="date" class="form-control"></div>
            <div class="form-group col-md-6"><label>Tanggal Selesai</label><input name="tanggal_selesai" id="editEnd" type="date" class="form-control"></div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Skema PBL</label>
              <select name="skema_pbl" id="editSkema" class="form-control">
                <option value="">- Pilih -</option><option>Penelitian</option><option>Pengabdian</option><option>Lomba</option><option>PBL x TeFa</option>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label>Status</label>
              <select name="status_proyek" id="editStatus" class="form-control">
                <option value="Proses">Proses</option>
                <option value="Dibatalkan">Dibatalkan</option>
              </select>
            </div>
          </div>
          <div class="form-group"><label>Nama Mitra</label><input name="nama_mitra" id="editMitra" type="text" class="form-control"></div>
          <div class="form-row">
            <div class="form-group col-md-6"><label>Biaya Barang</label><input name="biaya_barang" id="editBiayaBarang" type="number" step="0.01" min="0" class="form-control"></div>
            <div class="form-group col-md-6"><label>Biaya Jasa</label><input name="biaya_jasa" id="editBiayaJasa" type="number" step="0.01" min="0" class="form-control"></div>
          </div>
          <div class="form-group"><label>Link Drive Proyek</label><input name="link_drive_proyek" id="editDrive" type="url" class="form-control" placeholder="https://..."></div>
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

@push('styles')
<style>
  .board-wrapper{overflow-x:auto;overflow-y:hidden}
  .board{min-height:70vh;padding-bottom:.5rem}
  .board-column{width:300px;min-width:300px;margin-right:1rem;display:flex;flex-direction:column}
  .board-list{min-height:20px}
  .board-col-head{
    position:sticky;top:0;z-index:2;background:#fff;border:1px solid #eef1f5;border-radius:.75rem;
    padding:.6rem .7rem;box-shadow:0 2px 6px rgba(0,0,0,.03);margin-bottom:.5rem
  }
  .board-card{border-left:3px solid #4e73df;cursor:grab;border-radius:.75rem;overflow:hidden}
  .board-card.sortable-chosen{opacity:.8}
  .board-card.sortable-ghost{border:1px dashed #4e73df;background:#f8f9fc}
  .progress.progress-sm{height:6px}
  .badge-soft{background:#eef2ff;color:#4e73df}
  .truncate{max-width:150px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .x-small{font-size:.75rem}
  .text-body-2{font-size:.9rem;line-height:1.35}
  .clamp-2{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
  .shadow-xs{box-shadow:0 1px 3px rgba(0,0,0,.06)}
  .user-chip{display:inline-flex;align-items:center;border:1px solid rgba(0,0,0,.06);border-radius:12px;padding:0 6px 0 2px;background:#fff}
  .user-name{max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
  .user-avatar{width:20px;height:20px;border-radius:50%;border:1px solid rgba(0,0,0,.06);margin-right:6px;object-fit:cover}

  /* Tambahan baru */
  .hover-raise{transition:transform .12s ease, box-shadow .12s ease}
  .hover-raise:hover{transform:translateY(-1px);box-shadow:0 6px 18px rgba(0,0,0,.06)}
  .card-title-row{min-height:1.4rem}
  .card-title-text{font-size:.95rem;line-height:1.2;max-width:14rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .date-chip{font-size:.75rem;line-height:1;padding:.28rem .5rem;border-radius:999px;background:#f1f5ff;color:#2743d3;border:1px solid #e5e9ff;white-space:nowrap}
  .date-chip-danger{background:#fff2f2;color:#b42318;border-color:#ffd9d7}
  .border-top-light{border-top:1px solid #f1f2f4}
  .btn-drive{padding:.18rem .5rem}
  .labels-wrap{display:flex;flex-wrap:wrap}

  /* Card yang sudah selesai */
  .card-completed {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
    border-left: 4px solid #28a745 !important;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04) !important;
    border-radius: 0.75rem;
    position: relative;
    overflow: hidden;
  }

  .card-completed::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, rgba(40, 167, 69, 0.1) 0%, rgba(32, 201, 151, 0.05) 100%);
    border-radius: 0 0.75rem 0 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .card-completed::after {
    content: '✓';
    position: absolute;
    top: 2px;
    right: 8px;
    color: #28a745;
    font-size: 18px;
    font-weight: bold;
    z-index: 1;
  }

  .card-completed:hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.15) !important;
    transition: all 0.2s ease !important;
  }

  .card-completed .btn-edit-card,
  .card-completed .btn-delete-card {
    background-color: #e9ecef !important;
    color: #6c757d !important;
    border-color: #dee2e6 !important;
    opacity: 0.7;
    cursor: not-allowed;
    pointer-events: none;
  }

  .card-completed .btn-edit-card:hover,
  .card-completed .btn-delete-card:hover {
    background-color: #e9ecef !important;
    transform: none !important;
    box-shadow: none !important;
  }

  /* Badge yang lebih bagus untuk proyek selesai */
  .card-completed .badge-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3) !important;
    font-weight: 600;
    padding: 0.35rem 0.65rem;
  }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function(){
  const reorderUrl = "{{ route('proyek.reorder') }}";
  const reorderListsUrl = "{{ route('proyek.lists.reorder') }}";

  // Fallback buka modal Add List (BS4/BS5)
  function showModal(id){
    const $el = $('#'+id);
    if (typeof $el.modal === 'function') { $el.modal('show'); return; }
    const el = document.getElementById(id);
    if (window.bootstrap && el) { bootstrap.Modal.getOrCreateInstance(el).show(); }
  }
  $(document).on('click','#btnAddList, #btnAddListEmpty',function(e){
    e.preventDefault(); showModal('modalAddList');
  });

  // Drag antar kartu
  document.querySelectorAll('.board-list').forEach(function(listEl){
    new Sortable(listEl, {
      group:'board',animation:150,ghostClass:'sortable-ghost',chosenClass:'sortable-chosen',dragClass:'sortable-drag',
      filter: '.card-completed',
      preventOnFilter: false,
      onEnd:function(evt){
        const cardId = evt.item.getAttribute('data-card-id');
        const toList = evt.to.getAttribute('data-list-id');
        const newIndex = evt.newIndex;
        $.post(reorderUrl, {card_id:cardId,to_list:toList,position:newIndex,_token:'{{ csrf_token() }}'})
         .fail(()=> Swal.fire('Gagal','Tidak dapat menyimpan urutan','error'));
      }
    });
  });

  // Drag urutkan kolom (handle: head)
  const boardEl = document.getElementById('board');
  if (boardEl){
    new Sortable(boardEl, {
      group:'board-columns',animation:150,handle:'.board-col-head',
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
        const hay = ((card.getAttribute('data-title-search')||'') + ' ' + (card.getAttribute('data-desc')||'')).toLowerCase();
        card.style.display = hay.includes(q) ? '' : 'none';
      });
    });
  }

  // Tambah kartu: isi list id
  $(document).on('click','.btn-add-card',function(){
    $('#addCardListId').val($(this).data('list-id'));
  });

  // Hapus kartu
  $(document).on('click','.btn-delete-card',function(){
    const form = $(this).closest('form');
    Swal.fire({title:'Hapus Proyek?',icon:'warning',showCancelButton:true,confirmButtonText:'Ya, hapus'})
      .then(r=>{ if(r.isConfirmed) form.submit(); });
  });

  // Hapus kolom
  $(document).on('click','.btn-delete-list',function(){
    const form = $(this).closest('form');
    Swal.fire({title:'Hapus kolom?',text:'Kolom harus kosong.',icon:'warning',showCancelButton:true,confirmButtonText:'Ya, hapus'})
      .then(r=>{ if(r.isConfirmed) form.submit(); });
  });

  // Edit kartu: isi modal
  $(document).on('click','.btn-edit-card',function(){
    const card = $(this).closest('.board-card');
    const id = card.data('card-id');
    $('#editTitle').val(card.data('title'));
    $('#editDescription').val(card.data('desc')||'');
    $('#editLabels').val(((card.data('labels'))||[]).join(', '));
    $('#editStart').val(card.data('tmulai')||'');
    $('#editEnd').val(card.data('tselesai')||'');
    $('#editProgress').val(card.data('progress')||0);
    $('#editSkema').val(card.data('skema')||'');
    $('#editStatus').val(card.data('status')||'Proses');
    $('#editMitra').val(card.data('mitra')||'');
    $('#editKontak').val(card.data('kontak')||'');
    $('#editBiayaBarang').val(card.data('bbar')||'');
    $('#editBiayaJasa').val(card.data('bjas')||'');
    $('#editDrive').val(card.data('drive')||'');
    $('#editKendala').val(card.data('kendala')||'');
    $('#editCatatan').val(card.data('catatan')||'');
    $('#editCardForm').attr('action', "{{ url('mahasiswa/proyek/cards') }}/"+id);
  });

  // Edit kolom
  $(document).on('click','.btn-edit-list',function(){
    const id = $(this).data('list-id');
    $('#editListName').val($(this).data('list-name')||'');
    $('#editListForm').attr('action', "{{ url('mahasiswa/proyek/lists') }}/"+id);
  });

  // Share Link Penilaian Mitra
  window.sharePenilaianMitra = function(cardId, cardTitle) {
    const baseUrl = window.location.origin;
    const shareUrl = `${baseUrl}/penilaian-mitra/${cardId}`;

    Swal.fire({
      title: 'Share Link Penilaian Mitra',
      html: `
        <div class="text-left">
          <p class="mb-3">Bagikan link ini kepada mitra untuk menilai proyek:</p>
          <div class="form-group mb-3">
            <label class="small text-muted">Proyek:</label>
            <div class="font-weight-bold">${cardTitle}</div>
          </div>
          <div class="form-group mb-3">
            <label class="small text-muted">Link Penilaian:</label>
            <div class="input-group">
              <input type="text" id="shareLinkInput" class="form-control" value="${shareUrl}" readonly>
              <div class="input-group-append">
                <button type="button" class="btn btn-primary" onclick="copyShareLink()">
                  <i class="fas fa-copy"></i> Salin
                </button>
              </div>
            </div>
          </div>
          <div class="alert alert-info small">
            <i class="fas fa-info-circle mr-2"></i>
            Mitra dapat mengakses link ini tanpa perlu login untuk memberikan penilaian.
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
    input.select();
    input.setSelectionRange(0, 99999);

    try {
      document.execCommand('copy');

      // Show success message
      const originalValue = input.value;
      input.value = 'Tersalin!';
      input.classList.add('bg-success', 'text-white');

      setTimeout(() => {
        input.value = originalValue;
        input.classList.remove('bg-success', 'text-white');
      }, 2000);

      // Show toast notification
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: 'success',
          title: 'Berhasil!',
          text: 'Link penilaian berhasil disalin.',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true
        });
      }
    } catch (err) {
      console.error('Failed to copy link:', err);

      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: 'error',
          title: 'Gagal!',
          text: 'Gagal menyalin link. Silakan salin manual.',
        });
      }
    }
  };
})();
</script>
@endpush
