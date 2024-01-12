import { createRouter, createWebHistory } from 'vue-router';
import SharedDecksView from '../views/SharedDecksView.vue';
import DecksCreateView from '../views/DecksCreateView.vue';
import DeckView from '../views/DeckView.vue';
import FolderView from '../views/FolderView.vue';
import FoldersView from '../views/FoldersView.vue';
import SearchView from '../views/SearchView.vue';
import StudyView from '../views/StudyView.vue';

const absoluteUriStudip = new URL(window.STUDIP.ABSOLUTE_URI_STUDIP);
const cid = window.STUDIP.URLHelper.parameters.cid;
const baseUrl = `${absoluteUriStudip.pathname}plugins.php/lernkartenplugin/`;

const isCourse = 'cid' in window.STUDIP.URLHelper.parameters;

const router = createRouter({
    history: createWebHistory(baseUrl),
    routes: [
        {
            path: '/',
            name: 'home',
            component: isCourse ? SharedDecksView : FoldersView,
        },
        {
            path: '/search',
            name: 'search',
            component: SearchView,
            props: (route) => ({ query: route.query.q }),
        },
        {
            path: '/folders/:id',
            name: 'folder',
            component: FolderView,
            props: true,
        },
        {
            path: '/decks/:id',
            name: 'deck',
            component: DeckView,
            props: true,
        },
        {
            path: '/decks/create',
            name: 'decks-create',
            component: DecksCreateView,
            props: (route) => ({ folder: route.query.f }),
        },
        {
            path: '/study',
            name: 'study',
            component: StudyView,
            props: (route) => {
                return {
                    decks: route?.query?.decks ?? [],
                    order: route?.query?.order ?? null,
                };
            },
        },
    ],
});

router.beforeEach((to, from, next) => {
    if (cid && !('cid' in to.query)) {
        next({ ...to, query: { ...to.query, cid } });
    } else {
        next();
    }
});

export default router;
