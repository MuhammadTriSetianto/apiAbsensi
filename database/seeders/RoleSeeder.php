<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class RoleSeeder extends Seeder
{

    public function run()
    {
        DB::table('roles')->insert([
            [
                'nama_role' => 'Admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_role' => 'Projek Manager',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_role' => 'Pekerja',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
