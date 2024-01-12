<template>
    <div class="cw-block cw-block-lernkarten">
        <component
            :is="coursewarePluginComponents.CoursewareDefaultBlock"
            ref="defaultBlock"
            :block="block"
            :canEdit="canEdit"
            :isTeacher="isTeacher"
            :preview="true"
            :defaultGrade="false"
            @storeEdit="storeBlock"
            @closeEdit="initCurrentData"
        >
            <template #content>
                <span v-if="!isBlockInitialized" class="sr-only">
                    {{
                        $gettext(
                            'Die Lernkarten werden angezeigt, nachdem der Block gespeichert wurde.'
                        )
                    }}
                </span>
                <div v-else>
                    <lernkarten-block :deck="sharedDeckId"></lernkarten-block>
                </div>
            </template>

            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="onSubmit">
                    <lernkarten-deck-selector
                        :deck="sharedDeckId"
                        @change="onSelectDeck"
                    ></lernkarten-deck-selector>
                </form>
            </template>

            <template #info>
                {{ $gettext('Informationen zum Lernkartenblock') }}
            </template>
        </component>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    components: {},
    name: 'courseware-lernkarten-block',
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            callback: null,
            sharedDeckId: null,
        };
    },
    computed: {
        ...mapGetters({
            context: 'context',
        }),
        isBlockInitialized: function () {
            return this.block.attributes.payload.initialized;
        },
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
        }),
        initCurrentData() {
            this.sharedDeckId = this.block.attributes.payload.shareddeck || null;
        },
        onSelectDeck({ detail: [callback = null] }) {
            this.callback = callback;
        },
        storeBlock() {
            if (!this.callback) {
                return;
            }
            this.callback()
                .then((sharedDeck) => (this.sharedDeckId = sharedDeck.id))
                .then(() =>
                    this.updateBlock({
                        attributes: {
                            ...this.block.attributes,
                            payload: {
                                ...this.block.attributes.payload,
                                initialized: true,
                                shareddeck: this.sharedDeckId,
                            },
                        },
                        blockId: this.block.id,
                        containerId: this.block.relationships.container.data.id,
                    })
                )
                .then(() => this.$refs.defaultBlock.displayFeature(false))
                .catch((error) => {
                    console.debug('Error selecting deck', error);
                });
        },
    },
    watch: {
        $props: {
            handler() {
                console.warn('props watcher', JSON.parse(JSON.stringify(this.$props)));
            },
            deep: true,
            immediate: true,
        },
    },
    async mounted() {
        this.initCurrentData();
        if (!this.block.attributes.payload.initialized) {
            this.storeBlock();
        }
    },
    inject: ['coursewarePluginComponents'],
};
</script>

<style></style>
