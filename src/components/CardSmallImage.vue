<script setup>
const props = defineProps(['image']);
</script>
<template>
    <div>
        <img :src="props.image" />
    </div>
</template>

<style scoped>
img {
    height: 2em;
}
</style>
