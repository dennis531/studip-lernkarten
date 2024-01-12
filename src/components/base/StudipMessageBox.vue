<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    type: {
        type: String, // exception, error, success, info, warning
        default: 'info',
        validator(type) {
            return ['exception', 'error', 'warning', 'success', 'info'].includes(type);
        },
    },
    details: {
        type: Array,
        default: () => [],
    },
    hideDetails: {
        type: Boolean,
        default: false,
    },
    hideClose: {
        type: Boolean,
        default: false,
    },
});

const closed = ref(false);
const closedDetails = ref(false);

const classNames = computed(() => {
    return `messagebox_${props.type} ${showDetails.value ? '' : 'details_hidden'}`;
});
const hasDetails = computed(() => {
    return false;
    // return !!slots.details || props.details.length > 0;
});
const showDetails = computed(() => {
    return hasDetails.value && !closedDetails.value;
});

const toggleDetails = () => {
    closedDetails.value = !closedDetails.value;
};
</script>

<template>
    <div class="messagebox" :class="classNames" v-if="!closed">
        <div class="messagebox_buttons">
            <a
                v-if="hideDetails"
                class="details"
                href=""
                :title="$gettext('Detailanzeige umschalten')"
                @click.prevent.stop="toggleDetails"
            >
                <span>{{ $gettext('Detailanzeige umschalten') }}</span>
            </a>
            <a
                v-if="!hideClose"
                class="close"
                href=""
                :title="$gettext('Nachrichtenbox schließen')"
                @click.prevent="closed = true"
            >
                <span>{{ $gettext('Nachrichtenbox schließen') }}</span>
            </a>
        </div>
        <slot></slot>
        <div v-if="showDetails" class="messagebox_details">
            <slot name="details">
                <ul>
                    <li v-for="(detail, index) in details" v-html="detail" :key="index"></li>
                </ul>
            </slot>
        </div>
    </div>
</template>
