<script setup>
import ListItem from './SharedDeckListItem.vue';

defineProps(['sharedDecks']);
defineEmits(['select']);
</script>

<template>
    <section class="tw-flex tw-flex-col tw-gap-2">
        <article v-for="sharedDeck in sharedDecks" :key="sharedDeck.id">
            <ListItem :shared-deck="sharedDeck" @select="$emit('select', sharedDeck)" />
        </article>
    </section>
</template>
