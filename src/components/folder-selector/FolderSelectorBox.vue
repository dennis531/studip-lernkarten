<script setup>
import { ref } from 'vue';
import { useGettext } from 'vue3-gettext';
import FolderSelectorBreadcrumb from './FolderSelectorBreadcrumb.vue';
import FolderSelectorTable from './FolderSelectorTable.vue';
import FolderSelectorToolbar from './FolderSelectorToolbar.vue';
import StudipMessageBox from '../base/StudipMessageBox.vue';

defineProps(['selected']);
const emit = defineEmits(['select']);

const { $gettext } = useGettext();

const showMessageBox = ref(false);
const successMessage = ref('');

const selectFolder = (folder) => emit('select', folder);

const folderAdded = () => {
    showMessageBox.value = true;
    successMessage.value = $gettext('Der Ordner wurde angelegt.');
};
</script>

<template>
    <div class="folder-selector-box">
        <header>
            <FolderSelectorBreadcrumb :folder="selected" @select="selectFolder" />
        </header>
        <StudipMessageBox v-if="showMessageBox" type="success">
            {{ successMessage }}
        </StudipMessageBox>
        <div class="folder-selector-box-content">
            <FolderSelectorTable :folder="selected" @select="selectFolder" />
            <FolderSelectorToolbar
                class="with-table"
                :selected="selected"
                @update:folders="folderAdded"
            />
        </div>
    </div>
</template>

<style>
.folder-selector-box {
    flex-grow: 1;
}

.folder-selector-box header {
    display: flex;
    flex-direction: row;
    position: sticky;
    top: 0;
    background-color: var(--content-color-20);
    padding: 0.5em 1em;
    border: solid thin var(--content-color-40);
    margin-bottom: 1em;
}
.folder-selector-box header .folder-selector-breadcrumb {
    flex-grow: 1;
}
.folder-selector-box header .toggle-view {
    width: 20px;
    height: 20px;
    border: none;
    background-color: transparent;
    cursor: pointer;
}
.folder-selector-box .folder-selector-box-content {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    overflow-y: scroll;
    height: calc(100% - 36px);
}

.folder-selector-items {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    overflow-y: auto;
}
.folder-selector-items .folder-selector-item {
    display: flex;
    flex-direction: column;
    width: 104px;
    min-height: 104px;
    border: solid thin transparent;
    background-color: transparent;
    word-break: break-word;
    margin: 0 4px 4px 4px;
    padding: 4px;
    cursor: pointer;
}
.folder-selector-items .folder-selector-item.selected {
    background-color: var(--activity-color-20);
    border: solid thin var(--base-color);
    font-weight: 700;
}
.folder-selector-items .folder-selector-item.disabled {
    cursor: default;
}
.folder-selector-items .folder-selector-item img {
    margin: 0 auto;
}
.folder-selector-items .folder-selector-item span {
    width: 100%;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}
</style>
