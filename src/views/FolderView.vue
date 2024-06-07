<script setup>
import { computed, ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import DeckList from '../components/DeckList.vue';
import DialogAdjustLearningOptions from '../components/DialogAdjustLearningOptions.vue';
import DialogCreateFolder from '../components/DialogCreateFolder.vue';
import DialogEditFolder from '../components/DialogEditFolder.vue';
import DialogConfirmDeleteFolder from '../components/DialogConfirmDeleteFolder.vue';
import FolderList from '../components/FolderList.vue';
import IconButton from '../components/IconButton.vue';
import SidebarAction from '../components/SidebarAction.vue';
import StudipIcon from '../components/base/StudipIcon.vue';
import { useContextStore } from '../stores/context.js';
import { useDecksStore } from '../stores/decks.js';
import { useFoldersStore } from '../stores/folders.js';

const contextStore = useContextStore();
const decksStore = useDecksStore();
const foldersStore = useFoldersStore();
const router = useRouter();

decksStore.fetchContext();

const confirmDeleteDialogOpen = ref(false);
const createDialogOpen = ref(false);
const editDialogOpen = ref(false);
const editFolderObject = ref(null);
const selectedFolder = ref(null);
const showAdjustLearningDialog = ref(false);

const props = defineProps(['id']);

const folder = computed(() => foldersStore.byId(props.id));
const children = computed(() => {
    if (!folder.value) {
        return [];
    }
    return _.sortBy(foldersStore.children(props.id), 'name');
});

const decks = computed(() =>
    folder.value
        ? decksStore.byContext.filter((deck) => deck.folder.data?.id === folder.value.id)
        : [],
);
const isWorkplace = computed(() => !contextStore.isCourse);

const onAddChild = () => {
    createDialogOpen.value = true;
};
const createChild = (name) => {
    createDialogOpen.value = false;
    foldersStore.createFolder(name, folder.value);
};

const editFolder = (folder) => {
    editDialogOpen.value = true;
    editFolderObject.value = folder;
};

const onEditDialog = (folder, name) => {
    editDialogOpen.value = false;
    foldersStore.updateFolder(folder, { name: name });
};

const onDeleteFolder = (folder) => {
    confirmDeleteDialogOpen.value = true;
    selectedFolder.value = folder;
};
const deleteFolder = () => {
    foldersStore.deleteFolder(selectedFolder.value);
    confirmDeleteDialogOpen.value = false;
};

const onLearnDecks = () => (showAdjustLearningDialog.value = true);
const onCreateDeck = () => {
    router.push({ name: 'decks-create', query: { f: props.id } });
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
        <caption v-if="folder">
            <nav>
                <span>
                    <RouterLink :to="{ name: 'home' }">
                        <StudipIcon
                            shape="folder-home-empty"
                            :height="30"
                            :width="30"
                            class="tw-align-middle tw-mr-1 tw-mb-1"
                            ariaRole="none"
                        />
                        <span class="sr-only">
                            {{ $gettext('Zum Hauptordner') }}
                        </span>
                    </RouterLink>
                    <span v-for="ancestor in foldersStore.ancestors(folder)" :key="ancestor.id">
                        /
                        <RouterLink
                            :to="{ name: 'folder', params: { id: ancestor.id } }"
                            class="tw-whitespace-nowrap"
                        >
                            {{ ancestor.name }}
                        </RouterLink>
                    </span>
                    /
                    <span>{{ folder.name }}</span>
                </span>
            </nav>
        </caption>

        <FolderList :folders="children" @delete-folder="onDeleteFolder" @edit-folder="editFolder" />

        <tfoot>
            <tr>
                <td colspan="3">
                    <div class="footer-items">
                        <IconButton type="button" icon="add" @click="onAddChild">
                            {{ $gettext('Unterordner erstellen') }}
                        </IconButton>
                    </div>
                </td>
            </tr>
        </tfoot>
    </table>
    <section class="tw-mt-12" v-if="decks.length">
        <header>
            <h3 class="tw-mt-12">
                {{ $gettext('Kartensätze in diesem Ordner') }}
            </h3>
        </header>
        <DeckList :decks="decks" />
    </section>

    <DialogAdjustLearningOptions v-model:open="showAdjustLearningDialog" :decks="decks" />
    <DialogCreateFolder v-model:open="createDialogOpen" @confirm="createChild" />
    <DialogEditFolder
        v-model:open="editDialogOpen"
        :folder="editFolderObject"
        @confirm="onEditDialog"
    />
    <DialogConfirmDeleteFolder v-model:open="confirmDeleteDialogOpen" @confirm="deleteFolder" />
</template>
