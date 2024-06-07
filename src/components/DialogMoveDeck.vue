<script setup>
import { onMounted, ref, watch } from 'vue';
import StudipDialog from './base/StudipDialog.vue';
import FolderSelector from '../components/FolderSelector.vue';
import { useDecksStore } from '../stores/decks.js';
import { useFoldersStore } from '../stores/folders.js';

const decksStore = useDecksStore();
const foldersStore = useFoldersStore();

const props = defineProps(['open', 'deck']);
const emit = defineEmits(['update:open']);

const folder = ref(props.deck?.folder?.data?.id ?? null);

const resetFolder = () => {
    folder.value = foldersStore.byId(props.deck?.folder?.data?.id ?? null);
};

const setIsOpen = (value) => {
    emit('update:open', value);
    resetFolder();
};

onMounted(resetFolder);

watch(
    () => foldersStore.isLoading,
    (newV, oldV) => {
        if (oldV && !newV) {
            resetFolder();
        }
    },
);

watch(() => props.deck, resetFolder);

const onSelectFolder = (selected) => {
    folder.value = selected;
};

const onConfirm = () =>
    decksStore
        .updateDeck(props.deck, { folder_id: folder.value?.id ?? null })
        .then(() => setIsOpen(false));
</script>

<template>
    <StudipDialog
        :confirm-text="$gettext('Verschieben')"
        :close-text="$gettext('Schließen')"
        :height="500"
        :open="open"
        :title="$gettext('Kartensatz verschieben')"
        @confirm="onConfirm"
        @update:open="setIsOpen"
    >
        <template #dialogContent>
            <form class="default studipform" @submit.prevent="onConfirm">
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
                        <FolderSelector :folder="folder" @select="onSelectFolder" />
                    </label>
                </div>
            </form>
        </template>
    </StudipDialog>
</template>
