<script setup>
import { computed } from 'vue';

const props = defineProps({
    ariaRole: {
        type: String,
        required: false,
    },
    name: {
        type: String,
        required: false,
    },
    role: {
        type: String,
        required: false,
        default: 'clickable',
    },
    shape: String,
    size: {
        type: Number,
        required: false,
        default: 16,
    },
});

const color = computed(() => {
    switch (props.role) {
        case 'info':
            return 'black';

        case 'inactive':
            return 'grey';

        case 'accept':
        case 'status-green':
            return 'green';

        case 'attention':
        case 'new':
        case 'status-red':
            return 'red';

        case 'info_alt':
            return 'white';

        case 'status-yellow':
            return 'yellow';

        case 'sort':
        case 'clickable':
        case 'navigation':
        default:
            return 'blue';
    }
});

const url = computed(() => {
    if (props.shape.startsWith('http')) {
        return props.shape;
    }
    const path = props.shape.split('+').reverse().join('/');
    return `${window.STUDIP.ASSETS_URL}images/icons/${color.value}/${path}.svg`;
});
</script>

<template>
    <input
        v-if="name"
        type="image"
        :name="name"
        :src="url"
        :width="size"
        :height="size"
        :role="ariaRole"
    />
    <img v-else :src="url" :width="size" :height="size" :role="ariaRole" />
</template>
