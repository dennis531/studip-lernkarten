<script setup>
import { Dialog, DialogTitle, DialogPanel } from '@headlessui/vue';
import { computed, nextTick, ref } from 'vue';
import { useGettext } from 'vue3-gettext';
import VueResizable from 'vue-resizable';

const { $gettext } = useGettext();
const dialogPadding = 3;

const props = defineProps({
    alert: String,
    closeClass: String,
    closeText: String,
    confirmClass: String,
    confirmDisabled: { type: Boolean, default: false },
    confirmShow: { type: Boolean, default: true },
    confirmText: String,
    height: { type: Number, default: 300 },
    initialFocus: Object,
    message: String,
    open: Boolean,
    question: String,
    title: String,
    width: { type: Number, default: 450 },
});

const emit = defineEmits(['confirm', 'update:open']);

const currentHeight = ref(300);
const currentWidth = ref(450);
const footerRef = ref(null);
const footerHeight = ref(68);
const headerRef = ref(null);
const left = ref(0);
const top = ref(0);

const buttonA = computed(() => {
    let button = false;
    if (props.message) {
        return false;
    }
    if (props.question || props.alert) {
        button = {};
        button.text = $gettext('Ja');
        button.class = 'accept';
    }
    if (props.confirmText && props.confirmShow) {
        button = {};
        button.text = props.confirmText;
        button.class = props.confirmClass;
        button.disabled = props.confirmDisabled;
    }

    return button;
});

const buttonB = computed(() => {
    let button = false;
    if (props.message) {
        button = {};
        button.text = $gettext('Ok');
        button.class = '';
    }
    if (props.question || props.alert) {
        button = {};
        button.text = $gettext('Nein');
        button.class = 'cancel';
    }
    if (props.closeText) {
        button = {};
        button.text = props.closeText;
        if (props.closeClass) {
            button.class = props.closeClass;
        } else {
            button.class = 'cancel';
        }
    }

    return button;
});

const dialogTitle = computed(() => {
    if (props.title) {
        return props.title;
    }
    if (props.alert || props.question) {
        return $gettext('Bitte bestätigen Sie die Aktion');
    }
    if (props.message) {
        return $gettext('Information');
    }
    return '';
});
const dialogWidth = computed(() => {
    return currentWidth.value ? currentWidth.value - dialogPadding * 4 + 'px' : 'unset';
});
const dialogHeight = computed(() => {
    return currentHeight.value
        ? currentHeight.value - headerHeight.value - dialogPadding * 4 + 'px'
        : 'unset';
});
const contentHeight = computed(() => {
    return currentHeight.value ? currentHeight.value - footerHeight.value + 'px' : 'unset';
});
const headerHeight = computed(() => {
    return headerRef.value?.offsetHeight ?? 0;
});

const initSize = () => {
    nextTick(() => {
        currentWidth.value = props.width;
        currentHeight.value = props.height;
        if (window.innerWidth > currentWidth.value) {
            left.value = (window.innerWidth - currentWidth.value) / 2;
        } else {
            left.value = 5;
            currentWidth.value = window.innerWidth - 16;
        }

        top.value = (window.innerHeight - currentHeight.value) / 2;
        footerHeight.value = footerRef.value.offsetHeight;
    });
};

const resizeHandler = (data) => {
    currentWidth.value = data.width;
    currentHeight.value = data.height;
    left.value = data.left;
    top.value = data.top;
};

const setIsOpen = (value) => emit('update:open', value);
const confirmDialog = () => emit('confirm');
</script>

<template>
    <Dialog :open="open" :initial-focus="initialFocus" class="studip-dialog">
        <div class="studip-dialog-backdrop" aria-hidden="true">
            <VueResizable
                class="resizable"
                style="position: absolute"
                dragSelector=".studip-dialog-header"
                :left="left"
                :top="top"
                :width="currentWidth"
                :height="currentHeight"
                :min-width="100"
                :min-height="100"
                @mount="initSize"
                @resize:move="resizeHandler"
                @resize:start="resizeHandler"
                @resize:end="resizeHandler"
                @drag:move="resizeHandler"
                @drag:start="resizeHandler"
                @drag:end="resizeHandler"
            >
                <DialogPanel
                    class="studip-dialog-body"
                    :style="{ height: dialogHeight, width: dialogWidth }"
                    :class="{ 'studip-dialog-warning': question, 'studip-dialog-alert': alert }"
                >
                    <DialogTitle as="header" class="studip-dialog-header" ref="headerRef">
                        <span
                            class="studip-dialog-title"
                            :title="dialogTitle"
                            role="heading"
                            aria-level="2"
                        >
                            {{ dialogTitle }}
                        </span>
                        <slot name="dialogHeader"></slot>
                        <button
                            :aria-label="$gettext('Diesen Dialog schließen')"
                            :title="$gettext('Schließen')"
                            class="studip-dialog-close-button"
                            @click.prevent="setIsOpen(false)"
                            type="button"
                        ></button>
                    </DialogTitle>

                    <section class="studip-dialog-content" :style="{ height: contentHeight }">
                        <slot name="dialogContent"></slot>
                        <div v-if="message">{{ message }}</div>
                        <div v-if="question">{{ question }}</div>
                        <div v-if="alert">{{ alert }}</div>
                    </section>
                    <footer class="studip-dialog-footer" ref="footerRef">
                        <div class="studip-dialog-footer-buttonset-left">
                            <slot name="dialogButtonsBefore"></slot>
                        </div>
                        <div class="studip-dialog-footer-buttonset-center">
                            <button
                                v-if="buttonA"
                                :title="buttonA.text"
                                :class="[buttonA.class]"
                                :disabled="buttonA.disabled"
                                class="button"
                                type="button"
                                @click="confirmDialog"
                            >
                                {{ buttonA.text }}
                            </button>
                            <slot name="dialogButtons"></slot>
                            <button
                                v-if="buttonB"
                                :title="buttonB.text"
                                :class="[buttonB.class]"
                                class="button"
                                type="button"
                                @click="setIsOpen(false)"
                            >
                                {{ buttonB.text }}
                            </button>
                        </div>
                        <div class="studip-dialog-footer-buttonset-right">
                            <slot name="dialogButtonsAfter"></slot>
                        </div>
                    </footer>
                </DialogPanel>
            </VueResizable>
        </div>
    </Dialog>
</template>

<style scoped>
.studip-dialog-content {
    flex-direction: column;
}
</style>
