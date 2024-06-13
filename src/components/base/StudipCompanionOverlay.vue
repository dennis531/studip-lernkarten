<script setup>
import { useCompanionOverlay } from '../../composables/companion-overlay.js';

const { show, message, mood, hideCompanionOverlay } = useCompanionOverlay();
</script>

<template>
    <div class="cw-companion-overlay-wrapper">
        <div
            class="cw-companion-overlay"
            :class="[
                show ? 'cw-companion-overlay-in' : '',
                show ? '' : 'cw-companion-overlay-out',
                mood,
            ]"
        >
            <div class="cw-companion-overlay-content" v-html="message"></div>
            <button class="cw-compantion-overlay-close" @click="hideCompanionOverlay">
                <span class="sr-only">{{ $gettext('Schließen') }}</span>
            </button>
        </div>
        <div class="sr-only" aria-live="polite" role="log">
            <p>{{ message }}</p>
        </div>
    </div>
</template>
