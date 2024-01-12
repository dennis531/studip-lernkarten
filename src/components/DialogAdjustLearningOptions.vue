<script setup>
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import StudipDialog from './base/StudipDialog.vue';
import { useSchedulerOptions } from '../composables/scheduler.js';

const router = useRouter();
const schedulerOptions = useSchedulerOptions();

const props = defineProps(['open', 'decks']);
const emit = defineEmits(['update:open']);

const initialFocus = ref(null);
const selectedOrder = ref(schedulerOptions.defaultOrder.value);

const selectableOrders = computed(() => {
    return schedulerOptions.orders.value;
});

const reset = () => {
    selectedOrder.value = schedulerOptions.defaultOrder.value;
};

const setIsOpen = (value) => {
    emit('update:open', value);
    reset();
};

const onConfirm = () => {
    const decks = props.decks.map(({ id }) => id).join(',');
    const order = selectedOrder.value;
    router.push({ name: 'study', query: { decks, order } });
    setIsOpen(false);
};
</script>

<template>
    <StudipDialog
        :confirm-text="$gettext('Lernen')"
        :close-text="$gettext('Schließen')"
        :height="600"
        :initial-focus="initialFocus"
        :open="open"
        :title="$gettext('Lernoptionen')"
        :width="600"
        @confirm="onConfirm"
        @update:open="setIsOpen"
    >
        <template #dialogContent>
            <form class="default studipform" @submit.prevent="onConfirm">
                <div class="formpart">
                    <label class="studiprequired">
                        <span class="textlabel">
                            {{ $gettext('Kartensatz') }}
                        </span>
                        <span
                            class="asterisk"
                            :title="$gettext('Dies ist ein Pflichtfeld')"
                            aria-hidden="true"
                            >*</span
                        >
                        <ul>
                            <li v-for="deck in decks" :key="deck.id">
                                {{ deck.name }}
                            </li>
                        </ul>
                    </label>
                </div>
                <div class="formpart">
                    <label class="studiprequired">
                        <span class="textlabel">
                            {{ $gettext('Lernreihenfolge') }}
                        </span>
                        <span
                            class="asterisk"
                            :title="$gettext('Dies ist ein Pflichtfeld')"
                            aria-hidden="true"
                            >*</span
                        >
                        <select v-model="selectedOrder" ref="initialFocus">
                            <option
                                v-for="[value, { text }] in selectableOrders"
                                :key="value"
                                :value="value"
                            >
                                {{ text }}
                            </option>
                        </select>
                    </label>
                </div>
            </form>
        </template>
    </StudipDialog>
</template>
