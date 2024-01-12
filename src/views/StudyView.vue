<script setup>
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';

import StudyView from '../components/StudyView.vue';

defineProps(['decks', 'order']);

const router = useRouter();
onMounted(enableCompactNavigation);

const onCancel = () => {
    router.push({ name: 'home' });
    disableCompactNavigation();
};

function disableCompactNavigation() {
    // STUDIP.Vue.emit('toggle-compact-navigation', false);
}
function enableCompactNavigation() {
    // STUDIP.Vue.emit('toggle-compact-navigation', true);
}
</script>

<template>
    <StudyView :decks="decks" :order="order" @cancel="onCancel" />
</template>
