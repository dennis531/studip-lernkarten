<script setup>
import StudipIcon from './base/StudipIcon.vue';

defineProps(['icon', 'variant']);
</script>

<template>
    <a class="button" :class="{ 'button-link': variant === 'link' }">
        <span class="tw-items-center tw-inline-flex tw-gap-1 tw-align-middle">
            <StudipIcon :shape="icon" role="clickable" ariaRole="none" />
            <StudipIcon :shape="icon" role="info_alt" ariaRole="none" />
            <span>
                <slot></slot>
            </span>
        </span>
    </a>
</template>

<style scoped>
a {
    height: calc(16px * 1.3 + 10px);
    line-height: calc(16px * 1.3 + 10px - 2px);
    padding-block: 0;
    padding-inline-start: 8px;
    transition: all 0.1s ease-in-out;
}

a:hover:not(:disabled) > span > img:nth-child(1) {
    display: none;
}
a:disabled > span > img:nth-child(2),
a:not(:hover) > span > img:nth-child(2) {
    display: none;
}

.button-link {
    background: transparent;
}
</style>
