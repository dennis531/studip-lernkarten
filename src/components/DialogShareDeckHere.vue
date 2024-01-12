<script setup>
import { computed, ref } from 'vue';
import DeckSelector from './DeckSelector.vue';
import StudipCompanion from './base/StudipCompanion.vue';
import StudipDialog from './base/StudipDialog.vue';
import StudipProgressIndicator from './base/StudipProgressIndicator.vue';
import { useContextStore } from '../stores/context.js';
import { useDecksStore } from '../stores/decks.js';
import { useSharedDecksStore } from '../stores/shared-decks.js';

const contextStore = useContextStore();
const decksStore = useDecksStore();
const sharedDecksStore = useSharedDecksStore();

// TODO: Wie kann ich verhindern, dass das an anderer Stelle doppelt/noch einmal passiert?
decksStore.fetchWorkplace();
sharedDecksStore.fetchContext();

defineProps(['open']);
const emit = defineEmits(['update:open']);

const initialFocus = ref(null);
const selectedDeck = ref(null);

const reset = () => {};
const setIsOpen = (value) => {
    emit('update:open', value);
    reset();
};

const isEmpty = computed(
    () => decksStore.fromWorkplace.filter(({ colearning }) => !colearning).length === 0,
);

const workingPlaceUrl = computed(() =>
    window.STUDIP.URLHelper.getURL('plugins.php/lernkartenplugin/search', {}, true),
);

const confirmDisabled = computed(() => !selectedDeck.value);

const alreadyShared = computed(() => {
    return sharedDecksStore.byContext.map(({ deck }) => deck.data.id);
});

const onConfirm = () => {
    if (selectedDeck.value && contextStore.isCourse) {
        sharedDecksStore
            .shareDeckWithCourse(
                { id: selectedDeck.value },
                { id: contextStore.id, type: contextStore.type },
            )
            .then(() => setIsOpen(false));
    }
};
</script>

<template>
    <StudipDialog
        :close-text="$gettext('Schließen')"
        :confirm-disabled="confirmDisabled"
        :confirm-text="$gettext('Teilen')"
        :initial-focus="initialFocus"
        :open="open"
        :title="$gettext('Kartensatz hierhin teilen')"
        :width="600"
        @confirm="onConfirm"
        @update:open="setIsOpen"
    >
        <template #dialogContent>
            <StudipProgressIndicator
                :description="$gettext('Lade Kartensätze …')"
                v-if="decksStore.isLoading"
            />
            <template v-if="!decksStore.isLoading">
                <form v-if="!isEmpty" class="default studipform" @submit.prevent="onConfirm">
                    <div class="formpart">
                        <label class="studiprequired">
                            <span class="textlabel">
                                {{
                                    $gettext(
                                        'Welchen Kartensatz möchten Sie mit dieser Veranstaltung teilen?',
                                    )
                                }}
                            </span>
                            <span
                                class="asterisk"
                                :title="$gettext('Dies ist ein Pflichtfeld')"
                                aria-hidden="true"
                                >*</span
                            >
                            <DeckSelector v-model="selectedDeck" :disabled-decks="alreadyShared" />
                        </label>
                    </div>
                </form>
                <StudipCompanion
                    v-if="isEmpty"
                    :msg-companion="
                        $gettext('Sie haben keinen Kartensatz im eigenen Arbeitsplatz.')
                    "
                >
                    <template #companionActions>
                        <a :href="workingPlaceUrl" class="button">
                            {{ $gettext('Zum eigenen Arbeitsplatz') }}
                        </a>
                    </template>
                </StudipCompanion>
            </template>
        </template>
    </StudipDialog>
</template>
