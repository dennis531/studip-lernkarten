<script setup>
import { computed, ref } from 'vue';
import { useGettext } from 'vue3-gettext';
import IconButton from './IconButton.vue';
import RadialProgress from './RadialProgress.vue';
import StudipActionMenu from './base/StudipActionMenu.vue';
import StudipAvatar from './base/StudipAvatar.vue';
import StudipIcon from './base/StudipIcon.vue';
import DialogAdjustLearningOptions from './DialogAdjustLearningOptions.vue';
import DialogConfirmCopyDeck from './DialogConfirmCopyDeck.vue';
import DialogConfirmDeleteDeck from './DialogConfirmDeleteDeck.vue';
import DialogShareDeck from './DialogShareDeck.vue';
import { useDecksStore } from '../stores/decks.js';

const { $gettext } = useGettext();
const decksStore = useDecksStore();

const props = defineProps(['deck']);
const emit = defineEmits(['deleted', 'select']);

const showAdjustLearningDialog = ref(false);
const showConfirmCopy = ref(false);
const showConfirmDelete = ref(false);
const showShareDialog = ref(false);

const editable = computed(() => props.deck['is-editable']);
const templateOwner = computed(() => props.deck.template.data.owner.data);
const templateAvatarUrl = computed(() => templateOwner.value.meta.avatar.small);
const templateFormattedName = computed(() => templateOwner.value['formatted-name']);

const actionMenuItems = computed(() => {
    const items = [
        {
            id: 'copy',
            label: $gettext('Kartensatz kopieren'),
            icon: 'copy',
            emit: 'copy',
        },
    ];
    if (editable.value) {
        if (!props.deck.colearning) {
            items.push({
                id: 'share',
                label: $gettext('Kartensatz teilen'),
                icon: 'share',
                emit: 'share',
            });
        }
        items.push({
            id: 'delete',
            label: props.deck.colearning
                ? $gettext('Abonnement aufheben')
                : $gettext('Kartensatz löschen'),
            icon: 'trash',
            emit: 'delete',
        });
    }
    return items;
});

const progress = computed(() => {
    const total = props.deck.progress.reduce((sum, n) => sum + n, 0);

    return total ? props.deck.progress[2] / total : 0;
});

const onAdjustLearning = () => (showAdjustLearningDialog.value = true);
const onCopyDeck = () => (showConfirmCopy.value = true);
const onDeleteDeck = () => (showConfirmDelete.value = true);
const onShareDeck = () => (showShareDialog.value = true);
const deleteDeck = () => {
    showConfirmDelete.value = false;
    decksStore.deleteDeck(props.deck).then(() => {
        emit('deleted');
    });
};
</script>

<template>
    <section
        class="tw-flex tw-gap-2 tw-py-2 tw-border tw-border-solid tw-border-[var(--light-gray-color-20)]"
    >
        <div
            class="tw-flex tw-items-center tw-justify-center tw-w-24 tw-aspect-square tw-cursor-pointer"
            @click="$emit('select', deck)"
        >
            <RadialProgress :progress="progress" />
        </div>
        <div class="tw-flex tw-flex-col tw-flex-grow tw-justify-between">
            <div
                class="tw-cursor-pointer tw-flex-grow tw-text-[var(--base-color)]"
                @click="$emit('select', deck)"
            >
                <span class="tw-text-lg tw-font-bold">{{ deck.name }}</span>
            </div>
            <div class="tw-italic tw-flex tw-gap-2 tw-items-center" v-if="deck.template.data"></div>
            <div class="tw-flex tw-items-center tw-justify-between">
                <div v-if="deck.template.data && deck.colearning" class="tw-italic">
                    <span> {{ $gettext('Im Abonnement von') }} </span>
                    <StudipAvatar
                        class="tw-inline"
                        :avatar-url="templateAvatarUrl"
                        :formatted-name="templateFormattedName"
                    />
                </div>
                <div>
                    <StudipIcon shape="dialog-cards" role="info" ariaRole="none" />
                    {{ deck.meta['cards-count'] }}
                </div>
                <div class="tw-px-4 tw-flex tw-gap-2 tw-items-center">
                    <IconButton icon="refresh" type="button" @click="onAdjustLearning">
                        {{ $gettext('Lernen') }}
                    </IconButton>
                    <StudipActionMenu
                        v-if="actionMenuItems.length"
                        :items="actionMenuItems"
                        :collapseAt="0"
                        @copy="onCopyDeck"
                        @delete="onDeleteDeck"
                        @share="onShareDeck"
                    />
                </div>
            </div>
        </div>
    </section>
    <DialogAdjustLearningOptions v-model:open="showAdjustLearningDialog" :decks="[deck]" />
    <DialogConfirmDeleteDeck v-model:open="showConfirmDelete" @confirm="deleteDeck" />
    <DialogShareDeck v-if="showShareDialog" v-model:open="showShareDialog" :deck="deck" />
    <DialogConfirmCopyDeck v-model:open="showConfirmCopy" :deck="deck" />
</template>
