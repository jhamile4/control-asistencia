<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Se lanza cuando los datos son válidos pero una regla del negocio impide la operación
 * (por ejemplo, marcar entrada dos veces el mismo día). Responde 422.
 */
class ReglaNegocioException extends Exception
{
    public function render(Request $request): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 422);
    }
}
