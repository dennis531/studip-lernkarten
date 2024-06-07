<script setup>
import { computed } from 'vue';
import { RouterLink } from 'vue-router';
import StudipAvatar from './base/StudipAvatar.vue';
import StudipDate from '../components/base/StudipDate.vue';
import StudipIcon from './base/StudipIcon.vue';

const props = defineProps(['sharedDeck']);

const avatarUrl = computed(() => {
    const recipient = props.sharedDeck.recipient.data;
    switch (recipient.type) {
        case 'courses':
            return `${window.STUDIP.ABSOLUTE_URI_STUDIP}/pictures/course/nobody_small.png`;
        case 'users':
            return recipient.meta.avatar.small;
    }
    return null;
});
const formattedName = computed(() => {
    const recipient = props.sharedDeck.recipient.data;
    switch (recipient.type) {
        case 'courses':
            return recipient.title;
        case 'users':
            return recipient['formatted-name'];
    }

    return null;
});
</script>

<template>
    <tr>
        <td>
            <RouterLink :to="{ name: 'deck', params: { id: sharedDeck.deck.data.id } }">
                {{ sharedDeck.deck.data.name }}
            </RouterLink>
        </td>
        <td class="hidden-small-down">
            <StudipIcon shape="dialog-cards" role="info" class="tw-align-middle" aria-role="none" />
            {{ sharedDeck.deck.data.meta['cards-count'] }}
        </td>
        <td>
            <StudipAvatar :avatar-url="avatarUrl" :formatted-name="formattedName" />
        </td>
        <td class="hidden-small-down">
            <StudipDate :date="new Date(sharedDeck.chdate)" />
        </td>
        <td class="actions"></td>
    </tr>
</template>

<style scoped>
td:nth-child(1) button {
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
