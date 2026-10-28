<?php

namespace Database\Seeders;

use App\Enums\Rol;
use App\Models\Area;
use App\Models\Asistencia;
use App\Models\Empleado;
use App\Models\User;
use App\Services\AsistenciaService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Crea 2 usuarios de demostración, 5 áreas, 30 empleados y las asistencias
     * de los últimos 10 días hábiles. La contraseña sale de DEMO_PASSWORD en .env.
     */
    public function run(AsistenciaService $asistencias): void
    {
        $password = config('app.demo_password');

        $admin = User::factory()->create([
            'name' => 'Administradora RR.HH.',
            'email' => 'admin@asistencia.test',
            'password' => $password,
            'rol' => Rol::Admin,
        ]);

        User::factory()->create([
            'name' => 'Supervisor de Turno',
            'email' => 'supervisor@asistencia.test',
            'password' => $password,
            'rol' => Rol::Supervisor,
        ]);

        $areas = collect(['Recursos Humanos', 'Tecnología', 'Ventas', 'Contabilidad', 'Operaciones'])
            ->map(fn (string $nombre): Area => Area::create(['nombre' => $nombre]));

        $empleados = $areas->flatMap(fn (Area $area) => Empleado::factory()
            ->count(6)
            ->for($area)
            ->create(['hora_entrada' => $area->nombre === 'Operaciones' ? '08:00:00' : '09:00:00']));

        // Dos empleados inactivos para probar filtros y reglas
        $empleados->random(2)->each(fn (Empleado $e) => $e->update(['activo' => false]));

        $diasHabiles = collect(range(14, 1))
            ->map(fn (int $dias) => now()->subDays($dias))
            ->reject(fn ($dia) => $dia->isWeekend())
            ->take(-10);

        foreach ($diasHabiles as $dia) {
            foreach ($empleados->where('activo', true) as $empleado) {
                // 1 de cada 12 falta; el resto llega entre 20 min antes y 20 min después
                if (random_int(1, 12) === 1) {
                    continue;
                }

                [$hora, $minuto] = explode(':', $empleado->hora_entrada);
                $llegada = $dia->copy()->setTime((int) $hora, (int) $minuto)->addMinutes(random_int(-20, 20));
                $salida = $llegada->copy()->addHours(9)->addMinutes(random_int(0, 40));

                Asistencia::create([
                    'empleado_id' => $empleado->id,
                    'fecha' => $dia->toDateString(),
                    'hora_entrada' => $llegada->format('H:i:s'),
                    'hora_salida' => $salida->format('H:i:s'),
                    'minutos_tardanza' => $asistencias->calcularMinutosTardanza($empleado->hora_entrada, $llegada->format('H:i:s')),
                    'registrado_por' => $admin->id,
                ]);
            }
        }
    }
}
