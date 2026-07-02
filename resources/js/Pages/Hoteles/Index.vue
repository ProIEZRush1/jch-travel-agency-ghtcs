<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const WHATSAPP = '5215594356241';
const waLink = `https://wa.me/${WHATSAPP}`;

const page = usePage();
const trialLocked = computed(() => page.props.trialLocked ?? false);

function toISODate(d) {
    return d.toISOString().slice(0, 10);
}
function addDays(iso, days) {
    const d = new Date(`${iso}T00:00:00`);
    d.setDate(d.getDate() + days);
    return toISODate(d);
}

const today = toISODate(new Date());
const minCheckin = addDays(today, 1);

const destinoQuery = ref('');
const destinoSuggestions = ref([]);
const destinoSelected = ref(null);
const destinoLoading = ref(false);

const checkin = ref(minCheckin);
const checkout = ref(addDays(minCheckin, 3));
const adultos = ref(2);

const loading = ref(false);
const searched = ref(false);
const results = ref([]);
const noches = ref(0);
const destinoResultado = ref('');
const errorMsg = ref('');
const sortBy = ref('precio');

let destinoTimer = null;

async function fetchSugerencias(query) {
    const res = await fetch(`/hoteles/sugerencias?q=${encodeURIComponent(query)}`);
    if (!res.ok) return [];
    return res.json();
}

function onDestinoInput() {
    destinoSelected.value = null;
    clearTimeout(destinoTimer);
    const q = destinoQuery.value.trim();
    if (q.length < 2) {
        destinoSuggestions.value = [];
        return;
    }
    destinoLoading.value = true;
    destinoTimer = setTimeout(async () => {
        destinoSuggestions.value = await fetchSugerencias(q);
        destinoLoading.value = false;
    }, 350);
}

function selectDestino(s) {
    destinoSelected.value = s;
    destinoQuery.value = s.nombre;
    destinoSuggestions.value = [];
}

const canSearch = computed(() => {
    return !!destinoSelected.value && checkin.value && checkout.value && adultos.value >= 1;
});

async function buscar() {
    if (!canSearch.value || loading.value) return;
    loading.value = true;
    searched.value = true;
    errorMsg.value = '';
    results.value = [];

    try {
        const params = new URLSearchParams({
            destino: destinoSelected.value.codigo,
            checkin: checkin.value,
            checkout: checkout.value,
            adultos: adultos.value,
        });
        const res = await fetch(`/hoteles/buscar?${params.toString()}`);
        const data = await res.json();
        if (data.success && data.hoteles?.length) {
            results.value = data.hoteles;
            noches.value = data.noches;
            destinoResultado.value = data.destino;
        } else if (data.success) {
            errorMsg.value = 'No encontramos hoteles disponibles para ese destino y fechas. Intenta con otra búsqueda.';
        } else {
            errorMsg.value = 'No pudimos completar la búsqueda en este momento. Intenta de nuevo en unos minutos.';
        }
    } catch (e) {
        errorMsg.value = 'Ocurrió un error al buscar. Intenta de nuevo.';
    } finally {
        loading.value = false;
    }
}

const filteredResults = computed(() => {
    const list = [...results.value];
    if (sortBy.value === 'precio') {
        list.sort((a, b) => a.precio_total - b.precio_total);
    } else if (sortBy.value === 'estrellas') {
        list.sort((a, b) => b.estrellas - a.estrellas);
    } else if (sortBy.value === 'ahorro') {
        list.sort((a, b) => b.ahorro_pct - a.ahorro_pct);
    }
    return list;
});

function money(n) {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN', maximumFractionDigits: 0 }).format(n);
}

function whatsappReserva(h) {
    const msg = `Hola, me interesa reservar ${h.nombre} en ${destinoResultado.value} — ${noches.value} noche(s), ${adultos.value} adulto(s).`;
    return `${waLink}?text=${encodeURIComponent(msg)}`;
}
</script>

