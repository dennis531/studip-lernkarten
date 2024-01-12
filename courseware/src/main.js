import LernkartenBlock from './LernkartenBlock.vue';

window.STUDIP.eventBus.on('courseware:init-plugin-manager', (pluginManager) => {
    pluginManager.addBlock('courseware-lernkarten-block', LernkartenBlock);
});

export default LernkartenBlock;
