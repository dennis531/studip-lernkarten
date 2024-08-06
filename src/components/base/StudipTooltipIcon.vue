<script setup>
import { computed } from "vue";
import StudipIcon from "@/components/base/StudipIcon.vue";

const props = defineProps({
    text: String,
    isHtml: {
        type: Boolean,
        required: false,
        default: false
    },
    isImportant: {
        type: Boolean,
        required: false,
        default: false
    },
    size: {
        type: Number,
        default: 16
    }
});

const cssClass = computed(() => props.isImportant ? 'tooltip-important' : '');
const title = computed(() => !props.isHtml ? props.text : '');
</script>

<template>
    <span data-tooltip class="tooltip tooltip-icon" :class="cssClass" :title="title" tabindex="0">
        <span class="tooltip-content" v-if="isHtml" v-html="text"></span>
        <studipIcon shape="info-circle" role="inactive" :size="size"></studipIcon>
    </span>
</template>

<style lang="scss" scoped>
.tooltip.tooltip-icon::before {
    display: none;
}
</style>