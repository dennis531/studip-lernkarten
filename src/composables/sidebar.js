import { computed, ref } from 'vue';

const actions = ref([]);

export function useSidebar() {
    const addAction = (icon, text, handler) => {
        const action = { icon, text, handler };
        actions.value.push(action);

        return action;
    };

    const clearActions = () => {
        actions.value = [];
    };

    const removeAction = (action) => {
        actions.value = actions.value.filter((item) => item !== action);
    };

    return {
        actions,
        addAction,
        clearActions,
        removeAction,
    };
}
