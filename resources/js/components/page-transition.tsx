import { router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import type { ReactNode } from 'react';

type PageTransitionProps = {
    children: ReactNode;
    initialUrl?: string;
};

export function PageTransition({ children, initialUrl }: PageTransitionProps) {
    const [currentUrl, setCurrentUrl] = useState(initialUrl ?? '');
    const containerRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        const removeListener = router.on('success', (event) => {
            setCurrentUrl(event.detail.page?.url ?? '');
        });

        return () => {
            removeListener();
        };
    }, []);

    useEffect(() => {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        containerRef.current?.animate(
            [
                {
                    opacity: 0,
                    transform: 'translateY(4px)',
                },
                {
                    opacity: 1,
                    transform: 'translateY(0)',
                },
            ],
            {
                duration: 220,
                easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
                fill: 'both',
            },
        );
    }, [currentUrl]);

    return (
        <div key={currentUrl} ref={containerRef}>
            {children}
        </div>
    );
}
