<?php

namespace App\Services;

use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
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
    /**
     * AgentCars doesn't sell "Full Protection" on every route/company — many
     * searches (confirmed against the live agency feed) only have "Basic
     * Protection" or "Just Car" rates available. Requiring an exact "Full
     * Protection" match discarded every single offer on those searches and
     * showed "no autos disponibles" even though the route had 100+ real,
     * bookable cars. We now rank tiers and keep the best one actually on
     * offer for each car instead of hiding it entirely.
     *
     * @var array<string, int>
     */
    private const PROTECTION_RANK = [
        'Full Protection' => 0,
        'Basic Protection' => 1,
        'Just Car' => 2,
    ];

    /**
     * AgentCars' /api/suggest only returns "airport" entries for terms that
     * match an airport/city name directly. A country name (e.g. "Ecuador")
     * only comes back as a "country" entry with no airports at all, which
     * left pickup-place autocomplete empty and the search button disabled.
     * When AgentCars tags the query as a recognized country, we fan out to
     * its principal cities/airports (by ISO-3166-1 alpha-2 code) so the
     * country search still surfaces real, bookable pickup locations.
     *
     * @var array<string, array<int, string>>
     */
    private const COUNTRY_AIRPORT_TERMS = [
        'MX' => ['Ciudad de México', 'Cancún', 'Guadalajara', 'Monterrey', 'Puerto Vallarta', 'Los Cabos'],
        'US' => ['Miami', 'Los Ángeles', 'Nueva York', 'Orlando', 'Las Vegas'],
        'CA' => ['Toronto', 'Vancouver', 'Montreal'],
        'EC' => ['Quito', 'Guayaquil', 'Cuenca'],
        'CO' => ['Bogotá', 'Medellín', 'Cartagena'],
        'PE' => ['Lima', 'Cusco'],
        'AR' => ['Buenos Aires', 'Córdoba', 'Mendoza'],
        'CL' => ['Santiago'],
        'BR' => ['São Paulo', 'Río de Janeiro'],
        'BO' => ['La Paz', 'Santa Cruz de la Sierra'],
        'PY' => ['Asunción'],
        'UY' => ['Montevideo'],
        'VE' => ['Caracas'],
        'PA' => ['Ciudad de Panamá'],
        'CR' => ['San José'],
        'GT' => ['Ciudad de Guatemala'],
        'HN' => ['Tegucigalpa', 'San Pedro Sula'],
        'SV' => ['San Salvador'],
        'NI' => ['Managua'],
        'CU' => ['La Habana', 'Varadero'],
        'DO' => ['Punta Cana', 'Santo Domingo'],
        'PR' => ['San Juan'],
        'BZ' => ['Belize City'],
        'ES' => ['Madrid', 'Barcelona'],
        'FR' => ['París'],
        'IT' => ['Roma', 'Milán'],
        'DE' => ['Fráncfort', 'Múnich'],
        'GB' => ['Londres'],
        'PT' => ['Lisboa'],
        'NL' => ['Ámsterdam'],
        'CH' => ['Zúrich'],
        'GR' => ['Atenas'],
        'JP' => ['Tokio'],
        'CN' => ['Pekín', 'Shanghái'],
        'AW' => ['Aruba'],
        'BS' => ['Nassau'],
        'JM' => ['Kingston', 'Montego Bay'],
    ];

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
            $raw = $this->fetchRaw($query);

            if ($raw === null) {
                return [];
            }

            $airports = $this->extractAirports($raw);
            $extraTerms = $this->extraTermsFor($raw);

            if ($extraTerms !== []) {
                $responses = Http::pool(fn (Pool $pool) => collect($extraTerms)
                    ->map(fn ($term) => $pool->timeout(8)->get("{$this->baseUrl}/api/suggest", [
                        'q' => $term,
                        'locale' => 'es',
                        'type' => 'cars',
                    ]))
                    ->all());

                foreach ($responses as $res) {
                    if (! $res instanceof Response || ! $res->successful()) {
                        continue;
                    }

                    foreach ($this->extractAirports($res->json() ?? []) as $code => $airport) {
                        $airports[$code] ??= $airport;
                    }
                }
            }

            return array_slice(array_values($airports), 0, 12);
        } catch (\Throwable $e) {
            Log::warning('agentcars suggest failed', ['error' => $e->getMessage()]);

            return [];
        }
    }

    private function fetchRaw(string $query): ?array
    {
        $res = Http::timeout(10)->get("{$this->baseUrl}/api/suggest", [
            'q' => $query,
            'locale' => 'es',
            'type' => 'cars',
        ]);

        return $res->successful() ? ($res->json() ?? []) : null;
    }

    /** @return array<string, array{code:string,name:string,lat:float,lng:float}> keyed by airport code */
    private function extractAirports(array $raw): array
    {
        $out = [];

        foreach (($raw['airport'] ?? []) as $a) {
            $code = $a['airport_code'] ?? null;

            if (filled($code)) {
                $out[$code] = [
                    'code' => $code,
                    'name' => $a['name'],
                    'lat' => $a['lat'],
                    'lng' => $a['lng'],
                ];
            }
        }

        return $out;
    }

    /**
     * Decide which extra search terms (if any) to fan out to AgentCars in
     * order to resolve non-airport queries (countries, cities without a
     * direct airport match) into real, bookable airport locations.
     *
     * @return array<int, string>
     */
    private function extraTermsFor(array $raw): array
    {
        $countryCode = collect($raw['country'] ?? [])->first()['country_code'] ?? null;

        if ($countryCode && isset(self::COUNTRY_AIRPORT_TERMS[$countryCode])) {
            return self::COUNTRY_AIRPORT_TERMS[$countryCode];
        }

        if (filled($raw['airport'] ?? null)) {
            return [];
        }

        // Ni país reconocido ni aeropuerto directo: intenta con los nombres
        // de ciudad/lugar que AgentCars sí reconoció, para no dejar la
        // búsqueda vacía (ej. barrios, puntos de interés, estaciones).
        $segmentIndexByType = [
            'city' => 0,
            'neighborhood' => 1,
            'point_of_interest' => 1,
            'metro_station' => 1,
            'bus_station' => 1,
            'train_station' => 1,
        ];

        $terms = [];

        foreach ($segmentIndexByType as $type => $idx) {
            foreach (($raw[$type] ?? []) as $item) {
                $parts = array_map('trim', explode(',', (string) ($item['name'] ?? '')));
                $term = $parts[$idx] ?? $parts[0] ?? null;

                if (filled($term)) {
                    $terms[] = $term;
                }
            }
        }

        return collect($terms)->unique()->take(4)->all();
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

            /** @var array<string, array<string,mixed>> $bestByCar */
            $bestByCar = [];

            foreach ($matrix as $categoryOffers) {
                foreach ((array) $categoryOffers as $offer) {
                    $alias = $offer['rateInfo']['alias'] ?? null;
                    $rank = self::PROTECTION_RANK[$alias] ?? PHP_INT_MAX;
                    $price = (float) ($offer['rateAmount'] ?? 0);

                    // One entry per physical car offering (same category, company and
                    // model), keeping whichever protection tier ranks best; ties broken
                    // by price so we still show the cheapest option at that tier.
                    $carKey = implode('|', [
                        $offer['category'] ?? '',
                        $offer['companyCode'] ?? $offer['companyName'] ?? '',
                        $offer['carModel'] ?? '',
                    ]);

                    $current = $bestByCar[$carKey] ?? null;

                    if ($current !== null && ($current['_rank'] < $rank || ($current['_rank'] === $rank && $current['_price'] <= $price))) {
                        continue;
                    }

                    $bestByCar[$carKey] = [
                        '_rank' => $rank,
                        '_price' => $price,
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
                        'precio' => $price,
                        'moneda' => $offer['currency'] ?? 'USD',
                        'dias' => $offer['days'] ?? null,
                        'proteccion' => $offer['rateInfo']['name'] ?? 'Protección Total',
                        'rate_identifier' => $offer['rateIdentifier'] ?? null,
                        'lugar_recogida' => $offer['pickUpAddress'] ?? null,
                        'lugar_entrega' => $offer['dropOffAddress'] ?? null,
                    ];
                }
            }

            $offers = array_values(array_map(function ($offer) {
                unset($offer['_rank'], $offer['_price']);

                return $offer;
            }, $bestByCar));

            usort($offers, fn ($a, $b) => $a['precio'] <=> $b['precio']);

            return ['success' => true, 'offers' => $offers];
        } catch (\Throwable $e) {
            Log::warning('agentcars search failed', ['error' => $e->getMessage()]);

            return ['success' => false, 'offers' => []];
        }
    }
}
