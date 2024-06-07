<script setup>
import { computed, ref } from 'vue';
import { useGettext } from 'vue3-gettext';
import StudipDialog from './base/StudipDialog.vue';
import StudipMultiPersonSearch from './base/StudipMultiPersonSearch.vue';
import RangeTypeSelector from './RangeTypeSelector.vue';
import { useCompanionOverlay } from '../composables/companion-overlay.js';
import { useContextStore } from '../stores/context.js';
import { useCourseMembershipsStore } from '../stores/course-memberships.js';
import { useDecksStore } from '../stores/decks.js';
import { useInstancesStore } from '../stores/instances.js';
import { useSemestersStore } from '../stores/semesters.js';
import { useSharedDecksStore } from '../stores/shared-decks.js';

const { $gettext } = useGettext();
const contextStore = useContextStore();
const courseMembershipsStore = useCourseMembershipsStore();
const decksStore = useDecksStore();
const instancesStore = useInstancesStore();
const semestersStore = useSemestersStore();
const sharedDecksStore = useSharedDecksStore();
const { showCompanionOverlay } = useCompanionOverlay();

const props = defineProps(['open', 'deck']);
const emit = defineEmits(['update:open']);

const initialFocus = ref(null);
const isLoading = ref(true);
const selectedCourse = ref(null);
const selectedUsers = ref([]);
const typeOfSource = ref('');

const reset = () => {
    selectedCourse.value = null;
    selectedUsers.value = [];
    typeOfSource.value = null;
};

Promise.all([courseMembershipsStore.fetch(), instancesStore.fetch(), semestersStore.fetch()]).then(
    () => (isLoading.value = false),
);

const instanceRangeIds = computed(() => {
    return instancesStore.all.map(({ range }) => range.data.id);
});

const courses = computed(() => {
    return courseMembershipsStore.all
        .filter((membership) => ['tutor', 'dozent'].includes(membership.permission))
        .map(({ course }) => course.data)
        .filter((course) => instanceRangeIds.value.includes(course.id));
});

const idsOfType = (ary, type) => ary.filter((item) => item.type === type).map(({ id }) => id);

const sharedCourses = computed(() => idsOfType(props.deck?.['shared-with'].data ?? [], 'courses'));
const sharedUsers = computed(() => {
    return [...idsOfType(props.deck?.['shared-with'].data ?? [], 'users'), contextStore.userId];
});

const setIsOpen = (value) => {
    emit('update:open', value);
    reset();
};

const semesterOf = (course) => {
    const semesterId = course['start-semester'].data.id;

    return semestersStore.byId(semesterId);
};

const onSelectUsers = (users) => {
    selectedUsers.value = users.map?.(({ id }) => id) ?? [];
};

const confirmDisabled = computed(() => {
    return !(
        (typeOfSource.value === 'courses' && selectedCourse.value !== null) ||
        (typeOfSource.value === 'users' && selectedUsers.value.length)
    );
});

const onConfirm = () => {
    let promise;
    switch (typeOfSource.value) {
        case 'courses':
            promise = sharedDecksStore.shareDeckWithCourse(props.deck, selectedCourse.value);
            break;
        case 'users':
            promise = sharedDecksStore.shareDeckWithUserIds(props.deck, selectedUsers.value);
            break;
    }
    promise
        .then(() =>
            showCompanionOverlay($gettext('Kartensatz erfolgreich geteilt'), { mood: 'happy' }),
        )
        .then(() => decksStore.fetchById(props.deck.id));
    setIsOpen(false);
};
</script>

<template>
    <StudipDialog
        :close-text="$gettext('Schließen')"
        :confirm-disabled="confirmDisabled"
        :confirm-text="$gettext('Teilen')"
        :height="600"
        :initial-focus="initialFocus"
        :open="open"
        :title="$gettext('Kartensatz teilen: %{ name }', { name: props.deck?.name })"
        :width="600"
        @confirm="onConfirm"
        @update:open="setIsOpen"
    >
        <template #dialogContent>
            <form v-if="!isLoading" class="default studipform" @submit.prevent="onConfirm">
                <RangeTypeSelector v-model="typeOfSource" />

                <section v-if="typeOfSource === 'courses'">
                    <div class="formpart">
                        <label class="studiprequired">
                            <span class="textlabel">
                                {{ $gettext('Veranstaltung') }}
                            </span>
                            <span
                                class="asterisk"
                                :title="$gettext('Dies ist ein Pflichtfeld')"
                                aria-hidden="true"
                                >*</span
                            >
                            <span class="tw-block tw-opacity-70 tw-italic">
                                {{
                                    $gettext(
                                        'Das Lernkarten-Modul muss in den Veranstaltungen aktiv sein, um den Kartensatz mit diesen zu teilen.',
                                    )
                                }}
                            </span>
                            <select v-model="selectedCourse">
                                <option :value="null" disabled>
                                    {{ $gettext('Veranstaltung auswählen') }}
                                </option>

                                <option
                                    v-for="course in courses"
                                    :key="course.id"
                                    :value="course"
                                    :disabled="sharedCourses.includes(course.id)"
                                >
                                    {{ course.title }} ({{ semesterOf(course).title }})
                                    <span v-if="sharedCourses.includes(course.id)">
                                        {{ $gettext('(bereits geteilt)') }}
                                    </span>
                                </option>
                            </select>
                            <pre></pre>
                        </label>
                    </div>
                </section>
            </form>

            <section v-if="typeOfSource === 'users'">
                <StudipMultiPersonSearch
                    name="content-persons"
                    @input="onSelectUsers"
                    :disabled-ids="sharedUsers"
                />
            </section>

            <div v-if="isLoading">{{ $gettext('Laden…') }}</div>
        </template>
    </StudipDialog>
</template>
