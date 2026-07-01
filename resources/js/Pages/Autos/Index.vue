<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Renta de Autos" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold text-slate-800">🚗 Renta de Autos — AgentCars</h2>
        </template>

        <div class="mx-auto max-w-6xl space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#003580] text-white">
                        🚗
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Búsqueda de Autos — AgentCars</h3>
                        <p class="text-sm text-slate-500">Usa el buscador para cotizar autos para tus clientes. Tarifas exclusivas de agente.</p>
                    </div>
                </div>

                <div class="rounded-lg overflow-hidden border border-slate-200">
                    <iframe
                        src="https://subsite.agentcars.com/es/site/form-iframe?agency=archer-operadora"
                        width="100%"
                        height="600"
                        style="border:0"
                        title="Búsqueda de autos AgentCars"
                    ></iframe>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
