import type { ComponentPropsWithoutRef } from 'react';
import { cn } from '@/lib/utils';

type GridColumns = 1 | 2 | 3 | 4;

const columnsMap: Record<GridColumns, string> = {
    1: 'grid-cols-1',
    2: 'grid-cols-1 md:grid-cols-2',
    3: 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
    4: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4',
};

type GridProps = ComponentPropsWithoutRef<'div'> & {
    columns?: GridColumns;
};

export function Grid({ className, columns = 3, ...props }: GridProps) {
    return (
        <div
            className={cn('grid gap-6', columnsMap[columns], className)}
            {...props}
        />
    );
}
