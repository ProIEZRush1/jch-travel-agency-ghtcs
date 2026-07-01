<?php

namespace App\Services;

use App\Models\BotContact;
use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\Plan;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BotEngine
{
    private const STEP_NEW        = 'new';
    private const STEP_CHOOSING   = 'choosing';
    private const STEP_DETAILS    = 'details';
    private const STEP_CONFIRMING = 'confirming';
    private const STEP_DONE       = 'done';
    private const STEP_HUMAN      = 'human';

    public function __construct(private GatewayClient $gateway) {}

    public function handle(string $from, ?string $fromName, string $text): void
    {
        $contact = BotContact::firstOrCreate(['phone' => $from]);

        if (filled($fromName) && $contact->name !== $fromName) {
            $contact->name = $fromName;
            $contact->save();
        }

        $normalized = Str::lower(trim($text));

        if ($this->wantsHuman($normalized)) {
            if ($contact->step !== self::STEP_HUMAN) {
                $this->setStep($contact, self::STEP_HUMAN);
                $this->reply($from, $this->copyHandoff());
            }
            return;
        }

        if ($contact->step === self::STEP_HUMAN) {
            return;
        }

        if (in_array($normalized, ['menu', 'menú', 'inicio', 'hola', 'hi', 'hey'], true)) {
            $this->setStep($contact, self::STEP_NEW);
        }

        match ($contact->step) {
            self::STEP_CHOOSING   => $this->onChoosing($contact, $from, $fromName, $normalized),
            self::STEP_DETAILS    => $this->onDetails($contact, $from, $fromName, $text),
            self::STEP_CONFIRMING => $this->onConfirming($contact, $from, $fromName, $normalized),
            self::STEP_DONE       => $this->onDone($contact, $from),
            default               => $this->onNew($contact, $from),
        };
    }

    private function onNew(BotContact $contact, string $from): void
    {
        $plans = $this->activePlans();
        if ($plans->isEmpty()) {
            $this->reply($from, $this->copyNoPlans());
            return;
        }

        $this->setStep($contact, self::STEP_CHOOSING);
        $this->reply($from, $this->copyGreeting($contact->name) . $this->planList($plans) . $this->copyAskChoice());
    }

    private function onChoosing(BotContact $contact, string $from, ?string $fromName, string $text): void
    {
        $plans = $this->activePlans();
        if ($plans->isEmpty()) {
            $this->reply($from, $this->copyNoPlans());
            return;
        }

        $plan = $this->matchPlan($plans, $text);
        if (!$plan) {
            $this->reply($from, $this->copyNoMatch() . $this->planList($plans) . $this->copyAskChoice());
            return;
        }

        $data = $contact->data ?? [];
        $data['plan_id'] = $plan->id;
        $data['plan_nombre'] = $plan->nombre;
        $contact->data = $data;
        $contact->step = self::STEP_DETAILS;
        $contact->save();

        $this->reply($from, $this->copyAskDetails($plan));
    }

    private function onDetails(BotContact $contact, string $from, ?string $fromName, string $text): void
    {
        $data = $contact->data ?? [];
        $planId = $data['plan_id'] ?? null;
        $plan = $planId ? Plan::find($planId) : null;

        $data['detalles'] = $text;
        $contact->data = $data;
        $contact->save();

        Pedido::create([
            'bot_contact_id' => $contact->id,
            'plan_id'        => $planId,
            'cliente'        => $fromName ?: $contact->name,
            'telefono'       => $from,
            'estado'         => 'pendiente',
        ]);

        $this->setStep($contact, self::STEP_CONFIRMING);
        $this->reply($from, $this->copyConfirmPrompt($plan, $text));
    }

    private function onConfirming(BotContact $contact, string $from, ?string $fromName, string $text): void
    {
        if ($this->isYes($text)) {
            $pedido = $this->pendingPedido($contact);
            if ($pedido) {
                $pedido->update(['estado' => 'confirmado']);
            }

            Cliente::updateOrCreate(
                ['telefono' => $from],
                ['nombre' => $fromName ?: $contact->name],
            );

            $this->setStep($contact, self::STEP_DONE);
            $this->reply($from, $this->copyConfirmed());
            return;
        }

        if ($this->isNo($text)) {
            $this->setStep($contact, self::STEP_CHOOSING);
            $plans = $this->activePlans();
            $this->reply($from, $this->copyChangedMind() . $this->planList($plans) . $this->copyAskChoice());
            return;
        }

        $this->reply($from, $this->copyConfirmRetry());
    }

    private function onDone(BotContact $contact, string $from): void
    {
        $this->reply($from, $this->copyAlreadyDone());
    }

    private function activePlans(): Collection
    {
        return Plan::where('activo', true)->orderBy('orden')->orderBy('id')->get();
    }

    private function matchPlan(Collection $plans, string $text): ?Plan
    {
        $text = trim($text);
        if ($text !== '' && ctype_digit($text)) {
            return $plans->values()->get(((int) $text) - 1);
        }
        foreach ($plans as $plan) {
            $name = Str::lower(trim($plan->nombre));
            if ($name !== '' && (Str::contains($text, $name) || Str::contains($name, $text))) {
                return $plan;
            }
        }
        return null;
    }

    private function pendingPedido(BotContact $contact): ?Pedido
    {
        $planId = $contact->data['plan_id'] ?? null;
        return $contact->pedidos()
            ->where('estado', 'pendiente')
            ->when($planId, fn($q) => $q->where('plan_id', $planId))
            ->latest('id')
            ->first()
            ?? $contact->pedidos()->where('estado', 'pendiente')->latest('id')->first();
    }

    // ---- copy (editable Spanish strings) --------------------------------

    private function copyGreeting(?string $name): string
    {
        $greeting = $name ? "¡Hola, {$name}! 👋✈️" : '¡Hola! 👋✈️';
        return $greeting . " Soy el asistente de *" . config('app.name') . "*.\n\n"
            . "¿En qué puedo ayudarte hoy? Estos son nuestros servicios:\n\n";
    }

    private function planList(Collection $plans): string
    {
        $lines = $plans->values()->map(function (Plan $plan, int $i) {
            $line = ($i + 1) . '. *' . $plan->nombre . '*';
            if (filled($plan->descripcion)) {
                $line .= "\n   " . $plan->descripcion;
            }
            return $line;
        });
        return $lines->implode("\n\n");
    }

    private function copyAskChoice(): string
    {
        return "\n\n¿Cuál te interesa? Responde con el *número* o el *nombre* del servicio 🙂";
    }

    private function copyNoMatch(): string
    {
        return "No identifiqué esa opción 🤔 Aquí están nuestros servicios:\n\n";
    }

    private function copyAskDetails(Plan $plan): string
    {
        return "¡Excelente elección! 🌟 *{$plan->nombre}*\n\n"
            . "Para preparar tu cotización, cuéntame:\n"
            . "📍 *Destino*\n"
            . "📅 *Fechas* (salida y regreso)\n"
            . "👥 *Número de personas*\n\n"
            . "Puedes escribirlo todo en un solo mensaje 😊";
    }

    private function copyConfirmPrompt(?Plan $plan, string $detalles): string
    {
        $servicio = $plan ? $plan->nombre : 'el servicio seleccionado';
        return "Perfecto ✅ Aquí tu resumen:\n\n"
            . "*Servicio:* {$servicio}\n"
            . "*Detalles:* {$detalles}\n\n"
            . "¿Confirmamos que un asesor de *" . config('app.name') . "* te contacte para afinar tu cotización?\n"
            . "Responde *sí* para confirmar o *no* para cambiar el servicio.";
    }

    private function copyConfirmRetry(): string
    {
        return "Para continuar, respóndeme *sí* para confirmar o *no* para elegir otro servicio 🙂";
    }

    private function copyChangedMind(): string
    {
        return "Sin problema 🙌 ¿Qué otro servicio te interesa?\n\n";
    }

    private function copyConfirmed(): string
    {
        return "¡Listo! ✅ Registramos tu solicitud.\n\n"
            . "Un asesor de *" . config('app.name') . "* te contactará en breve para enviarte tu cotización personalizada 🙌\n\n"
            . "¿Necesitas algo más? Escribe *menu* para ver los servicios nuevamente.";
    }

    private function copyAlreadyDone(): string
    {
        return "Ya tenemos tu solicitud registrada ✅ Un asesor te contactará pronto 🙌\n\n"
            . "Si quieres hacer otra consulta, escribe *menu*.";
    }

    private function copyNoPlans(): string
    {
        return "Gracias por escribir a *" . config('app.name') . "* 🙌 En un momento un asesor te atiende personalmente. ✈️";
    }

    private function copyHandoff(): string
    {
        return "¡Claro que sí! 🙌 Te pongo en contacto con uno de nuestros agentes de viaje. "
            . "En breve te atienden personalmente. ¡Que tengas un excelente día! ✈️😊";
    }

    // ---- matchers -------------------------------------------------------

    private function isYes(string $text): bool
    {
        if ($this->isNo($text)) return false;
        if (preg_match('/\b(s[ií]|sip|sale|va|dale|ok|okay|claro|listo|correcto|adelante|confirm\w*|acept\w*|procede)\b/u', $text)) return true;
        return Str::contains($text, ['de acuerdo', 'me late', 'por supuesto', 'está bien', 'esta bien', 'hágale', 'hagale', 'perfecto']);
    }

    private function isNo(string $text): bool
    {
        return (bool) preg_match('/\b(no|nel|nop|nope|todav[ií]a no|a[uú]n no|aun no|por ahora no|ahorita no|mejor no|otro|otra|cambiar)\b/u', $text);
    }

    private function wantsHuman(string $text): bool
    {
        $text = ' ' . trim($text) . ' ';
        return (bool) preg_match('/(asesor real|un asesor|una asesora|atenci[oó]n humana|'
            . '(hablar|hablo|comunicar|comunicarme|pasar|pasas?|p[aá]same|contactar|conectar|con[eé]ctame) con (un|una|alg[uú]ien|el|la)?\s*(humano|persona|asesor|asesora|agente|ejecutiv|alguien real|alguien|due[ñn]o|encargad)|'
            . 'quiero (un|una|hablar con|que me atienda un|que me atienda una)?\s*(humano|persona|asesor|asesora|agente|alguien real)|'
            . 'prefiero (un|una|hablar con|que me atienda)?\s*(humano|persona|asesor|asesora|agente|alguien)|'
            . 'no quiero (hablar con)?\s*(un|una)?\s*(bot|ia|robot|inteligencia artificial|asistente))/u', $text);
    }

    private function setStep(BotContact $contact, string $step): void
    {
        $contact->step = $step;
        $contact->save();
    }

    private function formatPrice(int $cents): string
    {
        return '$' . number_format($cents / 100, 0, '.', ',') . ' MXN';
    }

    private function reply(string $to, string $message): void
    {
        $this->gateway->send($to, $message);
    }
}
