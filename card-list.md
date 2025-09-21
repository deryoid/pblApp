# Card List Preview + DB Revision Plan

Dokumen ini merangkum tampilan kartu (card-list) pada halaman Proyek Mahasiswa dan opsi revisi skema database agar skalabel dan mudah dikembangkan.

## Preview Tampilan Proyek
```
+---------------------------------------------+
| [Backend] [Prioritas]          [PBL x TeFa] |
| Judul Proyek                    [Proses]    |
| PT Satu Maju (Whatsapp +62-812-0000-0000)   |
| Deskripsi singkat (maks 140 karakter)       |
| [###########-----------] 65%                 |
| 20 Sep – 24 Sep                   [GDrive]  |
| AN  RK      💬 3                            |
+---------------------------------------------+
```
Elemen yang ditampilkan per kartu:
- Label: array badge (opsional)
- Skema: badge kecil di baris label
- Judul + Status: badge status di kanan judul
- Mitra: nama mitra di bawah judul (opsional)
- Kontak Mitra: nomor WhatsApp/email PIC (opsional)
- Deskripsi: ringkasan dari `description`
- Progress: progress bar + persentase
- Tanggal: rentang `tanggal_mulai – tanggal_selesai` (merah jika overdue)
- Drive: tombol/link ke `link_drive_proyek` (opsional)
- Anggota: avatar/initial (opsional)
- Komentar: badge angka (Lampiran disembunyikan di kartu)

## Data Kartu (Client-side)
Contoh struktur data yang dipakai view saat ini (disusun per list) — Opsi A:
```json
{
  "id": "uuid-card",
  "title": "Implementasi autentikasi & RBAC",
  "description": "Detail pekerjaan singkat...",
  "labels": ["Backend", "Prioritas"],
  "skema_pbl": "Penelitian",
  "status_proyek": "Proses",
  "nama_mitra": "PT Satu Maju",
  "kontak_mitra": "+62-812-0000-0000",
  "tanggal_mulai": "2025-09-20",
  "tanggal_selesai": "2025-09-24",
  "progress": 65,
  "link_drive_proyek": "https://drive.google.com/xxx",
  "members": ["Andi Nugraha", "Rika Kusuma"],
  "comments": 3,
  
}
```

## Target Skema (Revisi)
Tabel `project_cards` (direvisi sesuai kebutuhan):
- `id`, `uuid`
- `kelompok_id` (FK), `periode_id` (FK, nullable), `list_id` (FK -> `project_lists`)
- `title`, `description`, `position`, `labels` (JSON, nullable)
- `nama_mitra` (string, nullable) — ambil dari perusahaan pada kunjungan mitra (tanpa relasi, hanya simpan string)
- `kontak_mitra` (JSON/string, nullable) - telepon/email PIC mitra
- `skema_pbl` (enum/string): Penelitian | Pengabdian | Lomba | "PBL x TeFa"
- `tanggal_mulai` (date, nullable)
- `tanggal_selesai` (date, nullable) — dipakai indikator overdue
- `biaya_barang` (decimal(15,2), default 0)
- `biaya_jasa`   (decimal(15,2), default 0)
- `progress` (tinyint 0..100, default 0)
- `kendala` (text, nullable)
- `catatan` (text, nullable)
- `status_proyek` (enum/string): Proses | Dibatalkan | Selesai (default Proses)
- `link_drive_proyek` (string, nullable)
- `members` (JSON, nullable) — array nama user yang membuat/mengupdate
- `created_at` (alias: `tanggal_dibuat`), `updated_at` (alias: `tanggal_diupdate`)

Index yang disarankan:
- (`kelompok_id`,`periode_id`)
- (`list_id`,`position`)
- (`status_proyek`), (`tanggal_selesai`) untuk filter cepat

Tabel `project_lists` (tetap):
- `id`, `uuid`, `kelompok_id` (FK), `periode_id` (FK), `name`, `position`, timestamps

## Opsi Revisi Skema
### Opsi A — Minimal (tetap JSON + counter)
- Pertahankan `labels` sebagai JSON pada `project_cards`.
- Pertahankan `comments_count` & `attachments_count` sebagai counter (diisi oleh aplikasi saat CRUD).
- Tambahkan bila perlu:
  - `archived` (bool) pada `project_cards` untuk arsip kartu.
  - Index tambahan: (`kelompok_id`,`periode_id`), (`tanggal_selesai`), untuk filter cepat.

