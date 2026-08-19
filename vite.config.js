import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { networkInterfaces } from 'os';

// Detect the current machine's LAN IPv4 address instead of hardcoding one,
// so the dev server keeps working after the machine moves networks / gets a
// new office IP (previously pinned to a stale 192.168.20.168).
function getLanIp() {
    const nets = networkInterfaces();
    for (const name of Object.keys(nets)) {
        for (const net of nets[name]) {
            if (net.family === 'IPv4' && !net.internal) {
                return net.address;
            }
        }
    }
    return 'localhost';
}

const lanHost = getLanIp();

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],

    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        cors: true,

        origin: `http://${lanHost}:5173`,

        hmr: {
            host: lanHost,
            port: 5173,
        },
    },
});