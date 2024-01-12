<script setup>
import CardListItem from './CardListItem.vue';

defineProps(['cards']);
const emit = defineEmits(['select']);

const onSelect = (card) => emit('select', card);
</script>
<template>
    <article class="studip">
        <header>
            <h1>Card list</h1>
        </header>
        <CardListItem
            v-for="(card, index) in cards"
            :key="card.id"
            :card="card"
            :index="index"
            @select="() => onSelect(card)"
        />
    </article>
</template>
