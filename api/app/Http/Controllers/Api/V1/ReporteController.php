<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Asistencia\FiltrarAsistenciasRequest;
use App\Http\Requests\Reporte\RangoFechasRequest;
use App\Services\ReporteService;
use Illuminate\Http\JsonResponse;

class ReporteController extends Controller
{
    public function __construct(private ReporteService $reportes) {}

    public function tardanzas(RangoFechasRequest $request): JsonResponse
    {
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');

        return response()->json([
            'desde' => $desde,
            'hasta' => $hasta,
            'por_area' => $this->reportes->tardanzasPorArea($desde, $hasta),
            'mas_tardanzas' => $this->reportes->empleadosConMasTardanzas($desde, $hasta),
        ]);
    }

    public function resumenDelDia(FiltrarAsistenciasRequest $request): JsonResponse
    {
        return response()->json($this->reportes->resumenDelDia($request->fecha()));
    }
}
