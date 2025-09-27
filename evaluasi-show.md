# Evaluasi � Show Page (Projects & Weekly Activities)

Tujuan dokumen ini: menjadi spesifikasi singkat/terstruktur untuk layar detail evaluasi (show) yang menampilkan dua area utama saja � Proyek dan Aktivitas Mingguan � beserta fungsionalitasnya. Silakan beri catatan/revisi langsung di file ini, lalu kita terapkan ke Blade/Controller.

## Target Cakupan
- Hanya dua section yang ditampilkan:
  - Proyek (board/list kartu)
  - Aktivitas Mingguan (board/list kartu)
- Semua fungsi yang sudah berjalan dipertahankan:
  - Drag & drop kartu dan kolom (Proyek)
  - Pencarian (Proyek dan Aktivitas)
  - Edit Proyek (modal)
  - Hapus Proyek (konfirmasi)
  - Penilaian Dosen/Mitra per proyek (SweetAlert)
  - Toggle per-section (collapse) + persist ke localStorage
- Hal lain (tabs ringkasan/rekap/AP/dll) tidak ditampilkan (di luar scope dokumen ini).

## Data Input (dari Controller ? Blade)
- `kelompok` (objek, minimal: `id`, `uuid`, `nama_kelompok`)
- `periode` (objek, minimal: `periode`)
- `sesi` (objek, minimal: `id`, opsional: `jadwal_mulai`, `jadwal_selesai`, `lokasi`, `status`, relasi `evaluator`)
- `proyekLists` (Collection daftar kolom proyek)
  - `id`, `name`, `position`
  - `cards[]` (list kartu proyek)
    - `id`, `uuid`, `title`, `description`, `progress`, `status`/`status_proyek`, `tanggal_mulai`, `tanggal_selesai`/`due_date`
    - opsional: `link_drive_proyek`/`drive_link`, `createdBy`, `updatedBy`, `created_at`, `updated_at`
- `aktivitasLists` (Collection daftar kolom aktivitas)
  - `id`, `title/name`, `status_evaluasi`
  - `cards[]` (list kartu aktivitas)
    - `id`, `description`, `tanggal_aktivitas`, opsional: `bukti_kegiatan`, `createdBy`, `updatedBy`, `created_at`, `updated_at`
- `cardGrades` (map penilaian per card proyek)
  - keyed by `card_id`, masing-masing berisi `['dosen'=>row|null, 'mitra'=>row|null]`
  - setiap row setidaknya punya `total` dan `nilai` (array)
- `settings` (opsional; digunakan di tempat lain � tidak wajib untuk scope ini)

Catatan keselamatan: Controller sudah dipasangi guard `Schema::hasTable(...)` untuk tabel evaluasi yang mungkin belum dibuat, sehingga halaman tetap render.

## Susunan Layout (Blade)
- Header: info kelompok, periode, evaluator, jadwal, status (ringkas)
- Section: Proyek
  - Header bar (judul + tombol Toggle) + pencarian (`#boardSearch`)
  - Konten: board (`#board`) berisi kolom (`.board-column`)
    - Head kolom: judul + badge jumlah kartu
    - List kartu (`.board-list`)
      - Card berisi: judul, tanggal (rentang), deskripsi singkat, progress bar, meta created/updated, dan sub-card skor Dosen/Mitra
      - Footer: status badge + tombol tindakan (Drive, Nilai Dosen, Nilai Mitra, Edit, Hapus)
- Section: Aktivitas Mingguan
  - Header bar (judul + tombol Toggle) + pencarian (`#actSearch`)
  - Konten: board (`#actBoard`) dengan kolom mingguan
    - Head kolom: judul + badge jumlah + (opsional) status_evaluasi
    - List kartu aktivitas: tanggal, deskripsi singkat, meta created/updated, tombol Bukti (jika ada)

## Interaksi / Fungsionalitas
- Toggle per-section
  - Bootstrap collapse pada `#sectionProjects` dan `#sectionAktivitas`
  - Persist state (open/close) di localStorage: `ui.sectionProjects`, `ui.sectionAktivitas`
- Drag & drop (Proyek)
  - Kartu: SortableJS pada setiap `.board-list`
  - Kolom: SortableJS pada `#board` dengan handle `.board-col-head`
- Pencarian
  - Proyek: filter kartu pada setiap kolom (auto-keep kolom; tidak collapse)
  - Aktivitas: filter kartu dan menyembunyikan kolom yang tidak ada match
- Edit Proyek (modal `#modalEditProyek`)
  - Prefill dari data-* pada tombol Edit
  - Submit AJAX ? update DOM (judul, status badge, progress bar)
- Hapus Proyek
  - Konfirmasi SweetAlert ? AJAX DELETE ? remove elemen kartu
- Penilaian per proyek (SweetAlert)
  - Dosen: 6 indikator (input angka) ? kirim objek `items`
  - Mitra: Kehadiran + Presentasi (input angka)
  - Setelah simpan, skor total di sub-card diperbarui (tanpa reload)

