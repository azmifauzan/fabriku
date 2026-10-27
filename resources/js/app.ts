import '../css/app.css';

import { createInertiaApp, router } from '@inertiajs/vue3';
import axios from 'axios';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// GA4 is loaded by SeoHead on public pages only, but once loaded it keeps
// tracking Inertia history changes — so after login it would log tenant app
// screens too. Fire any flashed conversion event first, then disable GA on
// non-marketing pages via gtag's per-property opt-out flag.
const gaMeasurementId = import.meta.env.VITE_GA_MEASUREMENT_ID as string | undefined;
const marketingPage = (component: string) => /^(Welcome|Blog\/|Legal\/|Landing\/|Auth\/)/.test(component);

if (gaMeasurementId && typeof window !== 'undefined') {
    router.on('navigate', (event) => {
        const page = event.detail.page;
        const analyticsEvent = (page.props.flash as { analytics_event?: string } | undefined)?.analytics_event;
        const w = window as unknown as Record<string, unknown> & { gtag?: (...args: unknown[]) => void };

        const disable = () => (w[`ga-disable-${gaMeasurementId}`] = !marketingPage(page.component));

        // A full page load after OAuth (Google sign-up) lands on a page without
        // SeoHead, so gtag was never loaded; bootstrap it just for this event.
        if (analyticsEvent && !w.gtag) {
            const dataLayer = ((w as { dataLayer?: unknown[] }).dataLayer ??= []);
            w.gtag = function () {
                // eslint-disable-next-line prefer-rest-params
                dataLayer.push(arguments);
            };
            w.gtag('js', new Date());
            w.gtag('config', gaMeasurementId);
            const script = document.createElement('script');
            script.async = true;
            script.src = `https://www.googletagmanager.com/gtag/js?id=${gaMeasurementId}`;
            document.head.appendChild(script);
        }

        if (analyticsEvent && w.gtag) {
            // gtag sends hits async; keep GA enabled until the conversion hit is out.
            w[`ga-disable-${gaMeasurementId}`] = false;
            w.gtag('event', analyticsEvent, { event_callback: disable, event_timeout: 2000 });
        } else {
            disable();
        }
    });
}

createInertiaApp({
    title: (title) => (title && !title.includes(appName) ? `${title} - ${appName}` : title || appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
