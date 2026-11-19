import type { ReactElement } from 'react';
import type { ColumnaAgenda } from '../api/agenda';

interface Propiedades {
    columnas: ColumnaAgenda[];
    profesionalSeleccionadoId: number | null;
    onSeleccionar: (profesionalId: number) => void;
}

export default function SelectorProfesionalesAgenda({ columnas, profesionalSeleccionadoId, onSeleccionar }: Propiedades): ReactElement {
    return (
        <div className="flex flex-wrap gap-2">
            {columnas.map((columna) => {
                const seleccionado = columna.profesionalId === profesionalSeleccionadoId;

                return (
                    <button
                        key={columna.profesionalId}
                        type="button"
                        onClick={() => onSeleccionar(columna.profesionalId)}
                        className={`flex items-center gap-2 rounded-boton border p-1.5 pr-4 text-sm font-semibold transition-colors ${
                            seleccionado ? 'border-primario bg-celeste text-tinta' : 'border-gris-claro bg-blanco text-tinta hover:bg-fondo-panel'
                        }`}
                    >
                        {columna.profesionalFoto ? (
                            <img src={columna.profesionalFoto} alt="" className="h-9 w-9 rounded-full object-cover" />
                        ) : (
                            <span
                                className="flex h-9 w-9 items-center justify-center rounded-full text-sm font-semibold text-blanco"
                                style={{ backgroundColor: columna.profesionalColor ?? '#673DE6' }}
                            >
                                {columna.profesionalNombre.charAt(0).toUpperCase()}
                            </span>
                        )}
                        {columna.profesionalNombre}
                    </button>
                );
            })}
        </div>
    );
}
