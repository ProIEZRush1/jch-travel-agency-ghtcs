<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    markSize: { type: String, default: 'h-10 w-10' },
    textSize: { type: String, default: 'text-2xl' },
    showText: { type: Boolean, default: false },
});

const page = usePage();
const name = computed(() => page.props.name || 'JCH Travel Agency');
</script>

<template>
    <div class="flex items-center gap-3">
        <img
            src="/brand-logo.jpeg"
            :alt="name"
            :class="markSize"
            class="object-contain rounded-lg"
        />
        <span
            v-if="showText"
            :class="textSize"
            class="font-extrabold tracking-tight text-[#003580]"
        >
            {{ name }}
        </span>
    </div>
</template>
