<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auto_cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auto_busqueda_id')->constrained('auto_busquedas')->cascadeOnDelete();
            $table->string('categoria');
            $table->string('compania');
            $table->string('compania_logo')->nullable();
            $table->string('modelo')->nullable();
            $table->string('imagen')->nullable();
            $table->string('proteccion')->nullable();
            $table->decimal('precio', 10, 2);
            $table->string('moneda', 5)->default('USD');
            $table->unsignedInteger('dias')->nullable();
            $table->string('rate_identifier')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auto_cotizaciones');
    }
};
