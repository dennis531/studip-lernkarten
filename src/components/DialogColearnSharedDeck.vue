<script setup>
import { useGettext } from 'vue3-gettext';
import StudipDialog from './base/StudipDialog.vue';
import { useSharedDecksStore } from '../stores/shared-decks.js';

const { $gettext } = useGettext();
const sharedDecksStore = useSharedDecksStore();

const props = defineProps(['open', 'sharedDeck']);
const emit = defineEmits(['update:open']);

const setIsOpen = (value) => {
    emit('update:open', value);
};

const onConfirm = () => {
    sharedDecksStore.colearn(props.sharedDeck);
    setIsOpen(false);
};
</script>

<template>
    <StudipDialog
        :open="open"
        :question="$gettext('Möchten Sie den geteilten Kartensatz mitlernen?')"
        :title="$gettext('Geteilten Kartensatz mitlernen')"
        @confirm="onConfirm"
        @update:open="setIsOpen"
    >
        <template #dialogContent>
            <p>
                {{
                    $gettext(
                        'Wenn Sie den Kartensatz mitlernen, haben Sie keine Möglichkeit, die darin enthaltenen Karten zu bearbeiten.',
                    )
                }}
            </p>
        </template>
    </StudipDialog>
</template>
