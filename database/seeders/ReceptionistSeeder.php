<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReceptionistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'name' => 'Admin Réception',
            'email' => 'reception@clinique.sn',
            'password' => bcrypt('password123'),
            'role' => 'receptionist',
            'phone' => '770000000'
        ]);
    }
}
