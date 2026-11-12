import type { ReactElement } from 'react';
import { useEffect, useState } from 'react';
import { useMutation, useQuery } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import { type BloqueEditable, guardarYPublicarPagina, obtenerBloquesEditables } from '../api/paginaWeb';
import LayoutPanelNegocio from '../LayoutPanelNegocio';
import TarjetaBloqueEditable from './TarjetaBloqueEditable';

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
}

export default function PaginaContenidoWeb({ negocioNombre, negocioSlug, urlSalir, tokenCsrf }: Propiedades): ReactElement {
    const [bloques, setBloques] = useState<BloqueEditable[] | null>(null);
    const [mensajeExito, setMensajeExito] = useState<string | null>(null);

    const consulta = useQuery({
        queryKey: ['panel-pagina-web', negocioSlug],
        queryFn: () => obtenerBloquesEditables(negocioSlug),
    });

    useEffect(() => {
        if (consulta.data && bloques === null) {
            setBloques(consulta.data);
        }
    }, [consulta.data, bloques]);

    const mutacion = useMutation({
        mutationFn: () =>
            guardarYPublicarPagina(
                negocioSlug,
                (bloques ?? []).map((bloque) => ({ indice: bloque.indice, data: bloque.data })),
            ),
        onSuccess: (bloquesActualizados) => {
            setBloques(bloquesActualizados);
            setMensajeExito('Página publicada');
        },
    });

    function cambiarBloque(indice: number, data: Record<string, unknown>): void {
        setMensajeExito(null);
        setBloques((actuales) => (actuales ?? []).map((bloque) => (bloque.indice === indice ? { ...bloque, data } : bloque)));
    }

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <h2 className="text-lg font-semibold text-tinta">Página web</h2>

                {consulta.isLoading || bloques === null ? (
                    <p className="text-sm text-texto-suave">Cargando...</p>
                ) : consulta.isError ? (
                    <p className="text-sm text-texto-suave">Algo falló cargando tu página. Código REF-PAG0.</p>
                ) : bloques.length === 0 ? (
                    <p className="text-sm text-texto-suave">
                        Nada que editar todavía. Los bloques de tu página no tienen texto propio, o aún no tienen contenido. Pídele al super
                        admin que agregue un bloque de portada o de texto.
                    </p>
                ) : (
                    <>
                        <div className="space-y-4">
                            {bloques.map((bloque) => (
                                <TarjetaBloqueEditable
                                    key={bloque.indice}
                                    slug={negocioSlug}
                                    bloque={bloque}
                                    onCambiar={(data) => cambiarBloque(bloque.indice, data)}
                                />
                            ))}
                        </div>

                        {mensajeExito && <p className="text-sm text-secundario">{mensajeExito}</p>}

                        <Boton onClick={() => mutacion.mutate()} disabled={mutacion.isPending}>
                            {mutacion.isPending ? 'Guardando...' : 'Guardar y publicar'}
                        </Boton>
                    </>
                )}
            </div>
        </LayoutPanelNegocio>
    );
}
