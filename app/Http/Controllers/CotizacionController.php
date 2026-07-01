<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CotizacionController extends Controller
{
    public function index()
    {
        return Inertia::render('Cotizaciones/Index', [
            'cotizaciones' => Pedido::with('plan')->latest()->get(),
        ]);
    }

    public function show(Pedido $cotizacion)
    {
        return redirect()->route('cotizaciones.index');
    }

    public function update(Request $request, Pedido $cotizacion)
    {
        if (config('trial.locked')) {
            return back()->with('error', 'Función bloqueada en versión de prueba.');
        }

        $data = $request->validate([
            'estado' => 'required|in:pendiente,confirmado,cancelado',
        ]);

        $cotizacion->update($data);
        return redirect()->route('cotizaciones.index')->with('success', 'Cotización actualizada.');
    }

    public function destroy(Pedido $cotizacion)
    {
        $cotizacion->delete();
        return redirect()->route('cotizaciones.index')->with('success', 'Cotización eliminada.');
    }
}
