<?php

namespace App\Http\Requests\Reporte;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class RangoFechasRequest extends FormRequest
{
    /** Límite para que un reporte no recorra años de datos de una sola vez. */
    public const MAXIMO_DIAS = 93;

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
            'desde' => ['required', 'date_format:Y-m-d'],
            'hasta' => ['required', 'date_format:Y-m-d', 'after_or_equal:desde'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $dias = CarbonImmutable::parse($this->input('desde'))->diffInDays($this->input('hasta'));
                if ($dias > self::MAXIMO_DIAS) {
                    $validator->errors()->add('hasta', 'El rango no puede superar '.self::MAXIMO_DIAS.' días.');
                }
            },
        ];
    }
}
