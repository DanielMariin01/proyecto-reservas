import type { ReactElement, ReactNode } from 'react';
import Boton from '@/componentes/Boton';

export interface ColumnaTabla<T> {
    clave: string;
    etiqueta: string;
    render: (fila: T) => ReactNode;
}

interface PropiedadesTablaPaginada<T> {
    columnas: ColumnaTabla<T>[];
    filas: T[];
    idDeFila: (fila: T) => string | number;
    cargando: boolean;
    error: boolean;
    mensajeVacio: string;
    codigoError: string;
    onReintentar: () => void;
    paginaActual: number;
    totalPaginas: number;
    onCambiarPagina: (pagina: number) => void;
}

export default function TablaPaginada<T>({
    columnas,
    filas,
    idDeFila,
    cargando,
    error,
    mensajeVacio,
    codigoError,
    onReintentar,
    paginaActual,
    totalPaginas,
    onCambiarPagina,
}: PropiedadesTablaPaginada<T>): ReactElement {
    if (cargando) {
        return (
            <div className="space-y-2">
                {[1, 2, 3, 4, 5].map((fila) => (
                    <div key={fila} className="h-12 animate-pulse rounded-tarjeta bg-gris-claro" />
                ))}
            </div>
        );
    }

    if (error) {
        return (
            <div className="flex flex-col items-center gap-4 py-12 text-center">
                <p className="text-sm text-texto-suave">Algo falló cargando la lista. Código {codigoError}.</p>
                <Boton onClick={onReintentar}>Reintentar</Boton>
            </div>
        );
    }

    if (filas.length === 0) {
        return <p className="py-12 text-center text-sm text-texto-suave">{mensajeVacio}</p>;
    }

    return (
        <div className="space-y-4">
            <div className="overflow-x-auto rounded-tarjeta shadow-tarjeta">
                <table className="w-full text-left text-sm">
                    <thead className="bg-fondo-panel text-texto-suave">
                        <tr>
                            {columnas.map((columna) => (
                                <th key={columna.clave} className="px-4 py-3 font-semibold whitespace-nowrap">
                                    {columna.etiqueta}
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gris-claro bg-blanco">
                        {filas.map((fila) => (
                            <tr key={idDeFila(fila)}>
                                {columnas.map((columna) => (
                                    <td key={columna.clave} className="px-4 py-3 text-tinta">
                                        {columna.render(fila)}
                                    </td>
                                ))}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {totalPaginas > 1 && (
                <div className="flex items-center justify-center gap-3">
                    <Boton variante="contorno" disabled={paginaActual <= 1} onClick={() => onCambiarPagina(paginaActual - 1)}>
                        Anterior
                    </Boton>
                    <span className="text-sm text-texto-suave">
                        Página {paginaActual} de {totalPaginas}
                    </span>
                    <Boton variante="contorno" disabled={paginaActual >= totalPaginas} onClick={() => onCambiarPagina(paginaActual + 1)}>
                        Siguiente
                    </Boton>
                </div>
            )}
        </div>
    );
}
