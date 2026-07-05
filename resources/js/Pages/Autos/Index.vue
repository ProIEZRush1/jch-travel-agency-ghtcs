<script setup>
import { Head } from '@inertiajs/vue3';

const WHATSAPP = '5215594356241';
const waLink = `https://wa.me/${WHATSAPP}`;
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

        <!-- Hero -->
        <section class="border-b border-slate-200 bg-gradient-to-b from-[#003580] to-[#0a4faa] px-4 py-10 text-white sm:px-6">
            <div class="mx-auto max-w-6xl">
                <p class="text-sm font-semibold uppercase tracking-wide text-teal-300">JCH Travel Agency</p>
                <h1 class="mt-1 text-2xl font-bold sm:text-3xl">Encuentra el mejor auto para tu viaje</h1>
                <p class="mt-1 text-sm text-blue-100">Comparamos en vivo las tarifas de más de 50 compañías de renta — Dollar, Thrifty, Hertz, Enterprise y más.</p>
            </div>
        </section>

        <!-- Buscador AgentCars -->
        <section class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center gap-2 border-b border-slate-100 px-4 py-3 text-sm text-slate-500">
                    <span>🚗</span>
                    <span>Busca y compara autos de renta al instante — motor de nuestro proveedor AgentCars.</span>
                </div>
                <iframe src="https://subsite.agentcars.com/es/site/form-iframe?agency=archer-operadora" width="100%" height="600" style="border:0"></iframe>
            </div>
            <p class="mt-4 text-center text-sm text-slate-500">
                ¿Prefieres que te ayudemos directamente?
                <a :href="waLink" target="_blank" rel="noopener" class="font-medium text-[#003580] hover:underline">Escríbenos por WhatsApp</a>
            </p>
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
