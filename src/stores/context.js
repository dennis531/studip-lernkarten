import { computed, inject } from 'vue';
import { defineStore } from 'pinia';

// TODO: This should be a composable and not a store.
export const useContextStore = defineStore(
    'context',
    () => {
        const initialState = inject('initialState');

        const id = computed(() => {
            return isCourse.value ? window.STUDIP.URLHelper.parameters.cid : userId.value;
        });

        const isCourse = computed(() => {
            return 'cid' in window.STUDIP.URLHelper.parameters;
        });

        const isTeacher = computed(() => initialState.isTeacher);

        const type = computed(() => (isCourse.value ? 'courses' : 'users'));

        const userId = computed(() => window.STUDIP.USER_ID);

        return { id, isCourse, isTeacher, type, userId };
    },
    {
        persist: true,
    },
);
