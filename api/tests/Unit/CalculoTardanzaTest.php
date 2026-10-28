<?php

namespace Tests\Unit;

use App\Services\AsistenciaService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CalculoTardanzaTest extends TestCase
{
    /**
     * Valores límite alrededor de la tolerancia de 10 minutos (entrada a las 09:00).
     *
     * @return array<string, array{string, int}>
     */
    public static function llegadas(): array
    {
        return [
            'llega antes' => ['08:45:00', 0],
            'llega puntual' => ['09:00:00', 0],
            'último minuto de tolerancia' => ['09:10:00', 0],
            'un segundo después de la tolerancia' => ['09:10:01', 10],
            'un minuto después de la tolerancia' => ['09:11:00', 11],
            'media hora tarde' => ['09:30:00', 30],
            'acepta formato HH:MM' => ['09:20', 20],
        ];
    }

    #[DataProvider('llegadas')]
    public function test_calcula_minutos_de_tardanza(string $llegada, int $esperado): void
    {
        $servicio = new AsistenciaService;

        $this->assertSame($esperado, $servicio->calcularMinutosTardanza('09:00:00', $llegada));
    }
}
