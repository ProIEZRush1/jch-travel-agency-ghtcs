<script setup>
import { ref, computed } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const page = usePage();

const businessName = computed(() => page.props.name ?? 'JCH Travel Agency');
const userName = computed(() => page.props.auth?.user?.name ?? '');
const userEmail = computed(() => page.props.auth?.user?.email ?? '');
const trialLocked = computed(() => page.props.trialLocked ?? false);

const userInitials = computed(() => {
    const name = (userName.value || '').trim();
    if (!name) return '?';
    const parts = name.split(/\s+/).filter(Boolean);
    return parts.length === 1 ? parts[0].substring(0, 2).toUpperCase() : (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
});

const navItems = [
    { label: 'Inicio', routeName: 'dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { label: 'Renta de Autos', routeName: 'autos.index', icon: 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0' },
    { label: 'Hoteles & Vuelos', routeName: 'hoteles.index', icon: 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
    { label: 'Servicios & Paquetes', routeName: 'paquetes.index', icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4' },
    { label: 'Cotizaciones', routeName: 'cotizaciones.index', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
    { label: 'Clientes', routeName: 'clientes.index', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z' },
    { label: 'Conectar WhatsApp', routeName: 'conectar', icon: 'M3 20l1.3-3.9A8 8 0 1 1 7.9 19.7L3 20z' },
];
</script>

<template>
    <div class="min-h-screen bg-slate-50">
        <!-- Trial banner -->
        <div v-if="trialLocked" class="bg-[#003580] py-2 text-center text-xs font-medium text-white">
            🔒 Versión de prueba — activa todas las funciones con tu anticipo · <a href="https://wa.me/52" class="underline underline-offset-2">Contactar Overcloud</a>
        </div>

        <!-- Sidebar (desktop) -->
        <aside class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col border-r border-slate-200 bg-white lg:flex" :class="{ 'top-8': trialLocked }">
            <!-- Brand -->
            <div class="flex h-20 items-center gap-3 border-b border-slate-100 px-5">
                <Link :href="route('dashboard')" class="flex items-center gap-3">
                    <img src="/brand-logo.jpeg" alt="JCH Travel Agency" class="h-12 w-auto object-contain" />
                </Link>
            </div>

            <!-- Nav -->
            <nav class="flex-1 overflow-y-auto px-3 py-4">
                <div class="space-y-0.5">
                    <Link
                        v-for="item in navItems"
                        :key="item.routeName"
                        :href="route(item.routeName)"
                        :class="[
                            'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150',
                            route().current(item.routeName)
                                ? 'bg-[#003580] text-white shadow-sm'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-[#003580]',
                        ]"
                    >
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                        </svg>
                        {{ item.label }}
                    </Link>
                </div>
            </nav>

            <!-- Footer credit -->
            <div class="border-t border-slate-100 px-5 py-4">
                <p class="text-xs text-slate-400">Desarrollado por <span class="font-semibold text-slate-500">Overcloud</span></p>
            </div>
        </aside>

        <!-- Main column -->
        <div class="lg:pl-64" :class="{ 'mt-8': trialLocked }">
            <!-- Top bar -->
            <header class="sticky top-0 z-30 flex h-16 items-center gap-4 border-b border-slate-200 bg-white px-4 sm:px-6 lg:px-8">
                <!-- Mobile hamburger -->
                <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="inline-flex items-center justify-center rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 lg:hidden">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- Mobile brand -->
                <Link :href="route('dashboard')" class="flex items-center gap-2 lg:hidden">
                    <img src="/brand-logo.jpeg" alt="JCH Travel Agency" class="h-9 w-auto object-contain" />
                </Link>

                <!-- Page heading slot -->
                <div class="hidden min-w-0 flex-1 lg:block">
                    <slot name="header" />
                </div>

                <div class="flex-1 lg:hidden"></div>

                <!-- User dropdown -->
                <div class="relative">
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button type="button" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white py-1 pl-1 pr-3 text-sm font-medium text-slate-600 transition hover:border-slate-300 hover:text-slate-900 focus:outline-none">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-[#003580] text-xs font-bold text-white">{{ userInitials }}</span>
                                <span class="hidden sm:inline">{{ userName }}</span>
                                <svg class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </template>
                        <template #content>
                            <div class="border-b border-slate-100 px-4 py-3">
                                <p class="text-sm font-semibold text-slate-800">{{ userName }}</p>
                                <p class="truncate text-xs text-slate-500">{{ userEmail }}</p>
                            </div>
                            <DropdownLink :href="route('profile.edit')">Mi perfil</DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">Cerrar sesión</DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <!-- Mobile slide-down nav -->
            <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="border-b border-slate-200 bg-white lg:hidden">
                <div class="space-y-1 px-4 py-3">
                    <ResponsiveNavLink v-for="item in navItems" :key="item.routeName" :href="route(item.routeName)" :active="route().current(item.routeName)">
                        {{ item.label }}
                    </ResponsiveNavLink>
                </div>
                <div class="border-t border-slate-200 px-4 py-4">
                    <p class="text-base font-semibold text-slate-800">{{ userName }}</p>
                    <p class="text-sm text-slate-500">{{ userEmail }}</p>
                    <div class="mt-3 space-y-1">
                        <ResponsiveNavLink :href="route('profile.edit')">Mi perfil</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('logout')" method="post" as="button">Cerrar sesión</ResponsiveNavLink>
                    </div>
                </div>
            </div>

            <!-- Mobile page heading -->
            <div v-if="$slots.header" class="border-b border-slate-200 bg-white px-4 py-5 sm:px-6 lg:hidden">
                <slot name="header" />
            </div>

            <!-- Page content -->
            <main class="px-4 py-6 sm:px-6 lg:px-8">
                <slot />
            </main>
        </div>
    </div>
</template>
