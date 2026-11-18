<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv as CsvReader;
use Throwable;

class DosenImportService
{
    /**
     * Import dosen from an Excel (.xlsx, .xls) or CSV file.
     *
     * @param  string  $path  File path on disk
     * @param  string|null  $extension  Original file extension
     * @return array<string, mixed> [stats => array, errors => array]
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
     * Load rows from spreadsheet or CSV file into a 2D array.
     */
    protected function loadRows(string $path, ?string $extension = null): array
    {
        $ext = strtolower($extension ?: pathinfo($path, PATHINFO_EXTENSION));

        try {
            $reader = IOFactory::createReaderForFile($path);

            if ($reader instanceof CsvReader || in_array($ext, ['csv', 'txt'], true)) {
                $delim = $this->detectCsvDelimiter($path);
                if ($reader instanceof CsvReader) {
                    $reader->setDelimiter($delim);
                    $reader->setInputEncoding('UTF-8');
                }
            }

            $spreadsheet = $reader->load($path);
            $sheet = $spreadsheet->getActiveSheet();

            return $sheet->toArray(null, true, false, false);
        } catch (Throwable $e) {
            throw new \Exception('Gagal membaca berkas: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * Process 2D array rows and import into database.
     */
    protected function processRows(array $rows): array
    {
        $createdDosen = 0;
        $updatedDosen = 0;
        $attached = 0;
        $generatedUsers = 0;
        $skipped = 0;
        $errors = [];

        // Check header row
        $firstRow = $rows[0] ?? [];
        $headerMap = $this->detectHeaderMap($firstRow);

        $dataRows = $rows;
        if (! empty($headerMap)) {
            array_shift($dataRows);
        }

        $rowNum = ! empty($headerMap) ? 2 : 1;

        foreach ($dataRows as $row) {
            // Skip empty rows
            $hasContent = false;
            foreach ($row as $cell) {
                if ($cell !== null && trim((string) $cell) !== '') {
                    $hasContent = true;
                    break;
                }
            }
            if (! $hasContent) {
                $rowNum++;

                continue;
            }

            $nipRaw = $this->getValue($row, $headerMap, 'nip', 0);
            $namaRaw = $this->getValue($row, $headerMap, 'nama', 1);
            $noHpRaw = $this->getValue($row, $headerMap, 'no_hp', 2);
            $keahlianRaw = $this->getValue($row, $headerMap, 'keahlian', 3);
            $emailRaw = $this->getValue($row, $headerMap, 'email', 4);

            $nip = $this->cleanNip($nipRaw);
            $nama = $this->cleanString($namaRaw, 255);
            $noHp = $this->cleanPhone($noHpRaw);
            $keahlian = $this->cleanString($keahlianRaw, 255);
            $email = ! empty($emailRaw) ? strtolower(trim((string) $emailRaw)) : null;

            if (empty($nama)) {
                $errors[] = "Baris {$rowNum}: Nama dosen wajib diisi.";
                $rowNum++;

                continue;
            }

            try {
                DB::transaction(function () use (
                    $nip,
                    $nama,
                    $noHp,
                    $keahlian,
                    $email,
                    &$createdDosen,
                    &$updatedDosen,
                    &$attached,
                    &$generatedUsers,
                    &$skipped
                ) {
                    // Match existing user (role dosen / evaluator / admin)
                    $user = null;
                    if (! empty($nip)) {
                        $user = User::whereIn('role', ['dosen', 'evaluator', 'admin'])
                            ->where('username', $nip)
                            ->first();
                    }
                    if (! $user && ! empty($email)) {
                        $user = User::where('email', $email)->first();
                    }
                    if (! $user) {
                        $user = User::whereIn('role', ['dosen', 'evaluator', 'admin'])
                            ->whereRaw('LOWER(nama_user) = ?', [strtolower($nama)])
                            ->first();
                    }

                    // Check existing Dosen record
                    $dosen = null;
                    if (! empty($nip)) {
                        $dosen = Dosen::where('nip', $nip)->first();
                    }
                    if (! $dosen) {
                        $dosen = Dosen::whereRaw('LOWER(nama_dosen) = ?', [strtolower($nama)])->first();
                    }

                    if ($dosen && $dosen->user_id && ! $user) {
                        $user = User::find($dosen->user_id);
                    }

                    // Auto-generate User account dengan password 123456 jika belum ada
                    if (! $user) {
                        $username = ! empty($nip)
                            ? $nip
                            : ('dosen_'.Str::slug(explode(' ', $nama)[0]).rand(100, 999));

                        if (User::where('username', $username)->exists()) {
                            $username = $username.'_'.rand(10, 99);
                        }

                        $userEmail = ! empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)
                            ? strtolower(trim($email))
                            : null;

                        if ($userEmail && User::where('email', $userEmail)->exists()) {
                            $userEmail = null;
                        }

                        $user = User::create([
                            'nama_user' => $nama,
                            'username' => $username,
                            'email' => $userEmail,
                            'email_verified_at' => $userEmail ? now() : null,
                            'no_hp' => $noHp ?: '-',
                            'password' => Hash::make('123456'),
                            'role' => 'dosen',
                            'remember_token' => Str::random(10),
                        ]);
                        $generatedUsers++;
                    }

                    if ($dosen) {
                        $changed = false;
                        if (! empty($nip) && $dosen->nip !== $nip) {
                            $dosen->nip = $nip;
                            $changed = true;
                        }
                        if ($dosen->nama_dosen !== $nama) {
                            $dosen->nama_dosen = $nama;
                            $changed = true;
                        }
                        if (! empty($noHp) && $dosen->no_hp !== $noHp) {
                            $dosen->no_hp = $noHp;
                            $changed = true;
                        }
                        if (! empty($keahlian) && $dosen->keahlian !== $keahlian) {
                            $dosen->keahlian = $keahlian;
                            $changed = true;
                        }
                        if (! $dosen->user_id && $user) {
                            $dosen->user_id = $user->id;
                            $changed = true;
                            $attached++;
                        }

                        if ($changed) {
                            $dosen->save();
                            $updatedDosen++;
                        } else {
                            $skipped++;
                        }
                    } else {
                        Dosen::create([
                            'nip' => $nip,
                            'nama_dosen' => $nama,
                            'no_hp' => $noHp,
                            'keahlian' => $keahlian,
                            'user_id' => $user?->id,
                        ]);
                        $createdDosen++;
                        if ($user) {
                            $attached++;
                        }
                    }

                    // Keep user contact updated if matched
                    if ($user && ! empty($noHp) && (empty($user->no_hp) || $user->no_hp === '-')) {
                        $user->no_hp = $noHp;
                        $user->save();
                    }
                });
            } catch (Throwable $e) {
                $errors[] = "Baris {$rowNum} ({$nama}): ".$e->getMessage();
            }

            $rowNum++;
        }

        return [
            'stats' => [
                'created_dosen' => $createdDosen,
                'updated_dosen' => $updatedDosen,
                'attached' => $attached,
                'generated_users' => $generatedUsers,
                'skipped' => $skipped,
            ],
            'errors' => $errors,
        ];
    }

    /**
     * Detect header column indexes.
     *
     * @return array<string, int>
     */
    protected function detectHeaderMap(array $firstRow): array
    {
        $map = [];
        foreach ($firstRow as $idx => $val) {
            $norm = strtolower(trim((string) $val));
            $norm = preg_replace('/[^a-z0-9]/', '', $norm);

            if (str_contains($norm, 'nip')) {
                $map['nip'] = $idx;
            } elseif (str_contains($norm, 'nama') || str_contains($norm, 'dosen')) {
                $map['nama'] = $idx;
            } elseif (str_contains($norm, 'hp') || str_contains($norm, 'telp') || str_contains($norm, 'wa') || str_contains($norm, 'telepon') || str_contains($norm, 'kontak')) {
                $map['no_hp'] = $idx;
            } elseif (str_contains($norm, 'ahli') || str_contains($norm, 'keahlian') || str_contains($norm, 'spesialisasi') || str_contains($norm, 'bidang')) {
                $map['keahlian'] = $idx;
            } elseif (str_contains($norm, 'email') || str_contains($norm, 'surel')) {
                $map['email'] = $idx;
            }
        }

        return $map;
    }

    protected function getValue(array $row, array $map, string $key, int $fallbackIndex): mixed
    {
        if (isset($map[$key]) && array_key_exists($map[$key], $row)) {
            return $row[$map[$key]];
        }

        return $row[$fallbackIndex] ?? null;
    }

    protected function cleanNip(mixed $val): ?string
    {
        if ($val === null) {
            return null;
        }
        $s = trim(preg_replace('/\s+/', '', (string) $val));
        if ($s === '' || strtoupper($s) === 'NULL' || $s === '-') {
            return null;
        }

        return mb_substr($s, 0, 50);
    }

    protected function cleanString(mixed $val, int $maxLength = 255): ?string
    {
        if ($val === null) {
            return null;
        }
        $s = trim(preg_replace('/\s+/', ' ', (string) $val));
        if ($s === '' || strtoupper($s) === 'NULL' || $s === '-') {
            return null;
        }

        return mb_substr($s, 0, $maxLength);
    }

    protected function cleanPhone(mixed $val): ?string
    {
        if ($val === null) {
            return null;
        }
        $s = trim((string) $val);
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
}
