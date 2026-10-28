<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\Empleado;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Empleado>
 */
class EmpleadoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'area_id' => Area::factory(),
            'dni' => fake()->unique()->numerify('########'),
            'nombres' => fake()->firstName(),
            'apellidos' => fake()->lastName().' '.fake()->lastName(),
            // Correo armado con el nombre: lucia.quispe123@empresa.pe
            'email' => fn (array $empleado): string => Str::of($empleado['nombres'])->before(' ')
                ->append('.', Str::before($empleado['apellidos'], ' '), fake()->unique()->numerify('###'), '@empresa.pe')
                ->ascii()
                ->lower()
                ->value(),
            'hora_entrada' => '09:00:00',
            'activo' => true,
        ];
    }

    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => ['activo' => false]);
    }
}
