import type { ReactElement, ReactNode } from 'react';
import { Plus, Trash2 } from 'lucide-react';
import Boton from '@/componentes/Boton';
import Tarjeta from '@/componentes/Tarjeta';

interface Propiedades<T> {
    etiqueta: string;
    items: T[];
    maximo: number;
    itemPorDefecto: T;
    renderItem: (item: T, onCambiar: (item: T) => void) => ReactNode;
    onChange: (items: T[]) => void;
    textoAgregar: string;
}

export default function Repetidor<T>({
    etiqueta,
    items,
    maximo,
    itemPorDefecto,
    renderItem,
    onChange,
    textoAgregar,
}: Propiedades<T>): ReactElement {
    function agregar(): void {
        onChange([...items, itemPorDefecto]);
    }

    function quitar(indice: number): void {
        onChange(items.filter((_item, posicion) => posicion !== indice));
    }

    function cambiar(indice: number, item: T): void {
        onChange(items.map((actual, posicion) => (posicion === indice ? item : actual)));
    }

    return (
        <div className="space-y-3">
            <div className="flex items-center justify-between">
                <p className="text-sm font-medium text-tinta">{etiqueta}</p>
                {items.length < maximo && (
                    <Boton variante="contorno" onClick={agregar} className="flex items-center gap-1">
                        <Plus size={16} strokeWidth={1.75} />
                        {textoAgregar}
                    </Boton>
                )}
            </div>

            {items.map((item, indice) => (
                <Tarjeta key={indice} className="relative space-y-3">
                    <button
                        type="button"
                        aria-label="Quitar"
                        onClick={() => quitar(indice)}
                        className="absolute right-3 top-3 text-texto-suave hover:text-tinta"
                    >
                        <Trash2 size={16} strokeWidth={1.75} />
                    </button>
                    {renderItem(item, (nuevo) => cambiar(indice, nuevo))}
                </Tarjeta>
            ))}
        </div>
    );
}
