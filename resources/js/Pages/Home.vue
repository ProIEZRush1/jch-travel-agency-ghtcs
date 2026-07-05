<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const WHATSAPP = '5215594356241';
const waLink = `https://wa.me/${WHATSAPP}`;

const page = usePage();
const businessName = computed(() => page.props.name ?? 'JCH Travel Agency');
const isAuth = computed(() => !!page.props.auth?.user);
const trialLocked = computed(() => page.props.trialLocked ?? false);

const menuOpen = ref(false);

const servicios = [
    {
        icon: '🚗',
        title: 'Renta de Autos',
        text: 'Compara en vivo tarifas de más de 50 compañías de renta en cualquier ciudad o aeropuerto.',
        cta: 'Buscar autos',
        href: '/autos',
        external: false,
    },
    {
        icon: '🏨',
        title: 'Hoteles',
        text: 'Tarifas de agente reales, consultadas en tiempo real — nunca precios de catálogo.',
        cta: 'Buscar hoteles',
        href: '/hoteles',
        external: false,
    },
    {
        icon: '✈️',
        title: 'Vuelos',
        text: 'Accede a tu portal de agente para cotizar vuelos con tarifa preferencial en cientos de aerolíneas.',
        cta: 'Ir a mi portal de agente',
        href: 'https://www.expediataap.mx',
        external: true,
    },
    {
        icon: '🧳',
        title: 'Paquetes',
        text: 'Arma vuelo + hotel + auto en un solo paquete y comparamos contra la tarifa normal al público.',
        cta: 'Ir a mi portal de agente',
        href: 'https://www.expediataap.mx',
        external: true,
    },
];

const testimonios = [
    {
        nombre: 'Familia Reyes',
        texto: 'Nos ayudaron a armar todo el viaje a Cancún — auto, hotel y vuelos — y pagamos menos de lo que habíamos visto en otras páginas. Todo por WhatsApp, súper fácil.',
    },
    {
        nombre: 'Grupo empresarial Torres & Asociados',
        texto: 'Cotizan rápido y nos muestran claramente cuánto estamos ahorrando contra la tarifa normal. Ya es nuestra agencia de cabecera para viajes de trabajo.',
    },
    {
        nombre: 'Marisol G.',
        texto: 'Excelente atención, me mandaron la cotización con todo bien explicado y reservé mi auto de renta en minutos.',
    },
];
</script>

