<script setup>
import { computed } from 'vue';
import CardDeck from './CardDeck.vue';
import CardSharedDeck from './CardSharedDeck.vue';

const props = defineProps(['sharedDeck']);
defineEmits(['select']);

const colearningDeck = computed(() => props.sharedDeck['colearning-deck'].data);
</script>

<template>
    <CardDeck v-if="colearningDeck" :deck="colearningDeck" @select="$emit('select')" />
    <CardSharedDeck v-else :shared-deck="sharedDeck" @select="$emit('select')" />
</template>