<template>
    <Head title="Hoteles — JCH Travel Agency" />

    <div class="min-h-screen bg-slate-50 text-slate-800">
        <!-- Header -->
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 sm:px-6">
                <a href="/hoteles" class="flex items-center gap-3">
                    <img src="/brand-logo.jpeg" alt="JCH Travel Agency" class="h-12 w-auto object-contain" />
                </a>
                <nav class="hidden items-center gap-6 text-sm font-medium text-slate-600 sm:flex">
                    <span class="flex items-center gap-1.5 rounded-full bg-teal-50 px-3 py-1 text-teal-700">
                        🏨 Hoteles
                    </span>
                    <a href="/autos" class="flex items-center gap-1.5 text-slate-600 hover:text-teal-700">
                        🚗 Autos
                    </a>
                    <a :href="waLink" target="_blank" rel="noopener" class="flex items-center gap-1.5 text-slate-600 hover:text-teal-700">
                        💬 Contáctanos por WhatsApp
                    </a>
                </nav>
            </div>
        </header>

        <!-- Hero + búsqueda -->
        <section class="border-b border-slate-200 bg-gradient-to-b from-[#003580] to-[#0a4faa] px-4 py-10 text-white sm:px-6">
            <div class="mx-auto max-w-6xl">
                <p class="text-sm font-semibold uppercase tracking-wide text-teal-300">JCH Travel Agency</p>
                <h1 class="mt-1 text-2xl font-bold sm:text-3xl">Encuentra el mejor hotel para tu viaje</h1>
                <p class="mt-1 text-sm text-blue-100">Comparamos tarifa de agente contra la tarifa pública — tú ves el precio, tu cliente el ahorro.</p>

                <!-- Search card -->
                <div class="mt-6 rounded-2xl bg-white p-4 text-slate-800 shadow-xl sm:p-6">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <!-- Destino -->
                        <div class="relative lg:col-span-2">
                            <label class="mb-1 block text-xs font-semibold text-slate-500">Destino</label>
                            <div class="flex items-center rounded-lg border border-slate-300 px-3 focus-within:border-teal-500 focus-within:ring-1 focus-within:ring-teal-500">
                                <span class="text-slate-400">🔍</span>
                                <input
                                    v-model="destinoQuery"
                                    @input="onDestinoInput"
                                    type="text"
                                    placeholder="Ciudad o destino (ej. Cancún, Tulum...)"
                                    class="w-full border-0 bg-transparent px-2 py-2.5 text-sm focus:outline-none focus:ring-0"
                                />
                            </div>
                            <div
                                v-if="destinoSuggestions.length || destinoLoading"
                                class="absolute z-20 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-lg"
                            >
                                <div v-if="destinoLoading" class="px-3 py-2 text-sm text-slate-400">Buscando…</div>
                                <button
                                    v-for="s in destinoSuggestions"
                                    :key="s.codigo"
                                    type="button"
                                    @click="selectDestino(s)"
                                    class="block w-full px-3 py-2 text-left text-sm hover:bg-teal-50"
                                >
                                    🏨 {{ s.nombre }}
                                </button>
                            </div>
                        </div>

                        <!-- Check-in -->
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-500">Check-in</label>
                            <input v-model="checkin" type="date" :min="minCheckin" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500" />
                        </div>

                        <!-- Check-out -->
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-500">Check-out</label>
                            <input v-model="checkout" type="date" :min="checkin" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500" />
                        </div>

                        <!-- Adultos -->
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-500">Adultos</label>
                            <select v-model.number="adultos" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                                <option v-for="n in 8" :key="n" :value="n">{{ n }} adulto{{ n > 1 ? 's' : '' }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
                        <p class="text-xs text-slate-500">✅ Mostramos tarifa de agente vs. tarifa pública para que veas tu margen al instante.</p>
                        <button
                            type="button"
                            @click="buscar"
                            :disabled="!canSearch || loading"
                            class="flex w-full items-center justify-center gap-2 rounded-lg bg-teal-600 px-8 py-3 text-sm font-bold text-white shadow-md transition hover:bg-teal-700 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
                        >
                            <span v-if="loading">Buscando…</span>
                            <span v-else>🔎 Buscar hoteles</span>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Resultados -->
        <section class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
            <div v-if="!searched" class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500">
                <p class="text-4xl">🏨</p>
                <p class="mt-2 font-medium">Elige destino, fechas y número de adultos para ver tarifas de agente vs. tarifa pública.</p>
            </div>

            <div v-else-if="loading" class="rounded-xl border border-slate-200 bg-white p-10 text-center text-slate-500">
                <p class="text-3xl">⏳</p>
                <p class="mt-2 font-medium">Comparando tarifas de hoteles…</p>
            </div>

            <div v-else-if="errorMsg" class="rounded-xl border border-red-200 bg-red-50 p-8 text-center text-red-700">
                {{ errorMsg }}
            </div>

            <div v-else>
                <div class="mb-4 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                    <h2 class="text-lg font-bold text-slate-800">{{ filteredResults.length }} hoteles en {{ destinoResultado }} · {{ noches }} noche{{ noches > 1 ? 's' : '' }}</h2>
                    <select v-model="sortBy" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="precio">Ordenar: menor precio</option>
                        <option value="estrellas">Ordenar: categoría</option>
                        <option value="ahorro">Ordenar: mayor ahorro</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <article
                        v-for="(h, i) in filteredResults"
                        :key="i"
                        class="flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md"
                    >
                        <div class="flex h-24 items-center justify-center bg-gradient-to-br from-[#003580] to-teal-600 p-3 text-white">
                            <span class="text-4xl">🏨</span>
                        </div>
                        <div class="flex flex-1 flex-col p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-teal-700">
                                <span v-for="s in h.estrellas" :key="s">⭐</span> · {{ h.zona }}
                            </p>
                            <p class="mt-0.5 font-semibold text-slate-800">{{ h.nombre }}</p>

                            <div class="mt-3 flex flex-wrap gap-1.5">
                                <span v-for="a in h.amenidades" :key="a" class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-500">{{ a }}</span>
                            </div>

                            <div class="mt-3 inline-flex w-fit items-center gap-1 rounded-full bg-teal-50 px-2.5 py-1 text-xs font-medium text-teal-700">
                                💰 Ahorras {{ h.ahorro_pct }}% vs. tarifa pública
                            </div>

                            <div class="mt-auto pt-4">
                                <div class="flex items-end justify-between">
                                    <div>
                                        <p class="text-xs text-slate-400 line-through">{{ money(h.precio_normal_total) }}</p>
                                        <p class="text-xs text-slate-400">Total ({{ h.noches }} noches, tarifa de agente)</p>
                                        <p class="text-xl font-bold text-slate-900">{{ money(h.precio_total) }}</p>
                                    </div>
                                    <a
                                        :href="whatsappReserva(h)"
                                        target="_blank"
                                        rel="noopener"
                                        class="rounded-lg bg-[#003580] px-4 py-2 text-xs font-bold text-white hover:bg-[#00285f]"
                                    >
                                        Reservar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <!-- Vuelos y paquetes vía Expedia TAAP -->
        <section class="mx-auto max-w-6xl px-4 pb-10 sm:px-6">
            <div class="rounded-xl border border-slate-200 bg-white p-6 text-center shadow-sm sm:p-8">
                <h3 class="text-lg font-bold text-slate-800">✈️ ¿Buscas vuelos o paquetes?</h3>
                <p class="mt-2 text-sm text-slate-500">
                    Accede a tu portal Expedia TAAP con tarifa de agente para vuelos y paquetes armados.
                </p>

                <div v-if="trialLocked" class="mx-auto mt-6 max-w-md rounded-lg border border-amber-200 bg-amber-50 p-4">
                    <p class="text-sm font-semibold text-amber-800">🔒 Acceso disponible al confirmar tu proyecto</p>
                    <p class="mt-1 text-xs text-amber-700">Al activar el sistema se conecta directamente con tu portal Expedia TAAP.</p>
                </div>
                <a
                    v-else
                    href="https://www.expediataap.mx"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="mt-6 inline-flex items-center gap-2 rounded-lg bg-[#003580] px-8 py-3 text-sm font-bold text-white transition hover:bg-[#0071c2]"
                >
                    Ir a Expedia TAAP
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </a>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-slate-200 bg-white py-6">
            <div class="mx-auto flex max-w-6xl flex-col items-center gap-2 px-4 text-center text-sm text-slate-500 sm:px-6">
                <p>
                    Desarrollado por
                    <a :href="waLink" target="_blank" rel="noopener" class="font-semibold text-teal-700 hover:underline">Overcloud</a>
                </p>
                <a :href="waLink" target="_blank" rel="noopener" class="font-medium text-[#003580] hover:underline">
                    ¿Quieres tu sitio? Escríbenos por WhatsApp
                </a>
            </div>
        </footer>
    </div>
</template>