<template>
    <Head :title="`${businessName} — Autos, Hoteles, Vuelos y Paquetes`" />

    <div class="min-h-screen bg-slate-50 text-slate-800">
        <!-- Header -->
        <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 sm:px-6">
                <a href="#top" class="flex items-center gap-3">
                    <img src="/brand-logo.jpeg" :alt="businessName" class="h-12 w-auto object-contain" />
                </a>

                <nav class="hidden items-center gap-6 text-sm font-medium text-slate-600 lg:flex">
                    <a href="/autos" class="hover:text-[#003580]">🚗 Autos</a>
                    <a href="/hoteles" class="hover:text-[#003580]">🏨 Hoteles</a>
                    <a href="https://www.expediataap.mx" target="_blank" rel="noopener" class="hover:text-[#003580]">✈️ Vuelos y Paquetes</a>
                    <a href="#testimonios" class="hover:text-[#003580]">Testimonios</a>
                    <a href="#contacto" class="hover:text-[#003580]">Contacto</a>
                </nav>

                <div class="hidden items-center gap-3 lg:flex">
                    <a :href="waLink" target="_blank" rel="noopener" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-bold text-white hover:bg-teal-700">
                        💬 WhatsApp
                    </a>
                    <Link v-if="isAuth" href="/dashboard" class="rounded-lg border border-[#003580] px-4 py-2 text-sm font-bold text-[#003580] hover:bg-[#003580] hover:text-white">
                        Ir al panel
                    </Link>
                    <Link v-else href="/login" class="rounded-lg border border-[#003580] px-4 py-2 text-sm font-bold text-[#003580] hover:bg-[#003580] hover:text-white">
                        Iniciar sesión
                    </Link>
                </div>

                <button class="text-2xl lg:hidden" @click="menuOpen = !menuOpen">☰</button>
            </div>

            <div v-if="menuOpen" class="border-t border-slate-200 bg-white px-4 py-3 lg:hidden">
                <div class="flex flex-col gap-3 text-sm font-medium text-slate-600">
                    <a href="/autos" class="hover:text-[#003580]">🚗 Autos</a>
                    <a href="/hoteles" class="hover:text-[#003580]">🏨 Hoteles</a>
                    <a href="https://www.expediataap.mx" target="_blank" rel="noopener" class="hover:text-[#003580]">✈️ Vuelos y Paquetes</a>
                    <a href="#testimonios" class="hover:text-[#003580]">Testimonios</a>
                    <a href="#contacto" class="hover:text-[#003580]">Contacto</a>
                    <a :href="waLink" target="_blank" rel="noopener" class="rounded-lg bg-teal-600 px-4 py-2 text-center font-bold text-white">💬 WhatsApp</a>
                    <Link v-if="isAuth" href="/dashboard" class="rounded-lg border border-[#003580] px-4 py-2 text-center font-bold text-[#003580]">Ir al panel</Link>
                    <Link v-else href="/login" class="rounded-lg border border-[#003580] px-4 py-2 text-center font-bold text-[#003580]">Iniciar sesión</Link>
                </div>
            </div>
        </header>

        <!-- Hero -->
        <section id="top" class="border-b border-slate-200 bg-gradient-to-b from-[#003580] to-[#0a4faa] px-4 py-16 text-white sm:px-6">
            <div class="mx-auto max-w-4xl text-center">
                <p class="text-sm font-semibold uppercase tracking-wide text-teal-300">{{ businessName }}</p>
                <h1 class="mt-2 text-3xl font-extrabold sm:text-5xl">Tu agencia de viajes de confianza</h1>
                <p class="mx-auto mt-4 max-w-2xl text-base text-blue-100 sm:text-lg">
                    Autos, hoteles, vuelos y paquetes con tarifa de agente — te mostramos cuánto ahorras contra la tarifa normal al público.
                </p>

                <div class="mx-auto mt-10 grid max-w-3xl grid-cols-2 gap-3 sm:grid-cols-4">
                    <a href="/autos" class="flex flex-col items-center gap-2 rounded-2xl bg-white/10 p-5 backdrop-blur transition hover:bg-white/20">
                        <span class="text-3xl">🚗</span>
                        <span class="text-sm font-semibold">Autos</span>
                    </a>
                    <a href="/hoteles" class="flex flex-col items-center gap-2 rounded-2xl bg-white/10 p-5 backdrop-blur transition hover:bg-white/20">
                        <span class="text-3xl">🏨</span>
                        <span class="text-sm font-semibold">Hoteles</span>
                    </a>
                    <a href="https://www.expediataap.mx" target="_blank" rel="noopener" class="flex flex-col items-center gap-2 rounded-2xl bg-white/10 p-5 backdrop-blur transition hover:bg-white/20">
                        <span class="text-3xl">✈️</span>
                        <span class="text-sm font-semibold">Vuelos</span>
                    </a>
                    <a href="https://www.expediataap.mx" target="_blank" rel="noopener" class="flex flex-col items-center gap-2 rounded-2xl bg-white/10 p-5 backdrop-blur transition hover:bg-white/20">
                        <span class="text-3xl">🧳</span>
                        <span class="text-sm font-semibold">Paquetes</span>
                    </a>
                </div>
            </div>
        </section>

        <!-- Franja de confianza -->
        <section class="bg-[#171327]/95 bg-slate-900 text-white">
            <div class="mx-auto flex max-w-6xl flex-wrap justify-center gap-x-10 gap-y-2 px-4 py-3 text-xs font-medium sm:text-sm">
                <span>💳 Tarifa de agente vs. tarifa normal, siempre a la vista</span>
                <span>⚡ Cotización rápida por WhatsApp</span>
                <span>🔒 Reservas seguras</span>
                <span>🇲🇽 Atención en español</span>
            </div>
        </section>

        <!-- Servicios -->
        <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
            <h2 class="text-center text-2xl font-bold text-slate-800 sm:text-3xl">Todo lo que necesitas para tu viaje</h2>
            <p class="mx-auto mt-2 max-w-2xl text-center text-slate-500">
                Consultamos a nuestros proveedores en tiempo real y comparamos la tarifa de agente contra la tarifa normal, para que veas exactamente cuánto ahorras.
            </p>

            <div class="mt-10 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <article v-for="s in servicios" :key="s.title" class="flex flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                    <span class="text-4xl">{{ s.icon }}</span>
                    <h3 class="mt-4 text-lg font-bold text-slate-800">{{ s.title }}</h3>
                    <p class="mt-2 flex-1 text-sm text-slate-500">{{ s.text }}</p>

                    <template v-if="s.external && trialLocked">
                        <div class="mt-5 rounded-lg border border-amber-200 bg-amber-50 p-3 text-center text-xs font-semibold text-amber-800">
                            🔒 Disponible al activar el proyecto
                        </div>
                    </template>
                    <a
                        v-else
                        :href="s.href"
                        :target="s.external ? '_blank' : undefined"
                        :rel="s.external ? 'noopener noreferrer' : undefined"
                        class="mt-5 inline-flex items-center justify-center gap-2 rounded-lg bg-[#003580] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#0071c2]"
                    >
                        {{ s.cta }}
                    </a>
                </article>
            </div>
        </section>

        <!-- Ahorro / comparación -->
        <section class="border-y border-slate-200 bg-white">
            <div class="mx-auto grid max-w-6xl grid-cols-1 items-center gap-10 px-4 py-16 sm:px-6 lg:grid-cols-2">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800 sm:text-3xl">Siempre sabrás cuánto estás ahorrando</h2>
                    <p class="mt-4 text-slate-500">
                        En cada cotización te mostramos la <strong>tarifa de agente</strong> que conseguimos con nuestros proveedores junto a la <strong>tarifa normal</strong> al público, para que veas claramente tu ahorro antes de reservar.
                    </p>
                    <ul class="mt-6 space-y-3">
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-teal-100 text-teal-700">✓</span>
                            <span class="text-sm text-slate-600">Comparativo transparente: tarifa de agente vs. tarifa normal</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-teal-100 text-teal-700">✓</span>
                            <span class="text-sm text-slate-600">Cotizaciones formales, listas para compartir por WhatsApp</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-teal-100 text-teal-700">✓</span>
                            <span class="text-sm text-slate-600">Autos, hoteles, vuelos y paquetes en un solo lugar</span>
                        </li>
                    </ul>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                        <span class="text-sm font-semibold text-slate-500">Tarifa normal</span>
                        <span class="text-lg font-bold text-slate-400 line-through">$4,200 MXN</span>
                    </div>
                    <div class="flex items-center justify-between pt-3">
                        <span class="text-sm font-semibold text-[#003580]">Tu tarifa de agente</span>
                        <span class="text-2xl font-extrabold text-[#003580]">$3,350 MXN</span>
                    </div>
                    <div class="mt-4 rounded-lg bg-teal-50 px-4 py-2 text-center text-sm font-bold text-teal-700">
                        Ahorras $850 MXN en esta cotización
                    </div>
                    <p class="mt-3 text-center text-xs text-slate-400">Ejemplo ilustrativo — tu tarifa real depende del destino y las fechas.</p>
                </div>
            </div>
        </section>

        <!-- Testimonios -->
        <section id="testimonios" class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
            <h2 class="text-center text-2xl font-bold text-slate-800 sm:text-3xl">Lo que dicen nuestros clientes</h2>
            <div class="mt-10 grid grid-cols-1 gap-5 sm:grid-cols-3">
                <article v-for="t in testimonios" :key="t.nombre" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-amber-400">★★★★★</p>
                    <p class="mt-3 text-sm text-slate-600">“{{ t.texto }}”</p>
                    <p class="mt-4 text-sm font-bold text-slate-800">{{ t.nombre }}</p>
                </article>
            </div>
        </section>

        <!-- Contacto -->
        <section id="contacto" class="bg-gradient-to-br from-[#003580] to-teal-700 px-4 py-16 text-center text-white sm:px-6">
            <div class="mx-auto max-w-2xl">
                <h2 class="text-2xl font-bold sm:text-3xl">¿Listo para tu próximo viaje?</h2>
                <p class="mt-3 text-blue-100">Escríbenos por WhatsApp y arma tu cotización de autos, hoteles, vuelos o paquetes en minutos.</p>
                <a
                    :href="waLink"
                    target="_blank"
                    rel="noopener"
                    class="mt-8 inline-flex items-center gap-2 rounded-lg bg-teal-500 px-8 py-3.5 text-sm font-bold text-white shadow-lg transition hover:bg-teal-400"
                >
                    💬 Cotizar por WhatsApp
                </a>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-slate-200 bg-white py-8">
            <div class="mx-auto flex max-w-6xl flex-col items-center gap-3 px-4 text-center sm:px-6">
                <img src="/brand-logo.jpeg" :alt="businessName" class="h-10 w-auto object-contain" />
                <p class="text-sm text-slate-500">{{ businessName }} · Autos, Hoteles, Vuelos y Paquetes</p>
                <p class="text-sm text-slate-500">
                    Desarrollado por
                    <a :href="waLink" target="_blank" rel="noopener" class="font-semibold text-teal-700 hover:underline">Overcloud</a>
                </p>
                <a :href="waLink" target="_blank" rel="noopener" class="text-sm font-medium text-[#003580] hover:underline">
                    ¿Quieres tu sitio? Escríbenos por WhatsApp
                </a>
            </div>
        </footer>
    </div>
</template>
