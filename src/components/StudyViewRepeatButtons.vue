<script setup>
import IconButton from '../components/IconButton.vue';
import { useFsrs } from '../composables/fsrs.js';

defineEmits(['repeat']);

const { Rating } = useFsrs();
</script>

<template>
    <div>
        <IconButton icon="accept" @click="$emit('repeat', Rating.Easy)">
            {{ $gettext('Genau gewusst') }}
        </IconButton>
        <IconButton icon="accept" @click="$emit('repeat', Rating.Good)">
            {{ $gettext('Gewusst') }}
        </IconButton>
        <IconButton icon="accept" @click="$emit('repeat', Rating.Hard)">
            {{ $gettext('Teilweise gewusst') }}
        </IconButton>
        <IconButton icon="decline" @click="$emit('repeat', Rating.Again)">
            {{ $gettext('Nochmal') }}
        </IconButton>
    </div>
</template>
