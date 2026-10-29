import { Scissors, X } from 'lucide-react';
import type { ReactElement } from 'react';
import Tarjeta from '@/componentes/Tarjeta';
import type { Servicio } from '@/tipos';

interface PropiedadesSeleccionarServicio {
    servicios: Servicio[];
    cargando: boolean;
    onElegir: (servicio: Servicio) => void;
    onCerrar: () => void;
}

export default function SeleccionarServicio({
    servicios,
    cargando,
    onElegir,
    onCerrar,
}: PropiedadesSeleccionarServicio): ReactElement {
    return (
        <div className="scroll-primario relative max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-tarjeta bg-blanco p-6">
            <button
                type="button"
                onClick={onCerrar}
                aria-label="Cerrar"
                className="absolute right-4 top-4 flex h-9 w-9 items-center justify-center rounded-full bg-tinta text-blanco"
            >
                <X size={18} />
            </button>

            <h2 className="mb-4 text-xl font-semibold text-tinta">¿Qué quieres reservar?</h2>

            {cargando && (
                <div className="grid gap-4 sm:grid-cols-2" aria-label="Cargando servicios">
                    {[1, 2, 3, 4].map((n) => (
                        <div key={n} className="h-24 animate-pulse rounded-tarjeta bg-gris-claro" />
                    ))}
                </div>
            )}

            {!cargando && servicios.length === 0 && <p className="text-sm text-texto-suave">Este negocio aún no tiene servicios disponibles.</p>}

            {!cargando && servicios.length > 0 && (
                <div className="grid gap-4 sm:grid-cols-2">
                    {servicios.map((servicio) => (
                        <button type="button" key={servicio.id} onClick={() => onElegir(servicio)} className="text-left">
                            <Tarjeta className="flex h-full items-center gap-3 hover:shadow-none">
                                {servicio.foto ? (
                                    <img src={servicio.foto} alt="" className="h-14 w-14 rounded-boton object-cover" />
                                ) : (
                                    <span className="flex h-14 w-14 items-center justify-center rounded-boton bg-fondo-panel text-texto-suave">
                                        <Scissors size={24} strokeWidth={1.75} />
                                    </span>
                                )}
                                <span>
                                    <span className="block font-semibold text-tinta">{servicio.nombre}</span>
                                    <span className="block text-sm text-texto-suave">
                                        ${servicio.precio.toLocaleString('es-CO')} · {servicio.duracionMinutos} min
                                    </span>
                                </span>
                            </Tarjeta>
                        </button>
                    ))}
                </div>
            )}
        </div>
    );
}
