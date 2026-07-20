// vite.config.js

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],

    base: '/',
    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
        cors: true,
        hmr: {
            host: 'localhost',
        },
    },
    build: {
        outDir: 'public/build',
        assetsDir: 'build',
        rollupOptions: {
            output: {
                entryFileNames: 'assets/[name].[hash].js',
                chunkFileNames: 'assets/[name].[hash].js',
                assetFileNames: 'assets/[name].[hash].[ext]',
            },
        },
    },
});
