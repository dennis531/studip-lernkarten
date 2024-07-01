import { ref, computed } from 'vue';
import { defineStore } from 'pinia';
import { api } from '../api.js';

export const useCardsStore = defineStore(
    'cards',
    () => {
        const records = ref(new Map());
        const isLoading = ref(false);
        const errors = ref(false);

        function storeRecord(newRecord) {
            if (records.value.has(newRecord.id)) {
                const existingRecord = records.value.get(newRecord.id);
                Object.assign(existingRecord, newRecord);
            } else {
                records.value.set(newRecord.id, newRecord);
            }
        }

        const all = computed(() => {
            return [...records.value.values()];
        });

        const byDeck = (deck) => {
            return all.value.filter((card) => card.deck.data.id === deck.id);
        };

        async function fetchByDeck(deck) {
            isLoading.value = true;
            const { data } = await api.fetch(`lernkarten-decks/${deck.id}/cards`, {
                params: {
                    'page[limit]': 1000,
                },
            });
            isLoading.value = false;
            data.forEach(storeRecord);
        }

        async function createCard(deck, card) {
            const { data } = await api.create('lernkarten-cards', {
                deck: { data: { id: deck.id, type: 'lernkarten-decks' } },
                ...card,
            });
            storeRecord(data);

            return data;
        }

        async function generateCards(deck, content, number) {
            return api.axios.post('lernkarten-cards/generate',{
                deck_id: deck.id,
                content: content,
                number: number,
            }, {
                timeout: 60000 // Wait one minute
            });
        }

        async function deleteCard(card) {
            return api
                .delete('lernkarten-cards', card.id)
                .then(() => records.value.delete(card.id));
        }

        async function importCards(deck, cards) {
            const chunks = _.chunk(cards, 8);
            for (const chunk of chunks) {
                await Promise.all(chunk.map((card) => createCard(deck, card)));
            }
        }

        async function updateFields(card, fields) {
            try {
                const { data } = await api.patch('lernkarten-cards', { id: card.id, fields });
                storeRecord(data);
            } catch (err) {
                errors.value = err;
                console.error('Could not update fields', err);
            }
        }

        async function updateLearningStats(card, stats) {
            try {
                const { data } = await api.patch('lernkarten-cards', { id: card.id, ...stats });
                storeRecord(data);
            } catch (err) {
                errors.value = err;
                console.error('Could not update stats', err);
            }
        }

        return {
            all,
            byDeck,
            createCard,
            generateCards,
            deleteCard,
            errors,
            fetchByDeck,
            importCards,
            isLoading,
            updateFields,
            updateLearningStats,
        };
    },
    {
        persist: true,
    },
);
