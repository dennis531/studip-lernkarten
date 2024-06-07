<script setup>
import { computed } from 'vue';
import { useContextStore } from '../stores/context.js';
import { RouterLink, useRoute } from 'vue-router';

const contextStore = useContextStore();
const route = useRoute();

const matchedName = computed(() => route?.matched?.[0]?.name ?? '');
const catchAll = computed(() => !['shared'].includes(matchedName.value));
</script>

<template>
    <ul
        class="widget-list widget-links sidebar-navigation navigation-level-3"
        :aria-label="$gettext('Dritte Navigationsebene')"
    >
        <li :class="{ active: catchAll }">
            <RouterLink
                to="/"
                id="nav_lernkarten_index"
                activeClass="active"
                ariaCurrentValue="page"
            >
                {{ $gettext('Lernkarten') }}
            </RouterLink>
        </li>
        <li :class="{ active: matchedName === 'shared' }">
            <RouterLink
                v-if="!contextStore.isCourse"
                to="/shared"
                id="nav_lernkarten_shared"
                activeClass="active"
                ariaCurrentValue="page"
            >
                {{ $gettext('Geteilte Kartensätze') }}
            </RouterLink>
        </li>
    </ul>
</template>
