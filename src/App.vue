<script setup>
import { RouterView } from 'vue-router';
import SidebarActions from './components/SidebarActions.vue';
import SidebarNavigation from './components/SidebarNavigation.vue';
import SidebarSearch from './components/SidebarSearch.vue';
import StudipCompanionOverlay from './components/base/StudipCompanionOverlay.vue';
import { useFoldersStore } from './stores/folders.js';

const foldersStore = useFoldersStore();
foldersStore.fetch();

hideOriginalSidebarNavigation();

function hideOriginalSidebarNavigation() {
    const navigation = document.querySelector('#sidebar-navigation form');
    navigation.querySelector(
        '.sidebar-navigation:not(.lernkarten-sidebar-navigation)',
    ).hidden = true;
}
</script>

<template>
    <RouterView />

    <StudipCompanionOverlay />

    <Teleport to="#sidebar-navigation form">
        <SidebarNavigation />
    </Teleport>

    <Teleport to="#sidebar">
        <SidebarActions />
        <SidebarSearch />
    </Teleport>
</template>
