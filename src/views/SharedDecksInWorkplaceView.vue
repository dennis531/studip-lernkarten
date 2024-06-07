<script setup>
import { computed, ref } from 'vue';
import DialogColearnSharedDeck from '../components/DialogColearnSharedDeck.vue';
import DialogShowDeck from '../components/DialogShowDeck.vue';
import SharedByMeDeckList from '../components/SharedByMeDeckList.vue';
import SharedWithMeDeckList from '../components/SharedWithMeDeckList.vue';
import StudipCompanion from '../components/base/StudipCompanion.vue';
import StudipProgressIndicator from '../components/base/StudipProgressIndicator.vue';
import { useCardsStore } from '../stores/cards.js';
import { useContextStore } from '../stores/context.js';
import { useDecksStore } from '../stores/decks.js';
import { useSharedDecksStore } from '../stores/shared-decks.js';

const cardsStore = useCardsStore();
const contextStore = useContextStore();
const decksStore = useDecksStore();
const sharedDecksStore = useSharedDecksStore();

decksStore.fetchContext();
sharedDecksStore.fetchContext();

const selectedDeck = ref(null);
const selectedSharedDeck = ref(null);
const showColearnDialog = ref(false);
const showDeckDialog = ref(false);

const sharedByMe = computed(() =>
    sharedDecksStore.all.filter((sharedDeck) => sharedDeck.sharer.data.id === contextStore.userId),
);
const sharedWithMe = computed(() =>
    sharedDecksStore.all.filter((sharedDeck) => sharedDeck.sharer.data.id !== contextStore.userId),
);
const doneLoading = computed(() => !decksStore.isLoading && !sharedDecksStore.isLoading);

const onColearnSharedDeck = (sharedDeck) => {
    selectedSharedDeck.value = sharedDeck;
    showColearnDialog.value = true;
};

const onSelectSharedDeck = (sharedDeck) => {
    const deck = sharedDeck.deck.data;
    cardsStore.fetchByDeck({ id: deck.id });
    selectedDeck.value = deck;
    showDeckDialog.value = true;
};
</script>

<template>
    <main>
        <StudipProgressIndicator
            v-if="!doneLoading"
            :description="$gettext('Lade Kartensätze …')"
        />
        <template v-if="doneLoading">
            <article class="studip">
                <header>
                    <h1>{{ $gettext('Mit mir geteilte Kartensätze') }}</h1>
                </header>
                <SharedWithMeDeckList
                    v-if="sharedWithMe.length"
                    :shared-decks="sharedWithMe"
                    @colearn="onColearnSharedDeck"
                    @select="onSelectSharedDeck"
                />
                <StudipCompanion
                    v-if="!sharedWithMe.length"
                    mood="sad"
                    :msg-companion="
                        $gettext('Bisher hat niemand einen Kartensatz mit Ihnen geteilt.')
                    "
                >
                </StudipCompanion>
            </article>
            <article class="studip">
                <header>
                    <h1>{{ $gettext('Von mir geteilte Kartensätze') }}</h1>
                </header>
                <SharedByMeDeckList v-if="sharedByMe.length" :shared-decks="sharedByMe" />
                <StudipCompanion
                    v-if="!sharedByMe.length"
                    mood="sad"
                    :msg-companion="$gettext('Bisher haben Sie keinen Kartensatz geteilt.')"
                >
                </StudipCompanion>
            </article>
        </template>
    </main>
    <DialogColearnSharedDeck v-model:open="showColearnDialog" :shared-deck="selectedSharedDeck" />
    <DialogShowDeck v-model:open="showDeckDialog" :deck="selectedDeck" />
</template>
