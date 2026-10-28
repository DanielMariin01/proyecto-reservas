import { Users, X } from 'lucide-react';
import type { ReactElement } from 'react';
import Tarjeta from '@/componentes/Tarjeta';
import type { Profesional, Servicio } from '@/tipos';

interface PropiedadesSeleccionarProfesional {
    servicio: Servicio;
    profesionales: Profesional[];
    cargando: boolean;
    onElegir: (profesionalId: number | null) => void;
    onCerrar: () => void;
}

export default function SeleccionarProfesional({
    servicio,
    profesionales,
    cargando,
    onElegir,
    onCerrar,
}: PropiedadesSeleccionarProfesional): ReactElement {
    return (
        <div className="relative max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-tarjeta bg-blanco p-6">
            <button
                type="button"
                onClick={onCerrar}
                aria-label="Cerrar"
                className="absolute right-4 top-4 flex h-9 w-9 items-center justify-center rounded-full bg-tinta text-blanco"
            >
                <X size={18} />
            </button>

            <h2 className="text-xl font-semibold text-tinta">Elige un profesional</h2>
            <p className="mb-4 text-sm text-texto-suave">Para {servicio.nombre}</p>

            {cargando && (
                <div className="grid gap-4 sm:grid-cols-3" aria-label="Cargando profesionales">
                    {[1, 2, 3].map((n) => (
                        <div key={n} className="h-40 animate-pulse rounded-tarjeta bg-gris-claro" />
                    ))}
                </div>
            )}

            {!cargando && (
                <div className="grid gap-4 sm:grid-cols-3">
                    {profesionales.length > 1 && (
                        <button type="button" onClick={() => onElegir(null)} className="text-left">
                            <Tarjeta className="flex h-full flex-col items-center gap-2 text-center hover:shadow-none">
                                <span className="flex h-20 w-20 items-center justify-center rounded-full bg-fondo-panel text-texto-suave">
                                    <Users size={32} strokeWidth={1.75} />
                                </span>
                                <span className="font-semibold text-tinta">Cualquiera disponible</span>
                            </Tarjeta>
                        </button>
                    )}

                    {profesionales.map((profesional) => (
                        <button type="button" key={profesional.id} onClick={() => onElegir(profesional.id)} className="text-left">
                            <Tarjeta className="flex h-full flex-col items-center gap-2 text-center hover:shadow-none">
                                {profesional.foto ? (
                                    <img src={profesional.foto} alt="" className="h-20 w-20 rounded-full object-cover" />
                                ) : (
                                    <span className="flex h-20 w-20 items-center justify-center rounded-full bg-fondo-panel text-texto-suave">
                                        <Users size={32} strokeWidth={1.75} />
                                    </span>
                                )}
                                <span className="font-semibold text-tinta">{profesional.nombre}</span>
                                {profesional.especialidad && <span className="text-sm text-texto-suave">{profesional.especialidad}</span>}
                            </Tarjeta>
                        </button>
                    ))}
                </div>
            )}
        </div>
    );
}
