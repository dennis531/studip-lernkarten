<script setup>
import { computed, ref } from 'vue';
import DialogColearnSharedDeck from '../components/DialogColearnSharedDeck.vue';
import DialogShareDeckHere from '../components/DialogShareDeckHere.vue';
import DialogShowDeck from '../components/DialogShowDeck.vue';
import SharedByMeDeckList from '../components/SharedByMeDeckList.vue';
import SharedWithMeDeckList from '../components/SharedWithMeDeckList.vue';
import SidebarAction from '../components/SidebarAction.vue';
import StudipCompanion from '../components/base/StudipCompanion.vue';
import StudipProgressIndicator from '../components/base/StudipProgressIndicator.vue';
import { useCardsStore } from '../stores/cards.js';
import { useContextStore } from '../stores/context.js';
import { useCourseMembershipsStore } from '../stores/course-memberships.js';
import { useDecksStore } from '../stores/decks.js';
import { useSharedDecksStore } from '../stores/shared-decks.js';

const cardsStore = useCardsStore();
const contextStore = useContextStore();
const courseMembershipsStore = useCourseMembershipsStore();
const decksStore = useDecksStore();
const sharedDecksStore = useSharedDecksStore();

courseMembershipsStore.fetchContext();
decksStore.fetchContext();
decksStore.fetchWorkplace();
sharedDecksStore.fetchContext();

const selectedDeck = ref(null);
const selectedSharedDeck = ref(null);
const showColearnDialog = ref(false);
const showDeckDialog = ref(false);
const showShareDialog = ref(false);

const sharedByMe = computed(() =>
    sharedDecksStore.all.filter((sharedDeck) => sharedDeck.sharer.data.id === contextStore.userId),
);
const sharedWithMe = computed(() =>
    sharedDecksStore.all.filter((sharedDeck) => sharedDeck.sharer.data.id !== contextStore.userId),
);
const doneLoading = computed(
    () => !courseMembershipsStore.isLoading && !decksStore.isLoading && !sharedDecksStore.isLoading,
);
const workingPlaceUrl = computed(() =>
    window.STUDIP.URLHelper.getURL('plugins.php/lernkartenplugin/search', {}, true),
);
const isAtLeastTutor = computed(() =>
    ['tutor', 'dozent'].includes(courseMembershipsStore.byContext()?.permission),
);
const isTeacher = computed(() => contextStore.isCourse && contextStore.isTeacher);

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
const onShareDeck = () => (showShareDialog.value = true);
</script>

<template>
    <SidebarAction
        v-if="isTeacher"
        icon="share"
        :text="$gettext('Kartensatz hierhin teilen')"
        @click="onShareDeck"
    />
    <main>
        <StudipProgressIndicator
            v-if="!doneLoading"
            :description="$gettext('Lade Veranstaltungsdaten …')"
        />
        <template v-if="doneLoading">
            <article class="studip" v-if="sharedWithMe.length">
                <header>
                    <h1>{{ $gettext('Mit mir geteilte Kartensätze') }}</h1>
                </header>
                <SharedWithMeDeckList
                    :shared-decks="sharedWithMe"
                    @colearn="onColearnSharedDeck"
                    @select="onSelectSharedDeck"
                />
            </article>
            <article class="studip" v-if="isAtLeastTutor">
                <header>
                    <h1>{{ $gettext('Von mir geteilte Kartensätze') }}</h1>
                </header>
                <SharedByMeDeckList v-if="sharedByMe.length" :shared-decks="sharedByMe" />
                <StudipCompanion
                    v-else
                    :msg-companion="
                        $gettext(
                            'Sie haben noch keinen Kartensatz mit dieser Veranstaltung geteilt.',
                        )
                    "
                    mood="sad"
                >
                    <template #companionActions>
                        <a :href="workingPlaceUrl" class="button add">
                            {{ $gettext('Kartensatz teilen') }}
                        </a>
                    </template>
                </StudipCompanion>
            </article>
        </template>
    </main>
    <DialogColearnSharedDeck v-model:open="showColearnDialog" :shared-deck="selectedSharedDeck" />
    <DialogShareDeckHere v-if="showShareDialog" v-model:open="showShareDialog" />
    <DialogShowDeck v-model:open="showDeckDialog" :deck="selectedDeck" />
</template>
