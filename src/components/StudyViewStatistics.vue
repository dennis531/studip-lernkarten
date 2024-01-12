<script setup>
import { computed } from 'vue';
import StudipIcon from '../components/base/StudipIcon.vue';
import { useFsrs } from '../composables/fsrs.js';

const { State } = useFsrs();

const props = defineProps(['cardStates', 'cardsLeft', 'order']);

const newCards = computed(() => props.cardStates.get(State.New));
const learningCards = computed(() => props.cardStates.get(State.Learning));
const reviewCards = computed(() => props.cardStates.get(State.Review));
const relearningCards = computed(() => props.cardStates.get(State.Relearning));
</script>

<template>
    <div class="">
        <div class="tw-flex tw-gap-2 tw-justify-between tw-items-baseline tw-text-sm">
            <span>
                <span class="">
                    <StudipIcon class="tw-align-middle tw-mr-1" shape="dialog-cards" role="info" />
                    <span>{{ order.text }}</span>
                </span>
                <span v-if="false">
                    <span class="tw-bg-[new]">{{ newCards }}</span>
                    <span class="tw-bg-[red]">{{ learningCards }}</span>
                    <span class="tw-bg-[green]">{{ reviewCards }}</span>
                    <span class="tw-bg-[purple]">{{ relearningCards }}</span>
                </span>
            </span>
            <span>
                Verbleibende Karten:
                <span class="tw-text-lg">{{ cardsLeft }}</span>
            </span>
        </div>
        <div class="tw-h-[1px] tw-bg-slate-300 tw-w-full" />
    </div>
</template>
