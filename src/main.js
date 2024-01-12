import { createPinia } from 'pinia';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';
import { createApp } from 'vue';
import { createGettext } from 'vue3-gettext';
import App from './App.vue';
import router from './router';
import translations from './locales/translations.json';
import CKEditor from '@ckeditor/ckeditor5-vue';
import { loadWysiwyg } from './wysiwyg.js';
import './assets/main.css';

const mountLernkarten = (el, data) => {
    const app = createApp(App);

    app.provide('initialState', data);

    const pinia = createPinia();
    pinia.use(piniaPluginPersistedstate);

    app.use(pinia);
    app.use(router);
    app.use(createGettext({ translations }));

    app.use(CKEditor);

    loadWysiwyg()
        .then((wysiwyg) => app.use(wysiwyg))
        .then(() => app.mount(el));
};

if (window.STUDIP) {
    window.STUDIP.mountLernkarten = mountLernkarten;
}
