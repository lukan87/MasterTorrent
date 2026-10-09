import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
                'resources/js/torrent-browser.js',
                'resources/js/torrent-featured.js',
                'resources/js/torrent-detail.js',
                'resources/js/library-browser.js',
                'resources/js/calendar-browser.js',
                'resources/js/page-browser.js',
                'resources/js/forum-topic.js',
                'resources/js/seedbox-browser.js',
                'resources/js/messenger-browser.js',
                'resources/js/ticket-browser.js',
                'resources/js/contact-browser.js',
                'resources/js/profile-activity.js',
                'resources/js/library-detail.js',
                'resources/js/torrent-form.js',
                'resources/js/home-widgets.js',

            ],
            refresh: true,
        }),
    ],
});
