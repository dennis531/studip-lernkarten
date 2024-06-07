<script setup>
import { computed, ref } from 'vue';
import { useGettext } from 'vue3-gettext';
import DialogFolderSelector from './folder-selector/FolderSelectorDialog.vue';
import StudipDialog from './base/StudipDialog.vue';
import { useDecksStore } from '../stores/decks.js';
import { useFoldersStore } from '../stores/folders.js';
import { useSharedDecksStore } from '../stores/shared-decks.js';

const { $gettext } = useGettext();
const decksStore = useDecksStore();
const foldersStore = useFoldersStore();
const sharedDecksStore = useSharedDecksStore();

const props = defineProps(['open', 'sharedDeck']);
const emit = defineEmits(['update:open']);

const selectedFolder = ref(null);
const showFolderSelectorDialog = ref(false);

const setIsOpen = (value) => {
    emit('update:open', value);
};

const onConfirm = () => {
    setIsOpen(false);
    sharedDecksStore.colearn(props.sharedDeck).then((sharedDeck) => {
        const deck = sharedDeck['colearning-deck'].data;
        if (selectedFolder.value) {
            decksStore.updateDeck(deck, { folder_id: selectedFolder.value.id });
        }
    });
};

const onSelectFolder = (folderId) => {
    selectedFolder.value = folderId ? foldersStore.byId(folderId) : null;
    showFolderSelectorDialog.value = false;
};

const folderBreadcrumbs = computed(() =>
    selectedFolder.value
        ? [...foldersStore.ancestors(selectedFolder.value), selectedFolder.value]
        : [],
);
</script>

<template>
    <StudipDialog
        confirm-class="accept"
        :confirm-text="$gettext('Abonnieren')"
        :close-text="$gettext('Abbrechen')"
        :open="open"
        :title="$gettext('Geteilten Kartensatz abonnieren')"
        @confirm="onConfirm"
        @update:open="setIsOpen"
        :height="500"
    >
        <template #dialogContent>
            <p>
                {{
                    $gettext(
                        'Wählen Sie einen Ordner, in dem der geteilte Kartensatz abgelegt werden soll!',
                    )
                }}
            </p>
            <p>
                <label class="studiprequired"></label>
            </p>

            <form class="default studipform" @submit.prevent="">
                <div class="formpart">
                    <label class="studiprequired">
                        <span class="textlabel">
                            {{ $gettext('Ordner') }}
                        </span>
                        <span
                            class="asterisk"
                            :title="$gettext('Dies ist ein Pflichtfeld')"
                            aria-hidden="true"
                            >*</span
                        >

                        <div class="breadcrumbs" v-if="selectedFolder">
                            <span class="sr-only">{{ $gettext('Ordner ausgewählt:') }}</span>
                            <span v-for="folder in folderBreadcrumbs" :key="folder.id">
                                {{ folder.name }}
                            </span>
                        </div>
                        <div class="breadcrumbs root-folder" v-else>
                            <span>{{ $gettext('Kein Ordner ausgewählt.') }}</span>
                        </div>

                        <div>
                            <button class="button" @click="showFolderSelectorDialog = true">
                                {{ $gettext('Ordner auswählen') }}
                            </button>
                        </div>
                    </label>
                </div>
            </form>
        </template>
    </StudipDialog>
    <DialogFolderSelector v-model:open="showFolderSelectorDialog" @select="onSelectFolder" />
</template>

<style scoped>
.breadcrumbs {
    display: flex;
    flex-wrap: wrap;
    font-style: italic;
    font-weight: normal;
}
.breadcrumbs > span:not(.sr-only) + span:before {
    content: '»';
    margin-inline: 0.5em;
}
.breadcrumbs > span {
    white-space: nowrap;
}
</style>
