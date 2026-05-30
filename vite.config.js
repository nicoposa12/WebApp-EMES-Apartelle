import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    server: {
        cors: true,
        headers: {
            'Access-Control-Allow-Origin': '*',
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/fonts.css', 'resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
    build: {
        // Increase chunk size warning limit
        chunkSizeWarningLimit: 600,

        // Manual chunk splitting for optimal caching
        rollupOptions: {
            output: {
                manualChunks: {
                    // Core Vue framework — rarely changes, cached long-term
                    'vendor-vue': ['vue', 'vue-router'],

                    // Bootstrap UI framework
                    'vendor-bootstrap': ['bootstrap'],

                    // Charts library — only used by admin reports/dashboard
                    'vendor-charts': ['apexcharts', 'vue3-apexcharts'],

                    // SweetAlert2 — used across many pages
                    'vendor-sweetalert': ['sweetalert2'],
                },
            },
        },
    },
});
