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
    // The 'courseware' chunk exists and is required since Stud.IP v5.5.
    // Do not display errors about unknown chunks in Stud.IP v5.3 + v5.4.
    STUDIP.loadChunk('courseware', { silent: true }).catch(() => {});

    const app = createApp(App);

    app.provide('initialState', data);

    const pinia = createPinia();
    pinia.use(piniaPluginPersistedstate);

    app.use(pinia);
    app.use(router);
    app.use(createGettext({ translations, silent: true }));

    app.use(CKEditor);

    loadWysiwyg()
        .then((wysiwyg) => app.use(wysiwyg))
        .then(() => app.mount(el));
};

if (window.STUDIP) {
    window.STUDIP.mountLernkarten = mountLernkarten;
}
