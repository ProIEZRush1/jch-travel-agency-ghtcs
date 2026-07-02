<?php

namespace App\Services;

/**
 * Inventario manual de hoteles JCH Travel Agency.
 *
 * JCH no tiene todavía una API de hoteles conectada (Expedia TAAP no expone
 * búsqueda pública embebible, solo su portal de agente). Mientras esa
 * integración llega, este catálogo cubre el módulo de "inventario manual"
 * del alcance: hoteles reales y conocidos por destino, con tarifa de agente
 * (lo que paga JCH) y tarifa normal/pública (lo que ve un cliente directo),
 * para poder mostrar el ahorro/margen tal como pidió el cliente.
 */
class HotelCatalog
{
    /**
     * @return array<string, array{nombre: string, hoteles: array<int, array<string, mixed>>}>
     */
    public static function destinos(): array
    {
        return [
            'cancun' => [
                'nombre' => 'Cancún',
                'hoteles' => [
                    ['nombre' => 'Moon Palace Cancún', 'estrellas' => 5, 'zona' => 'Zona Hotelera', 'amenidades' => ['Todo incluido', 'Playa privada', 'Spa', 'Golf'], 'tarifa_agente' => 4200, 'tarifa_normal' => 5600],
                    ['nombre' => 'Grand Fiesta Americana Coral Beach Cancún', 'estrellas' => 5, 'zona' => 'Zona Hotelera', 'amenidades' => ['Todo incluido', 'Frente al mar', 'Spa'], 'tarifa_agente' => 5100, 'tarifa_normal' => 6800],
                    ['nombre' => 'Hyatt Ziva Cancún', 'estrellas' => 5, 'zona' => 'Zona Hotelera', 'amenidades' => ['Todo incluido', 'Alberca infinity', 'Kids club'], 'tarifa_agente' => 4700, 'tarifa_normal' => 6300],
                    ['nombre' => 'Krystal Cancún', 'estrellas' => 4, 'zona' => 'Zona Hotelera', 'amenidades' => ['Todo incluido', 'Playa', 'Bares'], 'tarifa_agente' => 2600, 'tarifa_normal' => 3400],
                    ['nombre' => 'Ibis Cancún Centro', 'estrellas' => 3, 'zona' => 'Centro', 'amenidades' => ['Desayuno incluido', 'Wifi', 'Cerca de Ado'], 'tarifa_agente' => 1050, 'tarifa_normal' => 1350],
                ],
            ],
            'playa-del-carmen' => [
                'nombre' => 'Playa del Carmen',
                'hoteles' => [
                    ['nombre' => 'Grand Hyatt Playa del Carmen', 'estrellas' => 5, 'zona' => 'Centro', 'amenidades' => ['Frente al mar', 'Rooftop', 'Spa'], 'tarifa_agente' => 4400, 'tarifa_normal' => 5900],
                    ['nombre' => 'Hotel Xcaret México', 'estrellas' => 5, 'zona' => 'Riviera Maya', 'amenidades' => ['Todo incluido', 'Parques incluidos', 'Río lazy'], 'tarifa_agente' => 6200, 'tarifa_normal' => 8100],
                    ['nombre' => 'Catalonia Riviera Maya', 'estrellas' => 4, 'zona' => 'Riviera Maya', 'amenidades' => ['Todo incluido', 'Playa', 'Deportes acuáticos'], 'tarifa_agente' => 2900, 'tarifa_normal' => 3800],
                    ['nombre' => 'Hotel Deseo Playa del Carmen', 'estrellas' => 4, 'zona' => 'Quinta Avenida', 'amenidades' => ['Boutique', 'Rooftop bar', 'Desayuno'], 'tarifa_agente' => 2350, 'tarifa_normal' => 3100],
                ],
            ],
            'tulum' => [
                'nombre' => 'Tulum',
                'hoteles' => [
                    ['nombre' => 'Belmond Maroma', 'estrellas' => 5, 'zona' => 'Riviera Maya', 'amenidades' => ['Playa privada', 'Spa', 'Selva'], 'tarifa_agente' => 7800, 'tarifa_normal' => 10200],
                    ['nombre' => 'Habitas Tulum', 'estrellas' => 5, 'zona' => 'Zona Hotelera Tulum', 'amenidades' => ['Eco-boutique', 'Playa', 'Yoga'], 'tarifa_agente' => 5300, 'tarifa_normal' => 7000],
                    ['nombre' => 'Papaya Playa Project', 'estrellas' => 4, 'zona' => 'Zona Hotelera Tulum', 'amenidades' => ['Frente al mar', 'Restaurante', 'Bar de playa'], 'tarifa_agente' => 3600, 'tarifa_normal' => 4700],
                ],
            ],
            'los-cabos' => [
                'nombre' => 'Los Cabos',
                'hoteles' => [
                    ['nombre' => 'Grand Velas Los Cabos', 'estrellas' => 5, 'zona' => 'Corredor Turístico', 'amenidades' => ['Todo incluido', 'Spa', 'Vista al mar'], 'tarifa_agente' => 8900, 'tarifa_normal' => 11700],
                    ['nombre' => 'Riu Palace Cabo San Lucas', 'estrellas' => 5, 'zona' => 'Médano Beach', 'amenidades' => ['Todo incluido', 'Playa', 'Vida nocturna'], 'tarifa_agente' => 4100, 'tarifa_normal' => 5400],
                    ['nombre' => 'Hotel Tesoro Los Cabos', 'estrellas' => 4, 'zona' => 'Marina Cabo San Lucas', 'amenidades' => ['Marina', 'Alberca', 'Restaurantes'], 'tarifa_agente' => 2800, 'tarifa_normal' => 3700],
                ],
            ],
            'puerto-vallarta' => [
                'nombre' => 'Puerto Vallarta',
                'hoteles' => [
                    ['nombre' => 'Grand Velas Riviera Nayarit', 'estrellas' => 5, 'zona' => 'Riviera Nayarit', 'amenidades' => ['Todo incluido', 'Playa privada', 'Spa'], 'tarifa_agente' => 7600, 'tarifa_normal' => 9900],
                    ['nombre' => 'Marriott Puerto Vallarta Resort & Spa', 'estrellas' => 5, 'zona' => 'Zona Hotelera', 'amenidades' => ['Frente al mar', 'Alberca', 'Spa'], 'tarifa_agente' => 3900, 'tarifa_normal' => 5100],
                    ['nombre' => 'Buenaventura Grand Hotel & Spa', 'estrellas' => 4, 'zona' => 'Malecón', 'amenidades' => ['Playa', 'Malecón a pie', 'Todo incluido'], 'tarifa_agente' => 2400, 'tarifa_normal' => 3150],
                ],
            ],
            'ciudad-de-mexico' => [
                'nombre' => 'Ciudad de México',
                'hoteles' => [
                    ['nombre' => 'St. Regis Mexico City', 'estrellas' => 5, 'zona' => 'Paseo de la Reforma', 'amenidades' => ['Lujo', 'Spa', 'Vista a Reforma'], 'tarifa_agente' => 6900, 'tarifa_normal' => 9200],
                    ['nombre' => 'Camino Real Polanco', 'estrellas' => 5, 'zona' => 'Polanco', 'amenidades' => ['Arquitectura icónica', 'Alberca', 'Spa'], 'tarifa_agente' => 3800, 'tarifa_normal' => 5000],
                    ['nombre' => 'Hotel Histórico Central', 'estrellas' => 4, 'zona' => 'Centro Histórico', 'amenidades' => ['Boutique', 'Terraza', 'Desayuno'], 'tarifa_agente' => 1800, 'tarifa_normal' => 2350],
                    ['nombre' => 'Fiesta Inn Centro Histórico', 'estrellas' => 3, 'zona' => 'Centro Histórico', 'amenidades' => ['Desayuno incluido', 'Wifi', 'Gimnasio'], 'tarifa_agente' => 1250, 'tarifa_normal' => 1650],
                ],
            ],
            'cozumel' => [
                'nombre' => 'Cozumel',
                'hoteles' => [
                    ['nombre' => 'Presidente InterContinental Cozumel', 'estrellas' => 5, 'zona' => 'Zona Sur', 'amenidades' => ['Arrecife privado', 'Buceo', 'Spa'], 'tarifa_agente' => 4600, 'tarifa_normal' => 6100],
                    ['nombre' => 'Occidental Cozumel', 'estrellas' => 4, 'zona' => 'Costa Sur', 'amenidades' => ['Todo incluido', 'Buceo', 'Playa'], 'tarifa_agente' => 2700, 'tarifa_normal' => 3550],
                ],
            ],
            'guadalajara' => [
                'nombre' => 'Guadalajara',
                'hoteles' => [
                    ['nombre' => 'Hilton Guadalajara', 'estrellas' => 5, 'zona' => 'Zona Financiera', 'amenidades' => ['Alberca', 'Gimnasio', 'Ejecutivo'], 'tarifa_agente' => 2200, 'tarifa_normal' => 2900],
                    ['nombre' => 'Hotel Demetria', 'estrellas' => 4, 'zona' => 'Providencia', 'amenidades' => ['Boutique', 'Arte', 'Restaurante'], 'tarifa_agente' => 1900, 'tarifa_normal' => 2500],
                ],
            ],
            'monterrey' => [
                'nombre' => 'Monterrey',
                'hoteles' => [
                    ['nombre' => 'Camino Real Monterrey', 'estrellas' => 5, 'zona' => 'Valle Oriente', 'amenidades' => ['Alberca', 'Spa', 'Ejecutivo'], 'tarifa_agente' => 2500, 'tarifa_normal' => 3300],
                    ['nombre' => 'Holiday Inn Monterrey Centro', 'estrellas' => 4, 'zona' => 'Centro', 'amenidades' => ['Desayuno incluido', 'Wifi', 'Gimnasio'], 'tarifa_agente' => 1450, 'tarifa_normal' => 1900],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{codigo: string, nombre: string}>
     */
    public static function sugerir(string $query): array
    {
        $query = self::normalizar($query);

        $out = [];
        foreach (self::destinos() as $codigo => $destino) {
            if (str_contains(self::normalizar($destino['nombre']), $query) || str_contains($codigo, $query)) {
                $out[] = ['codigo' => $codigo, 'nombre' => $destino['nombre']];
            }
        }

        return $out;
    }

    public static function destino(string $codigo): ?array
    {
        return self::destinos()[$codigo] ?? null;
    }

    private static function normalizar(string $texto): string
    {
        $texto = mb_strtolower(trim($texto));
        $texto = strtr($texto, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n',
        ]);

        return $texto;
    }
}
