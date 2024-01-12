<script setup>
import { computed } from 'vue';
const props = defineProps(['percent']);

const percentString = computed(() => `${props.percent}%`);
const style = computed(() => ({ width: percentString.value }));
</script>
<template>
    <div class="tw-w-full tw-bg-[var(--base-color-20)] tw-h-2.5">
        <div class="tw-bg-[var(--base-color-60)] tw-h-2.5" :style="style">
            <span class="sr-only">{{ percentString }}</span>
        </div>
    </div>
</template>
