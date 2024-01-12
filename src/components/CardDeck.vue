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
import DialogCopySharedDeck from './DialogCopySharedDeck.vue';
import DialogShareDeck from './DialogShareDeck.vue';
import { useContextStore } from '../stores/context.js';
import { useDecksStore } from '../stores/decks.js';

const { $gettext } = useGettext();
const contextStore = useContextStore();
const decksStore = useDecksStore();

const props = defineProps(['deck']);
const emit = defineEmits(['deleted', 'select']);

const showAdjustLearningDialog = ref(false);
const showConfirmCopy = ref(false);
const showConfirmDelete = ref(false);
const showShareDialog = ref(false);

const deckOwner = computed(() => props.deck.owner.data);
const avatarUrl = computed(() => deckOwner.value.meta.avatar.small);
const formattedName = computed(() => deckOwner.value['formatted-name']);
const editable = computed(() => props.deck['is-editable']);

const templateOwner = computed(() => props.deck.template.data.owner.data);
const templateAvatarUrl = computed(() => templateOwner.value.meta.avatar.small);
const templateFormattedName = computed(() => templateOwner.value['formatted-name']);

const actionMenuItems = computed(() => {
    const ownWorkplaceInTopFolder = !contextStore.isCourse && !props.deck.folder.data;
    return [
        {
            id: 'copy',
            label: ownWorkplaceInTopFolder
                ? $gettext('Kartensatz duplizieren')
                : $gettext('Kartensatz kopieren'),
            icon: 'copy',
            emit: 'copy',
        },
        ...(editable.value
            ? [
                  {
                      id: 'share',
                      label: $gettext('Kartensatz teilen'),
                      icon: 'share',
                      emit: 'share',
                  },
                  {
                      id: 'delete',
                      label: $gettext('Kartensatz löschen'),
                      icon: 'trash',
                      emit: 'delete',
                  },
              ]
            : []),
    ];
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
            <div class="tw-italic tw-flex tw-gap-2 tw-items-center" v-if="deck.template.data">
                <span v-if="deck.colearning">
                    {{ $gettext('Geteilter Kartensatz von') }}
                </span>
                <span v-else>{{ $gettext('Kopie eines Kartensatzes von') }}</span>
                <StudipAvatar
                    class="tw-inline"
                    :avatar-url="templateAvatarUrl"
                    :formatted-name="templateFormattedName"
                />
            </div>
            <div
                class="tw-cursor-pointer tw-flex-grow tw-text-[var(--base-color)]"
                @click="$emit('select', deck)"
            >
                <span class="tw-text-lg tw-font-bold">{{ deck.name }}</span>
            </div>
            <div class="tw-flex tw-items-center tw-justify-between">
                <StudipAvatar :avatar-url="avatarUrl" :formatted-name="formattedName" />
                <div>
                    <StudipIcon shape="dialog-cards" role="info" />
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
    <DialogConfirmCopyDeck v-if="!deck.colearning" v-model:open="showConfirmCopy" :deck="deck" />
    <DialogCopySharedDeck
        v-if="deck.colearning"
        v-model:open="showConfirmCopy"
        :shared-deck="{ id: deck['shared-deck'].data.id }"
    />
</template>
