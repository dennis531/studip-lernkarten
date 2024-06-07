<script setup>
import { computed, ref } from 'vue';
import { RouterLink } from 'vue-router';
import DeckDetails from '../components/DeckDetails.vue';
import IconButton from '../components/IconButton.vue';
import DialogAdjustLearningOptions from '../components/DialogAdjustLearningOptions.vue';
import DialogEditDeck from '../components/DialogEditDeck.vue';
import DialogMoveDeck from '../components/DialogMoveDeck.vue';
import DialogShareDeck from '../components/DialogShareDeck.vue';
import StudipIcon from '../components/base/StudipIcon.vue';
import StudipProgressIndicator from '../components/base/StudipProgressIndicator.vue';
import { useCardsStore } from '../stores/cards.js';
import { useContextStore } from '../stores/context.js';
import { useDecksStore } from '../stores/decks.js';

const cardsStore = useCardsStore();
const contextStore = useContextStore();
const decksStore = useDecksStore();

const props = defineProps(['id']);

const showEditDialog = ref(false);
const showAdjustLearningDialog = ref(false);
const showMoveDialog = ref(false);
const showShareDialog = ref(false);

decksStore.fetchById(props.id);
cardsStore.fetchByDeck({ id: props.id });

const deck = computed(() => decksStore.byId(props.id));
const deckOwner = computed(() => deck.value?.owner.data);
const folder = computed(() => deck.value?.folder.data ?? null);
const isColearning = computed(() => deck.value?.colearning);
const isOwner = computed(() => deckOwner.value && contextStore.userId === deckOwner.value.id);

const onAdjustLearn = () => (showAdjustLearningDialog.value = true);
const onShowEditDialog = () => (showEditDialog.value = true);
const onShowMoveDialog = () => (showMoveDialog.value = true);
const onShowShareDialog = () => (showShareDialog.value = true);
</script>

<template>
    <div v-if="decksStore.isLoading">
        <StudipProgressIndicator :description="$gettext('Lade Kartensatz…')" />
    </div>
    <div v-else>
        <div
            v-if="isOwner"
            class="tw-mb-6 tw-flex tw-flex-row tw-items-center tw-flex-wrap sm:tw-flex-nowrap"
        >
            <div class="tw-grow">
                <div v-if="folder" :title="$gettext('Zurück zum Ordner')">
                    <RouterLink
                        :to="{ name: 'folder', params: { id: folder.id } }"
                        class="tw-flex tw-items-center tw-gap-2"
                    >
                        <StudipIcon
                            shape="arr_1left"
                            :height="30"
                            :width="30"
                            class="tw-align-middle tw-mb-1"
                        />
                        <StudipIcon
                            shape="folder-empty"
                            :height="30"
                            :width="30"
                            class="tw-align-middle tw-mb-1"
                        />
                        <span class="breadcrumb">
                            {{ folder.name }}
                        </span>
                    </RouterLink>
                </div>
                <div v-else :title="$gettext('Zurück zur Ordnerübersicht')">
                    <RouterLink to="/" class="tw-flex tw-items-center tw-gap-2 tw-italic">
                        <StudipIcon
                            shape="arr_1left"
                            :height="30"
                            :width="30"
                            class="tw-align-middle tw-mb-1"
                            ariaRole="none"
                        />
                        <StudipIcon
                            shape="folder-home-empty"
                            :height="30"
                            :width="30"
                            class="tw-align-middle tw-mb-1"
                            ariaRole="none"
                        />
                    </RouterLink>
                </div>
                <div class="tw-mt-3 tw-font-bold tw-text-lg">{{ deck.name }}</div>
            </div>
            <div>
                <IconButton icon="refresh" type="button" @click="onAdjustLearn">
                    {{ $gettext('Lernen') }}
                </IconButton>
                <IconButton
                    v-if="!isColearning"
                    icon="share"
                    type="button"
                    @click="onShowShareDialog"
                >
                    {{ $gettext('Teilen') }}
                </IconButton>
                <IconButton
                    v-if="!isColearning"
                    icon="edit"
                    type="button"
                    @click="onShowEditDialog"
                >
                    {{ $gettext('Bearbeiten') }}
                </IconButton>
                <IconButton v-if="isColearning" icon="edit" type="button" @click="onShowMoveDialog">
                    {{ $gettext('Verschieben') }}
                </IconButton>
            </div>
        </div>
        <DeckDetails :deck="deck" />
    </div>
    <DialogAdjustLearningOptions v-model:open="showAdjustLearningDialog" :decks="[deck]" />
    <DialogEditDeck v-model:open="showEditDialog" :deck="deck" />
    <DialogMoveDeck v-if="showMoveDialog" v-model:open="showMoveDialog" :deck="deck" />
    <DialogShareDeck v-if="showShareDialog" v-model:open="showShareDialog" :deck="deck" />
</template>

<style scoped>
span.breadcrumb {
    color: var(--headings-color);
    font-size: 1.4em;
    text-align: left;
}
</style>
