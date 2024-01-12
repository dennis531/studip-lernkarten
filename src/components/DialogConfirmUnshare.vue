<script setup>
import StudipDialog from './base/StudipDialog.vue';
import { useSharedDecksStore } from '../stores/shared-decks.js';

const sharedDecksStore = useSharedDecksStore();

const props = defineProps(['open', 'sharedDeck']);
const emit = defineEmits(['update:open', 'confirm']);

const setIsOpen = (value) => {
    emit('update:open', value);
};

const onConfirm = () => {
    sharedDecksStore.unshareDeck(props.sharedDeck).then(() => {
        setIsOpen(false);
        emit('confirm');
    });
};
</script>

<template>
    <StudipDialog
        :open="open"
        :question="$gettext('Möchten Sie das Teilen des Kartensatzes aufheben?')"
        :title="$gettext('Nicht mehr teilen')"
        @confirm="onConfirm"
        @update:open="setIsOpen"
    >
    </StudipDialog>
</template>
