<?php

namespace App\Console\Commands;

use App\Models\Dosen;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GenerateDosenAccountsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dosen:generate-accounts {--password=123456 : Kata sandi default untuk akun dosen}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate akun login pengguna dosen dengan password default (123456) secara otomatis untuk master Bank Data Dosen';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $password = (string) $this->option('password') ?: '123456';
        $unlinked = Dosen::whereNull('user_id')->get();

        if ($unlinked->isEmpty()) {
            $this->info('Semua data dosen dalam Bank Data sudah memiliki akun pengguna aktif.');

            return self::SUCCESS;
        }

        $this->info("Menemukan {$unlinked->count()} data dosen yang belum memiliki akun pengguna. Memulai pembuatan akun otomatis...");

        $generated = 0;
        $rows = [];

        DB::transaction(function () use ($unlinked, $password, &$generated, &$rows) {
            foreach ($unlinked as $dosen) {
                $nip = $dosen->nip;
                $user = null;

                // 1. Coba cari user by NIP
                if (! empty($nip)) {
                    $user = User::where('username', $nip)->first();
                }

                // 2. Coba cari user by Nama dengan role dosen/evaluator
                if (! $user) {
                    $user = User::whereIn('role', ['dosen', 'evaluator'])
                        ->whereRaw('LOWER(nama_user) = ?', [strtolower(trim($dosen->nama_dosen))])
                        ->whereDoesntHave('dosen')
                        ->first();
                }

                // 3. Jika belum ada akun, buat akun baru
                if (! $user) {
                    $username = ! empty($nip)
                        ? $nip
                        : ('dosen_'.Str::slug(explode(' ', trim($dosen->nama_dosen))[0]).rand(100, 999));

                    if (User::where('username', $username)->exists()) {
                        $username = $username.'_'.rand(10, 99);
                    }

                    $user = User::create([
                        'nama_user' => $dosen->nama_dosen,
                        'email' => null,
                        'email_verified_at' => null,
                        'no_hp' => $dosen->no_hp ?: '-',
                        'username' => $username,
                        'password' => Hash::make($password),
                        'role' => 'dosen',
                        'remember_token' => Str::random(10),
                    ]);
                }

                $dosen->user_id = $user->id;
                $dosen->save();

                $generated++;
                $rows[] = [$dosen->nama_dosen, $dosen->nip ?? '-', $user->username, $user->email ?? '-', $password];
            }
        });

        $this->table(['Nama Dosen', 'NIP', 'Username', 'Email', 'Password'], $rows);
        $this->info("Berhasil membuat {$generated} akun dosen dengan kata sandi default: {$password}");

        return self::SUCCESS;
    }
}
