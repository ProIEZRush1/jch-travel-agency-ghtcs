<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaqueteController extends Controller
{
    public function index()
    {
        return Inertia::render('Paquetes/Index', [
            'paquetes' => Plan::orderBy('orden')->orderBy('id')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Paquetes/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:120',
            'precio'      => 'required|integer|min:0',
            'descripcion' => 'nullable|string|max:500',
            'activo'      => 'boolean',
            'orden'       => 'integer|min:0',
        ]);

        Plan::create($data);

        return redirect()->route('paquetes.index')->with('success', 'Paquete creado correctamente.');
    }

    public function edit(Plan $paquete)
    {
        return Inertia::render('Paquetes/Edit', ['paquete' => $paquete]);
    }

    public function update(Request $request, Plan $paquete)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:120',
            'precio'      => 'required|integer|min:0',
            'descripcion' => 'nullable|string|max:500',
            'activo'      => 'boolean',
            'orden'       => 'integer|min:0',
        ]);

        $paquete->update($data);

        return redirect()->route('paquetes.index')->with('success', 'Paquete actualizado correctamente.');
    }

    public function destroy(Plan $paquete)
    {
        $paquete->delete();
        return redirect()->route('paquetes.index')->with('success', 'Paquete eliminado.');
    }

    public function show(Plan $paquete)
    {
        return redirect()->route('paquetes.index');
    }
}
