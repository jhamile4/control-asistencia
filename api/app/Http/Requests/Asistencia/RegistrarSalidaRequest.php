<?php

namespace App\Http\Requests\Asistencia;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegistrarSalidaRequest extends FormRequest
{
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
            'hora' => ['nullable', 'date_format:H:i'],
        ];
    }

    public function horaSalida(): string
    {
        return $this->input('hora') ?? now()->format('H:i:s');
    }
}
