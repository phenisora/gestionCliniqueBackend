<?php

namespace Database\Seeders;

use App\Models\Speciality;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SpecialitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 10; $i++) {
            Speciality::create([
                'name' => fake()->unique()->word(), // Génère un mot unique 
                'description' => fake()->sentence(10), // Génère une phrase de 10 mots
            ]);
        }
    }
}
