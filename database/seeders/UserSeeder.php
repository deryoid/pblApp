<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Jalankan database seeder.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'nama_user'        => 'Dery Yuswanto Jaya',
                'email'            => 'deryyuswantojaya@politala.ac.id',
                'email_verified_at'=> now(),
                'no_hp'            => '081234567890',
                'username'         => 'admin',
                'password'         => Hash::make('123456'), // password default
                'role'             => 'admin',
                'remember_token'   => Str::random(10),
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'nama_user'        => 'Eka Wahyu Sholeha',
                'email'            => 'ekawahyus@politala.ac.id',
                'email_verified_at'=> now(),
                'no_hp'            => '08123123123',
                'username'         => 'eka',
                'password'         => Hash::make('123456'), // password default
                'role'             => 'evaluator', // bisa diganti 'mahasiswa' kalau perlu
                'remember_token'   => Str::random(10),
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'nama_user'        => 'Dewi Indra Anggraeni',
                'email'            => 'dewi.indra.anggraeni@politala.ac.id',
                'email_verified_at'=> now(),
                'no_hp'            => '08123123123',
                'username'         => 'dewi',
                'password'         => Hash::make('123456'), // password default
                'role'             => 'evaluator', // bisa diganti 'mahasiswa' kalau perlu
                'remember_token'   => Str::random(10),
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
        ]);
    }
}
