import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    server: {
        // Force IPv4. On Windows Vite resolves `localhost` to the IPv6
        // loopback `::1`, which produces a `public/hot` of `http://[::1]:5173`.
        // Browsers reject bracketed IPv6 literals in a CSP source list, so the
        // dev assets get blocked. Binding to 127.0.0.1 keeps the hot file, the
        // @vite() asset URLs, and the CSP in HeaderKeamanan all in agreement.
        host: '127.0.0.1',
        port: 5173,
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
