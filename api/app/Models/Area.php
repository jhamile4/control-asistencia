<?php

namespace App\Models;

use Database\Factories\AreaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    /** @use HasFactory<AreaFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['nombre'];

    /** @return HasMany<Empleado, $this> */
    public function empleados(): HasMany
    {
        return $this->hasMany(Empleado::class);
    }
}
