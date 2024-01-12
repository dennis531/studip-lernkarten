<script setup>
import { computed } from 'vue';
import IconButton from '../components/IconButton.vue';
import StudipAvatar from '../components/base/StudipAvatar.vue';
import StudipIcon from '../components/base/StudipIcon.vue';
import { useContextStore } from '../stores/context.js';

const contextStore = useContextStore();

const props = defineProps(['sharedDeck']);
defineEmits(['colearn']);

const avatarUrl = computed(() => sharer.value.meta.avatar.small);
const deckUrl = computed(() =>
    window.STUDIP.URLHelper.getURL(
        `plugins.php/lernkartenplugin/decks/${props.sharedDeck.deck.data.id}`,
        {},
        true
    )
);
const formattedName = computed(() => sharer.value['formatted-name']);
const sharer = computed(() => props.sharedDeck.sharer.data);
</script>

<template>
    <section
        class="tw-flex tw-gap-2 tw-h-24 tw-py-2 tw-border tw-border-solid tw-border-[var(--light-gray-color-20)]"
        @click="onSelect"
    >
        <div class="tw-flex tw-items-center tw-justify-center tw-w-24 tw-aspect-square">
            <StudipIcon shape="share" role="info" :size="32" />
        </div>
        <div class="tw-flex tw-flex-col tw-flex-grow tw-justify-between">
            <div class="tw-text-lg tw-font-bold">
                {{ sharedDeck.deck.data.name }}
            </div>
            <div class="tw-flex tw-items-center tw-justify-between">
                <StudipAvatar :avatar-url="avatarUrl" :formatted-name="formattedName" />
                <div>
                    <StudipIcon shape="dialog-cards" role="info" />
                    {{ sharedDeck.deck.data.meta['cards-count'] }}
                </div>
                <div class="tw-px-4">
                    <IconButton
                        icon="refresh"
                        type="button"
                        @click="$emit('colearn')"
                    >
                        {{ $gettext('Lernen') }}
                    </IconButton>
                </div>
            </div>
        </div>
    </section>
</template>
