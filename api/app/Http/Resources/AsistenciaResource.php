<?php

namespace App\Http\Resources;

use App\Models\Asistencia;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Asistencia */
class AsistenciaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fecha' => $this->fecha,
            'hora_entrada' => substr($this->hora_entrada, 0, 5),
            'hora_salida' => $this->hora_salida ? substr($this->hora_salida, 0, 5) : null,
            'minutos_tardanza' => $this->minutos_tardanza,
            'llego_tarde' => $this->llegoTarde(),
            'empleado' => new EmpleadoResource($this->whenLoaded('empleado')),
        ];
    }
}
