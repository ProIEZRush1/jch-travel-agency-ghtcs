<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    paquetes: { type: Array, default: () => [] },
});

function formatPrecio(cents) {
    return '$' + (cents / 100).toLocaleString('es-MX', { minimumFractionDigits: 0 }) + ' MXN';
}

function toggleActivo(paquete) {
    router.patch(route('paquetes.update', paquete.id), {
        nombre: paquete.nombre,
        precio: paquete.precio,
        descripcion: paquete.descripcion,
        activo: !paquete.activo,
        orden: paquete.orden,
    }, { preserveScroll: true });
}

function eliminar(paquete) {
    if (window.confirm('¿Eliminar este servicio/paquete?')) {
        router.delete(route('paquetes.destroy', paquete.id));
    }
}
</script>

<template>
    <Head title="Servicios & Paquetes" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold text-slate-800">📦 Servicios & Paquetes</h2>
        </template>

        <div class="mx-auto max-w-5xl space-y-6">
            <div class="flex items-center justify-between">
                <p class="text-sm text-slate-500">Estos son los servicios que el bot de WhatsApp ofrece a tus clientes.</p>
                <Link :href="route('paquetes.create')" class="inline-flex items-center gap-2 rounded-lg bg-[#003580] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#0071c2]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Nuevo servicio
                </Link>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Servicio</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Precio base</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Estado</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="p in paquetes" :key="p.id" class="hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-slate-800">{{ p.nombre }}</p>
                                <p class="text-xs text-slate-500">{{ p.descripcion }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-700">{{ formatPrecio(p.precio) }}</td>
                            <td class="px-6 py-4">
                                <button @click="toggleActivo(p)" :class="p.activo ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500'" class="rounded-full px-3 py-1 text-xs font-semibold transition hover:opacity-80">
                                    {{ p.activo ? 'Activo' : 'Inactivo' }}
                                </button>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <Link :href="route('paquetes.edit', p.id)" class="rounded-md px-3 py-1.5 text-xs font-semibold text-[#0071c2] border border-[#0071c2]/30 hover:bg-blue-50 transition">Editar</Link>
                                    <button @click="eliminar(p)" class="rounded-md px-3 py-1.5 text-xs font-semibold text-red-600 border border-red-200 hover:bg-red-50 transition">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!paquetes.length">
                            <td colspan="4" class="px-6 py-10 text-center text-sm text-slate-400">No hay servicios registrados aún.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
