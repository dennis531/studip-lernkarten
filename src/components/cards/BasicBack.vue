<script setup>
defineProps(['card']);
</script>

<template>
    <section v-if="card">
        <div v-html="card.fields.back"></div>
    </section>
</template>

<style scoped>
section {
    border: 1px solid var(--content-color-20);
}
section > div {
    background: var(--content-color-10);
    font-size: 20px;
    padding-block: 12px;
    padding-inline: 12px;
}
</style>
