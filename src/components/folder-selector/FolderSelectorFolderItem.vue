<template v-if="folderIsReadable">
    <tr @click.prevent="selectFolder">
        <td class="document-icon">
            <a href="#">
                <StudipIcon shape="folder-full" :size="24" />
            </a>
        </td>
        <td>
            <a href="#" @click.prevent="selectFolder">{{ folder.name }}</a>
        </td>
    </tr>
</template>

<script setup>
import StudipIcon from '../base/StudipIcon.vue';

const props = defineProps({
    folder: {
        type: Object,
        required: true,
    },
});
const emit = defineEmits(['select']);

const selectFolder = () => emit('select', props.folder);
</script>

<style scoped>
tr {
    cursor: pointer;
}
</style>
