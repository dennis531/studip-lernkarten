<script setup>
import { computed } from 'vue';
import BarChart from './BarChart.vue';
import StudipIcon from '../components/base/StudipIcon.vue';
import { useFsrs } from '../composables/fsrs.js';
import { useCardsStore } from '../stores/cards.js';

const cardsStore = useCardsStore();
const { State, translatedStates } = useFsrs();

const props = defineProps(['deck']);

const cards = computed(() => cardsStore.byDeck(props.deck));

const cardsByState = computed(() => {
    return cards.value.reduce(
        (memo, card) => {
            memo[card.state].count = memo[card.state].count + 1;

            return memo;
        },
        [
            { name: translatedStates[State.New], count: 0 },
            { name: translatedStates[State.Learning], count: 0 },
            { name: translatedStates[State.Review], count: 0 },
            { name: translatedStates[State.Relearning], count: 0 },
        ],
    );
});
</script>

<template>
    <article class="studip" v-if="cards.length">
        <header>
            <h1>{{ $gettext('Lernstatus') }}</h1>
        </header>
        <section>
            <table>
                <tbody>
                    <tr v-for="(bucket, index) in cardsByState.reverse()" :key="index">
                        <td>{{ bucket.name }}</td>
                        <td class="tw-w-32">
                            <BarChart :percent="Math.floor((100 * bucket.count) / cards.length)" />
                        </td>
                        <td>
                            <StudipIcon
                                shape="dialog-cards"
                                role="info"
                                class="tw-align-baseline"
                            />
                            {{ bucket.count }}
                        </td>
                        <td class="tw-text-right">
                            {{ Math.floor((100 * bucket.count) / cards.length) }}%
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>
    </article>
</template>

<style scoped>
td + td {
    padding-block: 0.25rem;
    padding-inline-start: 1rem;
}
</style>
