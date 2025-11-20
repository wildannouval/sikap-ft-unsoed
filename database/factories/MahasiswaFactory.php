<?php

namespace Database\Factories;

use App\Models\Mahasiswa;
use App\Models\Jurusan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MahasiswaFactory extends Factory
{
    protected $model = Mahasiswa::class;

    public function definition(): array
    {
        $name = $this->faker->name();

        return [
            'user_id'                  => User::factory()->state([
                'name'     => $name,
                'email'    => $this->faker->unique()->safeEmail(),
                'password' => 'password',
            ]),
            'jurusan_id'               => Jurusan::query()->inRandomOrder()->value('id') ?? Jurusan::factory(),
            'mahasiswa_name'           => $name,
            'mahasiswa_nim'            => $this->faker->unique()->numerify('F1E0########'),
            'mahasiswa_tahun_angkatan' => $this->faker->numberBetween(2018, 2025),
        ];
    }
}
