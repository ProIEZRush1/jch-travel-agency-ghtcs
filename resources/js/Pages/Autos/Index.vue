<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const WHATSAPP = '5215594356241';
const waLink = `https://wa.me/${WHATSAPP}`;

function toISODate(d) {
    return d.toISOString().slice(0, 10);
}
function addDays(iso, days) {
    const d = new Date(`${iso}T00:00:00`);
    d.setDate(d.getDate() + days);
    return toISODate(d);
}

const HORAS = Array.from({ length: 34 }, (_, i) => {
    const totalMin = 6 * 60 + i * 30; // 06:00 → 22:30
    const h = String(Math.floor(totalMin / 60)).padStart(2, '0');
    const m = String(totalMin % 60).padStart(2, '0');
    return `${h}:${m}`;
});

const today = toISODate(new Date());
const minPickup = addDays(today, 1);

const pickupQuery = ref('');
const pickupSuggestions = ref([]);
const pickupLoading = ref(false);
const pickupSelected = ref(null);

const dropoffQuery = ref('');
const dropoffSuggestions = ref([]);
const dropoffLoading = ref(false);
const dropoffSelected = ref(null);
const mismoLugar = ref(true);

const pickupDate = ref(minPickup);
const pickupHour = ref('10:00');
const dropoffDate = ref(addDays(minPickup, 3));
const dropoffHour = ref('10:00');

const loading = ref(false);
const searched = ref(false);
const results = ref([]);
const errorMsg = ref('');
const sortBy = ref('precio');

let pickupTimer = null;
let dropoffTimer = null;

async function fetchSugerencias(query) {
    const res = await fetch(`/autos/sugerencias?q=${encodeURIComponent(query)}`);
    if (!res.ok) return [];
    return res.json();
}

function onPickupInput() {
    pickupSelected.value = null;
    clearTimeout(pickupTimer);
    const q = pickupQuery.value.trim();
    if (q.length < 2) {
        pickupSuggestions.value = [];
        return;
    }
    pickupLoading.value = true;
    pickupTimer = setTimeout(async () => {
        pickupSuggestions.value = await fetchSugerencias(q);
        pickupLoading.value = false;
    }, 350);
}

function onDropoffInput() {
    dropoffSelected.value = null;
    clearTimeout(dropoffTimer);
    const q = dropoffQuery.value.trim();
    if (q.length < 2) {
        dropoffSuggestions.value = [];
        return;
    }
    dropoffLoading.value = true;
    dropoffTimer = setTimeout(async () => {
        dropoffSuggestions.value = await fetchSugerencias(q);
        dropoffLoading.value = false;
    }, 350);
}

function selectPickup(s) {
    pickupQuery.value = s.name;
    pickupSelected.value = s;
    pickupSuggestions.value = [];
    if (mismoLugar.value) {
        dropoffQuery.value = s.name;
        dropoffSelected.value = s;
    }
}

function selectDropoff(s) {
    dropoffQuery.value = s.name;
    dropoffSelected.value = s;
    dropoffSuggestions.value = [];
}

function onMismoLugarChange() {
    if (mismoLugar.value) {
        dropoffQuery.value = pickupQuery.value;
        dropoffSelected.value = pickupSelected.value;
        dropoffSuggestions.value = [];
    }
}

const canSearch = computed(() => {
    return !!pickupSelected.value
        && !!(mismoLugar.value ? pickupSelected.value : dropoffSelected.value)
        && pickupDate.value && dropoffDate.value
        && dropoffDate.value >= pickupDate.value;
});

async function buscar() {
    if (!canSearch.value || loading.value) return;
    loading.value = true;
    searched.value = true;
    errorMsg.value = '';
    results.value = [];
    pickupSuggestions.value = [];
    dropoffSuggestions.value = [];

    const dropoff = mismoLugar.value ? pickupSelected.value : dropoffSelected.value;

    try {
        const params = new URLSearchParams({
            pickup_code: pickupSelected.value.code,
            pickup_name: pickupSelected.value.name,
            dropoff_code: dropoff.code,
            dropoff_name: dropoff.name,
            pickup_date: pickupDate.value,
            pickup_hour: pickupHour.value,
            dropoff_date: dropoffDate.value,
            dropoff_hour: dropoffHour.value,
        });
        const res = await fetch(`/autos/buscar?${params.toString()}`);
        const data = await res.json();

        if (data.success && data.ofertas?.length) {
            results.value = data.ofertas;
        } else {
            errorMsg.value = 'No encontramos autos disponibles para esa búsqueda. Intenta con otras fechas o ubicación.';
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
        list.sort((a, b) => (a.precio ?? Infinity) - (b.precio ?? Infinity));
    } else if (sortBy.value === 'categoria') {
        list.sort((a, b) => (a.categoria ?? '').localeCompare(b.categoria ?? ''));
    }
    return list;
});

