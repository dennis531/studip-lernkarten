<script setup>
import { computed, ref, watch } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import { useGettext } from 'vue3-gettext';
import FolderSelector from '../components/FolderSelector.vue';
import { useDecksStore } from '../stores/decks.js';
import { useFoldersStore } from '../stores/folders.js';
import { useCardsStore } from "../stores/cards.js";
import StudipMessageBox from "../components/base/StudipMessageBox.vue";

const { $gettext } = useGettext();
const router = useRouter();
const decksStore = useDecksStore();
const foldersStore = useFoldersStore();
const cardsStore = useCardsStore();

const props = defineProps(['folder']);

const folder = computed(() => foldersStore.byId(props.folder));
const errorMessage = computed(() => {
    if (!error.value) {
        return '';
    }

    return error?.value?.response?.data?.errors?.[0]?.detail ??
        $gettext('Beim Generieren der Karten ist ein Fehler aufgetreten.');
});

const showFileInput = ref(false);
const file = ref(null);
const number = ref(5);
const description = ref('');
const selectedFolder = ref(folder.value);
const metadata = ref('');
const name = ref('');
const nameRef = ref(null);
const fileRef = ref(null);
const isGenerating = ref(false);
const error = ref(null);

watch(
    () => foldersStore.isLoading,
    (isLoading) => {
        if (!isLoading) {
            selectedFolder.value = folder.value;
        }
    },
);

const onSelectFolder = (selected) => {
    selectedFolder.value = selected;
};

const loadFile = (event) => {
    if (event.target.files.length) {
        let reader = new FileReader();
        reader.onload = function (e) {
            // event.target.result contains base64 encoded image
            file.value = e.target.result;
        }
        reader.readAsDataURL(event.target.files[0]);
    }
};

const generateCards = (deck) => {
    isGenerating.value = true;

    cardsStore.generateCards(deck, file.value, number.value)
        .then(() => {
            isGenerating.value = false;
            router.push({ name: 'deck', params: { id: deck.id } });
        })
        .catch((err) => {
            // Remove deck if generation fails
            decksStore.deleteDeck(deck);
            isGenerating.value = false;
            error.value = err
        })
}

const onSubmit = () => {
    if (validateName() && validateFile()) {
        error.value = null
        decksStore
            .createDeck(selectedFolder.value, name.value, description.value, metadata.value)
            .then((deck) => {
                if (showFileInput && file.value) {
                    generateCards(deck);
                } else {
                    router.push({ name: 'deck', params: { id: deck.id } });
                }
            })
    }
};

function validateName() {
    if (name.value.trim().length === 0) {
        nameRef.value.setCustomValidity(
            $gettext('Die Bezeichnung des Kartensatzes darf nicht leer sein.'),
        );
        return false;
    } else {
        nameRef.value.setCustomValidity('');
        return true;
    }
}

function validateFile() {
    if (!showFileInput.value) {
        return true;
    }

    if (!file.value) {
        fileRef.value.setCustomValidity(
            $gettext('Bitte wählen Sie eine Datei aus.'),
        );
        return false;
    } else {
        fileRef.value.setCustomValidity('');
        return true;
    }
}
</script>

<template>
    <article class="studip">
        <header>
            <h1>{{ $gettext('Kartensatz anlegen') }}</h1>
        </header>

        <!-- loading indicator -->
        <div v-show="isGenerating" class="lernkarten-loading-indicator" />

        <form v-show="!isGenerating" class="default studipform" @submit.prevent="onSubmit">
            <StudipMessageBox v-if="errorMessage" type="error">
                {{ errorMessage }}
            </StudipMessageBox>

            <div class="formpart">
                <label class="studiprequired">
                    <input
                        type="checkbox"
                        v-model="showFileInput"
                    />
                    <span class="textlabel">
                        {{ $gettext('Aus Datei generieren') }}
                    </span>
                </label>
            </div>

            <div v-if="showFileInput">
                <div class="formpart">
                    <label class="studiprequired file-upload">
                        <span class="textlabel">
                            {{ $gettext('Datei auswählen') }}
                        </span>
                        <span
                            class="asterisk"
                            :title="$gettext('Dies ist ein Pflichtfeld')"
                            aria-hidden="true"
                        >*</span
                        >
                        <input
                            type="file"
                            accept="application/pdf"
                            @change="loadFile"
                            ref="fileRef"
                            required
                            aria-required="true"
                        />
                    </label>
                </div>

                <div class="formpart">
                    <label class="studiprequired">
                        <span class="textlabel">
                            {{ $gettext('Kartenanzahl') }}
                        </span>
                        <span
                            class="asterisk"
                            :title="$gettext('Dies ist ein Pflichtfeld')"
                            aria-hidden="true"
                        >*</span
                        >
                        <input
                            type="number"
                            v-model="number"
                            min="1"
                            max="100"
                            step="1"
                            required
                            aria-required="true"
                        />
                    </label>
                </div>
            </div>

            <div class="formpart">
                <label class="studiprequired">
                    <span class="textlabel">
                        {{ $gettext('Ordner') }}
                    </span>
                    <span
                        class="asterisk"
                        :title="$gettext('Dies ist ein Pflichtfeld')"
                        aria-hidden="true"
                        >*</span
                    >
                    <FolderSelector :folder="selectedFolder" @select="onSelectFolder" />
                </label>
            </div>

            <div class="formpart">
                <label class="studiprequired">
                    <span class="textlabel">
                        {{ $gettext('Bezeichnung des Kartensatzes') }}
                    </span>
                    <span
                        class="asterisk"
                        :title="$gettext('Dies ist ein Pflichtfeld')"
                        aria-hidden="true"
                        >*</span
                    >
                    <input type="text" v-model="name" ref="nameRef" required aria-required="true" />
                </label>
            </div>

            <div class="formpart">
                <label class="studiprequired">
                    <span class="textlabel">
                        {{ $gettext('Beschreibung des Kartensatzes') }}
                    </span>
                    <span
                        class="asterisk"
                        :title="$gettext('Dies ist ein Pflichtfeld')"
                        aria-hidden="true"
                        >*</span
                    >
                    <textarea v-model="description" required aria-required="true" />
                </label>
            </div>

            <div class="formpart">
                <label>
                    <span class="textlabel">
                        {{ $gettext('Metadaten des Kartensatzes') }}
                    </span>
                    <textarea v-model="metadata" />
                </label>
            </div>

            <footer class="tw-mt-8">
                <button type="submit" class="button">{{ $gettext('Kartensatz anlegen') }}</button>
                <RouterLink to="/" class="button cancel">
                    {{ $gettext('Abbrechen') }}
                </RouterLink>
            </footer>
        </form>
    </article>
</template>

<style>
.lernkarten-loading-indicator {
    width: 100%;
    background-image: url("../../../../../assets/images/loading-indicator.svg");
    background-repeat: no-repeat;
    background-position: center;
    background-size: 32px;
    height: 150px;
}
</style>
