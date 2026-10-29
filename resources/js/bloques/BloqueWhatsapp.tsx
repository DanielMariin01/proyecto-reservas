import type { ReactElement } from 'react';
import IconoWhatsapp from '@/componentes/IconoWhatsapp';
import { obtenerEstiloColor } from './colorLibre';
import type { PropiedadesBloque } from './tipos';

export default function BloqueWhatsapp({ datos, pagina }: PropiedadesBloque): ReactElement {
    if (!pagina.whatsapp) {
        return <></>;
    }

    const enlace = `https://wa.me/${pagina.whatsapp.replace(/\D/g, '')}?text=${encodeURIComponent(
        `Hola, vi la página de ${pagina.nombreNegocio} y tengo una pregunta.`,
    )}`;

    return (
        <a
            href={enlace}
            target="_blank"
            rel="noreferrer"
            aria-label="Escribir por WhatsApp"
            style={obtenerEstiloColor(datos)}
            className="fixed bottom-6 right-6 z-40 flex h-14 w-14 items-center justify-center rounded-full shadow-tarjeta"
        >
            <IconoWhatsapp size={28} />
        </a>
    );
}
