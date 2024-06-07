<script setup>
import { computed } from 'vue';
import ListItem from './SharedWithMeDeckListItem.vue';

const props = defineProps(['sharedDecks']);
defineEmits(['colearn', 'select']);

const sortedSharedDecks = computed(() =>
    _.reverse(_.sortBy(props.sharedDecks, (card) => new Date(card.mkdate))),
);
</script>

<template>
    <table class="studip default">
        <thead>
            <tr>
                <th class="tw-w-12">{{ $gettext('Status') }}</th>
                <th>{{ $gettext('Name') }}</th>
                <th class="hidden-small-down tw-w-20">{{ $gettext('Karten') }}</th>
                <th>{{ $gettext('Geteilt von') }}</th>
                <th class="hidden-small-down tw-w-20">{{ $gettext('Geteilt am') }}</th>
                <th>
                    <span class="sr-only">{{ $gettext('Abonnement') }}</span>
                </th>
                <th>
                    <span class="sr-only">{{ $gettext('Kopien') }}</span>
                </th>
            </tr>
        </thead>
        <tbody>
            <ListItem
                v-for="sharedDeck in sortedSharedDecks"
                :key="sharedDeck.id"
                :shared-deck="sharedDeck"
                @colearn="$emit('colearn', sharedDeck)"
                @select="$emit('select', sharedDeck)"
            />
        </tbody>
    </table>
</template>

<style scoped>
td:nth-child(2) button {
    background-color: transparent;
    border-color: transparent;
    color: var(--base-color);
    cursor: pointer;
    outline-color: currentColor;
}

td.actions button {
    min-width: 10em;
}
</style>
