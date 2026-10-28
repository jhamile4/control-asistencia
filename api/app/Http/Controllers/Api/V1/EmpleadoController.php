<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\ReglaNegocioException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Empleado\ActualizarEmpleadoRequest;
use App\Http\Requests\Empleado\FiltrarEmpleadosRequest;
use App\Http\Requests\Empleado\GuardarEmpleadoRequest;
use App\Http\Resources\EmpleadoResource;
use App\Models\Empleado;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EmpleadoController extends Controller
{
    public function index(FiltrarEmpleadosRequest $request): AnonymousResourceCollection
    {
        $empleados = Empleado::with('area')
            ->buscar($request->input('buscar'))
            ->when($request->filled('area_id'), fn ($q) => $q->where('area_id', $request->integer('area_id')))
            ->when($request->filled('activo'), fn ($q) => $q->where('activo', $request->boolean('activo')))
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->paginate($request->integer('por_pagina', 15));

        return EmpleadoResource::collection($empleados);
    }

    public function store(GuardarEmpleadoRequest $request): JsonResponse
    {
        $empleado = Empleado::create($request->validated());

        return (new EmpleadoResource($empleado->load('area')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Empleado $empleado): EmpleadoResource
    {
        return new EmpleadoResource($empleado->load('area'));
    }

    public function update(ActualizarEmpleadoRequest $request, Empleado $empleado): EmpleadoResource
    {
        $empleado->update($request->validated());

        return new EmpleadoResource($empleado->load('area'));
    }

    /**
     * Un empleado con asistencias no se borra: se pierde su historial. Se desactiva.
     *
     * @throws ReglaNegocioException
     */
    public function destroy(Empleado $empleado): JsonResponse
    {
        if ($empleado->asistencias()->exists()) {
            throw new ReglaNegocioException('El empleado tiene asistencias registradas. Desactívalo en lugar de eliminarlo.');
        }

        $empleado->delete();

        return response()->json(null, 204);
    }
}
