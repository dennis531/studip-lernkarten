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
    sharedDecksStore.copy(props.sharedDeck);
    setIsOpen(false);
};
</script>

<template>
    <StudipDialog
        :open="open"
        :question="$gettext('Möchten Sie den geteilten Kartensatz in den Arbeitsplatz kopieren?')"
        :title="$gettext('Geteilten Kartensatz kopieren')"
        @confirm="onConfirm"
        @update:open="setIsOpen"
    >
        <template #dialogContent>
            <p>
                {{
                    $gettext(
                        'Wenn Sie den Kartensatz kopieren, können Sie diesen und die darin enthaltenen Karten beliebig bearbeiten. Ihr kopierter Kartensatz wird aber nicht aktualisiert, wenn die erstellende Person das Original bearbeitet.',
                    )
                }}
            </p>
        </template>
    </StudipDialog>
</template>
