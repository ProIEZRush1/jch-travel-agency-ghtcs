<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auto_busquedas', function (Blueprint $table) {
            $table->id();
            $table->string('lugar_recogida_codigo', 10);
            $table->string('lugar_recogida_nombre');
            $table->string('lugar_entrega_codigo', 10);
            $table->string('lugar_entrega_nombre');
            $table->date('fecha_recogida');
            $table->string('hora_recogida', 5);
            $table->date('fecha_entrega');
            $table->string('hora_entrega', 5);
            $table->unsignedInteger('resultados_count')->default(0);
            $table->decimal('precio_desde', 10, 2)->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auto_busquedas');
    }
};
