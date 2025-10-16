import './bootstrap.js';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { InertiaProgress } from '@inertiajs/progress';

InertiaProgress.init({ color: '#4F46E5' });

createInertiaApp({
    title: (title) => `${title} - Sistema de Inventario`,
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin);
            
        // Make route helper available globally
        app.config.globalProperties.route = window.route;
        
        return app.mount(el);
    },
    progress: {
        color: '#4F46E5',
    },
});