<script setup>
import StudipDialog from './base/StudipDialog.vue';

defineProps(['open']);
defineEmits(['update:open', 'confirm']);
</script>

<template>
    <StudipDialog
        :question="$gettext('Möchten Sie den Kartensatz wirklich löschen?')"
        :open="open"
        @update:open="$emit('update:open', false)"
        @confirm="$emit('confirm')"
        :title="$gettext('Kartensatz löschen')"
    />
</template>
