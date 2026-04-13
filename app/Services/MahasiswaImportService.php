<?php

namespace App\Services;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MahasiswaImportService
{
    /**
     * Import users and students from CSV file.
     *
     * @param string $path Path to the CSV file
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
                    // USER
                    $user = User::where('username', $nim)->first();

                    if (! $user) {
                        $safeNama = (string) $nama;
                        if (
                            $safeNama === '' ||
                            strtoupper(trim($safeNama)) === 'NULL' ||
                            trim($safeNama, "\"' \t\n\r\0\x0B") === ''
                        ) {
                            $safeNama = 'Mahasiswa '.$nim;
                        }

                        $email = strtolower($nim.'@politala.ac.id');
                        if (User::where('email', $email)->exists()) {
                            $email = strtolower($nim.'+'.Str::random(4).'@politala.ac.id');
                        }

                        $user = User::create([
                            'nama_user' => $safeNama,
                            'email' => $email,
                            'email_verified_at' => now(),
                            'no_hp' => '-',
                            'username' => $nim,
                            'password' => Hash::make($nim),
                            'role' => 'mahasiswa',
                            'remember_token' => Str::random(10),
                        ]);
                        $createdUsers++;
                    } else {
                        if ($user->nama_user === null || trim($user->nama_user) === '' || strtoupper(trim($user->nama_user)) === 'NULL') {
                            $user->nama_user = ($nama && strtoupper(trim($nama)) !== 'NULL') ? $nama : ('Mahasiswa '.$nim);
                            $user->save();
                        } elseif ($user->nama_user !== $nama && $nama && strtoupper(trim($nama)) !== 'NULL') {
                            $user->nama_user = $nama;
                            $user->save();
                        }
                    }

                    // MAHASISWA
                    $m = Mahasiswa::where('user_id', $user->id)->first();
                    if ($m) {
                        $changed = false;
                        if ($m->nim !== $nim) {
                            $dupe = Mahasiswa::where('nim', $nim)->where('id', '!=', $m->id)->exists();
                            if ($dupe) {
                                $conflict++;
                            } else {
                                $m->nim = $nim;
                                $changed = true;
                            }
                        }
                        if ($m->nama_mahasiswa !== $nama) {
                            $m->nama_mahasiswa = $nama;
                            $changed = true;
                        }
                        if ($changed) {
                            $m->save();
                            $updatedMhs++;
                        } else {
                            $skipped++;
                        }
                        return;
                    }

                    $mByNim = Mahasiswa::where('nim', $nim)->first();
                    if ($mByNim) {
                        if ($mByNim->user_id && $mByNim->user_id !== $user->id) {
                            $conflict++;
                        } else {
                            $mByNim->user_id = $user->id;
                            if ($mByNim->nama_mahasiswa !== $nama) {
                                $mByNim->nama_mahasiswa = $nama;
                            }
                            $mByNim->save();
                            $attached++;
                        }
                        return;
                    }

                    Mahasiswa::create([
                        'user_id' => $user->id,
                        'nim' => $nim,
                        'nama_mahasiswa' => $nama,
                    ]);
                    $createdMhs++;
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
            'errors' => $errors
        ];
    }
}
