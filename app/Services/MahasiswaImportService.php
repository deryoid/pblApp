<?php

namespace App\Services;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MahasiswaImportService
{
    /**
     * Import users and students from CSV file.
     *
     * @param  string  $path  Path to the CSV file
     * @return array [stats, errors]
     */
    public function importFromCsv(string $path): array
    {
        $fh = fopen($path, 'r');
        if (! $fh) {
            throw new \Exception('Tidak dapat membaca file.');
        }

        // --- Deteksi delimiter ---
        $firstLine = fgets($fh);
        $delims = [',', ';', "\t"];
        $delim = ',';
        $bestCount = -1;
        foreach ($delims as $d) {
            $c = substr_count($firstLine, $d);
            if ($c > $bestCount) {
                $bestCount = $c;
                $delim = $d;
            }
        }

        // --- Deteksi enclosure ---
        $enclosures = ['"', "'"];
        $enc = '"';
        $encCountBest = -1;
        foreach ($enclosures as $e) {
            $cnt = substr_count($firstLine, $e);
            if ($cnt > $encCountBest) {
                $encCountBest = $cnt;
                $enc = $e;
            }
        }

        // Reset pointer & handle BOM UTF-8
        rewind($fh);
        $bom = "\xEF\xBB\xBF";
        $headPeek = fread($fh, 3);
        if ($headPeek !== $bom) {
            rewind($fh);
        }

        // --- Baca header ---
        $header = fgetcsv($fh, 0, $delim, $enc);
        $header = $header ? array_map(fn ($v) => strtolower(trim((string) ($v ?? ''))), $header) : [];
        $hasHeader = in_array('nim', $header, true)
                || in_array('nama', $header, true)
                || in_array('nama_mahasiswa', $header, true);

        if (! $hasHeader) {
            rewind($fh);
            if ($headPeek === $bom) {
                fseek($fh, 3);
            }
        }

        // --- Helper: paksa UTF-8 valid ---
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

        // --- Helper: normalisasi kutip & spasi ---
        $normalizeText = function (?string $s) use ($toUtf8): string {
            $s = $toUtf8($s);
            $s = strtr($s, ['“' => '"', '”' => '"', '‘' => "'", '’' => "'"]);
            $s = trim($s);
            $tmp = preg_replace('/\s+/u', ' ', $s);
            if ($tmp === null) {
                $tmp = preg_replace('/\s+/', ' ', $s);
                if ($tmp === null) {
                    $tmp = $s;
                }
            }

            return $tmp;
        };

        // --- Helper: cek null/kosong/hanya kutip ---
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

        $rowNum = $hasHeader ? 2 : 1;

        while (($row = fgetcsv($fh, 0, $delim, $enc)) !== false) {
            $nim = null;
            $nama = null;

            if ($hasHeader) {
                $idxNim = array_search('nim', $header, true);
                $idxNama = array_search('nama', $header, true);
                if ($idxNama === false) {
                    $idxNama = array_search('nama_mahasiswa', $header, true);
                }

                $nim = ($idxNim !== false && isset($row[$idxNim])) ? $row[$idxNim] : null;
                $nama = ($idxNama !== false && isset($row[$idxNama])) ? $row[$idxNama] : null;
            } else {
                $nim = $row[0] ?? null;
                $nama = $row[1] ?? null;
            }

            // Normalisasi
            $nim = strtoupper(preg_replace('/\s+/', '', (string) $toUtf8($nim)));
            $nama = $normalizeText($nama);

            // Validasi minimal
            if (
                $nim === '' ||
                $nama === '' ||
                strtoupper(trim($nama)) === 'NULL' ||
                $onlyQuotes($nama)
            ) {
                $errors[] = "Baris {$rowNum}: NIM/Nama tidak valid (kosong/NULL/hanya kutip/encoding rusak).";
                $rowNum++;

                continue;
            }

            try {
                DB::transaction(function () use ($nim, $nama, &$createdUsers, &$createdMhs, &$updatedMhs, &$attached, &$skipped, &$conflict) {
                    // Cari user yang sudah ada dengan username = NIM
                    $user = User::where('username', $nim)->first();

                    // Cari record di Bank Data Mahasiswa berdasarkan NIM
                    $mByNim = Mahasiswa::where('nim', $nim)->first();

                    if ($user) {
                        // Sesuaikan nama user dengan nama dari import jika ada perubahan
                        if ($user->nama_user !== $nama && $nama && strtoupper(trim($nama)) !== 'NULL') {
                            $user->nama_user = $nama;
                            $user->save();
                        }

                        if ($mByNim) {
                            $changed = false;
                            if ($mByNim->nama_mahasiswa !== $nama) {
                                $mByNim->nama_mahasiswa = $nama;
                                $changed = true;
                            }
                            if ($mByNim->user_id !== $user->id) {
                                $mByNim->user_id = $user->id;
                                $changed = true;
                                $attached++;
                            }
                            if ($changed) {
                                $mByNim->save();
                                $updatedMhs++;
                            } else {
                                $skipped++;
                            }
                        } else {
                            // Mahasiswa belum ada, tautkan langsung ke user yang ada
                            Mahasiswa::create([
                                'user_id' => $user->id,
                                'nim' => $nim,
                                'nama_mahasiswa' => $nama,
                                'semester' => 1,
                            ]);
                            $createdMhs++;
                            $attached++;
                        }
                    } else {
                        // User belum ada: simpan ke Bank Data Mahasiswa (user_id = null)
                        // Mahasiswa akan mengklaim/mendaftar mandiri via halaman pendaftaran dengan identifikasi NIM
                        if ($mByNim) {
                            if ($mByNim->nama_mahasiswa !== $nama) {
                                $mByNim->nama_mahasiswa = $nama;
                                $mByNim->save();
                                $updatedMhs++;
                            } else {
                                $skipped++;
                            }
                        } else {
                            Mahasiswa::create([
                                'user_id' => null,
                                'nim' => $nim,
                                'nama_mahasiswa' => $nama,
                                'semester' => 1,
                            ]);
                            $createdMhs++;
                        }
                    }
                });
            } catch (\Throwable $e) {
                $errors[] = "Baris {$rowNum}: ".$e->getMessage();
            }

            $rowNum++;
        }
        fclose($fh);

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
}
