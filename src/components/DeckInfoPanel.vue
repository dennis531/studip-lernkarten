<script setup>
import { computed, ref } from 'vue';
import { useGettext } from 'vue3-gettext';
import CourseAvatar from './CourseAvatar.vue';
import DialogConfirmUnshare from './DialogConfirmUnshare.vue';
import StudipActionMenu from './base/StudipActionMenu.vue';
import StudipAvatar from './base/StudipAvatar.vue';
import StudipDate from '../components/base/StudipDate.vue';
import StudipIcon from '../components/base/StudipIcon.vue';
import { useContextStore } from '../stores/context.js';
import { useDecksStore } from '../stores/decks.js';

const contextStore = useContextStore();
const decksStore = useDecksStore();
const { $gettext } = useGettext();

const props = defineProps(['deck']);

const selectedSharedDeck = ref(null);
const showConfirmUnshareDialog = ref(false);

const creator = computed(() =>
    props.deck.colearning ? props.deck.template.data.owner.data : props.deck.owner.data,
);
const chdate = computed(
    () => new Date(props.deck.colearning ? props.deck.template.data.chdate : props.deck.chdate),
);
const avatarUrl = computed(() => creator.value.meta.avatar.small);
const formattedName = computed(() => creator.value['formatted-name']);
const isOwner = computed(() => contextStore.userId === props.deck.owner.data.id);
const progress = computed(() => {
    const total = props.deck.progress.reduce((sum, n) => sum + n, 0);

    return Math.floor((total ? props.deck.progress[2] / total : 0) * 100);
});
const sharedWithCourses = computed(() =>
    props.deck['shared-decks'].data.filter(({ recipient }) => recipient.data.type === 'courses'),
);
const sharedWithUsers = computed(() =>
    props.deck['shared-decks'].data.filter(({ recipient }) => recipient.data.type === 'users'),
);
const actionMenuItems = computed(() => {
    return isOwner.value
        ? [
              {
                  id: 'unshare',
                  label: $gettext('Teilen aufheben'),
                  icon: 'decline',
                  emit: 'unshare',
              },
          ]
        : [];
});

const courseUrl = (course) =>
    window.STUDIP.URLHelper.getURL('plugins.php/lernkartenplugin', { cid: course.id });
const userUrl = (user) =>
    window.STUDIP.URLHelper.getURL('dispatch.php/profile', { username: user.username });
const onUnshare = (sharedDeck) => {
    selectedSharedDeck.value = sharedDeck;
    showConfirmUnshareDialog.value = true;
};
const onDidUnshare = () => {
    decksStore.fetchById(props.deck.id);
};
</script>

<template>
    <article class="studip">
        <header>
            <h1>{{ $gettext('Allgemeine Informationen') }}</h1>
        </header>
        <section class="lernkarten-general-infos">
            <table>
                <tr>
                    <th>
                        <StudipIcon shape="add" role="info" ariaRole="none" />
                        {{ $gettext('Zuletzt bearbeitet') }}
                    </th>
                    <td>
                        <StudipDate :date="chdate" />
                        {{ $gettext('von') }}
                        <StudipAvatar :avatar-url="avatarUrl" :formatted-name="formattedName" />
                    </td>
                </tr>

                <tr>
                    <th>
                        <StudipIcon shape="dialog-cards" role="info" ariaRole="none" />
                        {{ $gettext('Anzahl Karten') }}
                    </th>
                    <td>
                        <span>{{ deck.cards.data.length }}</span>
                    </td>
                </tr>
                <tr>
                    <th>
                        <StudipIcon shape="vote" role="info" ariaRole="none" />
                        {{ $gettext('Gesamtfortschritt') }}
                    </th>
                    <td>
                        <span>{{ progress }}%</span>
                    </td>
                </tr>
            </table>
        </section>
    </article>

    <article class="studip">
        <header>
            <h1>{{ $gettext('Beschreibung') }}</h1>
        </header>
        <section>
            {{ deck.description }}
        </section>
    </article>

    <article class="studip">
        <header>
            <h1>{{ $gettext('Metadaten') }}</h1>
        </header>
        <section>
            <span v-if="deck.metadata.length">{{ deck.metadata }}</span>
            <span v-else>–</span>
        </section>
    </article>

    <article class="studip" v-if="isOwner && (sharedWithCourses.length || sharedWithUsers.length)">
        <header>
            <h1>{{ $gettext('Von mir geteilt mit') }}</h1>
        </header>

        <table class="default">
            <thead>
                <tr>
                    <th>
                        {{ $gettext('Name') }}
                    </th>
                    <th class="actions">{{ $gettext('Aktionen') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="sharedDeck in sharedWithCourses" :key="sharedDeck.id">
                    <td>
                        <a :href="courseUrl(sharedDeck.recipient.data)">
                            <CourseAvatar :course="sharedDeck.recipient.data" />
                        </a>
                    </td>
                    <td class="actions">
                        <StudipActionMenu
                            v-if="actionMenuItems.length"
                            :items="actionMenuItems"
                            :collapseAt="0"
                            @unshare="onUnshare(sharedDeck)"
                        />
                    </td>
                </tr>
                <tr v-for="sharedDeck in sharedWithUsers" :key="sharedDeck.id">
                    <td>
                        <a :href="userUrl(sharedDeck.recipient.data)">
                            <StudipAvatar
                                :avatar-url="sharedDeck.recipient.data.meta.avatar.small"
                                :formatted-name="sharedDeck.recipient.data['formatted-name']"
                            />
                        </a>
                    </td>
                    <td class="actions">
                        <StudipActionMenu
                            v-if="actionMenuItems.length"
                            :items="actionMenuItems"
                            :collapseAt="0"
                            @unshare="onUnshare(sharedDeck)"
                        />
                    </td>
                </tr>
            </tbody>
        </table>
    </article>

    <DialogConfirmUnshare
        v-model:open="showConfirmUnshareDialog"
        :shared-deck="selectedSharedDeck"
        @confirm="onDidUnshare"
    />
</template>

<style scoped>
.lernkarten-general-infos th {
    align-items: center;
    display: flex;
    font-weight: normal;
    gap: 0.25rem;
    opacity: 0.6;
    padding-inline-end: 2rem;
}
.lernkarten-general-infos td {
    font-weight: bold;
}
.lernkarten-general-infos tr + tr > * {
    padding-block-start: 1rem;
}

.lernkarten-breadcrumbs {
    display: flex-inline;
    flex-wrap: wrap;
    font-style: italic;
    font-weight: normal;
}
.lernkarten-breadcrumbs > span:not(.sr-only) + span:before {
    content: '»';
    margin-inline: 0.2em;
}
.lernkarten-breadcrumbs > span {
    white-space: nowrap;
}
</style>
