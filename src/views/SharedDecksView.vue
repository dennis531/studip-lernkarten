<script setup>
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import DialogShareDeckHere from '../components/DialogShareDeckHere.vue';
import DialogShowDeck from '../components/DialogShowDeck.vue';
import SharedDeckList from '../components/SharedDeckList.vue';
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
const router = useRouter();

courseMembershipsStore.fetchContext();
decksStore.fetchContext();
sharedDecksStore.fetchContext();

const selectedDeck = ref(null);
const showDeckDialog = ref(false);
const showShareDialog = ref(false);

const isAtLeastTutor = computed(() =>
    ['tutor', 'dozent'].includes(courseMembershipsStore.byContext()?.permission)
);
const sharedByMe = computed(() =>
    sharedDecksStore.all.filter((sharedDeck) => sharedDeck.sharer.data.id === contextStore.userId)
);
const sharedWithMe = computed(() =>
    sharedDecksStore.all.filter((sharedDeck) => sharedDeck.sharer.data.id !== contextStore.userId)
);
const doneLoading = computed(
    () => !courseMembershipsStore.isLoading && !decksStore.isLoading && !sharedDecksStore.isLoading
);
const workingPlaceUrl = computed(() =>
    window.STUDIP.URLHelper.getURL('plugins.php/lernkartenplugin/search', {}, true)
);
const isTeacher = computed(() => contextStore.isCourse && contextStore.isTeacher);

const onSelectSharedDeck = (sharedDeck) => {
    const deck = sharedDeck['colearning-deck'].data || sharedDeck.deck.data;
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
                <SharedDeckList :shared-decks="sharedWithMe" @select="onSelectSharedDeck" />
            </article>
            <article class="studip" v-if="isAtLeastTutor">
                <header>
                    <h1>{{ $gettext('Von mir geteilte Kartensätze') }}</h1>
                </header>
                <SharedDeckList
                    v-if="sharedByMe.length"
                    :shared-decks="sharedByMe"
                    @select="onSelectSharedDeck"
                />
                <StudipCompanion
                    v-else
                    :msg-companion="
                        $gettext(
                            'Sie haben noch keinen Kartensatz mit dieser Veranstaltung geteilt.'
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
    <DialogShareDeckHere v-if="showShareDialog" v-model:open="showShareDialog" />
    <DialogShowDeck v-model:open="showDeckDialog" :deck="selectedDeck" />
</template>
