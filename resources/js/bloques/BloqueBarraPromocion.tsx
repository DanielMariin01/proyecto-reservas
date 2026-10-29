import { X } from 'lucide-react';
import { useState } from 'react';
import type { ReactElement } from 'react';
import { obtenerEstiloColor } from './colorLibre';
import type { PropiedadesBloque } from './tipos';

/**
 * El cierre (x) es solo de esta visita del navegador — no se guarda en
 * ningún lado, así que vuelve a aparecer en la próxima carga de página.
 */
export default function BloqueBarraPromocion({ datos }: PropiedadesBloque): ReactElement {
    const [visible, setVisible] = useState(true);
    const texto = typeof datos.texto === 'string' ? datos.texto : '';
    const textoBoton = typeof datos.textoBoton === 'string' ? datos.textoBoton : '';
    const enlaceBoton = typeof datos.enlaceBoton === 'string' ? datos.enlaceBoton : '';

    if (!visible || texto === '') {
        return <></>;
    }

    return (
        <div style={obtenerEstiloColor(datos)} className="flex flex-wrap items-center justify-center gap-3 px-4 py-2 text-center text-sm">
            <p>{texto}</p>

            {textoBoton && (
                <a href={enlaceBoton || undefined} target="_blank" rel="noreferrer" className="shrink-0 font-semibold underline">
                    {textoBoton}
                </a>
            )}

            <button type="button" onClick={() => setVisible(false)} aria-label="Cerrar" className="ml-2 shrink-0">
                <X size={16} strokeWidth={1.75} />
            </button>
        </div>
    );
}