## Endpoint (route) yang dipakai
- Urutan kartu: `POST admin.evaluasi.project.reorder`
  - body: `card_id`, `to_list`, `position`, `_token`
- Urutan kolom: `POST admin.evaluasi.lists.reorder`
  - body: `list_ids[]`, `_token`
- Edit Proyek: `POST admin.evaluasi.project.update` (parameter `{card:uuid}`)
  - body: form edit + `_token`
- Hapus Proyek: `POST admin.evaluasi.project.destroy` (method spoof DELETE, `{card:uuid}`)
- Nilai Dosen per proyek: `POST admin.evaluasi.project.grade.dosen` (`{card:uuid}`)
  - body: `sesi_id`, `items{d_hasil,d_teknis,d_user,d_efisiensi,d_dokpro,d_inisiatif}`, `_token`
- Nilai Mitra per proyek: `POST admin.evaluasi.project.grade.mitra` (`{card:uuid}`)
  - body: `sesi_id`, `kehadiran`, `presentasi`, `_token`

## Ketergantungan Data/DB (dengan guard)
- Tabel yang dapat belum tersedia (sudah dijaga di Controller):
  - `evaluasi_absensi`, `evaluasi_sesi_indikator`, `evaluasi_nilai_detail`, `evaluasi_proyek_nilai`, `evaluasi_settings`
- Jika tabel tidak ada, fallback ke koleksi kosong / nilai default sehingga halaman tetap tampil.

## Gaya & Komponen UI
- Board (Proyek & Aktivitas) menggunakan kelas yang sama (`.board`, `.board-column`, `.board-list`, `.board-card`)
- Sub-card skor di Proyek menampilkan:
  - Dosen (ikon cap: `fa-user-graduate`) ? nilai total atau `�`
  - Mitra (ikon handshake: `fa-handshake`) ? nilai total atau `�`
- Meta created/updated diringkas dalam badge kecil.

## Checklist Kustomisasi (silakan beri tanda [x] dan komentar)
- [ ] Tampilkan evaluator & jadwal di header
- [ ] Tampilkan status sesi (badge)
- [ ] Tampilkan progress bar di kartu proyek
- [ ] Tampilkan skor Dosen/Mitra di dalam kartu proyek
- [ ] Perlu tombol quick-set progress/status proyek?
- [ ] Perlu tombol aksi lain (duplicate/move) pada proyek?
- [ ] Aktivitas: butuh drag & drop juga? (saat ini belum diaktifkan)
- [ ] Aktivitas: butuh aksi edit/hapus?
- [ ] Hilangkan/ubah Toggle per-section (collapse)?
- [ ] Hilangkan bagian lain di luar Proyek/Aktivitas (tab dll) dari Blade?

## Catatan Implementasi
- Controller `showKelompok` sudah:
  - Menjaga ketergantungan tabel via `Schema::hasTable`
  - Mengirim `proyekLists`, `aktivitasLists`, `cardGrades`, dll.
- Blade `resources/views/admin/evaluasi/show.blade.php`:
  - Sudah fokus pada dua section utama sesuai spec ini
  - Script sudah memuat SortableJS + SweetAlert2 dan handler yang diperlukan

## Revisi yang Diinginkan
Silakan tulis perubahan yang Anda inginkan (hapus/tambah/ubah) per bagian di bawah ini:

- Header:tambahkan nim, nama mahasiswa, kelas
- Proyek (board/kolom/kartu):
- Sub-card Skor (Dosen/Mitra):hanya tampilan dulu untuk koding masih menyesuaikan 
- Aktivitas Mingguan:
- Interaksi (drag & drop, search, toggle):
- Endpoint/API (payload/rute):
- Lainnya:



---

## Modules & Requirements (Checklist)

Gunakan daftar per modul berikut untuk menandai kebutuhan. Beri tanda [x] dan tambahkan komentar jika perlu.

### 1) Header Module
- [x] Tampilkan nama kelompok, periode, evaluator, jadwal, status
- [x] Format tanggal lokal (Indonesia) untuk jadwal
- [x] Tambahkan link/aksi lain (jadwal, export, dsb.)
- [ ] Sembunyikan elemen tertentu (sebutkan)

### 2) Projects Module (Board)
- Columns
  - [x] Tampilkan nama kolom + jumlah kartu (badge)
  - [x] Urutan kolom dapat di-drag (Sortable, handle: header)
- Cards (Field yang ditampilkan di kartu)
  - [x] Judul (wajib)
  - [x] Rentang tanggal (mulai � selesai/due) + overdue style
  - [x] Deskripsi (ringkas, limit N char)
  - [x] Progress bar (0�100%) + badge status (Proses/Selesai/Dibatalkan)
  - [x] Meta created/updated (user + waktu) dalam badge kecil
  - [x] Link Drive (opsional)
  - [x] Sub-card skor Dosen & Mitra dengan ikon dan fallback "�"
