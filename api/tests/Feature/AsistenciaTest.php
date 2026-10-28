<?php

namespace Tests\Feature;

use App\Models\Asistencia;
use App\Models\Empleado;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AsistenciaTest extends TestCase
{
    use RefreshDatabase;

    private User $supervisor;

    protected function setUp(): void
    {
        parent::setUp();

        // Miércoles 28 de octubre de 2026, 09:25 en Lima
        $this->travelTo(CarbonImmutable::parse('2026-10-28 09:25:00', 'America/Lima'));
        $this->supervisor = User::factory()->create();
        Sanctum::actingAs($this->supervisor);
    }

    public function test_marca_entrada_con_la_hora_actual_y_calcula_tardanza(): void
    {
        $empleado = Empleado::factory()->create(['hora_entrada' => '09:00:00']);

        $this->postJson('/api/v1/asistencias/entrada', ['empleado_id' => $empleado->id])
            ->assertCreated()
            ->assertJsonPath('data.fecha', '2026-10-28')
            ->assertJsonPath('data.hora_entrada', '09:25')
            ->assertJsonPath('data.minutos_tardanza', 25)
            ->assertJsonPath('data.llego_tarde', true);

        $this->assertDatabaseHas('asistencias', [
            'empleado_id' => $empleado->id,
            'registrado_por' => $this->supervisor->id,
        ]);
    }

    public function test_llegar_dentro_de_la_tolerancia_no_es_tardanza(): void
    {
        $empleado = Empleado::factory()->create(['hora_entrada' => '09:00:00']);

        $this->postJson('/api/v1/asistencias/entrada', [
            'empleado_id' => $empleado->id,
            'fecha' => '2026-10-27',
            'hora' => '09:10',
        ])
            ->assertCreated()
            ->assertJsonPath('data.minutos_tardanza', 0);
    }

    public function test_no_marca_dos_veces_el_mismo_dia(): void
    {
        $empleado = Empleado::factory()->create();
        Asistencia::factory()->for($empleado)->create(['fecha' => '2026-10-28']);

        $this->postJson('/api/v1/asistencias/entrada', ['empleado_id' => $empleado->id])
            ->assertStatus(422)
            ->assertJsonPath('message', "{$empleado->nombreCompleto()} ya marcó su entrada el 2026-10-28.");
    }

    public function test_empleado_inactivo_no_puede_marcar(): void
    {
        $empleado = Empleado::factory()->inactivo()->create();

        $this->postJson('/api/v1/asistencias/entrada', ['empleado_id' => $empleado->id])
            ->assertStatus(422)
            ->assertJsonPath('message', 'El empleado está inactivo y no puede marcar asistencia.');
    }

    public function test_no_acepta_fechas_futuras(): void
    {
        $empleado = Empleado::factory()->create();

        $this->postJson('/api/v1/asistencias/entrada', [
            'empleado_id' => $empleado->id,
            'fecha' => '2026-10-29',
            'hora' => '09:00',
        ])->assertJsonValidationErrors('fecha');

        $this->postJson('/api/v1/asistencias/entrada', [
            'empleado_id' => $empleado->id,
            'hora' => '18:00',
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'No se puede registrar una entrada en el futuro.');
    }

    public function test_registra_salida_una_sola_vez_y_posterior_a_la_entrada(): void
    {
        $asistencia = Asistencia::factory()->create(['fecha' => '2026-10-28', 'hora_entrada' => '08:55:00']);

        $this->patchJson("/api/v1/asistencias/{$asistencia->id}/salida", ['hora' => '08:30'])
            ->assertStatus(422)
            ->assertJsonPath('message', 'La hora de salida debe ser posterior a la hora de entrada.');

        $this->patchJson("/api/v1/asistencias/{$asistencia->id}/salida", ['hora' => '18:05'])
            ->assertOk()
            ->assertJsonPath('data.hora_salida', '18:05');

        $this->patchJson("/api/v1/asistencias/{$asistencia->id}/salida", ['hora' => '18:30'])
            ->assertStatus(422)
            ->assertJsonPath('message', 'La salida de esta asistencia ya fue registrada.');
    }

    public function test_lista_las_asistencias_del_dia_con_empleado_y_area(): void
    {
        Asistencia::factory()->count(2)->create(['fecha' => '2026-10-28']);
        Asistencia::factory()->create(['fecha' => '2026-10-27']);

        $this->getJson('/api/v1/asistencias?fecha=2026-10-28')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure(['data' => [['id', 'hora_entrada', 'empleado' => ['nombre_completo', 'area' => ['nombre']]]]]);
    }
}
