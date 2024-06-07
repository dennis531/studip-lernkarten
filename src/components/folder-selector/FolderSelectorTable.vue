<script setup>
import { computed, ref } from 'vue';
import { useDecksStore } from '../../stores/decks.js';
import { useFoldersStore } from '../../stores/folders.js';
import DeckItem from './FolderSelectorDeckItem.vue';
import FolderItem from './FolderSelectorFolderItem.vue';

const props = defineProps({
    folder: {
        type: Object,
        required: true,
    },
});

defineEmits(['select']);

const decksStore = useDecksStore();
const foldersStore = useFoldersStore();

const sortBy = ref('name');
const sortASC = ref(true);

const empty = computed(() => sortedDecks.value.length + sortedFolders.value.length === 0);

const comparator = computed(() => {
    switch (sortBy.value) {
        case 'name':
            return ({ name }) => name.toUpperCase();
    }
    return _.identity;
});

const sortedDecks = computed(() => {
    const decks = _.sortBy(decksStore.byFolder(props.folder), comparator.value);

    return sortASC.value ? decks : _.reverse(decks);
});

const sortedFolders = computed(() => {
    const folders = _.sortBy(
        props.folder ? foldersStore.children(props.folder.id) : foldersStore.topFolders,
        comparator.value,
    );

    return sortASC.value ? folders : _.reverse(folders);
});

const sort = (by) => (sortASC.value = sortBy.value === by ? !sortASC.value : by);
const getSortClass = (col) =>
    col === sortBy.value ? (sortASC.value ? 'sortasc' : 'sortdesc') : '';
</script>

<template>
    <table class="default">
        <colgroup>
            <col style="width: 36px" />
            <col />
        </colgroup>
        <thead>
            <tr class="sortable">
                <th>{{ $gettext('Typ') }}</th>
                <th :class="getSortClass('name')" @click="sort('name')">
                    <a href="#">{{ $gettext('Name') }}</a>
                </th>
            </tr>
        </thead>
        <tbody v-if="empty">
            <tr class="empty">
                <td colspan="2">{{ $gettext('Dieser Ordner ist leer') }}</td>
            </tr>
        </tbody>
        <template v-else>
            <tbody class="subfolders">
                <FolderItem
                    v-for="folder in sortedFolders"
                    :key="folder.id"
                    :folder="folder"
                    tag="tr"
                    @select="$emit('select', folder)"
                />
            </tbody>
            <tbody class="decks">
                <DeckItem v-for="deck in sortedDecks" :key="deck.id" :deck="deck" />
            </tbody>
        </template>
    </table>
</template>
