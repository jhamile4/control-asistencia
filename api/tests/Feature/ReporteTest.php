<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Asistencia;
use App\Models\Empleado;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReporteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(CarbonImmutable::parse('2026-10-28 12:00:00', 'America/Lima'));
        Sanctum::actingAs(User::factory()->create());
    }

    public function test_tardanzas_por_area_incluye_areas_sin_asistencias(): void
    {
        $ventas = Area::factory()->create(['nombre' => 'Ventas']);
        Area::factory()->create(['nombre' => 'Contabilidad']);
        $rosa = Empleado::factory()->for($ventas)->create(['nombres' => 'Rosa', 'apellidos' => 'Huamán']);

        Asistencia::factory()->for($rosa)->tarde(20)->create(['fecha' => '2026-10-26']);
        Asistencia::factory()->for($rosa)->tarde(15)->create(['fecha' => '2026-10-27']);
        Asistencia::factory()->for($rosa)->create(['fecha' => '2026-10-28']);
        // Fuera del rango: no debe contarse
        Asistencia::factory()->for($rosa)->tarde(40)->create(['fecha' => '2026-09-30']);

        $this->getJson('/api/v1/reportes/tardanzas?desde=2026-10-01&hasta=2026-10-28')
            ->assertOk()
            ->assertJsonPath('por_area.0.area', 'Ventas')
            ->assertJsonPath('por_area.0.asistencias', 3)
            ->assertJsonPath('por_area.0.tardanzas', 2)
            ->assertJsonPath('por_area.0.minutos_tardanza', 35)
            ->assertJsonPath('por_area.0.porcentaje_tardanza', 66.7)
            ->assertJsonPath('por_area.1.area', 'Contabilidad')
            ->assertJsonPath('por_area.1.asistencias', 0)
            ->assertJsonPath('mas_tardanzas.0.empleado', 'Rosa Huamán')
            ->assertJsonPath('mas_tardanzas.0.tardanzas', 2);
    }

    public function test_valida_el_rango_de_fechas(): void
    {
        $this->getJson('/api/v1/reportes/tardanzas?desde=2026-10-28&hasta=2026-10-01')
            ->assertJsonValidationErrors('hasta');

        $this->getJson('/api/v1/reportes/tardanzas?desde=2026-01-01&hasta=2026-10-28')
            ->assertStatus(422)
            ->assertJsonPath('errors.hasta.0', 'El rango no puede superar 93 días.');
    }

    public function test_resumen_del_dia(): void
    {
        $empleados = Empleado::factory()->count(4)->create();
        Empleado::factory()->inactivo()->create();
        Asistencia::factory()->for($empleados[0])->create(['fecha' => '2026-10-28']);
        Asistencia::factory()->for($empleados[1])->tarde(12)->create(['fecha' => '2026-10-28']);

        $this->getJson('/api/v1/reportes/resumen-del-dia')
            ->assertOk()
            ->assertExactJson([
                'fecha' => '2026-10-28',
                'empleados_activos' => 4,
                'presentes' => 2,
                'tardanzas' => 1,
                'sin_marcar' => 2,
            ]);
    }
}
