<script setup>
import { computed, ref } from 'vue';
import { useGettext } from 'vue3-gettext';
import BasicBack from './cards/BasicBack.vue';
import BasicFront from './cards/BasicFront.vue';
import ImageBack from './cards/ImageBack.vue';
import ImageFront from './cards/ImageFront.vue';
import IconButton from './IconButton.vue';
import StudipCompanion from './base/StudipCompanion.vue';
import StudipIcon from './base/StudipIcon.vue';
import StudipProgressIndicator from './base/StudipProgressIndicator.vue';
import StudyViewCongratulations from './StudyViewCongratulations.vue';
import StudyViewRepeatButtons from './StudyViewRepeatButtons.vue';
import StudyViewStatistics from './StudyViewStatistics.vue';
import { useFsrs } from '../composables/fsrs.js';
import { useScheduler } from '../composables/scheduler.js';

const { translatedStates } = useFsrs();

const props = defineProps({
    decks: { type: String },
    order: { type: String },
    standalone: { type: Boolean, default: false },
});
const emit = defineEmits(['cancel']);

const { $gettext } = useGettext();
const {
    cardStates,
    cards,
    cardsLeft,
    decks,
    isLoading,
    order,
    queuedCard,
    ratings,
    repeat,
    reset,
} = useScheduler({
    decks: props.decks,
    order: props.order,
});

const showAnswer = ref(false);
const showCongratulations = ref(false);

const cardFront = computed(() => {
    switch (queuedCard.value.model) {
        case 'image':
            return ImageFront;
        default:
            return BasicFront;
    }
});
const cardBack = computed(() => {
    switch (queuedCard.value.model) {
        case 'image':
            return ImageBack;
        default:
            return BasicBack;
    }
});
const deckName = computed(() => {
    return decks.value.get(queuedCard.value.deck.data.id).name;
});
const hasCards = computed(() => !!cards.value.length);

const readableState = computed(() =>
    queuedCard.value ? translatedStates[queuedCard.value.state] : null,
);

const onAgain = () => {
    reset();
    showCongratulations.value = false;
};
const onCancel = () => emit('cancel');
const onContinue = () => (showCongratulations.value = false);
const onShowAnswer = () => (showAnswer.value = true);
const onRepeat = (rating) => {
    repeat(rating);
    showAnswer.value = false;
    if (!queuedCard.value) {
        showCongratulations.value = true;
    }
};
const onStop = () => (showCongratulations.value = true);
</script>

<template>
    <div class="tw-flex tw-flex-col tw-items-center">
        <StudipProgressIndicator :description="$gettext('Lade Karten …')" v-if="isLoading" />
        <div v-if="!isLoading" class="tw-max-w-[700px] tw-w-full">
            <StudipCompanion
                v-if="!hasCards"
                :msg-companion="$gettext('Keine Karten enthalten.')"
                mood="sad"
            >
                <template #companionActions>
                    <IconButton v-if="!standalone" icon="decline" type="button" @click="onCancel">
                        {{ $gettext('Beenden') }}
                    </IconButton>
                </template>
            </StudipCompanion>
            <div v-if="queuedCard && !showCongratulations">
                <StudyViewStatistics
                    class="tw-mb-8"
                    :card-states="cardStates"
                    :cards-left="cardsLeft"
                    :order="order"
                />
                <div class="tw-flex tw-items-center tw-gap-2 tw-my-6 tw-opacity-50">
                    <StudipIcon shape="dialog-cards" role="info" height="32" width="32" />
                    <span class="tw-flex-grow">{{ deckName }}</span>
                    <span class="tw-text-xs">{{ readableState }}</span>
                </div>
                <component
                    :is="cardFront"
                    :card="queuedCard"
                    class="tw-shadow-lg tw-my-2 tw-min-h-[4rem]"
                />
                <div class="tw-flex tw-justify-between tw-my-6">
                    <IconButton v-if="!showAnswer" icon="visibility-visible" @click="onShowAnswer">
                        {{ $gettext('Antwort ansehen') }}
                    </IconButton>
                    <StudyViewRepeatButtons v-else @repeat="onRepeat" />
                    <IconButton icon="decline" variant="link" type="button" @click="onStop">
                        {{ $gettext('Beenden') }}
                    </IconButton>
                </div>
                <component
                    v-if="showAnswer"
                    :is="cardBack"
                    :card="queuedCard"
                    class="tw-shadow-lg tw-my-2"
                />
            </div>
            <StudyViewCongratulations
                v-if="showCongratulations"
                :cards="cards"
                :standalone="standalone"
                :cards-left="cardsLeft"
                :ratings="ratings"
                @again="onAgain"
                @cancel="onCancel"
                @continue="onContinue"
            />
        </div>
    </div>
</template>
