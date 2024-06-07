<script setup>
defineProps({
    avatarUrl: {
        type: String,
        required: true,
    },
    formattedName: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <div class="tw-items-center tw-flex tw-gap-1">
        <span>
            <img :title="formattedName" alt="" class="tw-align-middle tw-h-4" :src="avatarUrl" />
        </span>
        <span>{{ formattedName }}</span>
    </div>
</template>
