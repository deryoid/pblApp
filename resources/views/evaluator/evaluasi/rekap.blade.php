@extends('layout.app')

@section('content')
<div class="container-fluid">
  <div class="d-flex align-items-center mb-3">
    <h1 class="h3 text-gray-800 mb-0">Penilaian Akhir – Sesi #{{ $sesiId }}</h1>
  </div>

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-bordered mb-0">
          <thead class="text-center align-middle">
            <tr>
              <th rowspan="3" style="min-width:260px;">Nama</th>

              {{-- AP --}}
              <th colspan="{{ ($indsByGroup['ap']->count() ?? 0) + 1 }}">Aktifitas Partisipatif</th>

              {{-- Hasil Proyek --}}
              @php $colD = ($indsByGroup['project_dosen']->count() ?? 0); $colM = ($indsByGroup['project_mitra']->count() ?? 0); @endphp
              <th colspan="{{ $colD + $colM + 1 }}">Hasil Proyek</th>
            </tr>
            <tr class="text-center">
              {{-- AP sub-head --}}
              <th colspan="{{ $indsByGroup['ap']->count() ?? 0 }}">Dosen</th>
              <th rowspan="2">NA Aktifitas<br>Partisipatif</th>

              {{-- Proyek: Dosen/Mitra --}}
              <th colspan="{{ $colD }}">Dosen<br><span class="small">({{ $groups['project_dosen']->weight ?? 80 }}%)</span></th>
              <th colspan="{{ $colM }}">Mitra<br><span class="small">({{ $groups['project_mitra']->weight ?? 20 }}%)</span></th>
              <th rowspan="2">NA Hasil<br>Proyek</th>
            </tr>
            <tr>
              {{-- AP detail --}}
              @foreach(($indsByGroup['ap'] ?? collect()) as $ind)
                <th style="min-width:160px;">
                  <div>{{ $ind->name }}</div>
                  <div class="small text-muted">{{ (int)$ind->weight }}%</div>
                </th>
              @endforeach

              {{-- Proyek Dosen detail --}}
              @foreach(($indsByGroup['project_dosen'] ?? collect()) as $ind)
                <th style="min-width:180px;">
                  <div>{{ $ind->name }}</div>
                  <div class="small text-muted">{{ (int)$ind->weight }}%</div>
                </th>
              @endforeach

              {{-- Proyek Mitra detail --}}
              @foreach(($indsByGroup['project_mitra'] ?? collect()) as $ind)
                <th style="min-width:180px;">
                  <div>{{ $ind->name }}</div>
                  <div class="small text-muted">{{ (int)$ind->weight }}%</div>
                </th>
              @endforeach
            </tr>
          </thead>

          <tbody>
          @foreach($members as $m)
            @php $r = $rekap[$m->id] ?? null; @endphp
            <tr data-mhs="{{ $m->id }}">
              <td>
                <div class="font-weight-bold">{{ $m->nama_mahasiswa }}</div>
                <div class="text-muted small">{{ $m->nim }}</div>
              </td>

              {{-- AP inputs --}}
              @foreach(($indsByGroup['ap'] ?? collect()) as $ind)
                @php
                  $cellId = "cell-{$m->id}-{$ind->code}";
                @endphp
                <td class="p-1">
                  <input type="number" min="0" max="100"
                         class="form-control form-control-sm score-input"
                         data-mhs="{{ $m->id }}"
                         data-code="{{ $ind->code }}"
                         id="{{ $cellId }}"
                         placeholder="-">
                  <div class="small text-muted js-status d-none">Menyimpan...</div>
                </td>
              @endforeach

              {{-- NA AP --}}
              <td id="na-ap-{{ $m->id }}" class="text-center font-weight-bold">
                {{ $r['ap'] ?? 'Belum dinilai' }}
              </td>

              {{-- Proyek Dosen inputs --}}
              @foreach(($indsByGroup['project_dosen'] ?? collect()) as $ind)
                <td class="p-1">
                  <input type="number" min="0" max="100"
                         class="form-control form-control-sm score-input"
                         data-mhs="{{ $m->id }}"
                         data-code="{{ $ind->code }}"
                         placeholder="-">
                  <div class="small text-muted js-status d-none">Menyimpan...</div>
                </td>
              @endforeach

              {{-- Proyek Mitra inputs --}}
              @foreach(($indsByGroup['project_mitra'] ?? collect()) as $ind)
                <td class="p-1">
                  <input type="number" min="0" max="100"
                         class="form-control form-control-sm score-input"
                         data-mhs="{{ $m->id }}"
                         data-code="{{ $ind->code }}"
                         placeholder="-">
                  <div class="small text-muted js-status d-none">Menyimpan...</div>
                </td>
              @endforeach

              {{-- NA Proyek --}}
              <td id="na-proyek-{{ $m->id }}" class="text-center font-weight-bold">
                {{ $r['proyek'] ?? 'Belum dinilai' }}
              </td>
            </tr>

            {{-- Baris Nilai Akhir --}}
            <tr class="table-active">
              <td colspan="{{ 1 + ($indsByGroup['ap']->count() ?? 0) + ($indsByGroup['project_dosen']->count() ?? 0) + ($indsByGroup['project_mitra']->count() ?? 0) + 1 }}">
                <div class="d-flex justify-content-between">
                  <div><strong>NA AP:</strong> <span id="sum-ap-{{ $m->id }}">{{ $r['ap'] ?? 0 }}</span></div>
                  <div><strong>NA Dosen:</strong> <span id="sum-dosen-{{ $m->id }}">{{ $r['dosen'] ?? 0 }}</span></div>
                  <div><strong>NA Mitra:</strong> <span id="sum-mitra-{{ $m->id }}">{{ $r['mitra'] ?? 0 }}</span></div>
                  <div><strong>NA Proyek:</strong> <span id="sum-proyek-{{ $m->id }}">{{ $r['proyek'] ?? 0 }}</span></div>
                  <div class="font-weight-bold">Nilai Akhir: <span id="sum-akhir-{{ $m->id }}">{{ $r['akhir'] ?? 0 }}</span></div>
                </div>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
  const URL_SAVE = @json(route('penilaian.score.save'));

  // simpan saat blur/enter
  document.querySelectorAll('.score-input').forEach(inp => {
    inp.addEventListener('change', async function(){
      const mhs   = this.dataset.mhs;
      const code  = this.dataset.code;
      let   score = parseInt(this.value || '0', 10);
      if (isNaN(score) || score<0) score = 0;
      if (score>100) score = 100;
      this.value = score;

      const st = this.parentElement.querySelector('.js-status');
      st && st.classList.remove('d-none');

      try{
        const res = await fetch(URL_SAVE, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': @json(csrf_token()),
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            sesi_id: @json($sesiId),
            mahasiswa_id: mhs,
            indicator_code: code,
            score: score
          })
        });
        const data = await res.json();
        if (data && data.success && data.rekap) {
          const r = data.rekap;
          // update NA row/summary
          ['ap','dosen','mitra','proyek','akhir'].forEach(k=>{
            const el1 = document.getElementById(`sum-${k}-${mhs}`);
            if (el1) el1.textContent = r[k];
          });
          const naAP = document.getElementById(`na-ap-${mhs}`);
          if (naAP) naAP.textContent = r.ap;
          const naProyek = document.getElementById(`na-proyek-${mhs}`);
          if (naProyek) naProyek.textContent = r.proyek;
        } else {
          alert('Gagal menyimpan nilai.');
        }
      }catch(e){
        alert('Gagal mengirim nilai.');
      }finally{
        st && st.classList.add('d-none');
      }
    });
  });
});
</script>
@endpush

