<script setup>
import StudipIcon from './base/StudipIcon.vue';

defineProps(['icon', 'variant']);
</script>

<template>
    <button class="button" :class="{ 'button-link': variant === 'link' }">
        <span>
            <StudipIcon :shape="icon" role="clickable" ariaRole="none" />
            <StudipIcon :shape="icon" role="info_alt" ariaRole="none" />
            <span>
                <slot></slot>
            </span>
        </span>
    </button>
</template>

<style scoped>
button {
    height: calc(16px * 1.3 + 10px);
    line-height: 1em;
    padding-block: 0;
    padding-inline-start: 8px;
}

button > span {
    align-items: center;
    display: inline-flex;
    gap: 0.25rem;
    vertical-align: middle;
}

button {
    transition: all 0.1s ease-in-out;
}

button:hover:not(:disabled) > span > img:nth-child(1) {
    display: none;
}
button:disabled > span > img:nth-child(2),
button:not(:hover) > span > img:nth-child(2) {
    display: none;
}

.button-link {
    background: transparent;
    border: none;
}
</style>
