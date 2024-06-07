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
import SidebarAction from '../components/SidebarAction.vue';
import StudipCompanion from '../components/base/StudipCompanion.vue';
import StudipIcon from '../components/base/StudipIcon.vue';
import { useContextStore } from '../stores/context.js';
import { useDecksStore } from '../stores/decks.js';
import { useFoldersStore } from '../stores/folders.js';
import { useSharedDecksStore } from '../stores/shared-decks.js';

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
        decksStore.byContext.filter((deck) => !deck.folder.data),
        ['name'],
    ),
);
const hasUnusedSharedDecks = computed(() => unusedSharedDecks.value.length > 0);
const isWorkplace = computed(() => !contextStore.isCourse);
const sharedWithMe = computed(() =>
    sharedDecksStore.all.filter((sharedDeck) => sharedDeck.sharer.data.id !== contextStore.userId),
);
const unusedSharedDecks = computed(() =>
    sharedWithMe.value.filter((sharedDeck) => !sharedDeck['colearning-deck'].data),
);

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

    <StudipCompanion
        v-if="hasUnusedSharedDecks"
        mood="curious"
        :msg-companion="
            $gettext(
                'Mit Ihnen wurde ein neuer Kartensatz geteilt.',
                'Mit Ihnen wurden %{ count } neue Kartensätze geteilt.',
                { count: unusedSharedDecks.length },
            )
        "
        class="tw-mb-8"
    >
        <template #companionActions>
            <RouterLink :to="{ name: 'shared' }" class="button">
                {{
                    $gettext('Zum geteilten Kartensatz', 'Zu den geteilten Kartensätzen.', {
                        count: unusedSharedDecks.length,
                    })
                }}
            </RouterLink>
        </template>
    </StudipCompanion>
    <table class="default">
        <caption>
            <nav>
                <span>
                    <RouterLink :to="{ name: 'home' }">
                        <StudipIcon
                            shape="folder-home-empty"
                            :height="30"
                            :width="30"
                            class="tw-align-middle tw-mr-2 tw-mb-1"
                            ariaRole="none"
                        />
                        <span class="sr-only">
                            {{ $gettext('Zum Hauptordner') }}
                        </span>
                    </RouterLink>
                </span>
            </nav>
        </caption>

        <FolderList :folders="topFolders" @delete-folder="deleteFolder" @edit-folder="editFolder">
            <template #empty>
                <StudipCompanion :msgCompanion="$gettext('Es gibt noch keinen Ordner.')" />
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
                {{ $gettext('Kartensätze in diesem Ordner') }}
            </h3>
        </header>
        <DeckList :decks="decks" />
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
