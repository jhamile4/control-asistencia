<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Asistencia;
use App\Models\Empleado;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EmpleadoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function datosValidos(Area $area): array
    {
        return [
            'area_id' => $area->id,
            'dni' => '45678912',
            'nombres' => '  Lucía ',
            'apellidos' => 'Quispe Rojas',
            'email' => 'LUCIA.QUISPE@empresa.pe',
            'hora_entrada' => '08:30',
        ];
    }

    public function test_admin_crea_empleado_y_responde_201(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $area = Area::factory()->create();

        $this->postJson('/api/v1/empleados', $this->datosValidos($area))
            ->assertCreated()
            ->assertJsonPath('data.nombres', 'Lucía')
            ->assertJsonPath('data.email', 'lucia.quispe@empresa.pe')
            ->assertJsonPath('data.hora_entrada', '08:30')
            ->assertJsonPath('data.area.id', $area->id);

        $this->assertDatabaseHas('empleados', ['dni' => '45678912', 'hora_entrada' => '08:30:00']);
    }

    public function test_supervisor_no_puede_crear_empleados(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/empleados', $this->datosValidos(Area::factory()->create()))
            ->assertForbidden();
    }

    public function test_valida_dni_de_8_digitos_y_correo_unico(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $area = Area::factory()->create();
        Empleado::factory()->create(['email' => 'lucia.quispe@empresa.pe']);

        $this->postJson('/api/v1/empleados', [...$this->datosValidos($area), 'dni' => '1234'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['dni', 'email'])
            ->assertJsonPath('errors.dni.0', 'El campo DNI debe tener 8 dígitos.');
    }

    public function test_al_editar_puede_conservar_su_propio_dni(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $empleado = Empleado::factory()->create(['dni' => '45678912']);

        $this->putJson("/api/v1/empleados/{$empleado->id}", [
            ...$this->datosValidos($empleado->area),
            'email' => $empleado->email,
            'activo' => false,
        ])
            ->assertOk()
            ->assertJsonPath('data.activo', false);
    }

    public function test_lista_paginada_con_busqueda_y_filtro_por_area(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $ventas = Area::factory()->create(['nombre' => 'Ventas']);
        Empleado::factory()->for($ventas)->create(['nombres' => 'Rosa', 'apellidos' => 'Huamán Torres']);
        // Apellidos fijos: uno aleatorio como "Rosales" también coincidiría con "rosa"
        Empleado::factory()->for($ventas)->create(['nombres' => 'Pedro', 'apellidos' => 'Díaz Luna']);
        Empleado::factory()->create(['nombres' => 'Rosa', 'apellidos' => 'Mendoza Paz']);

        $this->getJson("/api/v1/empleados?buscar=rosa&area_id={$ventas->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.apellidos', 'Huamán Torres')
            ->assertJsonStructure(['data', 'links', 'meta' => ['current_page', 'total']]);
    }

    public function test_no_elimina_empleado_con_asistencias(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $empleado = Empleado::factory()->create();
        Asistencia::factory()->for($empleado)->create();

        $this->deleteJson("/api/v1/empleados/{$empleado->id}")
            ->assertStatus(422)
            ->assertJsonPath('message', 'El empleado tiene asistencias registradas. Desactívalo en lugar de eliminarlo.');

        $this->assertModelExists($empleado);
    }

    public function test_elimina_empleado_sin_asistencias(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $empleado = Empleado::factory()->create();

        $this->deleteJson("/api/v1/empleados/{$empleado->id}")->assertNoContent();

        $this->assertModelMissing($empleado);
    }

    public function test_empleado_inexistente_responde_404(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/v1/empleados/999')->assertNotFound();
    }
}
