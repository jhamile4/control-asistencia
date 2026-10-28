<?php

namespace App\Http\Requests\Empleado;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GuardarEmpleadoRequest extends FormRequest
{
    /** El rol ya lo controla el middleware rol:admin de la ruta. */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'area_id' => ['required', 'integer', 'exists:areas,id'],
            'dni' => ['required', 'digits:8', 'unique:empleados,dni'],
            'nombres' => ['required', 'string', 'max:80'],
            'apellidos' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:120', 'unique:empleados,email'],
            'hora_entrada' => ['required', 'date_format:H:i'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }

    /** Quita espacios sobrantes antes de validar. */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'nombres' => trim((string) $this->input('nombres')),
            'apellidos' => trim((string) $this->input('apellidos')),
            'email' => strtolower(trim((string) $this->input('email'))),
        ]);
    }
}
