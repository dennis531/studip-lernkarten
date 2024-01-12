<script setup>
import { computed, ref, watch } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import { useGettext } from 'vue3-gettext';
import FolderSelector from '../components/FolderSelector.vue';
import { useDecksStore } from '../stores/decks.js';
import { useFoldersStore } from '../stores/folders.js';

const { $gettext } = useGettext();
const router = useRouter();
const decksStore = useDecksStore();
const foldersStore = useFoldersStore();

const props = defineProps(['folder']);

const folder = computed(() => foldersStore.byId(props.folder));

const description = ref('');
const selectedFolder = ref(folder.value);
const metadata = ref('');
const name = ref('');
const nameRef = ref(null);

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

const onSubmit = () => {
    if (validateName()) {
        decksStore
            .createDeck(selectedFolder.value, name.value, description.value, metadata.value)
            .then(({ id }) => router.push({ name: 'deck', params: { id } }));
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
</script>

<template>
    <article class="studip">
        <header>
            <h1>{{ $gettext('Kartensatz anlegen') }}</h1>
        </header>

        <form class="default studipform" @submit.prevent="onSubmit">
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