Kapan cocok: cepat, sederhana, data relatif kecil, tidak butuh relasi kompleks label/anggota.

### Opsi B — Normalisasi (disarankan untuk skala lebih besar)
Tambahkan tabel-tabel pendukung agar fleksibel:
- `card_labels` (pivot labels per kartu) atau tabel `labels` + pivot `card_label` jika ingin master label.
- `card_comments` (komentar per kartu)
- `card_attachments` (lampiran per kartu)
- `card_assignees` (penugasan anggota ke kartu)
- (Opsional) `card_checklists` + `card_checklist_items` (checklist per kartu)

#### Contoh Migrasi (Laravel) — Skeleton
```php
// Alter project_cards sesuai skema revisi
Schema::table('project_cards', function (Blueprint $t) {
    if (!Schema::hasColumn('project_cards', 'nama_mitra'))          $t->string('nama_mitra')->nullable()->after('labels');
    if (!Schema::hasColumn('project_cards', 'skema_pbl'))           $t->string('skema_pbl', 50)->nullable()->after('nama_mitra');
    if (!Schema::hasColumn('project_cards', 'tanggal_mulai'))       $t->date('tanggal_mulai')->nullable()->after('skema_pbl');
    // gunakan renameColumn jika ingin migrasi dari due_date -> tanggal_selesai
    if (Schema::hasColumn('project_cards', 'due_date') && !Schema::hasColumn('project_cards', 'tanggal_selesai')) {
        $t->date('tanggal_selesai')->nullable()->after('tanggal_mulai');
    } elseif (!Schema::hasColumn('project_cards', 'tanggal_selesai')) {
        $t->date('tanggal_selesai')->nullable()->after('tanggal_mulai');
    }
    if (!Schema::hasColumn('project_cards', 'biaya_barang'))        $t->decimal('biaya_barang', 15, 2)->default(0)->after('tanggal_selesai');
    if (!Schema::hasColumn('project_cards', 'biaya_jasa'))          $t->decimal('biaya_jasa', 15, 2)->default(0)->after('biaya_barang');
    if (!Schema::hasColumn('project_cards', 'kendala'))             $t->text('kendala')->nullable()->after('progress');
    if (!Schema::hasColumn('project_cards', 'catatan'))             $t->text('catatan')->nullable()->after('kendala');
    if (!Schema::hasColumn('project_cards', 'status_proyek'))       $t->string('status_proyek', 20)->default('Proses')->after('catatan');
    if (!Schema::hasColumn('project_cards', 'link_drive_proyek'))   $t->string('link_drive_proyek')->nullable()->after('status_proyek');
    if (!Schema::hasColumn('project_cards', 'members'))             $t->json('members')->nullable()->after('link_drive_proyek');
    // index
    $t->index(['kelompok_id','periode_id']);
    $t->index(['list_id','position']);
    $t->index('status_proyek');
    $t->index('tanggal_selesai');
});
// 1) Labels master (opsional), jika ingin daftar label reusable
Schema::create('labels', function (Blueprint $t) {
    $t->id();
    $t->string('name');           // e.g. Backend
    $t->string('color')->nullable();
    $t->timestamps();
});

// 2) Pivot card_label
Schema::create('card_label', function (Blueprint $t) {
    $t->id();
    $t->foreignId('card_id')->constrained('project_cards')->cascadeOnDelete();
    $t->foreignId('label_id')->constrained('labels')->cascadeOnDelete();
    $t->unique(['card_id','label_id']);
});

// 3) Comments
Schema::create('card_comments', function (Blueprint $t) {
    $t->id();
    $t->foreignId('card_id')->constrained('project_cards')->cascadeOnDelete();
    $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $t->text('body');
    $t->timestamps();
});

// 4) Attachments (simpan path ke storage)
Schema::create('card_attachments', function (Blueprint $t) {
    $t->id();
    $t->foreignId('card_id')->constrained('project_cards')->cascadeOnDelete();
    $t->string('filename');
    $t->string('mime', 100)->nullable();
    $t->unsignedBigInteger('size')->nullable();
    $t->string('path');          // storage path
    $t->timestamps();
});

// 5) Assignees (gunakan user_id; bisa juga mahasiswa_id jika spesifik)
Schema::create('card_assignees', function (Blueprint $t) {
    $t->id();
    $t->foreignId('card_id')->constrained('project_cards')->cascadeOnDelete();
    $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $t->unique(['card_id','user_id']);
    $t->timestamps();
});

// 6) Checklist (opsional)
Schema::create('card_checklists', function (Blueprint $t) {
    $t->id();
    $t->foreignId('card_id')->constrained('project_cards')->cascadeOnDelete();
    $t->string('title');
    $t->timestamps();
});

Schema::create('card_checklist_items', function (Blueprint $t) {
    $t->id();
    $t->foreignId('checklist_id')->constrained('card_checklists')->cascadeOnDelete();
    $t->string('text');
    $t->boolean('done')->default(false);
    $t->unsignedInteger('position')->default(0);
    $t->timestamps();
});
```

