<script setup>
import { computed, ref, watch } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import { useGettext } from 'vue3-gettext';
import FolderSelector from '../components/FolderSelector.vue';
import { useDecksStore } from '../stores/decks.js';
import { useFoldersStore } from '../stores/folders.js';
import { useCardsStore } from "../stores/cards.js";
import StudipMessageBox from "../components/base/StudipMessageBox.vue";
import { getDocument, GlobalWorkerOptions } from "pdfjs-dist";
import pdfJSWorkerURL from "pdfjs-dist/build/pdf.worker?url";
import { useContextStore } from "@/stores/context.js";
import StudipTooltipIcon from "@/components/base/StudipTooltipIcon.vue";

GlobalWorkerOptions.workerSrc = pdfJSWorkerURL;

const { $gettext } = useGettext();
const router = useRouter();
const decksStore = useDecksStore();
const foldersStore = useFoldersStore();
const cardsStore = useCardsStore();
const contextStore = useContextStore();

const props = defineProps(['folder']);

const folder = computed(() => foldersStore.byId(props.folder));
const wordCount = computed(() => fileText.value.split(/\s+/).length);

const showFileInput = ref(false);
const file = ref(null);
const fileText = ref('');
const wordLimit = ref(contextStore.wordLimit);
const number = ref(5);
const description = ref('');
const selectedFolder = ref(folder.value);
const metadata = ref('');
const name = ref('');
const nameRef = ref(null);
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
            loadPdfText();
        }
        reader.readAsArrayBuffer(event.target.files[0]);
    }
};

const loadPdfText = async () => {
    if (file.value) {
        const pdf = await getDocument(file.value).promise;

        let text = '';
        for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
            const page = await pdf.getPage(pageNum);
            const textContent = await page.getTextContent()

            textContent.items.forEach(item => {
                text += item.str + ' ';
            });
        }

        fileText.value = text;
    }
};

const generateCards = (deck) => {
    isGenerating.value = true;

    cardsStore.generateCards(deck, fileText.value, number.value)
        .then(() => {
            isGenerating.value = false;
            router.push({ name: 'deck', params: { id: deck.id } });
        })
        .catch((err) => {
            // Remove deck if generation fails
            decksStore.deleteDeck(deck);
            isGenerating.value = false;
            error.value = $gettext('Beim Generieren der Karten ist ein Fehler aufgetreten.');
        })
}

const onSubmit = () => {
    if (validateName() && validateFile()) {
        error.value = null
        decksStore
            .createDeck(selectedFolder.value, name.value, description.value, metadata.value)
            .then((deck) => {
                if (showFileInput.value && file.value && fileText.value) {
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

    // Check file input
    if (!file.value) {
        error.value = $gettext('Bitte wählen Sie eine Datei aus.');
        return false;
    }

    if (!fileText.value) {
        error.value = $gettext('Die Datei enthält keinen Text oder konnte nicht gelesen werden.');
        return false;
    }

    // Check word limit
    if (wordCount.value > wordLimit.value) {
        error.value = $gettext(
            'Die Datei überschreitet mit %{ word_count } Wörtern das Limit von %{ word_limit } Wörtern.',
            { word_count: wordCount.value, word_limit: wordLimit.value }
        );
        return false;
    }

    return true;
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
            <StudipMessageBox
                v-if="error"
                @close="error = null"
                type="error"
                default-focus
            >
                {{ error }}
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
                    <StudipTooltipIcon
                        :text="$gettext('Sie können eine Datei angeben, um Lernkarten aus ihren Textinhalten zu generieren. Für die Generierung wird ein Large Language Model verwendet, sodass Halluzinationen in den Ergebnissen enthalten sein können.')"/>
                </label>
            </div>

            <div v-if="showFileInput">
                <div class="formpart">
                    <label class="studiprequired file-upload">
                        <span class="textlabel">
                            {{ $gettext('Datei auswählen (max. %{ word_limit } Wörter)', { word_limit: wordLimit }) }}
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
