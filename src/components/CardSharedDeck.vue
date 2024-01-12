<script setup>
import { computed, ref } from 'vue';
import { useGettext } from 'vue3-gettext';
import DialogColearnSharedDeck from './DialogColearnSharedDeck.vue';
import DialogConfirmUnshare from './DialogConfirmUnshare.vue';
import DialogCopySharedDeck from './DialogCopySharedDeck.vue';
import IconButton from './IconButton.vue';
import StudipActionMenu from './base/StudipActionMenu.vue';
import StudipAvatar from './base/StudipAvatar.vue';
import StudipIcon from './base/StudipIcon.vue';
import { useContextStore } from '../stores/context.js';

const { $gettext } = useGettext();
const contextStore = useContextStore();

const props = defineProps(['sharedDeck']);
defineEmits(['select']);

const showColearnDialog = ref(false);
const showCopyDialog = ref(false);
const showConfirmUnshareDialog = ref(false);

const sharer = computed(() => props.sharedDeck.sharer.data);
const avatarUrl = computed(() => sharer.value.meta.avatar.small);
const formattedName = computed(() => sharer.value['formatted-name']);

const actionMenuItems = computed(() => {
    return isSharer.value
        ? [
              {
                  id: 'unshare',
                  label: $gettext('Nicht mehr teilen'),
                  icon: 'decline',
                  emit: 'unshare',
              },
          ]
        : [
              {
                  id: 'copy',
                  label: $gettext('Kopieren'),
                  icon: 'copy',
                  emit: 'copy',
              },
          ];
});

const isSharer = computed(() => sharer.value.id === contextStore.userId);

const onCopy = () => (showCopyDialog.value = true);
const onColearn = () => (showColearnDialog.value = true);
const onUnshare = () => (showConfirmUnshareDialog.value = true);
</script>

<template>
    <section
        class="tw-flex tw-gap-2 tw-h-24 tw-py-2 tw-cursor-pointer tw-border tw-border-solid tw-border-[var(--light-gray-color-20)]"
    >
        <div
            class="tw-flex tw-items-center tw-justify-center tw-w-24 tw-aspect-square"
            @click="$emit('select', sharedDeck)"
        >
            <StudipIcon shape="share" role="info" :size="32" />
        </div>
        <div class="tw-flex tw-flex-col tw-flex-grow tw-justify-between">
            <div class="tw-text-lg tw-font-bold tw-text-[var(--base-color)]" @click="$emit('select', sharedDeck)">
                {{ sharedDeck.deck.data.name }}
            </div>
            <div class="tw-flex tw-items-center tw-justify-between">
                <StudipAvatar :avatar-url="avatarUrl" :formatted-name="formattedName" />
                <div>
                    <StudipIcon shape="dialog-cards" role="info" />
                    {{ sharedDeck.deck.data.meta['cards-count'] }}
                </div>
                <div class="tw-px-4">
                    <IconButton v-if="!isSharer" icon="refresh" type="button" @click="onColearn">
                        {{ $gettext('Mitlernen') }}
                    </IconButton>
                    <StudipActionMenu
                        v-if="actionMenuItems.length"
                        :items="actionMenuItems"
                        :collapseAt="0"
                        @copy="onCopy"
                        @unshare="onUnshare"
                    />
                </div>
            </div>
        </div>
    </section>
    <DialogColearnSharedDeck v-model:open="showColearnDialog" :shared-deck="sharedDeck" />
    <DialogConfirmUnshare v-model:open="showConfirmUnshareDialog" :shared-deck="sharedDeck" />
    <DialogCopySharedDeck v-model:open="showCopyDialog" :shared-deck="sharedDeck" />
</template>
