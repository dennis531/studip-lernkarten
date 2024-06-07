<script setup>
import { computed } from 'vue';
import ListItem from './SharedByMeDeckListItem.vue';

const props = defineProps(['sharedDecks']);

const sortedSharedDecks = computed(() =>
    _.reverse(_.sortBy(props.sharedDecks, (card) => new Date(card.mkdate))),
);
</script>

<template>
    <table class="studip default">
        <thead>
            <tr>
                <th>{{ $gettext('Name') }}</th>
                <th class="hidden-small-down tw-w-20">{{ $gettext('Karten') }}</th>
                <th>{{ $gettext('Geteilt mit') }}</th>
                <th class="hidden-small-down tw-w-20">{{ $gettext('Geteilt am') }}</th>
                <th class="actions"></th>
            </tr>
        </thead>
        <tbody>
            <ListItem
                v-for="sharedDeck in sortedSharedDecks"
                :key="sharedDeck.id"
                :shared-deck="sharedDeck"
            />
        </tbody>
    </table>
</template>
