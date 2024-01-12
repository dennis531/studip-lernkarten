<script setup>
import { computed, ref } from 'vue';
import CardList from './CardList.vue';
import DialogAddCard from './DialogAddCard.vue';
import DialogShowCard from './DialogShowCard.vue';
import { useCardsStore } from '../stores/cards.js';
import { useContextStore } from '../stores/context.js';

const cardsStore = useCardsStore();
const contextStore = useContextStore();

const props = defineProps(['deck']);

const selectedCard = ref(null);
const showAddCardDialog = ref(false);
const showCardDialog = ref(false);

const cards = computed(() => cardsStore.byDeck(props.deck));
const mayEdit = computed(
    () => !props.deck.colearning && contextStore.userId === props.deck.owner.data.id
);
const selectedIndex = computed(() => {
    return selectedCard.value
        ? cards.value.findIndex(({ id }) => id === selectedCard.value.id)
        : -1;
});

const onAddCard = () => {
    showAddCardDialog.value = true;
};

const onSelectCard = (card) => {
    showCardDialog.value = true;
    selectedCard.value = card;
};

const onShowNext = () => {
    const index = cards.value.findIndex(({ id }) => id === selectedCard.value.id);
    const nextIndex = index + 1 === cards.value.length ? 0 : index + 1;
    const nextCard = cards.value[nextIndex];
    selectedCard.value = nextCard;
};

const onShowPrev = () => {
    const index = cards.value.findIndex(({ id }) => id === selectedCard.value.id);
    const prevIndex = index - 1 < 0 ? cards.value.length - 1 : index - 1;
    const prevCard = cards.value[prevIndex];
    selectedCard.value = prevCard;
};

const onCheckCardLength = () => {
    if (cards.value.length == 0) {
        showCardDialog.value = false;
    }
};
</script>

<template>
    <div v-if="mayEdit">
        <button @click="onAddCard" class="button add" type="button">
            {{ $gettext('Neue Karte anlegen') }}
        </button>
    </div>
    <div>
        <CardList :cards="cards" @select="onSelectCard" />
    </div>
    <DialogAddCard v-model:open="showAddCardDialog" :deck="deck" />
    <DialogShowCard
        v-model:open="showCardDialog"
        :deck="deck"
        :card="selectedCard"
        :card-index="selectedIndex"
        :number-of-cards="cards.length"
        @show-next="onShowNext"
        @show-prev="onShowPrev"
        @delete="onCheckCardLength"
    />
</template>
