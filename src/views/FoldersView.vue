<script setup>
import { computed, ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import DeckList from '../components/DeckList.vue';
import DialogAdjustLearningOptions from '../components/DialogAdjustLearningOptions.vue';
import DialogCreateFolder from '../components/DialogCreateFolder.vue';
import DialogEditFolder from '../components/DialogEditFolder.vue';
import DialogConfirmDeleteFolder from '../components/DialogConfirmDeleteFolder.vue';
import DialogShowDeck from '../components/DialogShowDeck.vue';
import FolderList from '../components/FolderList.vue';
import IconButton from '../components/IconButton.vue';
import SharedDeckList from '../components/SharedDeckList.vue';
import SidebarAction from '../components/SidebarAction.vue';
import StudipCompanion from '../components/base/StudipCompanion.vue';
import StudipIcon from '../components/base/StudipIcon.vue';
import { useCardsStore } from '../stores/cards.js';
import { useContextStore } from '../stores/context.js';
import { useDecksStore } from '../stores/decks.js';
import { useFoldersStore } from '../stores/folders.js';
import { useSharedDecksStore } from '../stores/shared-decks.js';

const cardsStore = useCardsStore();
const contextStore = useContextStore();
const decksStore = useDecksStore();
const foldersStore = useFoldersStore();
const sharedDecksStore = useSharedDecksStore();
const router = useRouter();

decksStore.fetchContext();
sharedDecksStore.fetchContext();

const confirmDeleteDialogOpen = ref(false);
const createDialogOpen = ref(false);
const editDialogOpen = ref(false);
const editFolderObject = ref(null);
const selectedDeck = ref(null);
const selectedFolder = ref(null);
const showAdjustLearningDialog = ref(false);
const showDeckDialog = ref(false);

const topFolders = computed(() => foldersStore.topFolders);
const decks = computed(() =>
    _.sortBy(
        decksStore.byContext.filter((deck) => !deck.folder.data && !deck.colearning),
        ['name']
    )
);
const sharedWithMe = computed(() =>
    sharedDecksStore.all.filter((sharedDeck) => sharedDeck.sharer.data.id !== contextStore.userId)
);
const isWorkplace = computed(() => !contextStore.isCourse);

const addTopFolder = () => (createDialogOpen.value = true);
const editFolder = (folder) => {
    editDialogOpen.value = true;
    editFolderObject.value = folder;
};
const onEditDialog = (folder, name) => {
    editDialogOpen.value = false;
    foldersStore.updateFolder(folder, { name });
};
const onCreateDialog = (name) => {
    createDialogOpen.value = false;
    foldersStore.createFolder(name, null);
};
const deleteFolder = (folder) => {
    confirmDeleteDialogOpen.value = true;
    selectedFolder.value = folder;
};
const onConfirmDeleteDialog = () => {
    confirmDeleteDialogOpen.value = false;
    foldersStore.deleteFolder(selectedFolder.value);
};
const onLearnDecks = () => (showAdjustLearningDialog.value = true);
const onSelectSharedDeck = (sharedDeck) => {
    const deck = sharedDeck['colearning-deck'].data || sharedDeck.deck.data;
    cardsStore.fetchByDeck({ id: deck.id });
    selectedDeck.value = deck;
    showDeckDialog.value = true;
};
const onCreateDeck = () => {
    router.push({ name: 'decks-create', query: { f: null } });
};
</script>

<template>
    <SidebarAction
        v-if="isWorkplace"
        icon="add"
        :text="$gettext('Neuen Kartensatz erstellen')"
        @click="onCreateDeck"
    />
    <SidebarAction
        v-if="decks.length"
        icon="refresh"
        :text="$gettext('Kartensätze lernen')"
        @click="onLearnDecks"
    />
    <table class="default">
        <caption>
            <nav>
                <span :title="$gettext('Zum Hauptordner')">
                    <RouterLink :to="{ name: 'home' }">
                        <StudipIcon
                            shape="folder-home-empty"
                            :height="30"
                            :width="30"
                            class="tw-align-middle tw-mr-2 tw-mb-1"
                        />
                        <span class="">{{ $gettext('Lernkarten') }}</span>
                    </RouterLink>
                </span>
            </nav>
        </caption>

        <FolderList :folders="topFolders" @delete-folder="deleteFolder" @edit-folder="editFolder">
            <template #empty>
                <StudipCompanion :msgCompanion="$gettext('Es gibt noch keinen Ordner.')">
                    <template #companionActions>
                        <IconButton icon="add" @click="addTopFolder">
                            {{ $gettext('Ordner anlegen') }}
                        </IconButton>
                    </template>
                </StudipCompanion>
            </template>
        </FolderList>

        <tfoot>
            <tr>
                <td colspan="3">
                    <div class="footer-items">
                        <IconButton type="button" icon="add" @click="addTopFolder">
                            {{ $gettext('Ordner anlegen') }}
                        </IconButton>
                    </div>
                </td>
            </tr>
        </tfoot>
    </table>

    <section class="tw-mt-12" v-if="decks.length">
        <header>
            <h3>
                {{ $gettext('Kartensätze ohne Ordner') }}
            </h3>
        </header>
        <DeckList :decks="decks" />
    </section>

    <section class="tw-mt-12" v-if="sharedWithMe.length">
        <header>
            <h3>
                {{ $gettext('Mit mir geteilte Kartensätze') }}
            </h3>
        </header>
        <SharedDeckList :shared-decks="sharedWithMe" @select="onSelectSharedDeck" />
    </section>

    <DialogAdjustLearningOptions v-model:open="showAdjustLearningDialog" :decks="decks" />
    <DialogCreateFolder v-model:open="createDialogOpen" @confirm="onCreateDialog" />
    <DialogEditFolder
        v-model:open="editDialogOpen"
        :folder="editFolderObject"
        @confirm="onEditDialog"
    />
    <DialogConfirmDeleteFolder
        v-model:open="confirmDeleteDialogOpen"
        @confirm="onConfirmDeleteDialog"
    />
    <DialogShowDeck v-model:open="showDeckDialog" :deck="selectedDeck" />
</template>
