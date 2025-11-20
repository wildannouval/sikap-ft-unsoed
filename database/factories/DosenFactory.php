<?php

namespace Database\Factories;

use App\Models\Dosen;
use App\Models\Jurusan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DosenFactory extends Factory
{
    protected $model = Dosen::class;

    public function definition(): array
    {
        $name = $this->faker->name();

        return [
            'user_id' => User::factory()->state(fn() => [
                'name'     => $name,
                'email'    => $this->faker->unique()->safeEmail(),
                'password' => 'password', // di-hash oleh casts User
            ]),
            'dosen_name'   => $name,
            'dosen_nip'    => $this->faker->unique()->numerify('1971#########'),
            'jurusan_id'   => Jurusan::query()->inRandomOrder()->value('id') ?? Jurusan::factory(),
            'is_komisi_kp' => $this->faker->boolean(30),
        ];
    }
}
