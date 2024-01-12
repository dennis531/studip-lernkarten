<script setup>
import { computed, ref } from 'vue';
import StudipDialog from './base/StudipDialog.vue';
import StudipMessageBox from './base/StudipMessageBox.vue';

defineProps(['open']);
const emit = defineEmits(['update:open', 'confirm']);

const invalid = ref(false);
const name = ref('');

const validName = computed(() => name.value.trim().length > 0);

const reset = () => {
    invalid.value = false;
    name.value = '';
};
const setIsOpen = (value) => {
    emit('update:open', value);
    reset();
};
const onConfirm = () => {
    if (validName.value) {
        emit('confirm', name.value);
        reset();
    } else {
        invalid.value = true;
    }
};
</script>

<template>
    <StudipDialog
        :open="open"
        @update:open="setIsOpen"
        @confirm="onConfirm"
        :title="$gettext('Ordner erstellen')"
        :confirm-text="$gettext('Erstellen')"
        :close-text="$gettext('Abbrechen')"
    >
        <template #dialogContent>
            <form class="default studipform" @submit.prevent="onConfirm">
                <StudipMessageBox v-if="invalid">{{
                    $gettext('Der Ordnername darf nicht leer sein.')
                }}</StudipMessageBox>

                <div class="formpart">
                    <label class="studiprequired">
                        <span class="textlabel">{{ $gettext('Ordnername') }}</span>
                        <span
                            class="asterisk"
                            :title="$gettext('Dies ist ein Pflichtfeld')"
                            aria-hidden="true"
                            >*</span
                        >
                        <input type="text" v-model="name" />
                    </label>
                </div>
            </form>
        </template>
    </StudipDialog>
</template>
