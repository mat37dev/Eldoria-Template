import { defineConfig } from 'vite'
import { resolve } from 'path'

export default defineConfig({
    build: {
        outDir: 'assets/dist',
        rollupOptions: {
            input: {
                app: resolve(__dirname, 'assets/js/app.js'),
                style: resolve(__dirname, 'assets/css/app.css'),
                profile: resolve(__dirname, 'assets/js/profile.js'),
                'vote-podium': resolve(__dirname, 'assets/js/vote-podium.js'),
            },
            output: {
                // Fixed filenames (no content hash): Azuriom themes are not aware of
                // Laravel's Vite manifest, so views reference these paths directly
                // via theme_asset() instead of the @vite() directive.
                entryFileNames: '[name].js',
                chunkFileNames: '[name].js',
                assetFileNames: '[name][extname]',
            },
        },
    },
})
