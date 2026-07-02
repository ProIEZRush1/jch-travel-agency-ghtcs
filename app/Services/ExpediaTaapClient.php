<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

/**
 * Live client for JCH's Expedia TAAP agent portal. TAAP has no public search API — it's an
 * authenticated agent portal — so this drives a real headless Chromium session (login +
 * search) through the Node/Playwright script in scripts/expedia-taap-search.mjs and returns
 * whatever it actually scraped.
 *
 * Absolute rule: this class NEVER fabricates a hotel or a price. Any failure (missing
 * credentials, login failure, timeout, a layout change on TAAP's side) surfaces as
 * success=false with an honest status so the UI tells the visitor to try again shortly
 * instead of showing invented rates.
 */
class ExpediaTaapClient
{
    private ?string $user;
    private ?string $pass;
    private string $baseUrl;
    private string $sessionPath;
    private string $scriptPath;

    public function __construct()
    {
        $this->user = config('services.expedia_taap.user') ?: null;
        $this->pass = config('services.expedia_taap.pass') ?: null;
        $this->baseUrl = rtrim((string) config('services.expedia_taap.url'), '/');
        $this->sessionPath = storage_path('app/private/expedia-taap-session.json');
        $this->scriptPath = base_path('scripts/expedia-taap-search.mjs');
    }

    /**
     * @return array{success:bool, status:string, hoteles:array<int,array<string,mixed>>, message:?string}
     */
    public function searchHotels(string $destino, string $checkin, string $checkout, int $adultos): array
    {
        if (! $this->user || ! $this->pass) {
            Log::warning('expedia taap: search skipped, EXPEDIA_TAAP_USER/EXPEDIA_TAAP_PASS not configured');

            return $this->unavailable();
        }

        try {
            $result = Process::path(base_path())
                ->timeout(75)
                ->env([
                    'EXPEDIA_TAAP_USER' => $this->user,
                    'EXPEDIA_TAAP_PASS' => $this->pass,
                    'EXPEDIA_TAAP_URL' => $this->baseUrl,
                ])
                ->run([
                    'node', $this->scriptPath,
                    $destino, $checkin, $checkout, (string) $adultos,
                    $this->sessionPath,
                ]);

            if (! $result->successful()) {
                Log::error('expedia taap: scraper process failed', [
                    'exit_code' => $result->exitCode(),
                    'stderr' => $result->errorOutput(),
                ]);

                return $this->unavailable();
            }

            $payload = json_decode($result->output(), true);

            if (! is_array($payload) || ($payload['success'] ?? false) !== true) {
                $error = is_array($payload) ? (string) ($payload['error'] ?? '') : '';

                if (str_contains($error, 'bot_check')) {
                    // Expedia served its anti-bot challenge. We deliberately do not try to
                    // defeat it (no stealth automation, no auto-solving) — see
                    // scripts/expedia-taap-search.mjs. Surface this honestly so the client
                    // knows to request official API/whitelist access from their Expedia rep
                    // instead of getting a generic "try again" forever.
                    Log::warning('expedia taap: blocked by anti-bot challenge, not attempting to bypass it', [
                        'destino' => $destino, 'checkin' => $checkin, 'checkout' => $checkout,
                    ]);

                    return $this->unavailable('Tu proveedor está pidiendo una verificación de seguridad en este momento. Nuestro equipo ya lo sabe; mientras tanto usa el botón "Ir a mi portal de agente" más abajo para cotizar directamente.');
                }

                Log::warning('expedia taap: scraper reported failure', [
                    'stdout' => $result->output(),
                    'stderr' => $result->errorOutput(),
                ]);

                return $this->unavailable();
            }

            $hoteles = collect($payload['hoteles'] ?? [])
                ->filter(fn ($h) => is_array($h) && filled($h['nombre'] ?? null))
                ->map(fn (array $h) => $this->normalize($h))
                ->values()
                ->all();

            if ($hoteles === []) {
                Log::info('expedia taap: search succeeded but returned zero hotels', [
                    'destino' => $destino, 'checkin' => $checkin, 'checkout' => $checkout,
                ]);

                return $this->unavailable('Tu proveedor no encontró hoteles para ese destino y esas fechas. Intenta con otro destino o rango.');
            }

            return [
                'success' => true,
                'status' => 'ok',
                'hoteles' => $hoteles,
                'message' => null,
            ];
        } catch (\Throwable $e) {
            Log::error('expedia taap: search exception', ['error' => $e->getMessage()]);

            return $this->unavailable();
        }
    }

    /** @return array<string, mixed> */
    private function normalize(array $h): array
    {
        return [
            'nombre' => (string) $h['nombre'],
            'estrellas' => is_numeric($h['estrellas'] ?? null) ? (float) $h['estrellas'] : null,
            'plan_alimentos' => filled($h['plan_alimentos'] ?? null) ? (string) $h['plan_alimentos'] : null,
            'precio_noche_texto' => filled($h['precio_noche_texto'] ?? null) ? (string) $h['precio_noche_texto'] : null,
            'precio_total_texto' => filled($h['precio_total_texto'] ?? null) ? (string) $h['precio_total_texto'] : null,
            'precio_total_valor' => $this->extraerNumero($h['precio_total_texto'] ?? null),
            'imagen' => filled($h['imagen'] ?? null) ? (string) $h['imagen'] : null,
        ];
    }

    /**
     * Best-effort numeric value parsed out of a scraped price string, used ONLY for sorting —
     * the raw scraped text (precio_*_texto) is always what gets displayed, so a parsing miss
     * here can never turn into an invented number shown to a visitor.
     */
    private function extraerNumero(?string $texto): ?float
    {
        if (! $texto) {
            return null;
        }

        $limpio = preg_replace('/[^\d.]/', '', str_replace(',', '', $texto));

        return filled($limpio) ? (float) $limpio : null;
    }

    /** @return array{success:bool, status:string, hoteles:array<int,mixed>, message:string} */
    private function unavailable(?string $message = null): array
    {
        return [
            'success' => false,
            'status' => 'unavailable',
            'hoteles' => [],
            'message' => $message ?? 'Estamos consultando tarifas en vivo con tu proveedor, intenta de nuevo en un momento.',
        ];
    }
}
