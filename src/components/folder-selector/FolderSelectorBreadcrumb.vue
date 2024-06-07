<script setup>
import { computed } from 'vue';
import { useFoldersStore } from '../../stores/folders.js';
import StudipIcon from '../base/StudipIcon.vue';

const props = defineProps(['folder']);
const emit = defineEmits(['select']);

const foldersStore = useFoldersStore();
const breadcrumbItems = computed(() => {
    return props.folder ? [null, ...foldersStore.ancestors(props.folder), props.folder] : [null];
});
const selectFolder = (folder) => {
    if (folder !== props.folder) {
        emit('select', folder);
    }
};
</script>

<template>
    <ol class="folder-selector-breadcrumb">
        <li v-for="(item, index) in breadcrumbItems" :key="item?.id ?? 'root'">
            <a href="#" @click.prevent="selectFolder(item)">
                <template v-if="item">{{ item.name }}</template>
                <StudipIcon v-else shape="home" :title="$gettext('Arbeitsplatz')" />
            </a>
            <span
                v-if="breadcrumbItems.length > 1 && index !== breadcrumbItems.length - 1"
                class="tw-mx-1"
            >
                /
            </span>
        </li>
    </ol>
</template>

<style>
.folder-selector-breadcrumb {
    display: flex;
    flex-direction: row;
    padding: 0;
    margin: 0;
}
.folder-selector-breadcrumb li {
    list-style: none;
}
.folder-selector-breadcrumb li a img {
    vertical-align: text-bottom;
}
</style>
