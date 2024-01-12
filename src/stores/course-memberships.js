import { ref, computed } from 'vue';
import { defineStore } from 'pinia';
import { api } from '../api.js';
import { useContextStore } from './context.js';

export const useCourseMembershipsStore = defineStore(
    'courseMemberships',
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

        const userId = computed(() => window.STUDIP.USER_ID);

        async function fetch() {
            isLoading.value = true;

            try {
                const { data } = await api.fetch(`users/${userId.value}/course-memberships`, {
                    params: {
                        include: 'course',
                        'page[limit]': 1000,
                    },
                });
                data.forEach(storeRecord);
            } catch (err) {
                console.error('fetching course-memberships', err);
                errors.value = err;
            }
            isLoading.value = false;
        }

        async function fetchContext() {
            isLoading.value = true;
            try {
                const { data } = await api.fetch(
                    `course-memberships/${contextStore.id}_${contextStore.userId}`,
                    {
                        params: { include: 'course' },
                    },
                );
                storeRecord(data);
            } catch (err) {
                console.error('fetching course-membership', err);
                errors.value = err;
            }
            isLoading.value = false;
        }

        function byContext() {
            return byId(`${contextStore.id}_${contextStore.userId}`);
        }

        function byId(id) {
            return records.value.get(id);
        }

        return {
            all,
            byContext,
            byId,
            errors,
            fetch,
            fetchContext,
            isLoading,
        };
    },
    {
        persist: true,
    },
);
