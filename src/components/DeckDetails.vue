<script setup>
import { computed } from 'vue';
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from '@headlessui/vue';
import DeckCardsPanel from '../components/DeckCardsPanel.vue';
import DeckInfoPanel from '../components/DeckInfoPanel.vue';
import DeckSettingsPanel from '../components/DeckSettingsPanel.vue';
import DeckStatisticsPanel from '../components/DeckStatisticsPanel.vue';
import { useContextStore } from '../stores/context.js';

const contextStore = useContextStore();

const props = defineProps(['deck']);

const isColearning = computed(() => props.deck.colearning);
const isOwner = computed(() => contextStore.userId === props.deck.owner.data.id);
</script>

<template>
    <TabGroup as="div" class="cw-tabs" :default-index="0">
        <TabList class="cw-tabs-nav">
            <Tab as="template" v-slot="{ selected }">
                <button :class="{ 'is-active': selected }">
                    {{ $gettext('Info') }}
                </button>
            </Tab>
            <Tab as="template" v-slot="{ selected }">
                <button :class="{ 'is-active': selected }">
                    {{ $gettext('Karten') }}
                </button>
            </Tab>
            <Tab v-if="isOwner" as="template" v-slot="{ selected }">
                <button :class="{ 'is-active': selected }">
                    {{ $gettext('Statistiken') }}
                </button>
            </Tab>
            <Tab v-if="isOwner && !isColearning" as="template" v-slot="{ selected }">
                <button :class="{ 'is-active': selected }">
                    {{ $gettext('Einstellungen') }}
                </button>
            </Tab>
        </TabList>
        <TabPanels class="cw-tabs-content">
            <TabPanel>
                <DeckInfoPanel :deck="deck" />
            </TabPanel>
            <TabPanel>
                <DeckCardsPanel :deck="deck" />
            </TabPanel>
            <TabPanel v-if="isOwner">
                <DeckStatisticsPanel :deck="deck" />
            </TabPanel>
            <TabPanel v-if="isOwner && !isColearning">
                <DeckSettingsPanel :deck="deck" />
            </TabPanel>
        </TabPanels>
    </TabGroup>
</template>
