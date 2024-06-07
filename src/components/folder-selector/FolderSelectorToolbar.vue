<script setup>
import { computed, ref, watch } from 'vue';
import { useFoldersStore } from '../../stores/folders.js';
import StudipIcon from '../base/StudipIcon.vue';

const props = defineProps(['selected']);
const emit = defineEmits(['update:folders']);

const foldersStore = useFoldersStore();

const showFolderAdder = ref(false);
const newFolderName = ref('');

const showButtons = computed(() => !showFolderAdder.value);

const closeAddFolder = () => {
    showFolderAdder.value = false;
    newFolderName.value = '';
};

const createFolder = () => {
    if (newFolderName.value === '') {
        return;
    }
    const name = newFolderName.value;
    closeAddFolder();
    foldersStore.createFolder(name, props.selected).then(() => {
        emit('update:folders');
    });
};

watch(() => props.selected, closeAddFolder);
</script>

<template>
    <div class="folder-selector-toolbar">
        <button v-if="showButtons" class="button" @click="showFolderAdder = true">
            {{ $gettext('Ordner hinzufügen') }}
        </button>
        <form v-if="showFolderAdder" class="inline-form" @submit.prevent="">
            <label for="folder-selector-add-folder">{{ $gettext('Ordner hinzufügen') }}:</label>
            <input
                id="folder-selector-add-folder"
                type="text"
                v-model="newFolderName"
                :placeholder="$gettext('Ordnername')"
            />
            <div class="inline-buttons">
                <button :title="$gettext('Ordner anlegen')" @click="createFolder">
                    <StudipIcon shape="accept" />
                </button>
                <button :title="$gettext('Abbrechen')" @click="closeAddFolder">
                    <StudipIcon shape="decline" />
                </button>
            </div>
        </form>
    </div>
</template>

<style>
.folder-selector-toolbar {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    border-top: solid thin var(--content-color-40);
}
.folder-selector-toolbar.with-table {
    border: none;
    margin-top: -16px;
}

.folder-selector-toolbar .inline-form {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: flex-start;
    gap: 4px;
    width: 100%;
    margin: 0.8em 0.6em 0.8em 0;
}
.folder-selector-toolbar .inline-form label {
    line-height: 30px;
}

.folder-selector-toolbar .inline-form input {
    flex-grow: 1;
    padding: 4px;
    border: solid thin var(--content-color-40);
    border-radius: 0;
}
.folder-selector-toolbar .inline-form button {
    border: solid thin var(--base-color);
    background-color: transparent;
    height: 30px;
    width: 30px;
    cursor: pointer;
}
.folder-selector-toolbar .inline-form button img {
    vertical-align: middle;
}
</style>
