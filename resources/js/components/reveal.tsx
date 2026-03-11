import { useEffect, useRef, useState } from 'react';
import type { PropsWithChildren } from 'react';
import { cn } from '@/lib/utils';

type RevealProps = PropsWithChildren<{
    className?: string;
    delay?: number;
}>;

export function Reveal({ children, className, delay = 0 }: RevealProps) {
    const elementRef = useRef<HTMLDivElement | null>(null);
    const [isVisible, setIsVisible] = useState(() => {
        if (typeof window === 'undefined') {
            return false;
        }

        return !('IntersectionObserver' in window);
    });

    useEffect(() => {
        const element = elementRef.current;

        if (!element) {
            return;
        }

        if (isVisible || !('IntersectionObserver' in window)) {
            return;
        }

        const observer = new IntersectionObserver(
            ([entry]) => {
                if (entry?.isIntersecting) {
                    setIsVisible(true);
                    observer.disconnect();
                }
            },
            {
                threshold: 0.2,
            },
        );

        observer.observe(element);

        return () => {
            observer.disconnect();
        };
    }, [isVisible]);

    return (
        <div
            ref={elementRef}
            className={cn('reveal', isVisible && 'reveal-visible', className)}
            style={delay > 0 ? { transitionDelay: `${delay}ms` } : undefined}
        >
            {children}
        </div>
    );
}
