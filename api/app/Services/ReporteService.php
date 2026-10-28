<?php

namespace App\Services;

use App\Models\Asistencia;
use App\Models\Empleado;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReporteService
{
    /**
     * Tardanzas por área en un rango de fechas. Incluye áreas sin asistencias (LEFT JOIN).
     *
     * @return Collection<int, array{area_id: int, area: string, empleados: int, asistencias: int, tardanzas: int, minutos_tardanza: int, porcentaje_tardanza: float}>
     */
    public function tardanzasPorArea(string $desde, string $hasta): Collection
    {
        $filas = DB::table('areas')
            ->leftJoin('empleados', 'empleados.area_id', '=', 'areas.id')
            ->leftJoin('asistencias', function (JoinClause $join) use ($desde, $hasta): void {
                $join->on('asistencias.empleado_id', '=', 'empleados.id')
                    ->whereBetween('asistencias.fecha', [$desde, $hasta]);
            })
            ->groupBy('areas.id', 'areas.nombre')
            ->select([
                'areas.id as area_id',
                'areas.nombre as area',
                DB::raw('COUNT(DISTINCT empleados.id) as empleados'),
                DB::raw('COUNT(asistencias.id) as asistencias'),
                DB::raw('SUM(CASE WHEN asistencias.minutos_tardanza > 0 THEN 1 ELSE 0 END) as tardanzas'),
                DB::raw('COALESCE(SUM(asistencias.minutos_tardanza), 0) as minutos_tardanza'),
            ])
            ->orderByDesc('tardanzas')
            ->orderBy('areas.nombre')
            ->get();

        return $filas->map(function (object $fila): array {
            $asistencias = (int) $fila->asistencias;
            $tardanzas = (int) $fila->tardanzas;

            return [
                'area_id' => (int) $fila->area_id,
                'area' => $fila->area,
                'empleados' => (int) $fila->empleados,
                'asistencias' => $asistencias,
                'tardanzas' => $tardanzas,
                'minutos_tardanza' => (int) $fila->minutos_tardanza,
                'porcentaje_tardanza' => $asistencias > 0 ? round($tardanzas * 100 / $asistencias, 1) : 0.0,
            ];
        });
    }

    /**
     * Los empleados con más tardanzas en el rango.
     *
     * @return Collection<int, array{empleado_id: int, empleado: string, area: string, tardanzas: int, minutos_tardanza: int}>
     */
    public function empleadosConMasTardanzas(string $desde, string $hasta, int $limite = 5): Collection
    {
        return DB::table('asistencias')
            ->join('empleados', 'empleados.id', '=', 'asistencias.empleado_id')
            ->join('areas', 'areas.id', '=', 'empleados.area_id')
            ->whereBetween('asistencias.fecha', [$desde, $hasta])
            ->where('asistencias.minutos_tardanza', '>', 0)
            ->groupBy('empleados.id', 'empleados.nombres', 'empleados.apellidos', 'areas.nombre')
            ->select([
                'empleados.id as empleado_id',
                'empleados.nombres',
                'empleados.apellidos',
                'areas.nombre as area',
                DB::raw('COUNT(*) as tardanzas'),
                DB::raw('SUM(asistencias.minutos_tardanza) as minutos_tardanza'),
            ])
            ->orderByDesc('tardanzas')
            ->orderByDesc('minutos_tardanza')
            ->limit($limite)
            ->get()
            ->map(fn (object $fila): array => [
                'empleado_id' => (int) $fila->empleado_id,
                'empleado' => "{$fila->nombres} {$fila->apellidos}",
                'area' => $fila->area,
                'tardanzas' => (int) $fila->tardanzas,
                'minutos_tardanza' => (int) $fila->minutos_tardanza,
            ]);
    }

    /**
     * Resumen de un día: activos, presentes, tardanzas y quiénes faltan marcar.
     *
     * @return array{fecha: string, empleados_activos: int, presentes: int, tardanzas: int, sin_marcar: int}
     */
    public function resumenDelDia(string $fecha): array
    {
        $activos = Empleado::where('activo', true)->count();
        $delDia = Asistencia::whereDate('fecha', $fecha);

        $presentes = (clone $delDia)->count();
        $tardanzas = (clone $delDia)->where('minutos_tardanza', '>', 0)->count();

        return [
            'fecha' => $fecha,
            'empleados_activos' => $activos,
            'presentes' => $presentes,
            'tardanzas' => $tardanzas,
            'sin_marcar' => max($activos - $presentes, 0),
        ];
    }
}
