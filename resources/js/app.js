import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { Ziggy } from './ziggy';

// Mendaftarkan collection ikon MDI ke @iconify/vue supaya <Icon icon="mdi:...">
// dilayani dari bundel, bukan dari api.iconify.design. Wajib import sebelum
// aplikasi di-mount.
import './icons';

const appName = import.meta.env.VITE_APP_NAME || 'KPM SMART';

createInertiaApp({
    title: (title) => title ? `${title} - ${appName}` : appName,
    resolve: (name) => resolvePageComponent(
        `./Pages/${name}.vue`,
        import.meta.glob('./Pages/**/*.vue')
    ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, { ...Ziggy, url: window.location.origin })
            .mount(el);
    },
    progress: {
        color: '#769826',
        showSpinner: false,
    },
});
