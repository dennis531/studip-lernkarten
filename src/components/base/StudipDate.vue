<script setup>
import { computed } from 'vue';

const props = defineProps({
    date: {
        type: Date,
        required: true,
    },
});
const formatDate = (date) => {
    return pad(date.getDate()) + '.' + pad(date.getMonth() + 1) + '.' + date.getFullYear();
};

const pad = (what) => {
    return what.toString().padStart(2, '0');
};

const formatted = computed(() => formatDate(props.date));
</script>

<template>
    <time :datetime="date.toISOString()">{{ formatted }}</time>
</template>
