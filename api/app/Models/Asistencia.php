<?php

namespace App\Models;

use Database\Factories\AsistenciaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asistencia extends Model
{
    /** @use HasFactory<AsistenciaFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['empleado_id', 'fecha', 'hora_entrada', 'hora_salida', 'minutos_tardanza', 'registrado_por'];

    /**
     * "fecha" se guarda como texto Y-m-d (sin cast de fecha) para que los filtros por
     * rango funcionen igual en MySQL y en SQLite.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'minutos_tardanza' => 'integer',
        ];
    }

    /** @return BelongsTo<Empleado, $this> */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    public function llegoTarde(): bool
    {
        return $this->minutos_tardanza > 0;
    }
}
