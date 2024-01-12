import { ref, computed } from 'vue';
import { defineStore } from 'pinia';
import { api } from '../api.js';
import { useContextStore } from './context.js';
import { useDecksStore } from './decks.js';

export const useSharedDecksStore = defineStore(
    'sharedDecks',
    () => {
        const contextStore = useContextStore();
        const decksStore = useDecksStore();

        const records = ref(new Map());
        const isLoading = ref(false);
        const errors = ref(false);

        function storeRecord(newRecord) {
            records.value.set(newRecord.id, newRecord);
        }

        const all = computed(() => {
            return [...records.value.values()];
        });

        function fetchById(id) {
            isLoading.value = true;
            return api
                .fetch(`lernkarten-shared-decks/${id}`, {
                    params: {
                        include: 'colearning-deck.owner,deck,sharer',
                    },
                })
                .then(({ data }) => {
                    storeRecord(data);
                    return byId(data.id);
                })
                .catch((err) => {
                    console.error('fetching shared deck', err);
                    errors.value = err;
                })
                .finally(() => (isLoading.value = false));
        }

        async function fetchContext() {
            isLoading.value = true;

            const { data } = await api.fetch(
                `${contextStore.type}/${contextStore.id}/lernkarten-shared-decks`,
                {
                    params: {
                        include: 'colearning-deck.owner,deck,sharer',
                        'page[limit]': 1000,
                    },
                }
            );
            isLoading.value = false;
            data.forEach(storeRecord);
        }

        const byContext = computed(() =>
            all.value.filter(
                ({ recipient }) =>
                    recipient.data.type === contextStore.type &&
                    recipient.data.id === contextStore.id
            )
        );

        function byId(id) {
            return records.value.get(id);
        }

        function shareDeckWithCourse(deck, course) {
            return createSharedDeck(deck, course);
        }

        function shareDeckWithUserIds(deck, userIds) {
            return Promise.all(userIds.map((id) => createSharedDeck(deck, { id, type: 'users' })));
        }

        function unshareDeck(sharedDeck) {
            return api
                .delete('lernkarten-shared-decks', sharedDeck.id)
                .then(() => records.value.delete(sharedDeck.id));
        }

        async function createSharedDeck(deck, recipient) {
            const record = {
                deck: { data: { id: deck.id, type: 'lernkarten-decks' } },
                recipient: { data: { id: recipient.id, type: recipient.type } },
            };
            const { data } = await api.create('lernkarten-shared-decks', record);

            return fetchById(data.id);
        }

        async function colearn(sharedDeck) {
            const { data } = await api.post(
                `lernkarten-shared-decks/${sharedDeck.id}/colearn`,
                sharedDeck
            );

            return decksStore.fetchById(data.id).then(() => fetchById(sharedDeck.id));
        }

        async function copy(sharedDeck) {
            const { data } = await api.post(`lernkarten-shared-decks/${sharedDeck.id}/copy`);

            return decksStore.fetchById(data.id);
        }

        return {
            all,
            byContext,
            byId,
            colearn,
            copy,
            errors,
            fetchById,
            fetchContext,
            isLoading,
            shareDeckWithCourse,
            shareDeckWithUserIds,
            unshareDeck,
        };
    },
    {
        persist: true,
    }
);
