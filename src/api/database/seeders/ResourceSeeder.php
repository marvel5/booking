<?php

namespace Database\Seeders;

use App\Models\Resource;
use Illuminate\Database\Seeder;

class ResourceSeeder extends Seeder
{
    public function run(): void
    {
        $apartments = [
            ['name' => 'Apartament 101', 'description' => 'Przestronny apartament z widokiem na ogród, 2 sypialnie.', 'capacity' => 4],
            ['name' => 'Apartament 102', 'description' => 'Przytulny apartament na parterze, idealne dla par.', 'capacity' => 2],
            ['name' => 'Apartament 201', 'description' => 'Luksusowy apartament z tarasem, 3 sypialnie.', 'capacity' => 6],
            ['name' => 'Apartament 202', 'description' => 'Apartament z aneksem kuchennym i widokiem na miasto.', 'capacity' => 2],
            ['name' => 'Apartament 301', 'description' => 'Penthouse na najwyższym piętrze, panoramiczny widok.', 'capacity' => 4],
        ];

        foreach ($apartments as $apartment) {
            Resource::create($apartment);
        }
    }
}
