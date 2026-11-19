import type { ReactElement } from 'react';
import { Moon, Sun, Sunrise } from 'lucide-react';
import type { FranjaHoraria } from '@/tipos';

const FRANJAS: { valor: FranjaHoraria; etiqueta: string; Icono: typeof Sunrise }[] = [
    { valor: 'manana', etiqueta: 'Mañana', Icono: Sunrise },
    { valor: 'tarde', etiqueta: 'Tarde', Icono: Sun },
    { valor: 'noche', etiqueta: 'Noche', Icono: Moon },
];

interface Propiedades {
    franjaSeleccionada: FranjaHoraria;
    onSeleccionar: (franja: FranjaHoraria) => void;
}

export default function SelectorFranjaHoraria({ franjaSeleccionada, onSeleccionar }: Propiedades): ReactElement {
    return (
        <div className="flex flex-wrap gap-2">
            {FRANJAS.map(({ valor, etiqueta, Icono }) => {
                const seleccionado = valor === franjaSeleccionada;

                return (
                    <button
                        key={valor}
                        type="button"
                        onClick={() => onSeleccionar(valor)}
                        className={`flex items-center gap-2 rounded-boton border px-4 py-2 text-sm font-semibold transition-colors ${
                            seleccionado ? 'border-primario bg-celeste text-tinta' : 'border-gris-claro bg-blanco text-tinta hover:bg-fondo-panel'
                        }`}
                    >
                        <Icono size={18} strokeWidth={1.75} />
                        {etiqueta}
                    </button>
                );
            })}
        </div>
    );
}
