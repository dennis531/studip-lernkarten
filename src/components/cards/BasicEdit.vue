<script setup>
import { ref, watch } from 'vue';
import IconButton from '../IconButton.vue';
import CardImage from '../CardImage.vue';
import FileDropzone from '../FileDropzone.vue';
import StudipWysiwyg from '../base/StudipWysiwyg.vue';
import { useCardsStore } from '../../stores/cards.js';

const cardsStore = useCardsStore();

const props = defineProps(['card', 'mode']);
const emit = defineEmits(['update:mode']);

const isStoring = ref(false);
const front = ref(props.card.fields.front);
const back = ref(props.card.fields.back);
const images = ref(props.card.fields.images);
const model = ref(props.card.model);

const reset = () => {
    isStoring.value = false;
    front.value = props.card.fields.front;
    back.value = props.card.fields.back;
    images.value = props.card.fields.images;
    model.value = props.card.mode;
};

const onStore = () => {
    if (isStoring.value) {
        return;
    }
    isStoring.value = true;
    cardsStore
        .updateFields(props.card, { front: front.value, back: back.value, images: images.value })
        .then(onCancel)
        .finally(() => (isStoring.value = false));
};
const onCancel = () => emit('update:mode', 'show');

const setImage = (base64, fileid) => {
    images.value.values[fileid] = base64;
};

watch(
    () => props.card,
    () => {
        reset();
    },
);
</script>
<template>
    <section>
        <article>
            <form class="default studipform" @submit.prevent="">
                <div class="formpart">
                    <label class="studiprequired" for="front">
                        <span class="textlabel">
                            {{ $gettext('Vorderseite') }}
                        </span>
                        <span
                            class="asterisk"
                            :title="$gettext('Dies ist ein Pflichtfeld')"
                            aria-hidden="true"
                            >*</span
                        >
                    </label>

                    <div v-if="model == 'image'">
                        <CardImage
                            v-if="images['front']"
                            :edit="true"
                            :image="images['front']"
                            fileid="front"
                            @update:files="setImage"
                        />
                        <FileDropzone v-else @update:files="setImage" fileid="front" />
                    </div>

                    <div class="tw-max-w-xl">
                        <StudipWysiwyg v-model="front"></StudipWysiwyg>
                    </div>
                </div>

                <div class="formpart">
                    <label class="studiprequired" for="back">
                        <span class="textlabel">
                            {{ $gettext('Rückseite') }}
                        </span>
                        <span
                            class="asterisk"
                            :title="$gettext('Dies ist ein Pflichtfeld')"
                            aria-hidden="true"
                            >*</span
                        >
                    </label>

                    <div v-if="model == 'image'">
                        <CardImage
                            v-if="images['back']"
                            :edit="true"
                            :image="images['back']"
                            fileid="back"
                            @update:files="setImage"
                        />
                        <FileDropzone v-else @update:files="setImage" fileid="back" />
                    </div>

                    <div class="tw-max-w-xl">
                        <StudipWysiwyg v-model="back"></StudipWysiwyg>
                    </div>
                </div>
            </form>
        </article>
        <footer>
            <IconButton @click="onStore" icon="accept" type="button">
                {{ $gettext('Speichern') }}
            </IconButton>
            <IconButton @click="onCancel" icon="decline" type="button">
                {{ $gettext('Abbrechen') }}
            </IconButton>
        </footer>
    </section>
</template>
