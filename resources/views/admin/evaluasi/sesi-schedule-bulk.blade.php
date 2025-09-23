{{-- Bulk schedule form fragment (can be embedded in a modal) --}}
<form id="bulk-schedule-form" method="POST" action="{{ route('admin.evaluasi.schedule.bulk') }}">
  @csrf
  <div class="form-group">
    <label>Periode</label>
    <select name="periode_id" class="form-control">
      @foreach($periodes as $p)
        <option value="{{ $p->id }}" {{ (int)$periodeId===(int)$p->id?'selected':'' }}>{{ $p->periode }}</option>
      @endforeach
    </select>
  </div>
  <div class="form-group">
    <label>Tanggal & Waktu Mulai</label>
    <input type="datetime-local" name="jadwal_mulai" class="form-control" required>
  </div>
  <div class="form-group">
    <label>Tanggal & Waktu Selesai <small class="text-muted">(opsional)</small></label>
    <input type="datetime-local" name="jadwal_selesai" class="form-control">
  </div>
  <div class="form-group">
    <label>Durasi (menit)</label>
    <input type="number" name="durasi_menit" class="form-control" value="90">
  </div>
  <div class="form-group">
    <label>Evaluator (opsional)</label>
    <select name="evaluator_id" class="form-control">
      <option value="">-- Tidak ada --</option>
      @foreach(\App\Models\User::adminAndEvaluator('nama_user')->get(['id','nama_user']) as $u)
        <option value="{{ $u->id }}">{{ $u->nama_user }}</option>
      @endforeach
    </select>
  </div>

  <div class="form-group">
    <label>Lokasi <small class="text-muted">(opsional)</small></label>
    <input type="text" name="lokasi" class="form-control" maxlength="150">
  </div>

  {{-- Hidden inputs for selected kelompok ids (populated by JS) --}}
  <div id="bulk-selected-inputs">
    @if(!empty($selected_ids) && is_array($selected_ids))
      @foreach($selected_ids as $sid)
        <input type="hidden" name="selected_ids[]" value="{{ $sid }}">
      @endforeach
    @endif
  </div>

  <div class="text-right">
    <button type="submit" class="btn btn-primary">Jadwalkan Terpilih</button>
  </div>
</form>
