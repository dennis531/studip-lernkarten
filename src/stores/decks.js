import { ref, computed } from 'vue';
import { defineStore } from 'pinia';
import { api } from '../api.js';
import { useContextStore } from './context.js';

export const useDecksStore = defineStore(
    'decks',
    () => {
        const contextStore = useContextStore();

        const records = ref(new Map());
        const isLoading = ref(false);
        const errors = ref(false);

        function storeRecord(newRecord) {
            records.value.set(newRecord.id, newRecord);
        }

        const all = computed(() => {
            return [...records.value.values()];
        });

        const byContext = computed(() => {
            const context = contextStore.id;
            return all.value.filter((deck) => deck.context.data.id === context);
        });

        const fromWorkplace = computed(() => {
            return all.value.filter((deck) => deck.context.data.type === 'users');
        });

        function fetchDecksOf(type, id) {
            isLoading.value = true;
            return api
                .fetch(`${type}/${id}/lernkarten-decks`, {
                    params: {
                        include: 'folder,owner,shared-decks.recipient,shared-with,template.owner',
                        'page[limit]': 1000,
                    },
                })
                .then(({ data }) => {
                    data.forEach(storeRecord);
                    isLoading.value = false;
                })
                .catch((err) => {
                    console.error('fetching decks', err);
                    errors.value = err;
                })
                .finally(() => (isLoading.value = false));
        }

        function fetchContext() {
            return fetchDecksOf(contextStore.type, contextStore.id);
        }

        function fetchWorkplace() {
            return fetchDecksOf('users', contextStore.userId);
        }

        async function fetchById(id) {
            isLoading.value = true;
            try {
                const { data } = await api.fetch(`lernkarten-decks/${id}`, {
                    params: {
                        include: 'folder,owner,shared-decks.recipient,shared-with,template.owner',
                    },
                });
                storeRecord(data);
            } catch (err) {
                console.error('fetching decks', err);
                errors.value = err;
            }
            isLoading.value = false;
        }

        function byId(id) {
            return records.value.get(id);
        }

        async function copyDeck(deck) {
            const { data } = await api.post(`lernkarten-decks/${deck.id}/copy`, deck);

            return fetchById(data.id);
        }

        async function createDeck(folder, name, description, metadata) {
            const record = {
                name,
                description,
                metadata,
                context: { data: { id: contextStore.id, type: contextStore.type } },
                folder: {
                    data: folder ? { id: folder.id, type: 'lernkarten-folders' } : null,
                },
            };
            const { data } = await api.create('lernkarten-decks', record);
            storeRecord(data);

            return data;
        }

        function deleteDeck(deck) {
            return api
                .delete('lernkarten-decks', deck.id)
                .then(() => records.value.delete(deck.id));
        }

        async function updateDeck(deck, attributes) {
            await api.patch('lernkarten-decks', { id: deck.id, ...attributes });
            return fetchById(deck.id);
        }

        return {
            all,
            byContext,
            byId,
            copyDeck,
            createDeck,
            deleteDeck,
            errors,
            fetchById,
            fetchContext,
            fetchWorkplace,
            fromWorkplace,
            isLoading,
            updateDeck,
        };
    },
    {
        persist: true,
    },
);
