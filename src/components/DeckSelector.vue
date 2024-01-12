<script setup>
import { computed, ref, watch } from 'vue';
import { useDecksStore } from '../stores/decks.js';
import { useFoldersStore } from '../stores/folders.js';

const decksStore = useDecksStore();
const foldersStore = useFoldersStore();

// TODO: Das macht der DialogShareDeckHere schon.
// decksStore.fetchWorkplace();

const props = defineProps(['modelValue', 'disabledDecks']);
const emit = defineEmits(['update:modelValue']);

const selectedDeck = ref(props.modelValue ?? null);

const workplaceDecks = computed(() =>
    decksStore.fromWorkplace.filter(({ colearning }) => !colearning),
);

const folderedDecks = computed(() => {
    const groupedDecks = groupDecks(workplaceDecks.value);
    const deckIds = new Set(groupedDecks.keys());

    return [
        ...sortFoldersAndDecks(
            foldersStore.all.filter(({ id }) => deckIds.has(id)),
            groupedDecks,
        ),
        { decks: _.sortBy(groupedDecks.get(null), ['name']), folder: null, path: null },
    ];
});

function groupDecks(workplaceDecks) {
    return workplaceDecks.reduce((memo, deck) => {
        const folderId = deck.folder.data?.id ?? null;
        if (!memo.has(folderId)) {
            memo.set(folderId, [deck]);
        } else {
            memo.get(folderId).push(deck);
        }

        return memo;
    }, new Map());
}

function path(folder) {
    return foldersStore.ancestors(folder).map((a) => a.name);
}

function sortFoldersAndDecks(folders, groupedDecks) {
    return _.sortBy(
        folders.map((folder) => ({
            decks: _.sortBy(groupedDecks.get(folder.id), ['name']),
            folder,
            path: [..._.reverse(path(folder)), folder.name],
        })),
        ['path'],
    );
}

watch(
    () => props.modelValue,
    () => (selectedDeck.value = props.modelValue),
);
watch(selectedDeck, () => emit('update:modelValue', selectedDeck.value));
</script>

<template>
    <select v-model="selectedDeck">
        <option disabled :value="null">
            {{ $gettext('Wählen Sie einen Kartensatz') }}
        </option>
        <optgroup
            v-for="({ path, decks }, index) in folderedDecks"
            :key="index"
            :label="path ? path.join(' » ') : $gettext('(Ohne Ordner)')"
        >
            <option
                v-for="deck in decks"
                :key="deck.id"
                :value="deck.id"
                :disabled="disabledDecks?.includes?.(deck.id) ?? false"
            >
                {{ deck.name }}
            </option>
        </optgroup>
    </select>
</template>
