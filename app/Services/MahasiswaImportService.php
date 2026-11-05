<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv as CsvReader;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

class MahasiswaImportService
{
    /**
     * Import users and students from an Excel (.xlsx, .xls) or CSV file.
     *
     * @param  string  $path  File path on disk
     * @param  string|null  $extension  Original file extension
     * @return array [stats => array, errors => array]
     */
    public function importFile(string $path, ?string $extension = null): array
    {
        $rows = $this->loadRows($path, $extension);

        if (empty($rows)) {
            throw new \Exception('File kosong atau tidak dapat dibaca.');
        }

        return $this->processRows($rows);
    }

    /**
     * Backward-compatible method for CSV import.
     */
    public function importFromCsv(string $path): array
    {
        return $this->importFile($path, 'csv');
    }

    /**
     * Load rows from spreadsheet or CSV file into a 2D array.
     */
    protected function loadRows(string $path, ?string $extension = null): array
    {
        $ext = strtolower($extension ?: pathinfo($path, PATHINFO_EXTENSION));

        // Use PhpSpreadsheet for Excel and CSV
        try {
            $reader = IOFactory::createReaderForFile($path);

            if ($reader instanceof CsvReader || in_array($ext, ['csv', 'txt'], true)) {
                $delim = $this->detectCsvDelimiter($path);
                $enclosure = $this->detectCsvEnclosure($path);
                if ($reader instanceof CsvReader) {
                    $reader->setDelimiter($delim);
                    $reader->setEnclosure($enclosure);
                    $reader->setInputEncoding('UTF-8');
                }
            }

            $spreadsheet = $reader->load($path);
            $sheet = $spreadsheet->getActiveSheet();

            // toArray(nullValue, calculateFormulas, formatData, returnCellRef)
            // formatData = false so we can inspect raw date values/numbers properly
            return $sheet->toArray(null, true, false, false);
        } catch (Throwable $e) {
            // Fallback manual CSV parser if PhpSpreadsheet reader encounters a streaming/BOM issue
            if (in_array($ext, ['csv', 'txt'], true) || str_contains(strtolower($e->getMessage()), 'csv')) {
                return $this->loadCsvManually($path);
            }

            throw new \Exception('Gagal membaca file: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * Fallback manual CSV loader.
     */
    protected function loadCsvManually(string $path): array
    {
        $fh = fopen($path, 'r');
        if (! $fh) {
            throw new \Exception('Tidak dapat membuka file CSV.');
        }

        $delim = $this->detectCsvDelimiter($path);
        $enc = $this->detectCsvEnclosure($path);

        $bom = "\xEF\xBB\xBF";
        $peek = fread($fh, 3);
        if ($peek !== $bom) {
            rewind($fh);
        }

        $rows = [];
        while (($row = fgetcsv($fh, 0, $delim, $enc)) !== false) {
            $rows[] = $row;
        }
        fclose($fh);

        return $rows;
    }

    /**
     * Detect CSV delimiter (comma, semicolon, tab).
     */
    protected function detectCsvDelimiter(string $path): string
    {
        $fh = @fopen($path, 'r');
        if (! $fh) {
            return ',';
        }

        $firstLine = fgets($fh) ?: '';
        fclose($fh);

        $delims = [',', ';', "\t"];
        $bestCount = -1;
        $bestDelim = ',';

        foreach ($delims as $d) {
            $count = substr_count($firstLine, $d);
            if ($count > $bestCount) {
                $bestCount = $count;
                $bestDelim = $d;
            }
        }

        return $bestDelim;
    }

    /**
     * Detect CSV quote enclosure.
     */
    protected function detectCsvEnclosure(string $path): string
    {
        $fh = @fopen($path, 'r');
        if (! $fh) {
            return '"';
        }

        $firstLine = fgets($fh) ?: '';
        fclose($fh);

        $enclosures = ['"', "'"];
        $bestCount = -1;
        $bestEnc = '"';

        foreach ($enclosures as $e) {
            $count = substr_count($firstLine, $e);
            if ($count > $bestCount) {
                $bestCount = $count;
                $bestEnc = $e;
            }
        }

        return $bestEnc;
    }

    /**
     * Process 2D array rows and persist student records.
     */
    protected function processRows(array $rows): array
    {
        $toUtf8 = function (?string $s): string {
            $s = (string) $s;
            if (! mb_detect_encoding($s, 'UTF-8', true)) {
                $s = @mb_convert_encoding($s, 'UTF-8', 'UTF-8, Windows-1252, ISO-8859-1');
                if ($s === false || $s === null) {
                    $s = preg_replace('/[^\x20-\x7E\x0A\x0D\x09]/', '', (string) $s) ?? '';
                }
            }

            return $s;
        };

        $normalizeText = function ($s) use ($toUtf8): string {
            if ($s === null) {
                return '';
            }
            $s = $toUtf8((string) $s);
            $s = strtr($s, ['“' => '"', '”' => '"', '‘' => "'", '’' => "'"]);
            $s = trim($s);
            $tmp = preg_replace('/\s+/u', ' ', $s);

            return $tmp ?? $s;
        };

        $onlyQuotes = function (?string $s) use ($toUtf8): bool {
            if ($s === null) {
                return true;
            }
            $t = trim($toUtf8($s));
            $t = trim($t, "\"'");

            return $t === '';
        };

        $createdUsers = 0;
        $createdMhs = 0;
        $updatedMhs = 0;
        $attached = 0;
        $skipped = 0;
        $conflict = 0;
        $errors = [];

        // Check first row for header
        $firstRow = $rows[0] ?? [];
        $headerMap = $this->mapHeaders($firstRow);
        $hasHeader = ! empty($headerMap);

        $dataRows = $hasHeader ? array_slice($rows, 1) : $rows;
        $rowNum = $hasHeader ? 2 : 1;

        foreach ($dataRows as $row) {
            // Check if entire row is empty
            if (empty(array_filter($row, fn ($v) => $v !== null && trim((string) $v) !== ''))) {
                $rowNum++;

                continue;
            }

            // Extract values according to header map or column position
            $nimRaw = $this->getValue($row, $headerMap, 'nim', 1, 0);
            $namaRaw = $this->getValue($row, $headerMap, 'nama', 2, 1);
            $angkatanRaw = $this->getValue($row, $headerMap, 'angkatan', 0);
            $nikRaw = $this->getValue($row, $headerMap, 'nik', 3);
            $jkRaw = $this->getValue($row, $headerMap, 'jk', 4);
            $namaIbuRaw = $this->getValue($row, $headerMap, 'nama_ibu', 5);
            $agamaRaw = $this->getValue($row, $headerMap, 'agama', 6);
            $kelasRaw = $this->getValue($row, $headerMap, 'kelas', 7);
            $tempatLahirRaw = $this->getValue($row, $headerMap, 'tempat_lahir', 8);
            $tanggalLahirRaw = $this->getValue($row, $headerMap, 'tanggal_lahir', 9);
            $alamatRaw = $this->getValue($row, $headerMap, 'alamat', 10);
            $noHpRaw = $this->getValue($row, $headerMap, 'no_hp', 11);
            $emailKampusRaw = $this->getValue($row, $headerMap, 'email_kampus', 12);

            // Clean & normalize core identity
            $nim = strtoupper(preg_replace('/\s+/', '', (string) $toUtf8($nimRaw)));
            $nama = $normalizeText($namaRaw);

            // Validation
            if (
                $nim === '' ||
                $nama === '' ||
                strtoupper(trim($nama)) === 'NULL' ||
                $onlyQuotes($nama)
            ) {
                $errors[] = "Baris {$rowNum}: NIM/Nama tidak valid (kosong, NULL, atau encoding rusak).";
                $rowNum++;

                continue;
            }

            // Clean other fields
            $angkatan = $this->cleanString($angkatanRaw, 10);
            $nik = preg_replace('/[^0-9]/', '', (string) $nikRaw) ?: null;
            $jk = $this->parseGender($jkRaw);
            $namaIbu = $this->cleanString($namaIbuRaw, 255);
            $agama = $this->cleanString($agamaRaw, 50);
            $kelasId = $this->findOrCreateKelasId($kelasRaw);
            $tempatLahir = $this->cleanString($tempatLahirRaw, 255);
            $tanggalLahir = $this->parseDate($tanggalLahirRaw);
            $alamat = $this->cleanString($alamatRaw, 1000);
            $noHp = $this->cleanPhone($noHpRaw);
            $emailKampus = $this->cleanEmail($emailKampusRaw);

            try {
                DB::transaction(function () use (
                    $nim,
                    $nama,
                    $angkatan,
                    $nik,
                    $jk,
                    $namaIbu,
                    $agama,
                    $kelasId,
                    $tempatLahir,
                    $tanggalLahir,
                    $alamat,
                    $noHp,
                    $emailKampus,
                    &$createdUsers,
                    &$createdMhs,
                    &$updatedMhs,
                    &$attached,
                    &$skipped,
                    &$conflict
                ) {
                    // Match existing user by username (NIM) or email
                    $user = User::where('username', $nim)
                        ->orWhere(function ($q) use ($emailKampus) {
                            if (! empty($emailKampus)) {
                                $q->where('email', $emailKampus);
                            }
                        })->first();

                    // Check existing Mahasiswa in bank data
                    $mByNim = Mahasiswa::where('nim', $nim)->first();

                    $importedAttributes = array_filter([
                        'nama_mahasiswa' => $nama,
                        'angkatan' => $angkatan,
                        'nik' => $nik,
                        'jk' => $jk,
                        'nama_ibu' => $namaIbu,
                        'agama' => $agama,
                        'kelas_id' => $kelasId,
                        'tempat_lahir' => $tempatLahir,
                        'tanggal_lahir' => $tanggalLahir,
                        'alamat' => $alamat,
                        'no_hp' => $noHp,
                        'email_kampus' => $emailKampus,
                    ], fn ($v) => $v !== null && $v !== '');

                    if ($user) {
                        // Keep user profile in sync
                        $userUpdated = false;
                        if ($nama && $user->nama_user !== $nama) {
                            $user->nama_user = $nama;
                            $userUpdated = true;
                        }
                        if ($noHp && (empty($user->no_hp) || $user->no_hp === '-')) {
                            $user->no_hp = $noHp;
                            $userUpdated = true;
                        }
                        if ($emailKampus && (empty($user->email) || str_contains($user->email, '@example.com'))) {
                            $emailTaken = User::where('email', $emailKampus)->where('id', '!=', $user->id)->exists();
                            if (! $emailTaken) {
                                $user->email = $emailKampus;
                                $userUpdated = true;
                            }
                        }
                        if ($userUpdated) {
                            $user->save();
                        }

                        if ($mByNim) {
                            $changed = false;
                            foreach ($importedAttributes as $key => $val) {
                                if ($mByNim->{$key} != $val) {
                                    $mByNim->{$key} = $val;
                                    $changed = true;
                                }
                            }

                            if ($mByNim->user_id !== $user->id) {
                                $mByNim->user_id = $user->id;
                                $changed = true;
                                $attached++;
                            }

                            if ($changed) {
                                $mByNim->checkAndUpdateProfileCompletion();
                                $mByNim->save();
                                $updatedMhs++;
                            } else {
                                $skipped++;
                            }
                        } else {
                            // Create Mahasiswa linked to user
                            $m = new Mahasiswa($importedAttributes);
                            $m->nim = $nim;
                            $m->user_id = $user->id;
                            $m->semester = 1;
                            $m->save();
                            $m->checkAndUpdateProfileCompletion();

                            $createdMhs++;
                            $attached++;
                        }
                    } else {
                        // User does not exist yet: store in Bank Data with user_id = null
                        if ($mByNim) {
                            $changed = false;
                            foreach ($importedAttributes as $key => $val) {
                                if ($mByNim->{$key} != $val) {
                                    $mByNim->{$key} = $val;
                                    $changed = true;
                                }
                            }

                            if ($changed) {
                                $mByNim->checkAndUpdateProfileCompletion();
                                $mByNim->save();
                                $updatedMhs++;
                            } else {
                                $skipped++;
                            }
                        } else {
                            $m = new Mahasiswa($importedAttributes);
                            $m->nim = $nim;
                            $m->user_id = null;
                            $m->semester = 1;
                            $m->save();
                            $m->checkAndUpdateProfileCompletion();

                            $createdMhs++;
                        }
                    }
                });
            } catch (Throwable $e) {
                $errors[] = "Baris {$rowNum}: ".$e->getMessage();
            }

            $rowNum++;
        }

        return [
            'stats' => [
                'created_users' => $createdUsers,
                'created_mhs' => $createdMhs,
                'updated_mhs' => $updatedMhs,
                'attached' => $attached,
                'skipped' => $skipped,
                'conflict' => $conflict,
            ],
            'errors' => $errors,
        ];
    }

    /**
     * Map header column positions flexibly.
     */
    protected function mapHeaders(array $firstRow): array
    {
        $map = [];
        $normalize = fn ($v) => strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string) $v));

        foreach ($firstRow as $idx => $col) {
            $n = $normalize($col);
            if ($n === '') {
                continue;
            }

            if (in_array($n, ['angkatan', 'tahunmasuk', 'batch', 'tahunangkatan'], true)) {
                $map['angkatan'] = $idx;
            } elseif (in_array($n, ['nim', 'nomorindukmahasiswa'], true)) {
                $map['nim'] = $idx;
            } elseif (in_array($n, ['namamahasiswa', 'nama', 'namalengkap', 'mahasiswa'], true)) {
                $map['nama'] = $idx;
            } elseif (in_array($n, ['nik', 'nonik', 'nomorindukkependudukan'], true)) {
                $map['nik'] = $idx;
            } elseif (in_array($n, ['jk', 'jeniskelamin', 'gender', 'lp', 'sex'], true)) {
                $map['jk'] = $idx;
            } elseif (in_array($n, ['namaibu', 'namaibukandung', 'ibukandung', 'ibu'], true)) {
                $map['nama_ibu'] = $idx;
            } elseif (in_array($n, ['agama', 'religion'], true)) {
                $map['agama'] = $idx;
            } elseif (in_array($n, ['kelas', 'namakelas', 'kelasid', 'rombel'], true)) {
                $map['kelas'] = $idx;
            } elseif (in_array($n, ['tempatlahir', 'tmplahir', 'kotalahir'], true)) {
                $map['tempat_lahir'] = $idx;
            } elseif (in_array($n, ['tanggallahir', 'tgllahir', 'tgl', 'birthdate'], true)) {
                $map['tanggal_lahir'] = $idx;
            } elseif (in_array($n, ['alamat', 'alamatlengkap', 'domisili', 'tempattinggal'], true)) {
                $map['alamat'] = $idx;
            } elseif (in_array($n, ['nohp', 'notelepon', 'nomorhp', 'telepon', 'hp', 'wa', 'nowa', 'kontak', 'whatsapp'], true)) {
                $map['no_hp'] = $idx;
            } elseif (in_array($n, ['emailkampus', 'email', 'emailmahasiswa', 'kampusemail', 'surel'], true)) {
                $map['email_kampus'] = $idx;
            }
        }

        // Must at least identify NIM or Nama to qualify as a valid header row
        if (! isset($map['nim']) && ! isset($map['nama'])) {
            return [];
        }

        return $map;
    }

