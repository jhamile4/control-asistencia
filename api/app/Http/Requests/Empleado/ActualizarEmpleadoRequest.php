<?php

namespace App\Http\Requests\Empleado;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class ActualizarEmpleadoRequest extends GuardarEmpleadoRequest
{
    /**
     * Mismas reglas que al crear, pero el DNI y el correo pueden repetirse
     * con el propio empleado que se está editando.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $empleadoId = $this->route('empleado')->id;

        return [
            ...parent::rules(),
            'dni' => ['required', 'digits:8', Rule::unique('empleados', 'dni')->ignore($empleadoId)],
            'email' => ['required', 'email', 'max:120', Rule::unique('empleados', 'email')->ignore($empleadoId)],
        ];
    }
}
