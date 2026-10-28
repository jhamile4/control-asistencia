<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            // restrictOnDelete: no se puede borrar un área que tiene empleados
            $table->foreignId('area_id')->constrained('areas')->restrictOnDelete();
            $table->char('dni', 8)->unique();
            $table->string('nombres', 80);
            $table->string('apellidos', 80);
            $table->string('email', 120)->unique();
            // Hora a la que debe llegar; con ella se calcula la tardanza
            $table->time('hora_entrada')->default('09:00:00');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['apellidos', 'nombres']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
