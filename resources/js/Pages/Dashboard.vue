<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const props = defineProps({
    stats: { type: Object, default: () => ({}) },
});

const businessName = computed(() => page.props.name ?? 'JCH Travel Agency');
const userFirstName = computed(() => {
    const name = (page.props.auth?.user?.name ?? '').trim();
    return name ? name.split(/\s+/)[0] : '';
});

const statCards = computed(() => [
    {
        label: 'Clientes registrados',
        value: props.stats.clientes ?? 0,
        hint: 'Contactos del portal',
        color: 'bg-[#003580]',
        icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
        href: '/clientes',
    },
    {
        label: 'Cotizaciones este mes',
        value: props.stats.cotizaciones ?? 0,
        hint: 'Solicitudes recibidas',
        color: 'bg-[#0071c2]',
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        href: '/cotizaciones',
    },
    {
        label: 'Confirmadas este mes',
        value: props.stats.confirmadas ?? 0,
        hint: 'Reservas cerradas',
        color: 'bg-[#0ea5e9]',
        icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        href: '/cotizaciones',
    },
    {
        label: 'Servicios activos',
        value: props.stats.servicios ?? 0,
        hint: 'En catálogo del bot',
        color: 'bg-slate-700',
        icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
        href: '/paquetes',
    },
]);

const quickLinks = [
    { label: 'Renta de Autos', desc: 'AgentCars — busca y cotiza autos', href: '/autos', icon: '🚗' },
    { label: 'Hoteles & Vuelos', desc: 'Expedia TAAP — tarifa de agente', href: '/hoteles', icon: '✈️' },
    { label: 'Servicios & Paquetes', desc: 'Gestiona el catálogo del bot', href: '/paquetes', icon: '📦' },
    { label: 'Cotizaciones', desc: 'Solicitudes de clientes por WhatsApp', href: '/cotizaciones', icon: '📋' },
    { label: 'Clientes', desc: 'Base de contactos registrados', href: '/clientes', icon: '👥' },
    { label: 'Conectar WhatsApp', desc: 'Vincular el número de la agencia', href: '/conectar', icon: '💬' },
];
</script>

<template>
    <Head title="Inicio" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">Panel de control</h2>
        </template>

        <div class="mx-auto max-w-7xl space-y-7">
            <!-- Hero -->
            <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#003580] to-[#0071c2] p-8 text-white sm:p-10">
                <div class="pointer-events-none absolute right-0 top-0 h-full w-1/3 bg-white/5 [clip-path:polygon(30%_0%,100%_0%,100%_100%,0%_100%)]"></div>
                <div class="relative">
                    <p class="text-sm font-medium uppercase tracking-widest text-blue-200">Bienvenido a tu panel</p>
                    <h1 class="mt-2 text-3xl font-extrabold sm:text-4xl">
                        Hola<span v-if="userFirstName">, {{ userFirstName }}</span> 👋
                    </h1>
                    <p class="mt-3 max-w-2xl text-base text-blue-100">
                        Panel de <span class="font-semibold text-white">{{ businessName }}</span>.
                        Gestiona tus cotizaciones, clientes, paquetes y el bot de WhatsApp desde aquí.
                    </p>
                </div>
            </section>

            <!-- Stats -->
            <section class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <Link
                    v-for="card in statCards"
                    :key="card.label"
                    :href="card.href"
                    class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                >
                    <div :class="[card.color, 'mb-3 inline-flex h-10 w-10 items-center justify-center rounded-lg text-white']">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="card.icon" />
                        </svg>
                    </div>
                    <p class="text-2xl font-extrabold text-slate-800">{{ card.value }}</p>
                    <p class="text-sm font-semibold text-slate-600">{{ card.label }}</p>
                    <p class="text-xs text-slate-400">{{ card.hint }}</p>
                </Link>
            </section>

            <!-- Quick links -->
            <section>
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-widest text-slate-400">Accesos rápidos</h3>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="link in quickLinks"
                        :key="link.label"
                        :href="link.href"
                        class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-[#0071c2]/30 hover:shadow-md"
                    >
                        <span class="text-2xl">{{ link.icon }}</span>
                        <div>
                            <p class="text-sm font-semibold text-slate-800">{{ link.label }}</p>
                            <p class="text-xs text-slate-500">{{ link.desc }}</p>
                        </div>
                        <svg class="ml-auto h-4 w-4 shrink-0 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
