<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutoBusqueda extends Model
{
    protected $table = 'auto_busquedas';

    protected $fillable = [
        'lugar_recogida_codigo',
        'lugar_recogida_nombre',
        'lugar_entrega_codigo',
        'lugar_entrega_nombre',
        'fecha_recogida',
        'hora_recogida',
        'fecha_entrega',
        'hora_entrega',
        'resultados_count',
        'precio_desde',
        'ip',
    ];

    public function cotizaciones(): HasMany
    {
        return $this->hasMany(AutoCotizacion::class);
    }
}
