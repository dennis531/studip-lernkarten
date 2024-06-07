<script setup>
import Papa from 'papaparse';
import { computed, ref } from 'vue';
import StudipCompanion from './base/StudipCompanion.vue';
import DialogImportCards from './DialogImportCards.vue';
import IconAnchor from './IconAnchor.vue';
import IconButton from './IconButton.vue';
import { useCardsStore } from '../stores/cards.js';

const cardsStore = useCardsStore();

const props = defineProps(['deck']);

const showImportDialog = ref(false);
const countImported = ref(null);

const cards = computed(() => cardsStore.byDeck(props.deck));

const mostRecentCard = computed(() =>
    cards.value.reduce((memo, card) => {
        return !memo || new Date(card.chdate) > new Date(memo.chdate) ? card : memo;
    }, null),
);

const chdate = computed(() => {
    if (!mostRecentCard.value) {
        return props.deck.chdate;
    }

    return new Date(mostRecentCard.value.chdate) > new Date(props.deck.chdate)
        ? mostRecentCard.value.chdate
        : props.deck.chdate;
});

const downloadName = computed(() =>
    `kartensatz-${props.deck.name}-${chdate.value}.pdf`.replace(/[/|\\:*?"<>]/g, ''),
);

const exportPdfUrl = computed(() =>
    window.STUDIP.URLHelper.getURL(
        `plugins.php/lernkartenplugin/api/pdf/${props.deck.id}`,
        {},
        true,
    ),
);

const onImport = () => {
    showImportDialog.value = true;
};

const onImportSuccessful = (records) => {
    countImported.value = records;
};

const onExportCsv = () => {
    const data = cards.value
        .map((card) => {
            switch (card.model) {
                case 'basic':
                    return [card.fields.front, card.fields.back];
                default:
                    console.error('Could not export card', card);
                    return null;
            }
        })
        .filter(Boolean);

    const csv = Papa.unparse(data);

    const filename = `kartensatz-${props.deck.name}-${chdate.value}.csv`.replace(
        /[/|\\:*?"<>]/g,
        '',
    );

    download(filename, csv);
};

function download(filename, data) {
    const blob = new Blob([data], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.setAttribute('href', url);
    a.setAttribute('download', filename);
    a.click();
}
</script>

<template>
    <article class="studip">
        <header>
            <h1>{{ $gettext('Importieren') }}</h1>
        </header>
        <section>
            <StudipCompanion
                v-if="countImported !== null"
                :msg-companion="
                    $gettext('Es wurden erfolgreich %{ count } Karten importiert', {
                        count: countImported,
                    })
                "
            />
            <p>
                {{ $gettext('Importiere Karten aus einer CSV-Datei.') }}
            </p>
            <IconButton icon="import" type="button" @click="onImport">
                {{ $gettext('Importieren') }}
            </IconButton>
        </section>
    </article>
    <article class="studip">
        <header>
            <h1>{{ $gettext('Exportieren') }}</h1>
        </header>
        <section>
            <p>
                {{
                    $gettext(
                        'Exportiere Karten in eine CSV-Datei (Microsoft Excel, LibreOffice Calc, …)',
                    )
                }}
            </p>
            <IconButton icon="export" type="button" @click="onExportCsv">
                {{ $gettext('CSV exportieren') }}
            </IconButton>
            <p class="tw-mt-8">
                {{ $gettext('Exportiere Karten in eine PDF-Datei') }}
            </p>

            <IconAnchor :href="exportPdfUrl" :download="downloadName" target="_blank" icon="export">
                {{ $gettext('PDF exportieren') }}
            </IconAnchor>
        </section>
    </article>
    <DialogImportCards v-model:open="showImportDialog" :deck="deck" @success="onImportSuccessful" />
</template>