function whatsappReserva(o) {
    const precio = o.precio ? ` — ${o.moneda ?? 'USD'} ${o.precio}` : '';
    const msg = `Hola, me interesa rentar el auto ${o.modelo || o.categoria} (${o.compania}) del ${pickupDate.value} al ${dropoffDate.value}${precio}.`;
    return `${waLink}?text=${encodeURIComponent(msg)}`;
}

function onImgError(e) {
    e.target.style.display = 'none';
}
</script>

<template>
    <Head title="Renta de Autos — JCH Travel Agency" />

    <div class="min-h-screen bg-slate-50 text-slate-800">
        <!-- Header -->
        <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 sm:px-6">
                <a href="/autos" class="flex items-center gap-3">
                    <img src="/brand-logo.jpeg" alt="JCH Travel Agency" class="h-12 w-auto object-contain" />
                </a>
                <nav class="hidden items-center gap-6 text-sm font-medium text-slate-600 sm:flex">
                    <span class="flex items-center gap-1.5 rounded-full bg-teal-50 px-3 py-1 text-teal-700">
                        🚗 Renta de Autos
                    </span>
                    <a href="/hoteles" class="flex items-center gap-1.5 text-slate-600 hover:text-teal-700">
                        🏨 Hoteles
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
                <h1 class="mt-1 text-2xl font-bold sm:text-3xl">Encuentra el mejor auto para tu viaje</h1>
                <p class="mt-1 text-sm text-blue-100">Comparamos en vivo las tarifas de más de 50 compañías de renta — Dollar, Thrifty, Hertz, Enterprise y más.</p>

                <!-- Search card -->
                <div class="mt-6 rounded-2xl bg-white p-4 text-slate-800 shadow-xl sm:p-6">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <!-- Lugar de recogida -->
                        <div class="relative">
                            <label class="mb-1 block text-xs font-semibold text-slate-500">Lugar de recogida</label>
                            <div class="flex items-center rounded-lg border border-slate-300 px-3 focus-within:border-teal-500 focus-within:ring-1 focus-within:ring-teal-500">
                                <span class="text-slate-400">📍</span>
                                <input
                                    v-model="pickupQuery"
                                    @input="onPickupInput"
                                    type="text"
                                    placeholder="Ciudad o aeropuerto (ej. Cancún, CUN...)"
                                    class="w-full border-0 bg-transparent px-2 py-2.5 text-sm focus:outline-none focus:ring-0"
                                />
                            </div>
                            <div
                                v-if="pickupSuggestions.length || pickupLoading"
                                class="absolute z-20 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-lg"
                            >
                                <div v-if="pickupLoading" class="px-3 py-2 text-sm text-slate-400">Buscando…</div>
                                <button
                                    v-for="s in pickupSuggestions"
                                    :key="s.code"
                                    type="button"
                                    @click="selectPickup(s)"
                                    class="block w-full px-3 py-2 text-left text-sm hover:bg-teal-50"
                                >
                                    🚗 {{ s.name }}
                                </button>
                            </div>
                        </div>

                        <!-- Lugar de devolución -->
                        <div class="relative">
                            <label class="mb-1 block text-xs font-semibold text-slate-500">Lugar de devolución</label>
                            <div
                                class="flex items-center rounded-lg border border-slate-300 px-3"
                                :class="mismoLugar ? 'bg-slate-100' : 'focus-within:border-teal-500 focus-within:ring-1 focus-within:ring-teal-500'"
                            >
                                <span class="text-slate-400">📍</span>
                                <input
                                    v-model="dropoffQuery"
                                    @input="onDropoffInput"
                                    type="text"
                                    :disabled="mismoLugar"
                                    placeholder="Ciudad o aeropuerto"
                                    class="w-full border-0 bg-transparent px-2 py-2.5 text-sm focus:outline-none focus:ring-0 disabled:cursor-not-allowed"
                                />
                            </div>
                            <div
                                v-if="!mismoLugar && (dropoffSuggestions.length || dropoffLoading)"
                                class="absolute z-20 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-lg"
                            >
                                <div v-if="dropoffLoading" class="px-3 py-2 text-sm text-slate-400">Buscando…</div>
                                <button
                                    v-for="s in dropoffSuggestions"
                                    :key="s.code"
                                    type="button"
                                    @click="selectDropoff(s)"
                                    class="block w-full px-3 py-2 text-left text-sm hover:bg-teal-50"
                                >
                                    🚗 {{ s.name }}
                                </button>
                            </div>
                            <label class="mt-1.5 flex items-center gap-1.5 text-xs text-slate-500">
                                <input v-model="mismoLugar" @change="onMismoLugarChange" type="checkbox" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500" />
                                Devolver en el mismo lugar
                            </label>
                        </div>
                    </div>

                    <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <!-- Fecha recogida -->
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-500">Fecha de recogida</label>
                            <input v-model="pickupDate" type="date" :min="minPickup" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500" />
                        </div>
                        <!-- Hora recogida -->
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-500">Hora de recogida</label>
                            <select v-model="pickupHour" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                                <option v-for="h in HORAS" :key="h" :value="h">{{ h }}</option>
                            </select>
                        </div>
                        <!-- Fecha devolución -->
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-500">Fecha de devolución</label>
                            <input v-model="dropoffDate" type="date" :min="pickupDate" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500" />
                        </div>
                        <!-- Hora devolución -->
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-500">Hora de devolución</label>
                            <select v-model="dropoffHour" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                                <option v-for="h in HORAS" :key="h" :value="h">{{ h }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
                        <p class="text-xs text-slate-500">✅ Tarifas de agente reales, consultadas en vivo en cada búsqueda — nunca precios de catálogo.</p>
                        <button
                            type="button"
                            @click="buscar"
                            :disabled="!canSearch || loading"
                            class="flex w-full items-center justify-center gap-2 rounded-lg bg-teal-600 px-8 py-3 text-sm font-bold text-white shadow-md transition hover:bg-teal-700 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
                        >
                            <span v-if="loading">Consultando…</span>
                            <span v-else>🔎 Buscar autos</span>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Resultados -->
        <section class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
            <div v-if="!searched" class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500">
                <p class="text-4xl">🚗</p>
                <p class="mt-2 font-medium">Elige lugar de recogida, fechas y horarios para ver tarifas de agente en vivo.</p>
            </div>

            <div v-else-if="loading" class="rounded-xl border border-slate-200 bg-white p-10 text-center text-slate-500">
                <p class="text-3xl">⏳</p>
                <p class="mt-2 font-medium">Estamos consultando tarifas en vivo con nuestros proveedores…</p>
                <p class="mt-1 text-xs text-slate-400">Esto puede tardar unos segundos porque comparamos varias compañías de renta.</p>
            </div>

            <div v-else-if="errorMsg" class="rounded-xl border border-amber-200 bg-amber-50 p-8 text-center text-amber-800">
                <p class="text-3xl">🔄</p>
                <p class="mt-2 font-medium">{{ errorMsg }}</p>
                <a
                    :href="waLink"
                    target="_blank"
                    rel="noopener"
                    class="mt-4 inline-flex items-center gap-2 rounded-lg bg-[#003580] px-6 py-2.5 text-sm font-bold text-white hover:bg-[#00285f]"
                >
                    💬 Cotizar por WhatsApp mientras tanto
                </a>
            </div>

            <div v-else>
                <div class="mb-4 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                    <h2 class="text-lg font-bold text-slate-800">{{ filteredResults.length }} autos disponibles en {{ pickupQuery }}</h2>
                    <select v-model="sortBy" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="precio">Ordenar: menor precio</option>
                        <option value="categoria">Ordenar: categoría</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <article
                        v-for="(o, i) in filteredResults"
                        :key="i"
                        class="flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md"
                    >
                        <div class="relative flex h-32 items-center justify-center overflow-hidden bg-gradient-to-br from-[#003580] to-teal-600 p-3 text-white">
                            <img
                                v-if="o.imagen"
                                :src="o.imagen"
                                :alt="o.modelo || o.categoria"
                                @error="onImgError"
                                class="absolute inset-0 h-full w-full object-contain bg-white p-2"
                            />
                            <span class="relative text-4xl">🚗</span>
                        </div>
                        <div class="flex flex-1 flex-col p-4">
                            <p v-if="o.categoria" class="text-xs font-semibold uppercase tracking-wide text-teal-700">{{ o.categoria }}</p>
                            <p class="mt-0.5 font-semibold text-slate-800">{{ o.modelo || 'Auto similar' }}</p>
                            <p v-if="o.compania" class="mt-0.5 text-xs text-slate-400">Proveedor: {{ o.compania }}</p>

                            <div class="mt-2 flex flex-wrap gap-2 text-xs text-slate-500">
                                <span v-if="o.pasajeros" class="rounded-full bg-slate-100 px-2 py-0.5">👤 {{ o.pasajeros }}</span>
                                <span v-if="o.puertas" class="rounded-full bg-slate-100 px-2 py-0.5">🚪 {{ o.puertas }}</span>
                                <span v-if="o.transmision" class="rounded-full bg-slate-100 px-2 py-0.5">⚙️ {{ o.transmision }}</span>
                                <span v-if="o.aire_acondicionado" class="rounded-full bg-slate-100 px-2 py-0.5">❄️ A/C</span>
                            </div>

                            <div v-if="o.proteccion" class="mt-3">
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-500">🛡️ {{ o.proteccion }}</span>
                            </div>

                            <div class="mt-auto pt-4">
                                <div class="flex items-end justify-between gap-2">
                                    <div>
                                        <p v-if="o.dias" class="text-xs text-slate-400">{{ o.dias }} día{{ o.dias > 1 ? 's' : '' }}</p>
                                        <p class="text-xs text-slate-400">Total (tarifa de agente)</p>
                                        <p class="text-xl font-bold text-slate-900">{{ o.precio ? `${o.moneda || 'USD'} ${o.precio}` : 'Consulta precio' }}</p>
                                    </div>
                                    <a
                                        :href="whatsappReserva(o)"
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
