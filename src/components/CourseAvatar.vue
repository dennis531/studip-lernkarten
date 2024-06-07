<script setup>
import { computed } from 'vue';

defineProps(['course']);
const avatarUrl = computed(
    () => `${window.STUDIP.ABSOLUTE_URI_STUDIP}pictures/course/nobody_medium.png`,
);
</script>

<template>
    <div class="tw-items-center tw-flex tw-gap-1">
        <img :title="course.title" alt="" class="tw-align-middle tw-h-4" :src="avatarUrl" />
        <span>{{ course.title }}</span>
    </div>
</template>
