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

const today = toISODate(new Date());
// AgentCars rejects same-day pickups ("the date should be minimal today" — their
// server clock/timezone treats "today" as already past), so default and clamp to tomorrow.
const minPickupDate = addDays(today, 1);

const pickupQuery = ref('');
const pickupSuggestions = ref([]);
const pickupSelected = ref(null);
const pickupLoading = ref(false);

const dropoffSame = ref(true);
const dropoffQuery = ref('');
const dropoffSuggestions = ref([]);
const dropoffSelected = ref(null);
const dropoffLoading = ref(false);

const pickupDate = ref(minPickupDate);
const pickupHour = ref('11:00');
const dropoffDate = ref(addDays(minPickupDate, 6));
const dropoffHour = ref('10:00');

const loading = ref(false);
const searched = ref(false);
const results = ref([]);
const errorMsg = ref('');
const sortBy = ref('precio');
const categoryFilter = ref('todas');

let pickupTimer = null;
let dropoffTimer = null;

async function fetchSuggestions(query) {
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
        pickupSuggestions.value = await fetchSuggestions(q);
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
        dropoffSuggestions.value = await fetchSuggestions(q);
        dropoffLoading.value = false;
    }, 350);
}

function selectPickup(s) {
    pickupSelected.value = s;
    pickupQuery.value = s.name;
    pickupSuggestions.value = [];
}
function selectDropoff(s) {
    dropoffSelected.value = s;
    dropoffQuery.value = s.name;
    dropoffSuggestions.value = [];
}

const canSearch = computed(() => {
    if (!pickupSelected.value) return false;
    if (!dropoffSame.value && !dropoffSelected.value) return false;
    return pickupDate.value && dropoffDate.value && pickupHour.value && dropoffHour.value;
});

async function buscar() {
    if (!canSearch.value || loading.value) return;
    loading.value = true;
    searched.value = true;
    errorMsg.value = '';
    results.value = [];
    categoryFilter.value = 'todas';

    const dest = dropoffSame.value ? pickupSelected.value : dropoffSelected.value;

    try {
        const params = new URLSearchParams({
            pickup_code: pickupSelected.value.code,
            pickup_name: pickupSelected.value.name,
            dropoff_code: dest.code,
            dropoff_name: dest.name,
            pickup_date: pickupDate.value,
            pickup_hour: pickupHour.value,
            dropoff_date: dropoffDate.value,
            dropoff_hour: dropoffHour.value,
        });
        const res = await fetch(`/autos/buscar?${params.toString()}`);
        const data = await res.json();
        if (data.success && data.ofertas?.length) {
            results.value = data.ofertas;
        } else if (data.success) {
            errorMsg.value = 'No encontramos autos disponibles para esas fechas y lugar. Intenta con otra búsqueda.';
        } else {
            errorMsg.value = 'No pudimos completar la búsqueda en este momento. Intenta de nuevo en unos minutos.';
        }
    } catch (e) {
        errorMsg.value = 'Ocurrió un error al buscar. Intenta de nuevo.';
    } finally {
        loading.value = false;
    }
}

const categories = computed(() => {
    const set = new Set(results.value.map((o) => o.categoria).filter(Boolean));
    return Array.from(set);
});

const filteredResults = computed(() => {
    let list = results.value;
    if (categoryFilter.value !== 'todas') {
        list = list.filter((o) => o.categoria === categoryFilter.value);
    }
    list = [...list];
    if (sortBy.value === 'precio') {
        list.sort((a, b) => a.precio - b.precio);
    } else if (sortBy.value === 'categoria') {
        list.sort((a, b) => a.categoria.localeCompare(b.categoria));
    }
    return list;
});

function money(n, currency) {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: currency || 'USD' }).format(n);
}

function swapDropoff() {
    dropoffSame.value = !dropoffSame.value;
    if (dropoffSame.value) {
        dropoffQuery.value = '';
        dropoffSelected.value = null;
        dropoffSuggestions.value = [];
    }
}
</script>

