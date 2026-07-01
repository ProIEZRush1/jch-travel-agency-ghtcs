<script setup>
import { computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const name = computed(() => page.props.name || 'JCH Travel Agency');
</script>

<template>
    <div class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-gradient-to-br from-sky-50 via-white to-blue-50 px-4 py-10">
        <!-- Decorative background elements -->
        <div class="pointer-events-none absolute -top-32 right-0 h-96 w-96 rounded-full bg-[#0ea5e9]/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-40 -left-24 h-[28rem] w-[28rem] rounded-full bg-[#003580]/8 blur-3xl"></div>

        <div class="relative z-10 w-full max-w-md">
            <!-- Brand header -->
            <div class="mb-8 flex flex-col items-center text-center">
                <Link href="/" class="transition hover:opacity-90">
                    <ApplicationLogo mark-size="h-20 w-auto" :show-text="false" />
                </Link>
                <p class="mt-3 text-sm text-slate-500">{{ name }}</p>
            </div>

            <!-- Card -->
            <div class="w-full rounded-2xl border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/60 sm:p-10">
                <slot />
            </div>

            <!-- Footer credit -->
            <p class="mt-8 text-center text-xs text-slate-400">
                {{ name }} &middot; Desarrollado por
                <span class="font-semibold text-slate-500">Overcloud</span>
            </p>
        </div>
    </div>
</template>
