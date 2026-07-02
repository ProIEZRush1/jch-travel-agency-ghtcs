<?php

namespace App\Http\Controllers;

use App\Services\HotelCatalog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HotelController extends Controller
{
    public function index()
    {
        return Inertia::render('Hoteles/Index');
    }

    public function sugerencias(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        return response()->json(HotelCatalog::sugerir($q));
    }

    public function buscar(Request $request)
    {
        $data = $request->validate([
            'destino' => ['required', 'string'],
            'checkin' => ['required', 'date'],
            'checkout' => ['required', 'date', 'after:checkin'],
            'adultos' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $destino = HotelCatalog::destino($data['destino']);

        if (! $destino) {
            return response()->json(['success' => false, 'hoteles' => []]);
        }

        $noches = (new \DateTime($data['checkin']))->diff(new \DateTime($data['checkout']))->days;
        $noches = max(1, $noches);

        $hoteles = array_map(function (array $h) use ($noches) {
            $totalAgente = $h['tarifa_agente'] * $noches;
            $totalNormal = $h['tarifa_normal'] * $noches;

            return array_merge($h, [
                'moneda' => 'MXN',
                'noches' => $noches,
                'precio_total' => $totalAgente,
                'precio_normal_total' => $totalNormal,
                'ahorro' => $totalNormal - $totalAgente,
                'ahorro_pct' => $totalNormal > 0 ? round((($totalNormal - $totalAgente) / $totalNormal) * 100) : 0,
            ]);
        }, $destino['hoteles']);

        usort($hoteles, fn ($a, $b) => $a['precio_total'] <=> $b['precio_total']);

        return response()->json([
            'success' => true,
            'destino' => $destino['nombre'],
            'noches' => $noches,
            'adultos' => (int) $data['adultos'],
            'hoteles' => $hoteles,
        ]);
    }
}
