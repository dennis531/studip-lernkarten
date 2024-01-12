<script setup>
import { computed, ref, watch } from 'vue';
import { useFoldersStore } from '../stores/folders.js';

const foldersStore = useFoldersStore();
const props = defineProps(['folder']);
defineEmits(['select']);

const selectedFolder = ref(props.folder ?? null);

watch(
    () => props.folder,
    () => {
        selectedFolder.value = props.folder ?? null;
    },
);

const sortedFolders = computed(() => {
    return _.sortBy(
        foldersStore.all.map((folder) => ({
            folder,
            path: [..._.reverse(foldersStore.ancestors(folder).map((a) => a.name)), folder.name],
        })),
        ['path'],
    );
});
</script>

<template>
    <select
        v-model="selectedFolder"
        @change="$emit('select', selectedFolder)"
        required
        aria-required="true"
    >
        <option :value="null">{{ $gettext('Kein Ordner ausgewählt') }}</option>
        <option :value="folder" v-for="{ folder, path } in sortedFolders" :key="folder.id">
            <span v-for="(item, index) in path" :key="index"
                ><span v-if="index"> » </span>{{ item }}</span
            >
        </option>
    </select>
</template>
