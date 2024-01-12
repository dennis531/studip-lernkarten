import { createPinia } from 'pinia';
import { createApp, defineCustomElement, getCurrentInstance, h } from 'vue';
import { createGettext } from 'vue3-gettext';
import translations from '../locales/translations.json';
import LernkartenBlock from './LernkartenBlock.ce.vue';
import LernkartenDeckSelector from './LernkartenDeckSelector.ce.vue';

const LernkartenBlockElement = createElementInstance(LernkartenBlock);
customElements.define('lernkarten-block', LernkartenBlockElement);

const LernkartenDeckSelectorElement = createElementInstance(LernkartenDeckSelector);
customElements.define('lernkarten-deck-selector', LernkartenDeckSelectorElement);

function createElementInstance(component) {
    return defineCustomElement({
        props: component.props,
        // styles: component.styles,
        render() {
            return h(component, this.$props);
        },
        setup() {
            const app = createApp();
            app.use(createPinia()).use(createGettext({ translations }));

            const inst = getCurrentInstance();
            Object.assign(inst.appContext, app._context);
            Object.assign(inst.provides, app._context.provides);
        },
        styles: [
            `@import url('` +
                window.STUDIP.URLHelper.getURL('assets/stylesheets/studip-base.css') +
                `')`,
        ],
    });
}
