<?php

namespace App\Http\Requests\Asistencia;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegistrarEntradaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * "fecha" y "hora" son opcionales: sin ellas se usa el momento actual.
     * Sirven para registrar una marca olvidada de un día anterior.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'empleado_id' => ['required', 'integer', 'exists:empleados,id'],
            'fecha' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
            'hora' => ['nullable', 'date_format:H:i', 'required_with:fecha'],
        ];
    }

    public function momento(): CarbonImmutable
    {
        if (! $this->filled('hora')) {
            return CarbonImmutable::now();
        }

        $fecha = $this->input('fecha', now()->toDateString());

        return CarbonImmutable::createFromFormat('Y-m-d H:i', "{$fecha} {$this->input('hora')}");
    }
}
