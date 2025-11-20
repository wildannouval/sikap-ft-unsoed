<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class JurusanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nama_jurusan' => fake()->unique()->randomElement([
                'Teknik Informatika',
                'Teknik Elektro',
                'Teknik Mesin',
                'Teknik Sipil',
                'Teknik Industri',
                'Teknik Komputer',
                'Teknik Geologi',
            ]),
        ];
    }
}
