<script setup>
import { computed } from 'vue';
import { RouterLink } from 'vue-router';
import { useGettext } from 'vue3-gettext';
import IconButton from './IconButton.vue';
import StudipAvatar from './base/StudipAvatar.vue';
import StudipDate from '../components/base/StudipDate.vue';
import StudipIcon from './base/StudipIcon.vue';

const props = defineProps(['sharedDeck']);
defineEmits(['colearn', 'select']);

const { $gettext } = useGettext();

const avatarUrl = computed(() => props.sharedDeck.sharer.data.meta.avatar.small);
const formattedName = computed(() => props.sharedDeck.sharer.data['formatted-name']);
const colearnDeck = computed(() => props.sharedDeck['colearning-deck']?.data ?? null);
const hasColearn = computed(() => !!colearnDeck.value);
const copies = computed(() => props.sharedDeck['copied-decks'].data ?? []);
const isUsed = computed(() => hasColearn.value || copies.value.lenght > 0);
</script>

<template>
    <tr>
        <td>
            <template v-if="isUsed">
                <StudipIcon
                    shape="accept"
                    role="status-green"
                    :title="$gettext('Kartensatz abonniert und/oder kopiert')"
                />
            </template>
            <template v-else>
                <StudipIcon
                    shape="question"
                    role="status-yellow"
                    :title="$gettext('Kartensatz noch nicht verwendet')"
                />
            </template>
        </td>
        <td>
            <RouterLink
                v-if="hasColearn"
                :to="{ name: 'deck', params: { id: colearnDeck.id }, query: { cid: null } }"
            >
                {{ colearnDeck.name }}
            </RouterLink>
            <template v-else>
                <a href="#" @click.prevent="$emit('select')">
                    {{ sharedDeck.deck.data.name }}
                    <StudipIcon class="tw-align-middle" shape="info-circle" aria-role="none" />
                </a>
            </template>
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
        <td>
            <IconButton v-if="!hasColearn" icon="group" type="button" @click="$emit('colearn')">
                {{ $gettext('Abonnieren') }}
            </IconButton>
        </td>
        <td>
            <IconButton
                v-if="copies.length === 1"
                icon="arr_1right"
                type="button"
                @click="$emit('visit', copies[0])"
            >
                {{ $gettext('Zur Kopie') }}
            </IconButton>
            <template v-if="copies.length >= 1">
                <IconButton icon="arr_1right" type="button" @click="$emit('select')">
                    {{ $gettext('%{ count } Kopien', { count: copies.length }) }}
                </IconButton>
            </template>
        </td>
    </tr>
</template>
