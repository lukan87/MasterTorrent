import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
                'resources/js/torrent-browser.js',
                'resources/js/torrent-detail.js',
                'resources/js/library-browser.js',
                'resources/js/calendar-browser.js',
            ],
            refresh: true,
        }),
    ],
});
