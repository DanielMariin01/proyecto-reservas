import type { ButtonHTMLAttributes, ReactElement } from 'react';
import { Slot } from '@radix-ui/react-slot';
import { cva, type VariantProps } from 'class-variance-authority';
import { cn } from '@/lib/utils';

const varianteBoton = cva(
    'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-[var(--radius)] text-sm font-medium transition-colors disabled:pointer-events-none disabled:opacity-50',
    {
        variants: {
            variant: {
                default: 'bg-primary text-primary-foreground hover:bg-primary/90',
                outline: 'border border-input bg-background hover:bg-accent hover:text-accent-foreground',
                ghost: 'hover:bg-accent hover:text-accent-foreground',
                destructive: 'bg-destructive text-destructive-foreground hover:bg-destructive/90',
            },
            size: {
                default: 'h-10 px-4 py-2',
                sm: 'h-9 px-3',
                icon: 'h-9 w-9',
            },
        },
        defaultVariants: {
            variant: 'default',
            size: 'default',
        },
    },
);

interface PropiedadesButton extends ButtonHTMLAttributes<HTMLButtonElement>, VariantProps<typeof varianteBoton> {
    asChild?: boolean;
}

export function Button({ className, variant, size, asChild = false, ...props }: PropiedadesButton): ReactElement {
    const Componente = asChild ? Slot : 'button';

    return <Componente className={cn(varianteBoton({ variant, size, className }))} {...props} />;
}
