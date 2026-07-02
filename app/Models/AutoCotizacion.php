<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoCotizacion extends Model
{
    protected $table = 'auto_cotizaciones';

    protected $fillable = [
        'auto_busqueda_id',
        'categoria',
        'compania',
        'compania_logo',
        'modelo',
        'imagen',
        'proteccion',
        'precio',
        'moneda',
        'dias',
        'rate_identifier',
    ];

    public function busqueda(): BelongsTo
    {
        return $this->belongsTo(AutoBusqueda::class, 'auto_busqueda_id');
    }
}
