import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const host = (typeof window !== 'undefined' && window.location?.hostname)
    ? window.location.hostname
    : (import.meta.env.VITE_REVERB_HOST || 'localhost');

const port = parseInt(import.meta.env.VITE_REVERB_PORT || 8080, 10);
const scheme = import.meta.env.VITE_REVERB_SCHEME || 'http';
const isTls = scheme === 'https';

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY || '3ajr9yyhis1g8ysmblzj',
    wsHost: host,
    wsPort: port,
    wssPort: port,
    forceTLS: isTls,
    enabledTransports: ['ws', 'wss'],
});
