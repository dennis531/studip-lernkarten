<script setup>
import { ref, watch } from 'vue';
import { useGettext } from 'vue3-gettext';
import StudipDialog from './base/StudipDialog.vue';
import StudipWysiwyg from './base/StudipWysiwyg.vue';
import FileDropzone from './FileDropzone.vue';
import CardImage from './CardImage.vue';
import { useCardsStore } from '../stores/cards.js';

const cardsStore = useCardsStore();
const { $gettext } = useGettext();

const props = defineProps(['open', 'deck']);
const emit = defineEmits(['update:open']);

const cardType = ref('basic');
const cardTypes = ref([
    { text: $gettext('Einfach'), value: 'basic' },
    { text: $gettext('Bild und optionaler Text'), value: 'image' },
]);
const front = ref('');
const back = ref('');
const images = ref({});
const errors = ref({});

const setIsOpen = (value) => {
    emit('update:open', value);
};

const reset = () => {
    front.value = '';
    back.value = '';
    images.value = {};
    errors.value = {};
};

const createOne = () => {
    if (!validate()) {
        return;
    }
    const card = {
        model: cardType.value,
        fields: {
            front: window.STUDIP.wysiwyg.markAsHtml(front.value),
            back: window.STUDIP.wysiwyg.markAsHtml(back.value),
            images: images.value,
        },
    };
    cardsStore.createCard(props.deck, card).then(() => setIsOpen(false));
};
const createMore = () => {
    if (!validate()) {
        return;
    }
    const card = {
        model: cardType.value,
        fields: {
            front: window.STUDIP.wysiwyg.markAsHtml(front.value),
            back: window.STUDIP.wysiwyg.markAsHtml(back.value),
            images: images.value,
        },
    };
    cardsStore.createCard(props.deck, card).then(reset);
};

const setImage = (base64, fileid) => {
    images.value[fileid] = base64;
};

const validate = () => {
    errors.value = {};
    if (!front.value.trim().length) {
        errors.value.front = $gettext('Dieses Feld muss ausgefüllt werden.');
    }
    if (!back.value.trim().length) {
        errors.value.back = $gettext('Dieses Feld muss ausgefüllt werden.');
    }

    return Object.keys(errors.value).length === 0;
};

watch(
    () => props.open,
    (open) => {
        if (open) {
            reset();
        }
    }
);
</script>

<template>
    <StudipDialog
        :close-text="$gettext('Abbrechen')"
        :height="800"
        :open="open"
        :title="$gettext('Karten erstellen')"
        :width="600"
        @update:open="setIsOpen"
    >
        <template #dialogContent>
            <form class="default studipform" @submit.prevent="createOne">
                <div class="formpart">
                    <label class="studiprequired">
                        <span class="textlabel">
                            {{ $gettext('Kartentyp') }}
                        </span>
                        <span
                            class="asterisk"
                            :title="$gettext('Dies ist ein Pflichtfeld')"
                            aria-hidden="true"
                            >*</span
                        >
                        <select v-model="cardType">
                            <option
                                v-for="option in cardTypes"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.text }}
                            </option>
                        </select>
                    </label>
                </div>

                <div class="formpart">
                    <label class="studiprequired" for="card-text-front">
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

                    <div v-if="cardType == 'image'">
                        <CardImage
                            v-if="images['front']"
                            :edit="true"
                            :image="images['front']"
                            fileid="front"
                            @update:files="setImage"
                        />
                        <FileDropzone v-else @update:files="setImage" fileid="front" />
                    </div>

                    <StudipWysiwyg v-model="front"></StudipWysiwyg>
                    <div v-if="errors.front" class="dialog-add-card--error">{{ errors.front }}</div>
                </div>

                <div class="formpart">
                    <label class="studiprequired" for="card-text-back">
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

                    <div v-if="cardType == 'image'">
                        <CardImage
                            v-if="images['back']"
                            :edit="true"
                            :image="images['back']"
                            fileid="back"
                            @update:files="setImage"
                        />
                        <FileDropzone v-else @update:files="setImage" fileid="back" />
                    </div>

                    <StudipWysiwyg v-model="back"></StudipWysiwyg>
                    <div v-if="errors.back" class="dialog-add-card--error">{{ errors.back }}</div>
                </div>
            </form>
        </template>

        <template #dialogButtons>
            <button type="button" class="button add" @click="createOne">
                {{ $gettext('Erstellen') }}
            </button>
            <button type="button" class="button add" @click="createMore">
                {{ $gettext('Erstellen und danach noch eine') }}
            </button>
        </template>
    </StudipDialog>
</template>

<style scoped>
.dialog-add-card--error {
    background-color: var(--red-20);
    border: 1px solid var(--red-20);
    color: var(--black);
    font-size: 0.8em;
    padding: 0.4em 0.8em;
}
:deep(.ck-editor__editable) {
    max-height: 4rem !important;
}
</style>
