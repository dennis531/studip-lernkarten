<script setup>
import { computed } from 'vue';
const props = defineProps(['progress']);
const percent = computed(() => Math.floor(props.progress * 100));
const circumference = 30 * 2 * Math.PI;
</script>
<template>
    <div
        class="tw-overflow-hidden tw-rounded-full tw-relative tw-flex tw-items-center tw-justify-center"
    >
        <svg class="tw-w-20 tw-h-20">
            <circle
                class="tw-text-gray-300"
                stroke-width="5"
                stroke="currentColor"
                fill="transparent"
                r="30"
                cx="40"
                cy="40"
            />
            <circle
                class="tw-text-blue-600"
                stroke-width="5"
                :stroke-dasharray="circumference"
                :stroke-dashoffset="circumference - (percent / 100) * circumference"
                stroke-linecap="round"
                stroke="currentColor"
                fill="transparent"
                r="30"
                cx="40"
                cy="40"
            />
        </svg>
        <span class="tw-absolute tw-text-xl tw-text-blue-700">{{ percent }}%</span>
    </div>
</template>