    /**
     * Get value from row by mapped key, with fallback to positional indices.
     */
    protected function getValue(array $row, array $headerMap, string $key, ?int $primaryFallbackIndex = null, ?int $secondaryFallbackIndex = null): mixed
    {
        if (isset($headerMap[$key])) {
            $idx = $headerMap[$key];

            return $row[$idx] ?? null;
        }

        // If no header map at all, fall back to positional indices
        if (empty($headerMap)) {
            $count = count($row);

            // Legacy 2-column fallback (NIM, Nama)
            if ($count <= 3 && $secondaryFallbackIndex !== null) {
                return $row[$secondaryFallbackIndex] ?? null;
            }

            if ($primaryFallbackIndex !== null && isset($row[$primaryFallbackIndex])) {
                return $row[$primaryFallbackIndex];
            }
        }

        return null;
    }

    /**
     * Clean string and enforce max length.
     */
    protected function cleanString(mixed $val, int $maxLength = 255): ?string
    {
        if ($val === null) {
            return null;
        }
        $s = trim(preg_replace('/\s+/', ' ', (string) $val));
        if ($s === '' || strtoupper($s) === 'NULL') {
            return null;
        }

        return mb_substr($s, 0, $maxLength);
    }

    /**
     * Parse gender to 'L' or 'P'.
     */
    protected function parseGender(mixed $val): ?string
    {
        if ($val === null) {
            return null;
        }
        $v = strtoupper(trim((string) $val));
        if (str_starts_with($v, 'L') || str_contains($v, 'LAKI') || str_contains($v, 'PRIA')) {
            return 'L';
        }
        if (str_starts_with($v, 'P') || str_contains($v, 'PEREMPUAN') || str_contains($v, 'WANITA')) {
            return 'P';
        }

        return null;
    }

