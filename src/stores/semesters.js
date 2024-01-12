import { ref, computed } from 'vue';
import { defineStore } from 'pinia';
import { api } from '../api.js';

export const useSemestersStore = defineStore(
    'semesters',
    () => {
        const records = ref(new Map());
        const isLoading = ref(false);
        const errors = ref(false);

        function storeRecord(newRecord) {
            records.value.set(newRecord.id, newRecord);
        }

        const all = computed(() => {
            return [...records.value.values()];
        });

        async function fetch() {
            isLoading.value = true;

            try {
                const { data } = await api.fetch(`semesters`, {
                    params: {
                        'page[limit]': 1000,
                    },
                });
                data.forEach(storeRecord);
            } catch (err) {
                console.error('fetching semesters', err);
                errors.value = err;
            }
            isLoading.value = false;
        }

        function byId(id) {
            return records.value.get(id);
        }

        return {
            all,
            byId,
            errors,
            fetch,
            isLoading,
        };
    },
    {
        persist: true,
    },
);
