import type { HTMLAttributes, ReactElement, TdHTMLAttributes, ThHTMLAttributes } from 'react';
import { cn } from '@/lib/utils';

export function Table({ className, ...props }: HTMLAttributes<HTMLTableElement>): ReactElement {
    return (
        <div className="w-full overflow-x-auto rounded-[var(--radius)] border border-border">
            <table className={cn('w-full text-left text-sm', className)} {...props} />
        </div>
    );
}

export function TableHeader({ className, ...props }: HTMLAttributes<HTMLTableSectionElement>): ReactElement {
    return <thead className={cn('bg-muted text-muted-foreground', className)} {...props} />;
}

export function TableBody({ className, ...props }: HTMLAttributes<HTMLTableSectionElement>): ReactElement {
    return <tbody className={cn('divide-y divide-border', className)} {...props} />;
}

export function TableRow({ className, ...props }: HTMLAttributes<HTMLTableRowElement>): ReactElement {
    return <tr className={cn('', className)} {...props} />;
}

export function TableHead({ className, ...props }: ThHTMLAttributes<HTMLTableCellElement>): ReactElement {
    return <th className={cn('px-4 py-3 font-semibold', className)} {...props} />;
}

export function TableCell({ className, ...props }: TdHTMLAttributes<HTMLTableCellElement>): ReactElement {
    return <td className={cn('px-4 py-3', className)} {...props} />;
}