    /**
     * Parse phone number to digits.
     */
    protected function cleanPhone(mixed $val): ?string
    {
        if ($val === null) {
            return null;
        }
        $s = trim((string) $val);
        // Normalize scientific notation or float from Excel if any
        if (is_numeric($val) && str_contains(strtolower($s), 'e')) {
            $s = number_format((float) $val, 0, '', '');
        }
        $s = preg_replace('/[^\+0-9]/', '', $s);
        if ($s === '' || $s === '-') {
            return null;
        }

        // Normalize Indonesian mobile prefix when Excel drops leading zero
        if (str_starts_with($s, '628')) {
            $s = '08'.substr($s, 3);
        } elseif (str_starts_with($s, '8') && strlen($s) >= 9 && strlen($s) <= 13) {
            $s = '0'.$s;
        }

        return substr($s, 0, 30);
    }

    /**
     * Clean and validate email address.
     */
    protected function cleanEmail(mixed $val): ?string
    {
        if ($val === null) {
            return null;
        }
        $s = strtolower(trim((string) $val));
        if (filter_var($s, FILTER_VALIDATE_EMAIL)) {
            return $s;
        }

        return null;
    }

    /**
     * Find or auto-create Kelas by name.
     */
    protected function findOrCreateKelasId(mixed $val): ?int
    {
        if ($val === null) {
            return null;
        }
        $raw = trim((string) $val);
        if ($raw === '' || strtoupper($raw) === 'NULL') {
            return null;
        }

        // Direct match
        $k = Kelas::where('kelas', $raw)->first();
        if ($k) {
            return $k->id;
        }

        // Strip prefix "Kelas " or "Kls "
        $stripped = trim(preg_replace('/^(kelas|kls)\s*/i', '', $raw));
        if ($stripped !== '') {
            $k = Kelas::where('kelas', $stripped)->first();
            if ($k) {
                return $k->id;
            }
        }

        // Case-insensitive match
        $k = Kelas::whereRaw('LOWER(kelas) = ?', [strtolower($raw)])->first();
        if ($k) {
            return $k->id;
        }
        if ($stripped !== '') {
            $k = Kelas::whereRaw('LOWER(kelas) = ?', [strtolower($stripped)])->first();
            if ($k) {
                return $k->id;
            }
        }

        // Auto-create new Kelas entry
        try {
            $kelasName = (preg_match('/\d/', $stripped)) ? $stripped : $raw;

            $newK = Kelas::create([
                'kelas' => $kelasName,
                'semester' => 1,
            ]);

            return $newK->id;
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Parse date string or Excel serial number to Y-m-d format.
     */
    protected function parseDate(mixed $val): ?string
    {
        if ($val === null || $val === '') {
            return null;
        }

        // Excel date serial number (e.g., 38119)
        if (is_numeric($val) && $val > 1000 && $val < 60000) {
            try {
                return ExcelDate::excelToDateTimeObject((int) $val)->format('Y-m-d');
            } catch (Throwable $e) {
                // fall through
            }
        }

        $s = trim((string) $val);
        if ($s === '' || strtolower($s) === 'null') {
            return null;
        }

        // Standard date formats
        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d', 'j/n/Y', 'j-n-Y'];
        foreach ($formats as $fmt) {
            $dt = DateTime::createFromFormat($fmt, $s);
            $errors = DateTime::getLastErrors();
            if ($dt !== false && ($errors === false || ($errors['error_count'] === 0 && $errors['warning_count'] === 0))) {
                return $dt->format('Y-m-d');
            }
        }

        try {
            return Carbon::parse($s)->format('Y-m-d');
        } catch (Throwable $e) {
            return null;
        }
    }
}
