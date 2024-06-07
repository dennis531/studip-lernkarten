<script setup>
import { computed } from 'vue';
import StudipIcon from './base/StudipIcon.vue';
import { useSidebar } from '../composables/sidebar.js';

const sidebar = useSidebar();
const actionList = computed(() => sidebar.actions.value ?? []);
</script>

<template>
    <div v-if="actionList.length" id="sidebar-actions" class="sidebar-widget">
        <div class="sidebar-widget-header">{{ $gettext('Aktionen') }}</div>
        <div class="sidebar-widget-content">
            <form method="post">
                <ul class="widget-list widget-links" :aria-label="$gettext('Aktionen')">
                    <li v-for="(action, index) in actionList" :key="index" class="!tw-pl-0">
                        <button type="button" @click="action.handler">
                            <StudipIcon
                                :shape="action.icon"
                                class="!tw-align-middle"
                                ariaRole="none"
                            />
                            {{ action.text }}
                        </button>
                    </li>
                </ul>
            </form>
        </div>
    </div>
</template>
