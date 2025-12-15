<?php

namespace Database\Factories;

use App\Models\KpConsultation;
use Illuminate\Database\Eloquent\Factories\Factory;

class KpConsultationFactory extends Factory
{
    protected $model = KpConsultation::class;

    public function definition(): array
    {
        return [
            'konsultasi_dengan' => 'Dosen Pembimbing',
            'tanggal_konsultasi' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'topik_konsultasi' => $this->faker->sentence(),
            'hasil_konsultasi' => $this->faker->paragraph(),
        ];
    }

    public function verified()
    {
        return $this->state(fn(array $attributes) => [
            'verified_at' => now(),
        ]);
    }
}