#### Perubahan pada `project_cards` (jika pilih Opsi B)
- Opsional: hapus `labels` JSON jika migrasi ke tabel label.
- Opsional: hapus `comments_count` & `attachments_count` (atau tetap sebagai cached counter + trigger update di app).
- Tambah/pertahankan index (`kelompok_id`,`periode_id`), (`list_id`,`position`), (`status_proyek`), (`tanggal_selesai`).

## Pemetaan ke UI
- Label: dari `labels` JSON (Opsi A) atau join `card_label` + `labels` (Opsi B).
- Skema PBL: badge kecil di sisi kanan atas baris label.
- Status Proyek: badge di kanan judul (Proses/Dibatalkan/Selesai).
- Nama Mitra: teks kecil di bawah judul (secondary).
- Kontak Mitra: telepon/email kecil setelah nama mitra.
- Deskripsi: ringkas menggunakan `Str::limit(...)` (2–3 baris via CSS clamp).
- Progress: progress bar kecil + persentase.
- Tanggal Mulai – Selesai: tampil rentang; merah (overdue) bila `tanggal_selesai` < hari ini dan status ≠ Selesai.
- Biaya: disembunyikan dari kartu (opsional di modal/detail).
- Drive: tombol/link ke `link_drive_proyek` (ikon Google Drive) bila ada.
- Komentar: gunakan kolom counter (`comments_count`).
- Lampiran: disembunyikan dari kartu (kolom counter tetap boleh ada di DB).
- Anggota: dari `members` (JSON) — string nama diringkas jadi inisial; jika sudah inisial ≤3 huruf uppercase dipakai apa adanya.
- Kendala/Catatan: tidak ditampilkan di kartu; tersedia di form tambah/ubah (modal).
- Checklist: belum di UI saat ini (opsional untuk Opsi B).

## Catatan Kompatibilitas (due_date → tanggal_selesai)
- Skema lama menggunakan `due_date` (datetime). Skema revisi memakai `tanggal_selesai` (date).
- Migrasi menambahkan `tanggal_selesai` dan (bila memungkinkan) menyalin nilai dari `due_date` ke `tanggal_selesai`.
- View menampilkan rentang tanggal dan fallback ke `due_date` jika `tanggal_selesai` masih null.
- Endpoint store/update menjaga kompatibilitas: jika hanya salah satu diisi, nilai tersebut diisi ke keduanya untuk transisi mulus.
- Rekomendasi: setelah data konsisten, rencanakan penghapusan `due_date` agar skema bersih.

Tambahan kompatibilitas tampilan:
- Attachments: counter tetap ada pada data/DB namun disembunyikan dari kartu.
- Biaya: tersimpan di DB, namun disembunyikan dari kartu (boleh ditampilkan di modal/detail atau laporan bila dibutuhkan).

## Rekomendasi & Next Step
- Jika kebutuhan sederhana dan cepat: gunakan Opsi A (tetap JSON + counter) — perubahan minimal.
- Jika akan berkembang (komentar banyak, lampiran, penugasan anggota, filter berdasarkan label): gunakan Opsi B (normalisasi) — lebih fleksibel dan efisien.

Saya siap bantu:
- Generate migrasi sesuai opsi yang kamu pilih.
- Update model + relasi Eloquent.
- Tambahkan UI komentar/lampiran/assignee/checklist di kartu.
