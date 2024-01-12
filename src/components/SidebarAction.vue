<script setup>
import { onBeforeMount, onBeforeUnmount, ref } from 'vue';
import { useSidebar } from '../composables/sidebar.js';

const props = defineProps({
    icon: { type: String },
    text: { type: String },
});
const emit = defineEmits(['click']);

const action = ref(null);

const { addAction, removeAction } = useSidebar();

onBeforeMount(() => {
    action.value = addAction(props.icon, props.text, () => emit('click'));
});

onBeforeUnmount(() => {
    if (action.value) {
        removeAction(action.value);
    }
});
</script>
