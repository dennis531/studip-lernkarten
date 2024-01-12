<script setup>
import { computed } from 'vue';
import IconButton from './IconButton.vue';
import RadialProgress from './RadialProgress.vue';

const props = defineProps(['cards', 'cardsLeft', 'standalone', 'ratings']);
const emit = defineEmits(['again', 'cancel', 'continue']);

const progress = computed(() => {
    const total = props.cards.length;
    const learned = props.cards.reduce((count, { state }) => {
        return count + (state === 2 ? 1 : 0);
    }, 0);

    return total ? learned / total : 0;
});

const totalLearned = computed(() => {
    return [...props.ratings.values()].reduce((sum, item) => sum + item, 0);
});
</script>

<template>
    <article class="studip">
        <header>
            <h1>{{ $gettext('Lernstatistik') }}</h1>
        </header>

        <section class="tw-flex tw-gap-4">
            <div>
                <RadialProgress :progress="progress" />
                <span class="tw-font-bold">{{ $gettext('Lernfortschritt') }}</span>
            </div>
            <div class="tw-flex-grow">
                <div>{{ $gettext('%{ count } Karten abgefragt', { count: totalLearned }) }}</div>
                <div>
                    {{
                        $gettext('%{ count } Karten genau gewusst', {
                            count: ratings.get(4),
                        })
                    }}
                </div>
                <div>
                    {{ $gettext('%{ count } Karten gewusst', { count: ratings.get(3) }) }}
                </div>
                <div>
                    {{
                        $gettext('%{ count } Karten teilweise gewusst', {
                            count: ratings.get(2),
                        })
                    }}
                </div>
                <div>
                    {{
                        $gettext('%{ count } Karten nicht gewusst', {
                            count: ratings.get(1),
                        })
                    }}
                </div>
            </div>
            <div class="tw-flex tw-flex-column">
                <IconButton v-if="cardsLeft" icon="play" type="button" @click="emit('continue')">
                    {{ $gettext('Weiter lernen (%{ count })', { count: cardsLeft }) }}
                </IconButton>
                <IconButton icon="refresh" type="button" @click="emit('again')">
                    {{ $gettext('Noch einmal') }}
                </IconButton>
            </div>
        </section>
        <footer v-if="!standalone">
            <IconButton icon="stop" type="button" @click="emit('cancel')">
                {{ $gettext('Zurück') }}
            </IconButton>
        </footer>
    </article>
</template>
