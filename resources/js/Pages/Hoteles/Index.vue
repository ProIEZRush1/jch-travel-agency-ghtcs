<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const trialLocked = computed(() => page.props.trialLocked ?? false);
</script>

<template>
    <Head title="Hoteles & Vuelos" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold text-slate-800">✈️ Hoteles, Vuelos & Paquetes — Expedia TAAP</h2>
        </template>

        <div class="mx-auto max-w-4xl space-y-6">
            <!-- Info card -->
            <div class="rounded-xl border border-[#0071c2]/20 bg-blue-50 p-6">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#003580] text-white text-lg">✈️</div>
                    <div>
                        <h3 class="font-bold text-[#003580]">Acceso a Expedia TAAP — Tarifa de Agente</h3>
                        <p class="mt-1 text-sm text-slate-600">
                            Como agencia registrada en Expedia TAAP, tienes acceso a tarifas de agente que pueden ser
                            <strong class="text-[#003580]">hasta un 30% más bajas</strong> que las tarifas públicas.
                            Tu margen = la diferencia entre lo que pagas tú (tarifa agente) y lo que cobra el cliente (tarifa normal).
                        </p>
                    </div>
                </div>
            </div>

            <!-- CTA cards -->
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-white p-6 text-center shadow-sm">
                    <div class="mb-3 text-4xl">🏨</div>
                    <h4 class="font-bold text-slate-800">Hoteles</h4>
                    <p class="mt-1 text-xs text-slate-500">Miles de propiedades con tarifa de agente</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-6 text-center shadow-sm">
                    <div class="mb-3 text-4xl">✈️</div>
                    <h4 class="font-bold text-slate-800">Vuelos</h4>
                    <p class="mt-1 text-xs text-slate-500">Nacionales e internacionales con mejores precios</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-6 text-center shadow-sm">
                    <div class="mb-3 text-4xl">🌴</div>
                    <h4 class="font-bold text-slate-800">Paquetes</h4>
                    <p class="mt-1 text-xs text-slate-500">Combina vuelo + hotel con descuento adicional</p>
                </div>
            </div>

            <!-- Access button -->
            <div class="rounded-xl border border-slate-200 bg-white p-8 text-center shadow-sm">
                <h3 class="text-xl font-bold text-slate-800">Portal Expedia TAAP</h3>
                <p class="mt-2 text-sm text-slate-500">
                    Busca, compara y reserva hoteles, vuelos y paquetes con tarifa exclusiva para agentes de viaje.
                </p>

                <div v-if="trialLocked" class="mt-6 rounded-lg border border-amber-200 bg-amber-50 p-4">
                    <p class="text-sm font-semibold text-amber-800">🔒 Acceso disponible al confirmar tu proyecto</p>
                    <p class="mt-1 text-xs text-amber-700">Al activar el sistema se conecta directamente con tu portal Expedia TAAP.</p>
                </div>
                <a
                    v-else
                    href="https://www.expediataap.mx"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="mt-6 inline-flex items-center gap-2 rounded-lg bg-[#003580] px-8 py-3 text-base font-bold text-white transition hover:bg-[#0071c2]"
                >
                    Ir a Expedia TAAP
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </a>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
