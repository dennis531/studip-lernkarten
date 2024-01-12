import { resolve } from 'node:path';
import { fileURLToPath, URL } from 'node:url';

import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig(({ mode }) => {
    return {
        build: {
            lib: {
                entry: {
                    lernkarten: resolve(__dirname, 'src/main.js'),
                    register: resolve(__dirname, 'src/courseware/register.js'),
                },
            },
            sourcemap: mode === 'development' ? 'inline' : false,
        },
        define: { 'process.env.NODE_ENV': `"${mode}"` },
        plugins: [vue()],
        resolve: {
            alias: {
                '@': fileURLToPath(new URL('./src', import.meta.url)),
            },
        },
    };
});
