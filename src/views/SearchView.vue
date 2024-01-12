<script setup>
import { toRaw, watch } from 'vue';
import { useRoute } from 'vue-router';
import IconButton from '../components/IconButton.vue';
import StudipIcon from '../components/base/StudipIcon.vue';
import DeckList from '../components/DeckList.vue';

import { computed, ref } from 'vue';
import { useDecksStore } from '../stores/decks.js';

const decksStore = useDecksStore();

decksStore.fetchContext().then(() => {
    doSearch();
});

const props = defineProps(['query']);

const query = ref(props.query);

const allDecks = computed(() => decksStore.byContext);
const filteredDecks = ref([]);

const doSearch = () => {
    filteredDecks.value = toRaw(
        allDecks.value.filter((el) => {
            let found = false;

            if (el.name.search(query.value) != -1 || el.description.search(query.value) != -1) {
                found = true;
            }

            return found;
        }),
    );
};

const route = useRoute();

watch(
    () => route.query,
    async (newQuery) => {
        query.value = newQuery.q;
        doSearch();
    },
);
</script>

<template>
    <main>
        <form
            class="default tw-bg-[var(--content-color-20)] tw-p-3 tw-mb-2"
            @submit.prevent="doSearch"
        >
            <div class="tw-font-bold">
                <StudipIcon shape="dialog-cards" role="info" class="tw-align-middle" />
                {{ $gettext('Deine Kartensätze') }}
            </div>

            <section class="tw-flex tw-gap-3 tw-items-center">
                <div class="formpart">
                    <label>
                        <span class="textlabel">{{ $gettext('Kartensätze filtern') }}</span>
                        <input v-model="query" type="text" name="lernkarten-decks-filter" />
                    </label>
                </div>

                <IconButton icon="search" :disabled="decksStore.isLoading">
                    {{ $gettext('Nach Kartensätzen suchen') }}
                </IconButton>
            </section>
        </form>

        <DeckList :decks="filteredDecks" />
    </main>
</template>
