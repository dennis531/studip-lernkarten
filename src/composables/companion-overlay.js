import { ref } from 'vue';

const companionOverlayShow = ref(false);
const companionOverlayMessage = ref('');
const companionOverlayMood = ref('default');

export function useCompanionOverlay() {
    let timer = null;

    function hideCompanionOverlay() {
        companionOverlayMessage.value = '';
        companionOverlayShow.value = false;
    }

    function showCompanionOverlay(msg, { timeout = 4000, mood = 'default' } = {}) {
        companionOverlayMessage.value = msg;
        companionOverlayMood.value = mood;
        companionOverlayShow.value = true;
        if (timer) {
            clearTimeout(timer);
        }
        timer = setTimeout(hideCompanionOverlay, timeout);
    }

    return {
        message: companionOverlayMessage,
        mood: companionOverlayMood,
        show: companionOverlayShow,
        hideCompanionOverlay,
        showCompanionOverlay,
    };
}
