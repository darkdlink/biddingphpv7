<?php

namespace Database\Seeders;
//
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Usuário administrador
        DB::table('users')->insert([
            'name' => 'Administrador',
            'email' => 'admin@bidding.com',
            'password' => Hash::make('password'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Usuário gerente
        DB::table('users')->insert([
            'name' => 'Gerente',
            'email' => 'gerente@bidding.com',
            'password' => Hash::make('password'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Usuário analista
        DB::table('users')->insert([
            'name' => 'Analista',
            'email' => 'analista@bidding.com',
            'password' => Hash::make('password'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
