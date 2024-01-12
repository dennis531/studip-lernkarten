<script setup>
import { useRouter } from 'vue-router';
import CardDeck from './CardDeck.vue';

const router = useRouter();

defineProps(['decks']);

const onSelectDeck = (deck) => {
    router.push({ name: 'deck', params: { id: deck.id } });
};
</script>

<template>
    <section>
        <article v-for="deck in decks" :key="deck.id">
            <CardDeck :deck="deck" @select="onSelectDeck" />
        </article>
    </section>
</template>

<style scoped>
article + article {
    margin-block-start: 1rem;
}
</style>
