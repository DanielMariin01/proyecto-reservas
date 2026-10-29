import { MapPin } from 'lucide-react';
import type { ReactElement } from 'react';
import Boton from '@/componentes/Boton';
import { obtenerEstiloColor } from './colorLibre';
import type { PropiedadesBloque } from './tipos';

export default function BloqueUbicacion({ datos, pagina }: PropiedadesBloque): ReactElement {
    if (!pagina.direccion) {
        return <></>;
    }

    const enlaceMapa = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(pagina.direccion)}`;
    const mapaIncrustado = `https://www.google.com/maps?q=${encodeURIComponent(pagina.direccion)}&output=embed`;

    return (
        <section style={obtenerEstiloColor(datos)}>
            <div className="mx-auto max-w-5xl px-4 py-10 sm:py-16">
                <h2 className="mb-6 text-2xl font-bold sm:text-3xl">Ubicación</h2>

                <div className="grid gap-4 sm:grid-cols-2">
                    <iframe
                        title="Mapa"
                        src={mapaIncrustado}
                        className="h-64 w-full rounded-tarjeta border-0 sm:h-full"
                        loading="lazy"
                    />

                    <div className="flex flex-col justify-center gap-3">
                        <p className="flex items-center gap-2">
                            <MapPin size={18} className="opacity-80" strokeWidth={1.75} />
                            {pagina.direccion}
                        </p>
                        <a href={enlaceMapa} target="_blank" rel="noreferrer">
                            <Boton variante="contorno">Cómo llegar</Boton>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    );
}
