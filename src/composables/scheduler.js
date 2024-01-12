import { computed, ref } from 'vue';
import { useGettext } from 'vue3-gettext';
import { useFsrs } from './fsrs.js';
import { useCardsStore } from '../stores/cards.js';
import { useDecksStore } from '../stores/decks.js';

export function useSchedulerOptions() {
    const { $gettext } = useGettext();

    const sortBasic = (cards) =>
        _.sortBy(cards, [(card) => card.deck.data.id, (card) => new Date(card.mkdate)]);

    const sortRandom = (cards) => {
        return _.shuffle(cards);
    };

    const sortProgress = (cards) => {
        return _.sortBy(cards, [(card) => new Date(card.due)]);
    };

    const orders = ref(
        new Map([
            [
                'basic',
                {
                    text: $gettext('Feste Reihenfolge'),
                    sort: sortBasic,
                },
            ],
            [
                'random',
                {
                    text: $gettext('Zufällige Reihenfolge'),
                    sort: sortRandom,
                },
            ],
            [
                'progress',
                {
                    text: $gettext('Niedrigster Lernstand'),
                    sort: sortProgress,
                },
            ],
        ]),
    );
    const defaultOrder = ref('basic');

    return {
        defaultOrder,
        orders,
    };
}

export function useScheduler(options) {
    const { orders } = useSchedulerOptions();

    const { repeatWithRating, Rating, State } = useFsrs();
    const cardsStore = useCardsStore();
    const decksStore = useDecksStore();

    const errors = ref(null);
    const isLoading = ref(false);

    const order = ref(orders.value.get(options.order ?? 'basic'));
    const decks = ref(new Map());
    const cards = ref([]);
    const queuedCards = ref([]);
    const ratings = ref(
        new Map([
            [Rating.Again, 0],
            [Rating.Hard, 0],
            [Rating.Good, 0],
            [Rating.Easy, 0],
        ]),
    );

    const cardStates = computed(() => {
        return cards.value.reduce(
            (map, card) => {
                map.set(card.state, map.get(card.state) + 1);
                return map;
            },

            new Map([
                [State.New, 0],
                [State.Learning, 0],
                [State.Review, 0],
                [State.Relearning, 0],
            ]),
        );
    });

    const cardsLeft = computed(() => {
        return queuedCards.value.length;
    });

    const queuedCard = computed(() => {
        // return dueCards.value.length ? _.sample(dueCards.value) : null;
        return queuedCards.value.length ? queuedCards.value[0] : null;
    });

    const queueAllCards = () => {
        queuedCards.value = order.value.sort(cards.value);
    };

    const repeat = (rating) => {
        if (!(rating in Rating)) {
            throw new Error('Invalid rating.');
        }
        const card = queuedCard.value;

        const countAttr = `${Rating[rating].toLowerCase()}-count`;
        const stats = {
            ...repeatWithRating(card, rating),
            [countAttr]: card[countAttr] + 1,
        };

        cardsStore.updateLearningStats(card, stats);
        const [head, ...tail] = queuedCards.value;
        queuedCards.value = tail;
        ratings.value.set(rating, ratings.value.get(rating) + 1);

        return card;
    };

    const reset = () => {
        queueAllCards();
    };

    const ids = options.decks.split(',').filter((id) => +id);
    isLoading.value = true;
    Promise.all([
        ...ids.map((id) => decksStore.fetchById(id)),
        ...ids.map((id) => cardsStore.fetchByDeck({ id })),
    ]).then(() => {
        decks.value = new Map(ids.map((id) => [id, decksStore.byId(id)]));
        cards.value = _.flatMap(ids, (id) => cardsStore.byDeck({ id }));
        queueAllCards();
        isLoading.value = false;
    });

    return {
        cards,
        cardsLeft,
        cardStates,
        decks,
        errors,
        isLoading,
        order,
        queuedCard,
        ratings,
        repeat,
        reset,
    };
}
