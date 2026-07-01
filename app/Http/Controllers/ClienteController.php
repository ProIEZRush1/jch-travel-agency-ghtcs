<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Inertia\Inertia;

class ClienteController extends Controller
{
    public function index()
    {
        return Inertia::render('Clientes/Index', [
            'clientes' => Cliente::latest()->get(),
        ]);
    }

    public function show(Cliente $cliente)
    {
        return Inertia::render('Clientes/Index', [
            'clientes' => Cliente::latest()->get(),
        ]);
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado.');
    }
}
