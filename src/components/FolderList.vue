<script setup>
import { computed, ref } from 'vue';
import { RouterLink } from 'vue-router';
import StudipIcon from '../components/base/StudipIcon.vue';

const props = defineProps(['folders']);
const emit = defineEmits(['delete-folder', 'edit-folder']);
const deleteFolder = (folder) => emit('delete-folder', folder);
const editFolder = (folder) => emit('edit-folder', folder);
const sortOrder = ref('asc');
const sortBy = ref('name');

const sortData = (sortOrder, sortBy) => {
    return function (a, b) {
        let modifier = 1;
        if (sortOrder === 'desc') {
            modifier = -1;
        }

        if (a[sortBy] === b[sortBy]) {
            return 0;
        } else if (!a[sortBy]) {
            return 1;
        } else if (!b[sortBy]) {
            return -1;
        } else if (a[sortBy] < b[sortBy]) {
            return -1 * modifier;
        } else if (a[sortBy] > b[sortBy]) {
            return 1 * modifier;
        }
    };
};

const sortedFolders = computed(() => {
    if (props.folders && props.folders.length) {
        let newOrder = props.folders;
        return newOrder.sort(sortData(sortOrder.value, sortBy.value));
    }

    return [];
});

const toggleSort = (field) => {
    if (sortBy.value == field) {
        sortOrder.value = sortOrder.value == 'asc' ? 'desc' : 'asc';
    } else {
        sortOrder.value = 'asc';
    }

    sortBy.value = field;
};
</script>

<template>
    <colgroup>
        <col style="width: 36px" />
        <col />
        <col style="width: 64px" />
    </colgroup>
    <thead>
        <tr class="sortable">
            <th></th>
            <th
                :class="{
                    sortasc: sortBy == 'name' && sortOrder == 'asc',
                    sortdesc: sortBy == 'name' && sortOrder == 'desc',
                }"
                @click="toggleSort('name')"
                class="tw-cursor-pointer"
            >
                {{ $gettext('Name') }}
            </th>
            <th class="actions">{{ $gettext('Aktionen') }}</th>
        </tr>
    </thead>

    <tbody>
        <template v-if="sortedFolders.length > 0">
            <tr class="studip toggle tw-my-2" v-for="folder in folders" :key="folder.id">
                <td>
                    <StudipIcon
                        shape="folder-empty"
                        :height="26"
                        :width="26"
                        class="tw-align-middle tw-mr-1 tw-mb-1"
                        ariaRole="none"
                    />
                </td>
                <td>
                    <RouterLink :to="{ name: 'folder', params: { id: folder.id } }">{{
                        folder.name
                    }}</RouterLink>
                </td>
                <td class="actions">
                    <nav>
                        <button
                            type="button"
                            @click="editFolder(folder)"
                            class="tw-border-0 tw-p-0 tw-bg-transparent tw-cursor-pointer tw-mr-2"
                            :title="$gettext('Bearbeiten')"
                        >
                            <StudipIcon shape="edit" class="tw-align-middle" ariaRole="none" />
                            <span class="sr-only">{{ $gettext('Bearbeiten') }}</span>
                        </button>
                        <button
                            type="button"
                            @click="deleteFolder(folder)"
                            class="tw-border-0 tw-p-0 tw-bg-transparent tw-cursor-pointer"
                            :title="$gettext('Löschen')"
                        >
                            <StudipIcon shape="trash" class="tw-align-middle" ariaRole="none" />
                            <span class="sr-only">{{ $gettext('Löschen') }}</span>
                        </button>
                    </nav>
                </td>
            </tr>
        </template>
        <tr v-else>
            <td colspan="3">
                <slot name="empty">
                    {{ $gettext('Dieser Ordner enthält keine Unterordner.') }}
                </slot>
            </td>
        </tr>
    </tbody>
</template>
