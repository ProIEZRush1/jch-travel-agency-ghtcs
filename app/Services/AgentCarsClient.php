<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Direct client for AgentCars' subsite JSON endpoints (reverse-engineered from
 * the network calls the official form-iframe widget makes for the
 * 'archer-operadora' agency). No API key is involved — the widget itself
 * calls these same unauthenticated, agency-scoped URLs from the browser.
 */
class AgentCarsClient
{
    private const FULL_PROTECTION_ALIAS = 'Full Protection';

    private string $baseUrl;
    private string $agency;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.agentcars.base_url'), '/');
        $this->agency = (string) config('services.agentcars.agency');
    }

    /** @return array<int, array{code:string,name:string,lat:float,lng:float}> */
    public function suggest(string $query): array
    {
        try {
            $res = Http::timeout(10)->get("{$this->baseUrl}/api/suggest", [
                'q' => $query,
                'locale' => 'es',
                'type' => 'cars',
            ]);

            if (! $res->successful()) {
                return [];
            }

            $airports = $res->json('airport') ?? [];

            return collect($airports)
                ->filter(fn ($a) => filled($a['airport_code'] ?? null))
                ->map(fn ($a) => [
                    'code' => $a['airport_code'],
                    'name' => $a['name'],
                    'lat' => $a['lat'],
                    'lng' => $a['lng'],
                ])
                ->values()
                ->all();
        } catch (\Throwable $e) {
            Log::warning('agentcars suggest failed', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * @param  array{pickup_code:string,dropoff_code:string,pickup_date:string,dropoff_date:string,pickup_hour:string,dropoff_hour:string}  $params
     * @return array{success:bool, offers: array<int, array<string,mixed>>}
     */
    public function searchCars(array $params): array
    {
        try {
            $res = Http::timeout(30)->get("{$this->baseUrl}/api/services/cars/get-matrix", [
                'pickUpLocation' => $params['pickup_code'],
                'dropOffLocation' => $params['dropoff_code'],
                'pickUpDate' => $params['pickup_date'],
                'dropOffDate' => $params['dropoff_date'],
                'pickUpHour' => str_replace(':', '', $params['pickup_hour']),
                'dropOffHour' => str_replace(':', '', $params['dropoff_hour']),
                'source' => 'US',
                'country' => 'US',
                'rateType' => 'best',
                'language' => 'es',
                'agency' => $this->agency,
            ]);

            if (! $res->successful()) {
                return ['success' => false, 'offers' => []];
            }

            $matrix = $res->json('matrixAirport');

            if (! is_array($matrix) || array_key_exists('success', $matrix)) {
                return ['success' => false, 'offers' => []];
            }

            $offers = [];
            foreach ($matrix as $categoryOffers) {
                foreach ((array) $categoryOffers as $offer) {
                    if (($offer['rateInfo']['alias'] ?? null) !== self::FULL_PROTECTION_ALIAS) {
                        continue;
                    }

                    $offers[] = [
                        'categoria' => $offer['category'] ?? '',
                        'compania' => $offer['companyName'] ?? '',
                        'compania_logo' => $offer['companyImg'] ?? null,
                        'modelo' => $offer['carModel'] ?? null,
                        'imagen' => $offer['img'] ?? null,
                        'transmision' => $offer['trans'] ?? null,
                        'pasajeros' => $offer['passengers'] ?? null,
                        'puertas' => $offer['doors'] ?? null,
                        'maletas' => $offer['bags'] ?? null,
                        'aire_acondicionado' => $offer['air'] ?? null,
                        'km_incluidos' => $offer['km_included'] ?? null,
                        'precio' => (float) ($offer['rateAmount'] ?? 0),
                        'moneda' => $offer['currency'] ?? 'USD',
                        'dias' => $offer['days'] ?? null,
                        'proteccion' => $offer['rateInfo']['name'] ?? 'Protección Total',
                        'rate_identifier' => $offer['rateIdentifier'] ?? null,
                        'lugar_recogida' => $offer['pickUpAddress'] ?? null,
                        'lugar_entrega' => $offer['dropOffAddress'] ?? null,
                    ];
                }
            }

            usort($offers, fn ($a, $b) => $a['precio'] <=> $b['precio']);

            return ['success' => true, 'offers' => $offers];
        } catch (\Throwable $e) {
            Log::warning('agentcars search failed', ['error' => $e->getMessage()]);

            return ['success' => false, 'offers' => []];
        }
    }
}
