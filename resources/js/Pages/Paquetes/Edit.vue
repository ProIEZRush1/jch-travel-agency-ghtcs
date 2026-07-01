<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ paquete: Object });

const form = useForm({
    nombre: props.paquete.nombre,
    precio: props.paquete.precio,
    descripcion: props.paquete.descripcion ?? '',
    activo: props.paquete.activo,
    orden: props.paquete.orden ?? 0,
});

const submit = () => form.patch(route('paquetes.update', props.paquete.id));
</script>

<template>
    <Head title="Editar Servicio" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold text-slate-800">Editar Servicio</h2>
        </template>

        <div class="mx-auto max-w-2xl">
            <div class="rounded-xl border border-slate-200 bg-white p-8 shadow-sm">
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Nombre del servicio</label>
                        <input v-model="form.nombre" type="text" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#0071c2] focus:outline-none focus:ring-1 focus:ring-[#0071c2]" required />
                        <p v-if="form.errors.nombre" class="mt-1 text-xs text-red-600">{{ form.errors.nombre }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Precio base (centavos MXN)</label>
                        <input v-model="form.precio" type="number" min="0" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#0071c2] focus:outline-none focus:ring-1 focus:ring-[#0071c2]" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Descripción</label>
                        <textarea v-model="form.descripcion" rows="3" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#0071c2] focus:outline-none focus:ring-1 focus:ring-[#0071c2]"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Orden en el menú</label>
                        <input v-model="form.orden" type="number" min="0" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#0071c2] focus:outline-none focus:ring-1 focus:ring-[#0071c2]" />
                    </div>
                    <div class="flex items-center gap-2">
                        <input v-model="form.activo" type="checkbox" id="activo" class="h-4 w-4 rounded border-slate-300 text-[#003580]" />
                        <label for="activo" class="text-sm text-slate-700">Activo (visible para el bot)</label>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="submit" :disabled="form.processing" class="rounded-lg bg-[#003580] px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-[#0071c2] disabled:opacity-50">
                            Actualizar
                        </button>
                        <Link :href="route('paquetes.index')" class="rounded-lg border border-slate-300 px-6 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Cancelar
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
