<?php

namespace App\Http\Resources;

use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Empleado */
class EmpleadoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'dni' => $this->dni,
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'nombre_completo' => $this->nombreCompleto(),
            'email' => $this->email,
            'hora_entrada' => substr($this->hora_entrada, 0, 5),
            'activo' => $this->activo,
            'area' => new AreaResource($this->whenLoaded('area')),
        ];
    }
}
