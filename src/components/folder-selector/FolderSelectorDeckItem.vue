<script setup>
import StudipIcon from '../base/StudipIcon.vue';

defineProps({ deck: { type: Object, required: true } });
</script>

<template>
    <tr class="tw-pointer-events-none">
        <td class="document-icon">
            <StudipIcon shape="dialog-cards" role="info" :size="24" />
        </td>
        <td>
            {{ deck.name }}
        </td>
    </tr>
</template>
