<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\MataKuliah;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv as CsvReader;
use Throwable;

class MataKuliahImportService
{
    /**
     * Import Mata Kuliah from an Excel (.xlsx, .xls) or CSV file.
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
        $createdMatkul = 0;
        $updatedMatkul = 0;
        $syncedDosen = 0;
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

            $kodeRaw = $this->getValue($row, $headerMap, 'kode_matkul', 0);
            $namaRaw = $this->getValue($row, $headerMap, 'nama_matkul', 1);
            $semesterRaw = $this->getValue($row, $headerMap, 'semester', 2);
            $sksRaw = $this->getValue($row, $headerMap, 'sks', 3);
            $dosenRaw = $this->getValue($row, $headerMap, 'dosen_pengampu', 4);
            $deskripsiRaw = $this->getValue($row, $headerMap, 'deskripsi', 5);

            $kode = strtoupper(trim(preg_replace('/\s+/', '', (string) $kodeRaw)));
            $nama = trim(preg_replace('/\s+/', ' ', (string) $namaRaw));
            $semester = (int) preg_replace('/[^0-9]/', '', (string) $semesterRaw);
            $sks = (int) preg_replace('/[^0-9]/', '', (string) $sksRaw);
            $deskripsi = trim((string) $deskripsiRaw);

            if (empty($kode) || empty($nama)) {
                $errors[] = "Baris {$rowNum}: Kode MK dan Nama Mata Kuliah wajib diisi.";
                $rowNum++;

                continue;
            }

            if ($semester < 1 || $semester > 8) {
                $semester = 1;
            }
            if ($sks < 1 || $sks > 12) {
                $sks = 2;
            }

            try {
                DB::transaction(function () use (
                    $kode,
                    $nama,
                    $semester,
                    $sks,
                    $deskripsi,
                    $dosenRaw,
                    &$createdMatkul,
                    &$updatedMatkul,
                    &$syncedDosen,
                    &$skipped
                ) {
                    $matkul = MataKuliah::where('kode_matkul', $kode)->first();

                    if ($matkul) {
                        $changed = false;
                        if ($matkul->nama_matkul !== $nama) {
                            $matkul->nama_matkul = $nama;
                            $changed = true;
                        }
                        if ($matkul->semester !== $semester) {
                            $matkul->semester = $semester;
                            $changed = true;
                        }
                        if ($matkul->sks !== $sks) {
                            $matkul->sks = $sks;
                            $changed = true;
                        }
                        if ($deskripsi !== '' && $matkul->deskripsi !== $deskripsi) {
                            $matkul->deskripsi = $deskripsi;
                            $changed = true;
                        }

                        if ($changed) {
                            $matkul->save();
                            $updatedMatkul++;
                        } else {
                            $skipped++;
                        }
                    } else {
                        $matkul = MataKuliah::create([
                            'kode_matkul' => $kode,
                            'nama_matkul' => $nama,
                            'semester' => $semester,
                            'sks' => $sks,
                            'deskripsi' => $deskripsi ?: null,
                            'is_active' => true,
                        ]);
                        $createdMatkul++;
                    }

                    // Parse and link Dosen Pengampu
                    $dosenIds = $this->parseDosenIds($dosenRaw);
                    if (! empty($dosenIds)) {
                        $matkul->dosenPengampu()->syncWithoutDetaching($dosenIds);
                        $syncedDosen += count($dosenIds);
                    }
                });
            } catch (Throwable $e) {
                $errors[] = "Baris {$rowNum} ({$kode} - {$nama}): ".$e->getMessage();
            }

            $rowNum++;
        }

        return [
            'stats' => [
                'created_matkul' => $createdMatkul,
                'updated_matkul' => $updatedMatkul,
                'synced_dosen' => $syncedDosen,
                'skipped' => $skipped,
            ],
            'errors' => $errors,
        ];
    }

    /**
     * Parse lecturers from text into an array of User IDs.
     *
     * @return array<int>
     */
    protected function parseDosenIds(mixed $val): array
    {
        if ($val === null || trim((string) $val) === '') {
            return [];
        }

        $raw = (string) $val;
        // Split by semicolon, comma, or slash
        $tokens = preg_split('/[;,\|\n]+/', $raw);
        $userIds = [];

        foreach ($tokens as $token) {
            $token = trim($token);
            if ($token === '' || $token === '-') {
                continue;
            }

            // Clean numbers for NIP search
            $nipCandidate = preg_replace('/[^0-9]/', '', $token);

            $user = null;
            if (strlen($nipCandidate) >= 9) {
                $user = User::whereIn('role', ['dosen', 'evaluator', 'admin'])
                    ->where('username', $nipCandidate)
                    ->first();
            }

            if (! $user) {
                $user = User::whereIn('role', ['dosen', 'evaluator', 'admin'])
                    ->where(function ($q) use ($token) {
                        $q->whereRaw('LOWER(nama_user) = ?', [strtolower($token)])
                            ->orWhere('nama_user', 'like', "%{$token}%");
                    })->first();
            }

            if (! $user) {
                // Check in Dosen table to find linked user_id
                $dosen = Dosen::where('nip', $token)
                    ->orWhere('nama_dosen', 'like', "%{$token}%")
                    ->first();
                if ($dosen && $dosen->user_id) {
                    $user = User::find($dosen->user_id);
                }
            }

            if ($user && ! in_array($user->id, $userIds, true)) {
                $userIds[] = $user->id;
            }
        }

        return $userIds;
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

            if (str_contains($norm, 'kodemk') || str_contains($norm, 'kodematkul') || $norm === 'kode') {
                $map['kode_matkul'] = $idx;
            } elseif (str_contains($norm, 'namamk') || str_contains($norm, 'namamatkul') || str_contains($norm, 'matakuliah') || str_contains($norm, 'matkul') || $norm === 'nama') {
                $map['nama_matkul'] = $idx;
            } elseif (str_contains($norm, 'semester') || str_contains($norm, 'sem')) {
                $map['semester'] = $idx;
            } elseif (str_contains($norm, 'sks') || str_contains($norm, 'bobot')) {
                $map['sks'] = $idx;
            } elseif (str_contains($norm, 'dosen') || str_contains($norm, 'pengampu') || str_contains($norm, 'pengajar')) {
                $map['dosen_pengampu'] = $idx;
            } elseif (str_contains($norm, 'deskripsi') || str_contains($norm, 'keterangan') || str_contains($norm, 'silabus') || str_contains($norm, 'desc')) {
                $map['deskripsi'] = $idx;
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
