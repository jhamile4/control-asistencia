<?php

namespace Database\Factories;

use App\Models\Asistencia;
use App\Models\Empleado;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Asistencia>
 */
class AsistenciaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'empleado_id' => Empleado::factory(),
            'fecha' => now()->toDateString(),
            'hora_entrada' => '08:55:00',
            'hora_salida' => null,
            'minutos_tardanza' => 0,
        ];
    }

    public function tarde(int $minutos): static
    {
        return $this->state(fn (array $attributes) => [
            'hora_entrada' => now()->setTime(9, $minutos)->format('H:i:s'),
            'minutos_tardanza' => $minutos,
        ]);
    }
}
