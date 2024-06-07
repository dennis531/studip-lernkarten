import { ref, computed } from 'vue';
import { defineStore } from 'pinia';
import { api } from '../api.js';
import { useContextStore } from './context.js';

export const useFoldersStore = defineStore('folders', () => {
    const contextStore = useContextStore();
    const context = contextStore.id;

    const records = ref(new Map());
    const isLoading = ref(false);
    const errors = ref(false);

    function storeRecord(newRecord) {
        records.value.set(newRecord.id, newRecord);
    }

    const all = computed(() => {
        return [...records.value.values()];
    });

    const topFolders = computed(() => {
        return _.sortBy(
            all.value.filter((folder) => !folder.parent.data),
            'name',
        );
    });

    const byContext = computed(() => {
        return all.value.filter((folder) => folder.context.data.id === context);
    });

    async function fetch() {
        isLoading.value = true;
        try {
            const { data } = await api.fetch('lernkarten-folders', {
                params: {
                    'page[limit]': 1000,
                },
            });
            data.forEach(storeRecord);
        } catch (err) {
            console.error('fetching folders', err);
            errors.value = err;
        }
        isLoading.value = false;
    }

    async function fetchWorkplace() {
        isLoading.value = true;
        try {
            const { data } = await api.fetch(
                // TODO: Diese Route fehlt noch. Aktuell macht sie aber dasselbe
                // `users/${contextStore.userId}/lernkarten-folders`,
                `lernkarten-folders`,
                {
                    params: {
                        'page[limit]': 1000,
                    },
                },
            );
            data.forEach(storeRecord);
        } catch (err) {
            console.error('fetching folders', err);
            errors.value = err;
        }
        isLoading.value = false;
    }

    function ancestors(folder, path = []) {
        if (!folder?.parent.data) {
            return path;
        }
        const parent = byId(folder.parent.data.id);
        return ancestors(parent, [parent, ...path]);
    }

    function byId(id) {
        return records.value.get(id);
    }

    function children(id) {
        return all.value.filter((folder) => folder.parent.data?.id === id);
    }

    function createFolder(name, parent) {
        const data = {
            name,
            context: { data: { id: contextStore.id, type: contextStore.type } },
            parent: parent
                ? { data: { id: parent.id, type: 'lernkarten-folders' } }
                : { data: null },
        };
        return api.create('lernkarten-folders', data).then(({ data }) => {
            storeRecord(data);
        });
    }

    function updateFolder(folder, attributes) {
        return api
            .patch('lernkarten-folders', { id: folder.id, ...attributes })
            .then(({ data }) => {
                storeRecord(data);
            });
    }

    function deleteFolder(folder) {
        return api
            .delete('lernkarten-folders', folder.id)
            .then(() => records.value.delete(folder.id));
    }

    return {
        all,
        ancestors,
        byContext,
        byId,
        children,
        createFolder,
        updateFolder,
        deleteFolder,
        fetch,
        fetchWorkplace,
        isLoading,
        topFolders,
    };
});
