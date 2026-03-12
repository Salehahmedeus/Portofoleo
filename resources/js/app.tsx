import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { ErrorBoundary } from '@/components/error-boundary';
import { FlashToast } from '@/components/flash-toast';
import { PageTransition } from '@/components/page-transition';
import '../css/app.css';
import { initializeTheme } from '@/hooks/use-appearance';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.tsx`,
            import.meta.glob('./pages/**/*.tsx'),
        ),
    setup({ el, App, props }) {
        const root = createRoot(el);
        const initialFlash = (
            props.initialPage?.props as
                | {
                      flash?: {
                          success?: string | null;
                          error?: string | null;
                      };
                  }
                | undefined
        )?.flash;

        root.render(
            <StrictMode>
                <ErrorBoundary>
                    <FlashToast initialFlash={initialFlash} />
                    <PageTransition initialUrl={props.initialPage?.url}>
                        <App {...props} />
                    </PageTransition>
                </ErrorBoundary>
            </StrictMode>,
        );
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on load...
initializeTheme();
