<script setup>
import StudipIcon from './base/StudipIcon.vue';

defineEmits('update:modelValue');
</script>
<template>
    <fieldset class="radiobutton-set">
        <input
            @change="$emit('update:modelValue', 'courses')"
            id="cw-element-copy-source-courses"
            name="cw-element-copy-source"
            type="radio"
            value="courses"
            :aria-description="$gettext('Veranstaltungen')"
        />
        <label for="cw-element-copy-source-courses">
            <div class="icon">
                <StudipIcon shape="seminar" :height="32" :width="32" />
            </div>
            <div class="text">{{ $gettext('Veranstaltung') }}</div>
            <StudipIcon class="unchecked" shape="radiobutton-unchecked" :height="24" :width="24" />
            <StudipIcon class="check" shape="check-circle" :height="24" :width="24" />
        </label>
        <input
            @change="$emit('update:modelValue', 'users')"
            id="cw-element-copy-source-users"
            name="cw-element-copy-source"
            type="radio"
            value="users"
            :aria-description="$gettext('Arbeitsplatz anderer Nutzenden')"
        />
        <label for="cw-element-copy-source-users">
            <div class="icon">
                <StudipIcon shape="content" :height="32" :width="32" />
            </div>
            <div class="text">{{ $gettext('Arbeitsplatz anderer Nutzenden') }}</div>
            <StudipIcon class="unchecked" shape="radiobutton-unchecked" :height="24" :width="24" />
            <StudipIcon class="check" shape="check-circle" :height="24" :width="24" />
        </label>
    </fieldset>
</template>
