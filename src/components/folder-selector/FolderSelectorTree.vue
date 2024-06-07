<script setup>
import { computed, ref } from 'vue';
import FolderSelectorTree from './FolderSelectorTree.vue';
import StudipIcon from '../base/StudipIcon.vue';

const props = defineProps(['activeFolderId', 'folder']);
const emit = defineEmits(['select']);
const unfold = ref(false);

const isSelected = computed(() => props.folder.id === props.activeFolderId);
const hasSubfolders = computed(() => props.folder.children?.data?.length);

const openFolder = () => {
    unfold.value = true;
    selectFolder(props.folder);
};

const selectFolder = (folder) => emit('select', folder);
</script>

<template>
    <li class="folder-selector-tree-item">
        <span class="folder-toggle">
            <a
                v-if="hasSubfolders"
                href="#"
                @click.prevent="unfold = !unfold"
                :title="unfold ? $gettext('Ordner zuklappen') : $gettext('Ordner aufklappen')"
            >
                <StudipIcon :shape="unfold ? 'arr_1down' : 'arr_1right'" />
            </a>
        </span>
        <a href="#" @click.prevent="openFolder" :class="{ selected: isSelected }">
            <StudipIcon shape="folder-full" />
            <span>{{ folder.name }}</span>
        </a>
        <ul v-if="unfold" class="folder-selector-tree">
            <li
                v-for="child in folder.children.data"
                :key="child.id"
                class="folder-selector-tree-item"
            >
                <FolderSelectorTree
                    :active-folder-id="activeFolderId"
                    :folder="child"
                    @select="selectFolder"
                />
            </li>
        </ul>
    </li>
</template>

<style>
.folder-selector-tree {
    padding-inline-start: calc(16px + 2px);
}

.folder-selector-tree.folder-selector-tree-first-level {
    padding-inline-start: 0;
}

.folder-selector-tree-item {
    list-style: none;
    padding: 2px 0 0 0;
}
.folder-selector-tree-item .folder-toggle {
    margin-inline-end: 0.25em;
    width: 16px;
}
.folder-selector-tree-item a.selected {
    font-weight: 700;
}
.folder-selector-tree-item img {
    vertical-align: middle;
}
.folder-selector-tree-item span {
    margin-inline-start: 0.25em;
    width: calc(100% - 2 * 16px - 14px);
    display: inline-block;
    overflow: hidden;
    text-overflow: ellipsis;
    height: 16px;
    white-space: nowrap;
    vertical-align: sub;
}
</style>
