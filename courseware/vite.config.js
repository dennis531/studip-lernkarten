import { resolve } from 'node:path';

import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue2';

export default defineConfig(({ mode }) => {
    return {
        build: {
            lib: {
                entry: resolve(__dirname, 'src/main.js'),
                name: 'lernkarten-courseware',
            },
            sourcemap: mode === 'development' ? 'inline' : false,
        },
        define: { 'process.env.NODE_ENV': `"${mode}"` },
        plugins: [vue()],
    };
});
