<script setup>
import { ref } from 'vue';
import { useDecksStore } from '../../stores/decks.js';
import { useFoldersStore } from '../../stores/folders.js';
import StudipDialog from '../base/StudipDialog.vue';
import StudipIcon from '../base/StudipIcon.vue';
import FolderSelectorBox from './FolderSelectorBox.vue';
import FolderSelectorTree from './FolderSelectorTree.vue';

const decksStore = useDecksStore();
const foldersStore = useFoldersStore();

defineProps({
    open: {
        type: Boolean,
        required: true,
    },
    selectedId: {
        type: String,
        required: false,
    },
});

const emit = defineEmits(['select', 'update:open']);
const setIsOpen = (value) => {
    emit('update:open', value);
};

const selected = ref(null);

const confirm = () => {
    emit('select', selected.value?.id ?? null);
};

const selectFolder = (folder) => (selected.value = folder);
</script>

<template>
    <StudipDialog
        :close-text="$gettext('Abbrechen')"
        confirm-class="accept"
        :confirm-text="$gettext('Auswählen')"
        :height="600"
        :open="open"
        :title="$gettext('Ordner auswählen')"
        :width="1000"
        @confirm="confirm"
        @update:open="setIsOpen"
    >
        <template #dialogContent>
            <div
                v-if="!(foldersStore.isLoading || decksStore.isLoading)"
                class="folder-selector-content"
            >
                <ul class="folder-selector-folder-selector">
                    <li class="folder-selector-tree-item">
                        <a
                            href="#"
                            @click.prevent="selectFolder(null)"
                            :class="{ selected: !selected }"
                        >
                            <StudipIcon shape="content" />
                            <span>{{ $gettext('Arbeitsplatz') }}</span>
                        </a>
                        <ul class="folder-selector-tree folder-selector-tree-first-level">
                            <FolderSelectorTree
                                v-for="child in foldersStore.topFolders"
                                :key="child.id"
                                :folder="child"
                                @select="selectFolder"
                            />
                        </ul>
                    </li>
                </ul>
                <FolderSelectorBox :selected="selected" @select="selectFolder" />
            </div>
        </template>
    </StudipDialog>
</template>

<style scoped>
.folder-selector-content {
    display: flex;
    flex-direction: row;
    height: 100%;
}
.folder-selector-content .folder-selector-folder-selector {
    min-width: 270px;
    max-width: 270px;
    list-style: none;
    margin: 0 1em 0 0;
    padding: 0 1em 0 0;
    border-right: solid thin var(--content-color-40);
    overflow-y: auto;
}

@media (max-width: 580px) {
    .folder-selector-content .folder-selector-folder-selector {
        display: none;
    }
}
@media (max-width: 768px) {
    .folder-selector-content .folder-selector-folder-selector {
        min-width: 130px;
        max-width: 130px;
    }
}
</style>
