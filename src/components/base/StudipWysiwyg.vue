<script setup>
import { inject, ref, watch } from 'vue';

const ClassicEditor = inject('ClassicEditor');

const toolbar = {
    items: [
        'bold',
        'italic',
        'underline',
        'strikethrough',
        'subscript',
        'superscript',
        'fontColor',
        'fontBackgroundColor',
        '|',
        'removeFormat',
        '|',
        'bulletedList',
        'numberedList',
        '|',
        'insertTable',
        'codeBlock',
        'math',
    ],
    shouldNotGroupWhenFull: false,
};

const props = defineProps(['modelValue']);
const emit = defineEmits(['update:modelValue']);

const currentText = ref(props.modelValue);
const editor = ref(ClassicEditor);
const editorConfig = ref({ toolbar });

const prefill = () => (currentText.value = props.modelValue);

const onInput = (value) => {
    currentText.value = value;
    emit('update:modelValue', value);
};

watch(
    () => props.modelValue,
    () => (currentText.value = props.modelValue)
);
</script>

<template>
    <ckeditor
        :editor="editor"
        :config="editorConfig"
        @ready="prefill"
        v-model="currentText"
        @input="onInput"
    ></ckeditor>
</template>
