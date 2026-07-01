<?php

namespace App\Http\Controllers;

use App\Models\AutoBusqueda;
use App\Services\AgentCarsClient;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AutoController extends Controller
{
    public function index()
    {
        return Inertia::render('Autos/Index');
    }

    public function sugerencias(Request $request, AgentCarsClient $client)
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        return response()->json($client->suggest($q));
    }

    public function buscar(Request $request, AgentCarsClient $client)
    {
        $data = $request->validate([
            'pickup_code' => ['required', 'string', 'size:3'],
            'pickup_name' => ['nullable', 'string'],
            'dropoff_code' => ['required', 'string', 'size:3'],
            'dropoff_name' => ['nullable', 'string'],
            'pickup_date' => ['required', 'date'],
            'pickup_hour' => ['required', 'string'],
            'dropoff_date' => ['required', 'date', 'after_or_equal:pickup_date'],
            'dropoff_hour' => ['required', 'string'],
        ]);

        $result = $client->searchCars($data);

        $busqueda = AutoBusqueda::create([
            'lugar_recogida_codigo' => $data['pickup_code'],
            'lugar_recogida_nombre' => $data['pickup_name'] ?? $data['pickup_code'],
            'lugar_entrega_codigo' => $data['dropoff_code'],
            'lugar_entrega_nombre' => $data['dropoff_name'] ?? $data['dropoff_code'],
            'fecha_recogida' => $data['pickup_date'],
            'hora_recogida' => $data['pickup_hour'],
            'fecha_entrega' => $data['dropoff_date'],
            'hora_entrega' => $data['dropoff_hour'],
            'resultados_count' => count($result['offers']),
            'precio_desde' => $result['offers'][0]['precio'] ?? null,
            'ip' => $request->ip(),
        ]);

        foreach ($result['offers'] as $offer) {
            $busqueda->cotizaciones()->create([
                'categoria' => $offer['categoria'],
                'compania' => $offer['compania'],
                'compania_logo' => $offer['compania_logo'],
                'modelo' => $offer['modelo'],
                'imagen' => $offer['imagen'],
                'proteccion' => $offer['proteccion'],
                'precio' => $offer['precio'],
                'moneda' => $offer['moneda'],
                'dias' => $offer['dias'],
                'rate_identifier' => $offer['rate_identifier'],
            ]);
        }

        return response()->json([
            'success' => $result['success'],
            'busqueda_id' => $busqueda->id,
            'ofertas' => $result['offers'],
        ]);
    }

    public function historial()
    {
        return Inertia::render('Autos/Historial', [
            'busquedas' => AutoBusqueda::withCount('cotizaciones')->latest()->paginate(15),
        ]);
    }
}