<template>
    <Head title="Renta de Autos — JCH Travel Agency" />

    <div class="min-h-screen bg-slate-50 text-slate-800">
        <!-- Header -->
        <header class="border-b border-slate-200 bg-white">
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
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <!-- Lugar de recogida -->
                        <div class="relative lg:col-span-2">
                            <label class="mb-1 block text-xs font-semibold text-slate-500">Lugar de recogida</label>
                            <div class="flex items-center rounded-lg border border-slate-300 px-3 focus-within:border-teal-500 focus-within:ring-1 focus-within:ring-teal-500">
                                <span class="text-slate-400">🔍</span>
                                <input
                                    v-model="pickupQuery"
                                    @input="onPickupInput"
                                    type="text"
                                    placeholder="Ciudad o aeropuerto (ej. Cancún, Miami...)"
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
                                    ✈️ {{ s.name }}
                                </button>
                            </div>
                            <button type="button" @click="swapDropoff" class="mt-1 text-xs font-medium text-teal-700 hover:underline">
                                {{ dropoffSame ? 'Devolver en otro lugar' : 'Devolver en el mismo lugar' }}
                            </button>
                        </div>

                        <!-- Lugar de entrega (opcional) -->
                        <div v-if="!dropoffSame" class="relative lg:col-span-2">
                            <label class="mb-1 block text-xs font-semibold text-slate-500">Lugar de entrega</label>
                            <div class="flex items-center rounded-lg border border-slate-300 px-3 focus-within:border-teal-500 focus-within:ring-1 focus-within:ring-teal-500">
                                <span class="text-slate-400">🔍</span>
                                <input
                                    v-model="dropoffQuery"
                                    @input="onDropoffInput"
                                    type="text"
                                    placeholder="Ciudad o aeropuerto"
                                    class="w-full border-0 bg-transparent px-2 py-2.5 text-sm focus:outline-none focus:ring-0"
                                />
                            </div>
                            <div
                                v-if="dropoffSuggestions.length || dropoffLoading"
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
                                    ✈️ {{ s.name }}
                                </button>
                            </div>
                        </div>

                        <!-- Fecha/hora recogida -->
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-500">Fecha de recogida</label>
                            <input v-model="pickupDate" type="date" :min="minPickupDate" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-500">Hora</label>
                            <input v-model="pickupHour" type="time" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500" />
                        </div>

                        <!-- Fecha/hora entrega -->
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-500">Fecha de devolución</label>
                            <input v-model="dropoffDate" type="date" :min="pickupDate" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-500">Hora</label>
                            <input v-model="dropoffHour" type="time" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500" />
                        </div>
                    </div>

                    <div class="mt-4 flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
                        <p class="text-xs text-slate-500">✅ Te mostramos siempre la mejor protección disponible para tu auto, sin sorpresas al recoger tu auto.</p>
                        <button
                            type="button"
                            @click="buscar"
                            :disabled="!canSearch || loading"
                            class="flex w-full items-center justify-center gap-2 rounded-lg bg-teal-600 px-8 py-3 text-sm font-bold text-white shadow-md transition hover:bg-teal-700 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
                        >
                            <span v-if="loading">Buscando…</span>
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
                <p class="mt-2 font-medium">Elige tu lugar y fechas para ver tarifas en vivo de las mejores compañías de renta.</p>
            </div>

            <div v-else-if="loading" class="rounded-xl border border-slate-200 bg-white p-10 text-center text-slate-500">
                <p class="text-3xl">⏳</p>
                <p class="mt-2 font-medium">Comparando tarifas con nuestros proveedores…</p>
            </div>

            <div v-else-if="errorMsg" class="rounded-xl border border-red-200 bg-red-50 p-8 text-center text-red-700">
                {{ errorMsg }}
            </div>

            <div v-else>
                <div class="mb-4 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                    <h2 class="text-lg font-bold text-slate-800">{{ filteredResults.length }} autos disponibles</h2>
                    <div class="flex flex-wrap gap-2">
                        <select v-model="categoryFilter" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="todas">Todas las categorías</option>
                            <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
                        </select>
                        <select v-model="sortBy" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="precio">Ordenar: menor precio</option>
                            <option value="categoria">Ordenar: categoría</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <article
                        v-for="(o, i) in filteredResults"
                        :key="i"
                        class="flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md"
                    >
                        <div class="flex h-32 items-center justify-center bg-slate-50 p-3">
                            <img v-if="o.imagen" :src="o.imagen" :alt="o.modelo" class="max-h-full max-w-full object-contain" loading="lazy" />
                            <span v-else class="text-4xl">🚙</span>
                        </div>
                        <div class="flex flex-1 flex-col p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-teal-700">{{ o.categoria }}</p>
                            <p class="mt-0.5 font-semibold text-slate-800">{{ o.modelo }}</p>

                            <div class="mt-2 flex items-center gap-2">
                                <img v-if="o.compania_logo" :src="o.compania_logo" :alt="o.compania" class="h-5 w-auto object-contain" loading="lazy" />
                                <span class="text-xs text-slate-500">{{ o.compania }}</span>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-x-3 gap-y-1 text-xs text-slate-500">
                                <span>🚪 {{ o.puertas }} puertas</span>
                                <span>🧳 {{ o.maletas }} maletas</span>
                                <span>👤 {{ o.pasajeros }} pasajeros</span>
                                <span>⚙️ {{ o.transmision }}</span>
                            </div>

                            <div class="mt-3 inline-flex w-fit items-center gap-1 rounded-full bg-teal-50 px-2.5 py-1 text-xs font-medium text-teal-700">
                                🛡️ {{ o.proteccion }}
                            </div>

                            <div class="mt-auto flex items-end justify-between pt-4">
                                <div>
                                    <p class="text-xs text-slate-400">Total ({{ o.dias }} días)</p>
                                    <p class="text-xl font-bold text-slate-900">{{ money(o.precio, o.moneda) }}</p>
                                </div>
                                <a
                                    :href="waLink"
                                    target="_blank"
                                    rel="noopener"
                                    class="rounded-lg bg-[#003580] px-4 py-2 text-xs font-bold text-white hover:bg-[#00285f]"
                                >
                                    Reservar
                                </a>
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
