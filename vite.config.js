import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: [`resources/views/**/*`],
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
        // Vite tourne dans le conteneur Docker : écoute sur toutes les interfaces,
        // et le navigateur se connecte via localhost
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        hmr: {
            host: 'localhost',
        },
        // Les volumes montés depuis Windows ne remontent pas les événements fichiers
        watch: {
            usePolling: true,
        },
    },
});
