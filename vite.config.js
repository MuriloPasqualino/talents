import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
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
        VitePWA({
            registerType: 'autoUpdate',
            injectRegister: null,
            includeAssets: ['pwa-icon.svg'],
            manifest: {
                name: 'Talents — NR-1',
                short_name: 'Talents',
                description: 'Pesquisas NR-1, riscos psicossociais e gestão de pessoas',
                theme_color: '#632a7e',
                background_color: '#f1f5f9',
                display: 'standalone',
                orientation: 'portrait-primary',
                start_url: '/',
                lang: 'pt-BR',
                icons: [
                    {
                        src: '/pwa-icon.svg',
                        sizes: '512x512',
                        type: 'image/svg+xml',
                        purpose: 'any maskable',
                    },
                ],
            },
            workbox: {
                // Não precachear .js: após deploy, um SW antigo ainda servia chunks antigos
                // (ex.: ReferenceError por código já corrigido no repositório).
                globPatterns: ['**/*.{css,ico,svg,woff2}', 'manifest.webmanifest'],
                navigateFallback: null,
                runtimeCaching: [
                    {
                        urlPattern: /^https:\/\/fonts\.bunny\.net\/.*/i,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'bunny-fonts',
                            expiration: { maxEntries: 8, maxAgeSeconds: 60 * 60 * 24 * 365 },
                        },
                    },
                ],
            },
            devOptions: {
                enabled: false,
            },
        }),
    ],
});
