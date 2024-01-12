<script setup>
import { computed, ref, nextTick, onMounted } from 'vue';
import { useGettext } from 'vue3-gettext';
import StudipIcon from './StudipIcon.vue';

const { $gettext } = useGettext();

const props = defineProps({
    disabledIds: {
        type: Array,
        default: () => [],
    },
    name: String,
    withDetail: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits('input');

const id = ref(_.uniqueId());
const searchTerm = ref('');
const count = ref(0);
const users = ref([]);
const searchInputField = ref(null);
const selectbox = ref(null);

onMounted(() =>
    nextTick(() => {
        init();
        setTimeout(() => {
            searchInputField.value.focus();
        }, 100);
    }),
);

const count_text_id = computed(() => `${id.value}_count`);

function init() {
    let select_all_btn = document.createElement('a');
    select_all_btn.setAttribute('id', `${id.value}-select-all`);
    select_all_btn.setAttribute('href', '#');
    select_all_btn.innerText = $gettext('Alle hinzufügen');
    select_all_btn.addEventListener('click', (e) => {
        e.preventDefault();
        selectAll();
    });
    let unselect_all_btn = document.createElement('a');
    unselect_all_btn.setAttribute('id', `${id.value}-unselect-all`);
    unselect_all_btn.setAttribute('href', '#');
    unselect_all_btn.innerText = $gettext('Alle entfernen');
    unselect_all_btn.addEventListener('click', (e) => {
        e.preventDefault();
        unselectAll();
    });
    let selection_header = document.createElement('div');
    selection_header.setAttribute('id', count_text_id.value);
    selection_header.innerText = $gettext('Sie haben %{ count } Personen ausgewählt', {
        count: count.value,
    });

    window.$(selectbox.value).multiSelect({
        selectableHeader: '<div>' + $gettext('Suchergebnisse') + '</div>',
        selectionHeader: selection_header,
        selectableFooter: select_all_btn,
        selectionFooter: unselect_all_btn,
        afterSelect: updateSelection,
        afterDeselect: updateSelection,
    });
}

function search() {
    users.value = [];

    window.$.getJSON(
        window.STUDIP.URLHelper.getURL(
            'dispatch.php/multipersonsearch/ajax_search_vue/' + props.name,
            {
                s: searchTerm.value,
            },
        ),
        function (data) {
            removeAllNotSelected();
            let searchcount = 0;
            data.forEach(function (item) {
                searchcount += append(
                    item.id,
                    item.avatar + ' -- ' + item.text,
                    item.selected || props.disabledIds.includes(item.id),
                );
                delete item.selected;
                users.value.push(item);
            });
            refresh();

            if (searchcount === 0) {
                append(
                    '--',
                    $gettext('Es wurden keine neuen Ergebnisse für "%{ needle }" gefunden.', {
                        needle: searchTerm.value,
                    }),
                    true,
                );
                refresh();
            }
        },
    );
}

function selectAll() {
    window.$(selectbox.value).multiSelect('select_all');
    updateSelection();
}

function unselectAll() {
    window.$(selectbox.value).multiSelect('deselect_all');
    updateSelection();
}

function removeAllNotSelected() {
    window.$('option:not(:selected)', selectbox.value).remove();
    refresh();
}

function resetSearch() {
    searchTerm.value = '';
    removeAllNotSelected();
}

function append(value, text, disabled = false) {
    if (window.$('option[value=' + value + ']', selectbox.value).length === 0) {
        window.$(selectbox.value).multiSelect('addOption', { value, text, disabled });
        return 1;
    }
    return 0;
}

function refresh() {
    window.$(selectbox.value).multiSelect('refresh');
    updateSelection();
}

function updateCount() {
    count.value = window.$('option:enabled:selected', selectbox.value).length;
    window.$('#' + count_text_id.value).text(
        $gettext('Sie haben %{ count } Personen ausgewählt', {
            count: count.value,
        }),
    );
}

async function updateSelection() {
    updateCount();
    let selected_options = window.$('option:enabled:selected', selectbox.value);
    let user_ids = [];
    if (selected_options.length) {
        for (const option of selected_options) {
            user_ids.push(option.value);
        }
    }
    let returnValue = [];
    if (props.withDetail && users.value.length) {
        for (const user_id of user_ids) {
            let existing_index = users.value.findIndex((user) => {
                return user.id === user_id;
            });
            if (existing_index !== -1) {
                returnValue.push(users.value[existing_index]);
            }
        }
    } else {
        returnValue = user_ids;
    }
    emit('input', returnValue);
}
</script>

<template>
    <div class="mpscontainer studip-msp-vue">
        <form method="post" class="default" @submit.prevent="search">
            <label class="with-action">
                <input
                    type="text"
                    ref="searchInputField"
                    v-model="searchTerm"
                    :placeholder="$gettext('Suchen')"
                    style="width: 260px"
                />
                <a
                    href="#"
                    class="msp-btn"
                    @click.prevent="search"
                    :title="$gettext('Suche starten')"
                >
                    <StudipIcon shape="search" role="clickable" />
                </a>
                <a
                    href="#"
                    class="msp-btn"
                    @click.prevent="resetSearch"
                    :title="$gettext('Suche zurücksetzen')"
                >
                    <StudipIcon shape="decline" role="clickable" />
                </a>
            </label>
            <select ref="selectbox" multiple="multiple" name="selectbox[]"></select>
        </form>
    </div>
</template>
