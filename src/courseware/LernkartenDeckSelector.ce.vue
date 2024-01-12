<script setup>
import { computed, ref } from 'vue';
import DeckSelector from '../components/DeckSelector.vue';
import StudipCompanion from '../components/base/StudipCompanion.vue';
import StudipProgressIndicator from '../components/base/StudipProgressIndicator.vue';
import { useContextStore } from '../stores/context.js';
import { useDecksStore } from '../stores/decks.js';
import { useFoldersStore } from '../stores/folders.js';
import { useSharedDecksStore } from '../stores/shared-decks.js';

const selectedDeck = ref(null);

const contextStore = useContextStore();
const decksStore = useDecksStore();
const foldersStore = useFoldersStore();
const sharedDecksStore = useSharedDecksStore();
foldersStore.fetchWorkplace().then(() => {
    decksStore.fetchWorkplace();
    sharedDecksStore.fetchContext().then(() => {
        if (sharedDeck.value) {
            selectedDeck.value = sharedDeck.value.deck.data.id;
        }
    });
});

const props = defineProps(['deck']);
defineEmits(['change']);

// custom elements specifics
const externalCss = window.STUDIP.ASSETS_URL + 'stylesheets/studip-base.css';
const emitChange = (root, callback) => {
    console.debug('emitting to the host document', root, callback);
    root.$emit('change', callback);
};

const isEmpty = computed(
    () => decksStore.fromWorkplace.filter(({ colearning }) => !colearning).length === 0
);
const workingPlaceUrl = computed(() =>
    window.STUDIP.URLHelper.getURL('plugins.php/lernkartenplugin/search', {}, true)
);
const mySharedDecks = computed(() =>
    sharedDecksStore.all.filter((sharedDeck) => sharedDeck.sharer.data.id === contextStore.userId)
);
const sharedDeck = computed(() => (props.deck ? sharedDecksStore.byId(props.deck) : null));

const onSelectDeck = async (root) => {
    if (!(selectedDeck.value && contextStore.isCourse)) {
        return;
    }
    const callback = () =>
        new Promise((resolve, reject) => {
            const alreadyShared = mySharedDecks.value.find(
                ({ deck }) => deck.data.id === selectedDeck.value
            );
            if (alreadyShared) {
                resolve(alreadyShared);
            }

            sharedDecksStore
                .shareDeckWithCourse(
                    { id: selectedDeck.value },
                    { id: contextStore.id, type: contextStore.type }
                )
                .then(resolve)
                .catch(reject);
        });
    emitChange(root, callback);
};
</script>

<template>
    <link media="screen" rel="stylesheet" :href="externalCss" />
    <StudipProgressIndicator
        :description="$gettext('Lade Kartensätze …')"
        v-if="decksStore.isLoading"
    />

    <template v-if="!decksStore.isLoading">
        <form v-if="!isEmpty" class="default studipform" @submit.prevent="onConfirm">
            <div class="formpart">
                <label class="studiprequired">
                    <span class="textlabel">
                        {{ $gettext('Welchen Kartensatz möchten Sie teilen?') }}
                    </span>
                    <span
                        class="asterisk"
                        :title="$gettext('Dies ist ein Pflichtfeld')"
                        aria-hidden="true"
                        >*</span
                    >
                    <DeckSelector
                        v-model="selectedDeck"
                        @update:modelValue="() => onSelectDeck($root)"
                    />
                </label>
            </div>
        </form>
        <StudipCompanion
            v-if="isEmpty"
            :msg-companion="$gettext('Sie haben keinen Kartensatz im eigenen Arbeitsplatz.')"
        >
            <template #companionActions>
                <a :href="workingPlaceUrl" class="button">
                    {{ $gettext('Zum eigenen Arbeitsplatz') }}
                </a>
            </template>
        </StudipCompanion>
    </template>
</template>
