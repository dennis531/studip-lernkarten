<script setup>
import { ref, watch } from 'vue';
import StudipDialog from './base/StudipDialog.vue';
import FolderSelector from '../components/FolderSelector.vue';
import { useDecksStore } from '../stores/decks.js';
import { useFoldersStore } from '../stores/folders.js';

const decksStore = useDecksStore();
const foldersStore = useFoldersStore();

const props = defineProps(['open', 'deck']);
const emit = defineEmits(['update:open']);

const initialFocus = ref(null);

const name = ref(props.deck?.name ?? '');
const description = ref(props.deck?.description ?? '');
const metadata = ref(props.deck?.metadata ?? '');
const folder = ref(null);

const reset = () => {
    name.value = props.deck?.name ?? '';
    description.value = props.deck?.description ?? '';
    metadata.value = props.deck?.metadata ?? '';
};

watch(() => props.deck, reset);

const setIsOpen = (value) => {
    emit('update:open', value);
    reset();
};

watch(
    () => foldersStore.isLoading,
    (newV, oldV) => {
        if (oldV && !newV) {
            onSelectFolder(foldersStore.byId(props.deck?.folder?.data?.id));
        }
    },
);

watch(
    () => props.deck,
    (newV) => {
        folder.value = foldersStore.byId(newV.folder?.data?.id);
    },
);

const onSelectFolder = (selected) => {
    folder.value = selected;
};

const onConfirm = () => {
    decksStore
        .updateDeck(props.deck, {
            name: name.value,
            description: description.value,
            metadata: metadata.value,
            folder_id: folder.value?.id ?? null,
        })
        .then(() => setIsOpen(false));
};
</script>

<template>
    <StudipDialog
        :confirm-text="$gettext('Speichern')"
        :close-text="$gettext('Schließen')"
        :height="600"
        :initial-focus="initialFocus"
        :open="open"
        :title="$gettext('Kartensatz bearbeiten')"
        :width="600"
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

                <div class="formpart">
                    <label class="studiprequired">
                        <span class="textlabel">
                            {{ $gettext('Bezeichnung des Kartensatzes') }}
                        </span>
                        <span
                            class="asterisk"
                            :title="$gettext('Dies ist ein Pflichtfeld')"
                            aria-hidden="true"
                            >*</span
                        >
                        <input
                            type="text"
                            v-model="name"
                            ref="initialFocus"
                            required
                            aria-required="true"
                        />
                    </label>
                </div>

                <div class="formpart">
                    <label class="studiprequired">
                        <span class="textlabel">
                            {{ $gettext('Beschreibung des Kartensatzes') }}
                        </span>
                        <span
                            class="asterisk"
                            :title="$gettext('Dies ist ein Pflichtfeld')"
                            aria-hidden="true"
                            >*</span
                        >
                        <textarea v-model="description" required aria-required="true" />
                    </label>
                </div>

                <div class="formpart">
                    <label>
                        <span class="textlabel">
                            {{ $gettext('Metadaten des Kartensatzes') }}
                        </span>
                        <textarea v-model="metadata" />
                    </label>
                </div>
            </form>
        </template>
    </StudipDialog>
</template>
