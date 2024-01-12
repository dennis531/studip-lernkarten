<script setup>
import CardImage from '../CardImage.vue';
import { ref } from 'vue';

defineProps(['card']);

const answer = ref('');
const textarea = ref(null);
</script>

<template>
    <section v-if="card">
        <CardImage v-if="card.fields.images?.front" :image="card.fields.images['front']" />
        <div v-html="card.fields.front" class="front"></div>
        <div>
            <textarea ref="textarea" v-model="answer" :placeholder="$gettext('Antwort')" />
        </div>
    </section>
</template>

<style scoped>
section {
    border: 1px solid var(--content-color-20);
}
section > div:first-child,
section > div.front {
    background: var(--content-color-10);
    font-size: 20px;
    padding-block: 12px;
    padding-inline: 12px;
}
section > div:last-child {
    background: var(--content-color-20);
    font-size: 20px;
}
textarea {
    background: transparent;
    border: none;
    box-sizing: border-box;
    margin-block-start: 12px;
    opacity: 0.6;
    padding-block-start: 12px;
    padding-inline: 12px;
    resize: none;
    width: 100%;
}
</style>
