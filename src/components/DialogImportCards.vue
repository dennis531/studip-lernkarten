<script setup>
import Papa from 'papaparse';
import { computed, ref } from 'vue';
import StudipCompanion from './base/StudipCompanion.vue';
import StudipDialog from './base/StudipDialog.vue';
import { useCardsStore } from '../stores/cards.js';

const cardsStore = useCardsStore();

const props = defineProps(['open', 'deck']);
const emit = defineEmits(['update:open', 'success']);

const importFile = ref(null);
const imported = ref(false);
const records = ref([]);
const errorsFound = ref([]);

const confirmDisabled = computed(() => {
    return !(records.value.length > 0 && errorsFound.value.length === 0);
});

const reset = () => {
    imported.value = false;
    records.value = [];
    errorsFound.value = [];
};
const setIsOpen = (value) => {
    emit('update:open', value);
    reset();
};
const onConfirm = () => {
    if (!errorsFound.value.length) {
        cardsStore.importCards(props.deck, records.value).then(() => {
            setIsOpen(false);
            emit('success', records.value);
            reset();
        });
    }
};
const setImport = () => {
    reset();
    Papa.parse(importFile.value.files[0], {
        header: false,
        skipEmptyLines: true,
        step({ data, errors }) {
            if (errors.length) {
                console.error('Could not parse CSV file.', { errors, data });
                errorsFound.value.push([errors, data]);
            } else {
                // TODO: nachschauen, dass wir wirklich genau zwei Spalten bekommen
                const [front, back] = data;
                records.value.push({ model: 'basic', fields: { front, back } });
            }
        },
        complete() {
            imported.value = true;
        },
    });
};
</script>

<template>
    <StudipDialog
        :open="open"
        @update:open="setIsOpen"
        @confirm="onConfirm"
        :height="600"
        :title="$gettext('Karten importieren')"
        :confirm-disabled="confirmDisabled"
        :confirm-text="$gettext('Importieren')"
        :close-text="$gettext('Abbrechen')"
    >
        <template #dialogContent>
            <form class="default studipform" @submit.prevent="onConfirm">
                <div class="formpart">
                    <label class="studiprequired">
                        <span class="textlabel">{{ $gettext('CSV-Datei') }}</span>
                        <span
                            class="asterisk"
                            :title="$gettext('Dies ist ein Pflichtfeld')"
                            aria-hidden="true"
                            >*</span
                        >
                        <input
                            class="cw-file-input"
                            ref="importFile"
                            type="file"
                            accept="text/csv"
                            @change="setImport"
                        />
                    </label>
                </div>
                <div class="formpart" v-if="imported">
                    <StudipCompanion
                        v-if="errorsFound.length === 0"
                        :msg-companion="
                            $gettext('Diese Datei enthält %{ count } importierbare Karten.', {
                                count: records.length,
                            })
                        "
                        mood="curious"
                    />
                    <StudipCompanion
                        v-else
                        :msg-companion="
                            $gettext('Diese Datei enthält Fehler und kann nicht importiert werden.')
                        "
                        mood="sad"
                    />
                </div>
            </form>
        </template>
    </StudipDialog>
</template>