- Actions
  - [x] Nilai Dosen (SweetAlert, 6 indikator angka)
  - [x] Nilai Mitra (SweetAlert, Kehadiran/Presentasi angka)
  - [x] Edit Proyek (modal)
  - [x] Hapus Proyek (konfirmasi)
- Drag & Drop (Cards)
  - [x] Aktif (antar kolom dan dalam kolom)
  - [x] Endpoint reorder kartu (pastikan data yang dikirim sesuai)
- Search
  - [x] Filter kartu per kolom berdasarkan judul/deskripsi
  - [x] Kolom tetap terlihat (hanya kartu yang difilter)
- Empty State
  - [x] Teks/ikon saat tidak ada proyek

### 3) Edit Project Modal
- Fields & Validasi
  - [x] Title (required)
  - [x] Status (enum)
  - [x] Description (textarea)
  - [x] Progress (0�100)
  - [x] Labels (comma separated)
  - [x] Tanggal Mulai/Selesai (date)
  - [x] Skema, Nama Mitra, Kontak, Biaya (barang/jasa), Drive link, Kendala, Catatan
- Behavior
  - [x] Prefill dari data-* tombol
  - [x] Submit AJAX ? update DOM (judul/status/progress)
  - [x] Toast sukses / error

### 4) Grading Module (Per Proyek)
- Dosen
  - [x] 6 indikator (d_hasil, d_teknis, d_user, d_efisiensi, d_dokpro, d_inisiatif)
  - [x] berikan penilaian 6 indikatornya sesuai mahasiswanya tampilkan nim, nama : 
  - [ ] Input angka 0�100, prefill dari nilai tersimpan
  - [ ] Simpan ke endpoint `project.grade.dosen` (butuh sesi_id)
  - [ ] Update total pada kartu tanpa reload
- Mitra
  - [ ] Dua indikator (kehadiran, presentasi)
  - [ ] Input angka 0�100, prefill dari nilai tersimpan
  - [ ] Simpan ke endpoint `project.grade.mitra` (butuh sesi_id)
  - [ ] Update total pada kartu tanpa reload

### 5) Activities Module (Board)
- Columns
  - [ ] Tampilkan judul kolom + jumlah kartu (badge)
  - [ ] Tampilkan status_evaluasi (opsional)
  - [ ] (Opsional) Drag & drop list atau kartu aktivitas
- Cards
  - [ ] Tanggal aktivitas (chip)
  - [ ] Deskripsi ringkas (limit N char)
  - [ ] Meta created/updated (user + waktu) dalam badge kecil
  - [ ] Link Bukti (opsional)
- Search
  - [ ] Filter kartu; sembunyikan kolom yang tidak match
- Empty State
  - [ ] Teks/ikon saat tidak ada aktivitas

### 6) Section Toggle & Persistence
- [ ] Tombol Toggle untuk Proyek dan Aktivitas (Bootstrap collapse)
- [ ] Persist state di localStorage (`ui.sectionProjects`, `ui.sectionAktivitas`)
- [ ] State awal saat first visit (closed/open?)

### 7) Data/Backend Requirements
- Input Controller ? Blade (wajib)
  - [ ] kelompok, periode, sesi (+evaluator)
  - [ ] proyekLists (+cards), aktivitasLists (+cards)
  - [ ] cardGrades (map) untuk skor dosen/mitra per card
- Guard Tabel (Schema::hasTable)
  - [ ] evaluasi_absensi, evaluasi_sesi_indikator, evaluasi_nilai_detail, evaluasi_proyek_nilai, evaluasi_settings
- Defaults
  - [ ] Pakai config/evaluasi.php jika setting DB belum ada

### 8) Endpoint/API (konfirmasi payload)
- [ ] POST admin.evaluasi.project.reorder ? { card_id, to_list, position }
- [ ] POST admin.evaluasi.lists.reorder ? { list_ids[] }
- [ ] POST admin.evaluasi.project.update (card uuid) ? form edit
- [ ] POST admin.evaluasi.project.destroy (DELETE spoof)
- [ ] POST admin.evaluasi.project.grade.dosen ? { sesi_id, items{�} }
- [ ] POST admin.evaluasi.project.grade.mitra ? { sesi_id, kehadiran, presentasi }

### 9) UX & Visual
- [ ] Ikon (fontawesome) untuk skor & drive
- [ ] Badge & warna status
- [ ] Format tanggal lokal (id)
- [ ] Copy teks untuk empty state & toast

### 10) Akses & Otorisasi (opsional)
- [ ] Siapa yang boleh grading, edit, hapus
- [ ] State read-only ketika sesi status tertentu

### 11) QA Checklist
- [ ] Drag & drop update urutan benar (server-side & client-side)
- [ ] Grading tersimpan dan total ter-update di kartu
- [ ] Edit proyek memperbarui semua elemen terkait
- [ ] Pencarian bekerja pada semua kolom
- [ ] Toggle section persist setelah refresh
