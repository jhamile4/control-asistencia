<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutenticacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_correcto_devuelve_token_y_usuario(): void
    {
        $usuario = User::factory()->admin()->create(['password' => 'Clave-Prueba-1']);

        $this->postJson('/api/v1/login', ['email' => $usuario->email, 'password' => 'Clave-Prueba-1'])
            ->assertOk()
            ->assertJsonStructure(['token', 'usuario' => ['id', 'nombre', 'email', 'rol']])
            ->assertJsonPath('usuario.rol', 'admin');
    }

    public function test_contrasena_incorrecta_responde_422_sin_revelar_el_motivo(): void
    {
        $usuario = User::factory()->create(['password' => 'Clave-Prueba-1']);

        $this->postJson('/api/v1/login', ['email' => $usuario->email, 'password' => 'otra'])
            ->assertStatus(422)
            ->assertJsonPath('errors.email.0', 'El correo o la contraseña son incorrectos.');
    }

    public function test_login_sin_datos_responde_422_con_mensajes_en_espanol(): void
    {
        $this->postJson('/api/v1/login', [])
            ->assertStatus(422)
            ->assertJsonPath('errors.email.0', 'El campo correo es obligatorio.')
            ->assertJsonPath('errors.password.0', 'El campo contraseña es obligatorio.');
    }

    public function test_sin_token_responde_401(): void
    {
        $this->getJson('/api/v1/empleados')->assertUnauthorized();
    }

    public function test_bloquea_despues_de_5_intentos_por_minuto(): void
    {
        $datos = ['email' => 'nadie@asistencia.test', 'password' => 'x'];

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/login', $datos)->assertStatus(422);
        }

        $this->postJson('/api/v1/login', $datos)->assertStatus(429);
    }

    public function test_logout_invalida_el_token(): void
    {
        $usuario = User::factory()->create(['password' => 'Clave-Prueba-1']);
        $token = $this->postJson('/api/v1/login', ['email' => $usuario->email, 'password' => 'Clave-Prueba-1'])->json('token');

        $this->withToken($token)->postJson('/api/v1/logout')->assertNoContent();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
