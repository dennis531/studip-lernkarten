import { State, Rating, Card, FSRS } from 'fsrs.js';
import { useGettext } from 'vue3-gettext';

export function useFsrs() {
    const fromFlashcard = (flashCard) => {
        return {
            due: new Date(flashCard.due),
            stability: flashCard.stability,
            difficulty: flashCard.difficulty,
            elapsed_days: flashCard['elapsed-days'],
            scheduled_days: flashCard['scheduled-days'],
            reps: flashCard.reps,
            lapses: flashCard.lapses,
            state: flashCard.state,
            last_review: new Date(flashCard['last-review']),
        };
    };

    const toFlashcard = (card) => {
        return {
            due: card.due,
            stability: card.stability,
            difficulty: card.difficulty,
            'elapsed-days': Math.floor(card.elapsed_days),
            'scheduled-days': card.scheduled_days,
            reps: card.reps,
            lapses: card.lapses,
            state: card.state,
            'last-review': card.last_review,
        };
    };

    const createCard = (flashCard) => {
        return Object.assign(new Card(), fromFlashcard(flashCard));
    };

    const fsrs = new FSRS();

    const repeat = (flashCard, date = new Date()) => {
        const card = createCard(flashCard);

        return fsrs.repeat(card, date);
    };

    const repeatWithRating = (flashCard, rating) => {
        const card = createCard(flashCard);
        const schedulingInfos = fsrs.repeat(card, new Date());

        return toFlashcard(schedulingInfos[rating].card);
    };

    const { $gettext } = useGettext();

    const translatedStates = {
        [State.New]: $gettext('Neue Karte'),
        [State.Learning]: $gettext('Zu lernende Karte'),
        [State.Review]: $gettext('Gelernte Karte'),
        [State.Relearning]: $gettext('Erneut zu lernende Karte'),
    };

    return {
        State,
        Rating,
        createCard,
        repeat,
        repeatWithRating,
        translatedStates,
    };
}
