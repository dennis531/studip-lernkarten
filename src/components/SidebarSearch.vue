<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import StudipIcon from './base/StudipIcon.vue';

const router = useRouter();

const query = ref('');

const onSubmit = () => {
    router.push({ name: 'search', query: { q: query.value } });
};
</script>

<template>
    <div class="sidebar-widget">
        <div class="sidebar-widget-header">{{ $gettext('Suche') }}</div>
        <div class="sidebar-widget-content">
            <form method="get" class="sidebar-search" @submit.prevent="onSubmit">
                <ul class="needles">
                    <li>
                        <div class="input-group files-search">
                            <label for="lernkarten-sidebar-search--input">
                                <span class="sr-only">
                                    {{ $gettext('Suche nach Kartensätzen …') }}
                                </span>
                            </label>
                            <input
                                v-model="query"
                                type="text"
                                id="lernkarten-sidebar-search--input"
                                name="search"
                                :placeholder="$gettext('Suche nach Kartensätzen …')"
                            />
                            <button
                                type="submit"
                                class="submit-search"
                                :title="$gettext('Suche ausführen')"
                            >
                                <StudipIcon shape="search" :size="20" />
                            </button>
                        </div>
                    </li>
                </ul>
            </form>
        </div>
    </div>
</template>
