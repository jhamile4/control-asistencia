<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Asistencia\FiltrarAsistenciasRequest;
use App\Http\Requests\Asistencia\RegistrarEntradaRequest;
use App\Http\Requests\Asistencia\RegistrarSalidaRequest;
use App\Http\Resources\AsistenciaResource;
use App\Models\Asistencia;
use App\Models\Empleado;
use App\Services\AsistenciaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AsistenciaController extends Controller
{
    public function __construct(private AsistenciaService $asistencias) {}

    /** Asistencias de un día (hoy por defecto), opcionalmente de un área. */
    public function index(FiltrarAsistenciasRequest $request): AnonymousResourceCollection
    {
        $lista = Asistencia::with('empleado.area')
            ->whereDate('fecha', $request->fecha())
            ->when(
                $request->filled('area_id'),
                fn ($q) => $q->whereRelation('empleado', 'area_id', $request->integer('area_id')),
            )
            ->orderBy('hora_entrada')
            ->get();

        return AsistenciaResource::collection($lista);
    }

    public function registrarEntrada(RegistrarEntradaRequest $request): JsonResponse
    {
        $empleado = Empleado::findOrFail($request->integer('empleado_id'));

        $asistencia = $this->asistencias->registrarEntrada($empleado, $request->momento(), $request->user());

        return (new AsistenciaResource($asistencia->load('empleado.area')))
            ->response()
            ->setStatusCode(201);
    }

    public function registrarSalida(RegistrarSalidaRequest $request, Asistencia $asistencia): AsistenciaResource
    {
        $asistencia = $this->asistencias->registrarSalida($asistencia, $request->horaSalida());

        return new AsistenciaResource($asistencia->load('empleado.area'));
    }
}
