import type { ComponentPropsWithoutRef, ElementType, ReactNode } from 'react';
import { cn } from '@/lib/utils';

type SectionProps<T extends ElementType = 'section'> = {
    as?: T;
    children?: ReactNode;
    className?: string;
} & Omit<ComponentPropsWithoutRef<T>, 'as' | 'children' | 'className'>;

export function Section<T extends ElementType = 'section'>({
    as,
    children,
    className,
    ...props
}: SectionProps<T>) {
    const Component = as ?? 'section';

    return (
        <Component
            className={cn('py-12 sm:py-16 lg:py-20', className)}
            {...props}
        >
            {children}
        </Component>
    );
}
