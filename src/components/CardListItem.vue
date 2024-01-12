<script setup>
import CardSmallImage from './CardSmallImage.vue';

defineProps(['card', 'index']);
const emit = defineEmits(['select']);

const extractContent = (html) => {
    return new DOMParser().parseFromString(html, 'text/html').documentElement.textContent;
};
</script>

<template>
    <div class="lernkarten-card-list-item" @click="emit('select')">
        <div>
            <CardSmallImage v-if="card.fields.images?.front" :image="card.fields.images['front']" />
            {{ extractContent(card.fields.front) }}
        </div>
        <div>
            <CardSmallImage v-if="card.fields.images?.back" :image="card.fields.images['back']" />
            {{ extractContent(card.fields.back) }}
        </div>
    </div>
</template>

<style>
.lernkarten-card-list-item {
    display: flex;
    cursor: pointer;
    border: 1px solid var(--base-color-40);
}
.lernkarten-card-list-item + .lernkarten-card-list-item {
    margin-block-start: 0.5rem;
}
.lernkarten-card-list-item > div {
    flex-basis: 50%;
    padding: 0.5rem;
}
.lernkarten-card-list-item > div:last-child {
    background-color: var(--base-color-20);
    border-left: 1px solid var(--base-color-40);
}
</style>
