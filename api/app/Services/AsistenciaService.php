<?php

namespace App\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\Asistencia;
use App\Models\Empleado;
use App\Models\User;
use Carbon\CarbonImmutable;

class AsistenciaService
{
    /** Minutos de gracia después de la hora de entrada antes de contar tardanza. */
    public const TOLERANCIA_MINUTOS = 10;

    /**
     * Registra la entrada del día. Si no se envía hora, usa la hora actual de Lima.
     *
     * @throws ReglaNegocioException
     */
    public function registrarEntrada(Empleado $empleado, CarbonImmutable $momento, User $registradoPor): Asistencia
    {
        if (! $empleado->activo) {
            throw new ReglaNegocioException('El empleado está inactivo y no puede marcar asistencia.');
        }

        if ($momento->isAfter(now())) {
            throw new ReglaNegocioException('No se puede registrar una entrada en el futuro.');
        }

        $fecha = $momento->toDateString();

        $yaMarco = $empleado->asistencias()->whereDate('fecha', $fecha)->exists();
        if ($yaMarco) {
            throw new ReglaNegocioException("{$empleado->nombreCompleto()} ya marcó su entrada el {$fecha}.");
        }

        $horaLlegada = $momento->format('H:i:s');

        return $empleado->asistencias()->create([
            'fecha' => $fecha,
            'hora_entrada' => $horaLlegada,
            'minutos_tardanza' => $this->calcularMinutosTardanza($empleado->hora_entrada, $horaLlegada),
            'registrado_por' => $registradoPor->id,
        ]);
    }

    /**
     * @throws ReglaNegocioException
     */
    public function registrarSalida(Asistencia $asistencia, string $horaSalida): Asistencia
    {
        if ($asistencia->hora_salida !== null) {
            throw new ReglaNegocioException('La salida de esta asistencia ya fue registrada.');
        }

        $horaSalida = $this->normalizarHora($horaSalida);
        if ($horaSalida <= $asistencia->hora_entrada) {
            throw new ReglaNegocioException('La hora de salida debe ser posterior a la hora de entrada.');
        }

        $asistencia->update(['hora_salida' => $horaSalida]);

        return $asistencia;
    }

    /**
     * Devuelve 0 si llegó dentro de la tolerancia; si no, los minutos desde la hora programada.
     */
    public function calcularMinutosTardanza(string $horaProgramada, string $horaLlegada): int
    {
        $programada = CarbonImmutable::createFromFormat('H:i:s', $this->normalizarHora($horaProgramada));
        $llegada = CarbonImmutable::createFromFormat('H:i:s', $this->normalizarHora($horaLlegada));

        if ($llegada->lessThanOrEqualTo($programada->addMinutes(self::TOLERANCIA_MINUTOS))) {
            return 0;
        }

        return (int) $programada->diffInMinutes($llegada);
    }

    /** Acepta "09:05" o "09:05:00" y devuelve siempre "09:05:00". */
    private function normalizarHora(string $hora): string
    {
        return strlen($hora) === 5 ? "{$hora}:00" : $hora;
    }
}
