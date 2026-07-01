<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    clientes: { type: Array, default: () => [] },
});

function eliminar(cliente) {
    if (window.confirm('¿Eliminar este cliente?')) {
        router.delete(route('clientes.destroy', cliente.id));
    }
}
</script>

<template>
    <Head title="Clientes" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold text-slate-800">👥 Clientes</h2>
        </template>

        <div class="mx-auto max-w-5xl">
            <div class="mb-4 flex items-center justify-between">
                <p class="text-sm text-slate-500">Clientes registrados a través del bot de WhatsApp.</p>
                <span class="rounded-full bg-[#003580]/10 px-3 py-1 text-xs font-semibold text-[#003580]">{{ clientes.length }} clientes</span>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">#</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Teléfono</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Registrado</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="(c, i) in clientes" :key="c.id" class="hover:bg-slate-50">
                            <td class="px-6 py-4 text-sm text-slate-400">{{ i + 1 }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ c.nombre }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ c.telefono }}</td>
                            <td class="px-6 py-4 text-sm text-slate-500">{{ new Date(c.created_at).toLocaleDateString('es-MX') }}</td>
                            <td class="px-6 py-4 text-right">
                                <button @click="eliminar(c)" class="rounded-md px-3 py-1.5 text-xs font-semibold text-red-600 border border-red-200 hover:bg-red-50 transition">Eliminar</button>
                            </td>
                        </tr>
                        <tr v-if="!clientes.length">
                            <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-400">Aún no hay clientes registrados.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
