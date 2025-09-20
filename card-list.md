# Card List Preview + DB Revision Plan

Dokumen ini merangkum tampilan kartu (card-list) pada halaman Proyek Mahasiswa dan opsi revisi skema database agar skalabel dan mudah dikembangkan.

## Preview Tampilan Proyek
```
+---------------------------------------------+
| [Badge Label] [Badge Label]                 |
| Judul Proyek                                |
| Deskripsi singkat (maks 120 karakter)       |
| [███████████-------] 65%                    |
| ⏰ 24 Sep        [AN] [RK]     💬 3   📎 2 |
+---------------------------------------------+
```
Elemen yang ditampilkan per kartu:
- Label: array badge (opsional)
- Judul: wajib
- Deskripsi singkat: ringkasan dari `description`
- Progress: progress bar + persentase
- Due date: dengan indikator merah bila lewat tenggat (overdue)
- Anggota: avatar/initial (opsional)
- Komentar & Lampiran: badge angka

## Data Kartu (Client-side)
Contoh struktur data yang dipakai view saat ini (disusun per list):
```json
{
  "id": "uuid-card",
  "title": "Implementasi autentikasi & RBAC",
  "description": "Detail pekerjaan singkat...",
  "labels": ["Backend", "Prioritas"],
  "progress": 65,
  "due": "2025-09-24",
  "members": ["AN", "RK"],
  "comments": 3,
  "attachments": 2
}
```

## Target Skema (Revisi)
Tabel `project_cards` (direvisi sesuai kebutuhan):
- `id`, `uuid`
- `kelompok_id` (FK), `periode_id` (FK, nullable), `list_id` (FK → `project_lists`)
- `title`, `description`, `position`, `labels` (JSON, nullable)
- `nama_mitra` (string, nullable) — ambil nilai dari perusahaan pada kunjungan mitra (tanpa relasi, hanya simpan string)
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
  - Index tambahan: (`kelompok_id`,`periode_id`), (`due_date`), untuk filter cepat.

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
    // gunakan renameColumn jika ingin migrasi dari due_date → tanggal_selesai
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
- Label: ambil dari `labels` JSON (Opsi A) atau join `card_label` → `labels` (Opsi B).
- Nama Mitra: tampilkan di bawah judul (kecil/secondary).
- Skema PBL: badge kecil (misal warna berbeda per skema).
- Tanggal Mulai — Selesai: tampilkan rentang; merah jika `tanggal_selesai` lewat hari ini.
- Biaya Barang/Jasa: total kecil (opsional di card, lengkap di detail/modal).
- Komentar/Lampiran: gunakan count from related tables (atau cache ke kolom `_count`).
- Anggota: join `card_assignees` ke `users`/`mahasiswa`, atau pakai nama pada `members` (JSON) untuk tampilan cepat.
- Checklist: hitung progress checklist → dapat memengaruhi progress bar jika diinginkan.
- Kendala/Catatan: tampilkan ringkasan/tooltip; detail di modal/edit.

## Rekomendasi & Next Step
- Jika kebutuhan sederhana dan cepat: gunakan Opsi A (tetap JSON + counter) — perubahan minimal.
- Jika akan berkembang (komentar banyak, lampiran, penugasan anggota, filter berdasarkan label): gunakan Opsi B (normalisasi) — lebih fleksibel dan efisien.

Saya siap bantu:
- Generate migrasi sesuai opsi yang kamu pilih.
- Update model + relasi Eloquent.
- Tambahkan UI komentar/lampiran/assignee/checklist di kartu.
