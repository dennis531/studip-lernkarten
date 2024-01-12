<script setup>
import CardImage from '../CardImage.vue';

defineProps(['card']);
</script>

<template>
    <section>
        <article
            class="tw-p-4 tw-text-lg tw-border tw-border-solid tw-border-[var(--light-gray-color-20)]"
        >
            <CardImage v-if="card.fields.images?.front" :image="card.fields.images['front']" />
            <div v-html="card.fields.front"></div>
        </article>
        <article
            class="tw-p-4 tw-mt-2 tw-text-lg tw-border tw-border-solid tw-border-[var(--light-gray-color-20)]"
        >
            <CardImage v-if="card.fields.images?.back" :image="card.fields.images['back']" />
            <div v-html="card.fields.back"></div>
        </article>
    </section>
</template>
