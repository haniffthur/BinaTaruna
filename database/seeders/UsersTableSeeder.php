<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            [
                'username' => 'admin',
                'nik' => '1234567890123456',
                'password' => Hash::make('admin123'),
                'name' => 'Admin Satu',
                'alamat' => 'Jl. Merdeka No. 1',
                'no_telp' => '081234567890',
                'jenis_kelamin' => 'laki-laki',
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'petugas',
                'nik' => '6543210987654321',
                'password' => Hash::make('petugas123'),
                'name' => 'Petugas Satu',
                'alamat' => 'Jl. Kemerdekaan No. 2',
                'no_telp' => '089876543210',
                'jenis_kelamin' => 'perempuan',
                'role' => 'petugas',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
