<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const trialLocked = computed(() => page.props.trialLocked ?? false);

const props = defineProps({
    cotizaciones: { type: Array, default: () => [] },
});

const estadoConfig = {
    pendiente:  { label: 'Pendiente',  cls: 'bg-amber-100 text-amber-700' },
    confirmado: { label: 'Confirmado', cls: 'bg-green-100 text-green-700' },
    cancelado:  { label: 'Cancelado',  cls: 'bg-red-100 text-red-700' },
};

function cambiarEstado(cot, estado) {
    if (trialLocked.value) { alert('Función bloqueada en versión de prueba. Activa con tu anticipo.'); return; }
    router.patch(route('cotizaciones.update', cot.id), { estado }, { preserveScroll: true });
}

function eliminar(cot) {
    if (window.confirm('¿Eliminar esta cotización?')) {
        router.delete(route('cotizaciones.destroy', cot.id));
    }
}
</script>

<template>
    <Head title="Cotizaciones" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold text-slate-800">📋 Cotizaciones</h2>
        </template>

        <div class="mx-auto max-w-6xl space-y-4">
            <div class="flex items-center justify-between">
                <p class="text-sm text-slate-500">Solicitudes recibidas por el bot de WhatsApp.</p>
                <span class="rounded-full bg-[#003580]/10 px-3 py-1 text-xs font-semibold text-[#003580]">{{ cotizaciones.length }} cotizaciones</span>
            </div>

            <div v-if="trialLocked" class="flex items-center gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                🔒 <span>Puedes ver las cotizaciones, pero cambiar su estado se activa al confirmar tu proyecto.</span>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Cliente</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Servicio</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Teléfono</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Estado</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Fecha</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="c in cotizaciones" :key="c.id" class="hover:bg-slate-50">
                            <td class="px-5 py-4 font-semibold text-slate-800">{{ c.cliente }}</td>
                            <td class="px-5 py-4 text-sm text-slate-600">{{ c.plan?.nombre ?? '—' }}</td>
                            <td class="px-5 py-4 text-sm text-slate-500">{{ c.telefono }}</td>
                            <td class="px-5 py-4">
                                <span :class="[estadoConfig[c.estado]?.cls ?? 'bg-slate-100 text-slate-500', 'rounded-full px-2.5 py-1 text-xs font-semibold']">
                                    {{ estadoConfig[c.estado]?.label ?? c.estado }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-500">{{ new Date(c.created_at).toLocaleDateString('es-MX') }}</td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex justify-end gap-1.5">
                                    <button v-if="c.estado === 'pendiente'" @click="cambiarEstado(c, 'confirmado')" class="rounded-md px-2.5 py-1.5 text-xs font-semibold text-green-700 border border-green-200 hover:bg-green-50 transition">Confirmar</button>
                                    <button v-if="c.estado !== 'cancelado'" @click="cambiarEstado(c, 'cancelado')" class="rounded-md px-2.5 py-1.5 text-xs font-semibold text-slate-600 border border-slate-200 hover:bg-slate-50 transition">Cancelar</button>
                                    <button @click="eliminar(c)" class="rounded-md px-2.5 py-1.5 text-xs font-semibold text-red-600 border border-red-200 hover:bg-red-50 transition">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!cotizaciones.length">
                            <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-400">No hay cotizaciones aún. El bot las irá registrando automáticamente.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
