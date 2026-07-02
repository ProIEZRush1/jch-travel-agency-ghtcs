<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    busquedas: Object,
});

function money(n, currency) {
    if (n === null || n === undefined) return '—';
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: currency || 'USD' }).format(n);
}
</script>

<template>
    <Head title="Historial de Búsquedas — Autos" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold text-slate-800">🚗 Historial de Búsquedas — Autos</h2>
        </template>

        <div class="mx-auto max-w-6xl space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 p-4">
                    <p class="text-sm text-slate-500">
                        Cada vez que un cliente busca autos en la página pública
                        <Link href="/autos" class="font-medium text-teal-700 hover:underline">/autos</Link>
                        se guarda aquí junto con las cotizaciones que devolvió el proveedor.
                    </p>
                </div>

                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-left text-xs uppercase tracking-wide text-slate-400">
                            <th class="px-4 py-3">Fecha búsqueda</th>
                            <th class="px-4 py-3">Recogida</th>
                            <th class="px-4 py-3">Entrega</th>
                            <th class="px-4 py-3">Periodo</th>
                            <th class="px-4 py-3">Resultados</th>
                            <th class="px-4 py-3">Desde</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="busquedas.data.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400">Aún no hay búsquedas registradas.</td>
                        </tr>
                        <tr v-for="b in busquedas.data" :key="b.id" class="border-b border-slate-50 hover:bg-slate-50">
                            <td class="px-4 py-3 text-slate-500">{{ new Date(b.created_at).toLocaleString('es-MX') }}</td>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ b.lugar_recogida_nombre }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ b.lugar_entrega_nombre }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ b.fecha_recogida }} → {{ b.fecha_entrega }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-teal-50 px-2 py-0.5 text-xs font-medium text-teal-700">
                                    {{ b.cotizaciones_count }} autos
                                </span>
                            </td>
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ money(b.precio_desde) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="busquedas.links?.length > 3" class="flex flex-wrap gap-1">
                <Link
                    v-for="(link, i) in busquedas.links"
                    :key="i"
                    :href="link.url || '#'"
                    v-html="link.label"
                    class="rounded-md border px-3 py-1 text-sm"
                    :class="link.active ? 'border-teal-600 bg-teal-600 text-white' : 'border-slate-200 text-slate-600 hover:bg-slate-50'"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
