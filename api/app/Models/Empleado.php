<?php

namespace App\Models;

use Database\Factories\EmpleadoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empleado extends Model
{
    /** @use HasFactory<EmpleadoFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['area_id', 'dni', 'nombres', 'apellidos', 'email', 'hora_entrada', 'activo'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    /**
     * Guarda la hora siempre como HH:MM:SS aunque llegue como HH:MM.
     *
     * @return Attribute<string, string>
     */
    protected function horaEntrada(): Attribute
    {
        return Attribute::make(
            set: fn (string $valor): string => strlen($valor) === 5 ? "{$valor}:00" : $valor,
        );
    }

    /** @return BelongsTo<Area, $this> */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    /** @return HasMany<Asistencia, $this> */
    public function asistencias(): HasMany
    {
        return $this->hasMany(Asistencia::class);
    }

    public function nombreCompleto(): string
    {
        return "{$this->nombres} {$this->apellidos}";
    }

    /**
     * Busca por nombres, apellidos o DNI.
     *
     * @param  Builder<Empleado>  $query
     */
    public function scopeBuscar(Builder $query, ?string $texto): void
    {
        if (blank($texto)) {
            return;
        }

        $query->where(function (Builder $q) use ($texto): void {
            $q->where('nombres', 'like', "%{$texto}%")
                ->orWhere('apellidos', 'like', "%{$texto}%")
                ->orWhere('dni', 'like', "{$texto}%");
        });
    }
}
