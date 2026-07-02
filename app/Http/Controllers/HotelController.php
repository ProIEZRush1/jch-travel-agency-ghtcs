<?php

namespace App\Http\Controllers;

use App\Services\ExpediaTaapClient;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HotelController extends Controller
{
    /**
     * Solo nombres de destino para acelerar el autocompletado del buscador — NINGÚN hotel ni
     * tarifa vive aquí. Los hoteles y precios que ve el cliente siempre vienen 100% en vivo del
     * scraping de ExpediaTaapClient contra el portal real de TAAP.
     *
     * @var array<int, string>
     */
    private const DESTINOS_SUGERIDOS = [
        'Cancún', 'Playa del Carmen', 'Tulum', 'Cozumel', 'Isla Mujeres',
        'Los Cabos', 'La Paz', 'Puerto Vallarta', 'Riviera Nayarit', 'Mazatlán',
        'Ciudad de México', 'Guadalajara', 'Monterrey', 'Querétaro', 'San Miguel de Allende',
        'Oaxaca', 'Puebla', 'Mérida', 'Huatulco', 'Acapulco', 'Ixtapa Zihuatanejo',
        'Miami', 'Orlando', 'Las Vegas', 'Nueva York', 'Los Ángeles',
        'Madrid', 'Barcelona', 'París', 'Roma', 'Punta Cana',
    ];

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

        $qNormalizado = $this->normalizar($q);

        $sugerencias = array_values(array_filter(
            self::DESTINOS_SUGERIDOS,
            fn (string $destino) => str_contains($this->normalizar($destino), $qNormalizado)
        ));

        return response()->json(array_map(fn (string $nombre) => ['nombre' => $nombre], array_slice($sugerencias, 0, 8)));
    }

    public function buscar(Request $request, ExpediaTaapClient $client)
    {
        $data = $request->validate([
            'destino' => ['required', 'string', 'max:120'],
            'checkin' => ['required', 'date', 'after_or_equal:today'],
            'checkout' => ['required', 'date', 'after:checkin'],
            'adultos' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $noches = (new \DateTime($data['checkin']))->diff(new \DateTime($data['checkout']))->days;
        $noches = max(1, $noches);

        $resultado = $client->searchHotels($data['destino'], $data['checkin'], $data['checkout'], (int) $data['adultos']);

        return response()->json([
            'success' => $resultado['success'],
            'status' => $resultado['status'],
            'message' => $resultado['message'],
            'destino' => $data['destino'],
            'noches' => $noches,
            'adultos' => (int) $data['adultos'],
            'hoteles' => $resultado['hoteles'],
        ]);
    }

    private function normalizar(string $texto): string
    {
        $texto = mb_strtolower(trim($texto));

        return strtr($texto, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n',
        ]);
    }
}
