import type { ReactElement } from 'react';
import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import Modal from '@/componentes/Modal';
import { obtenerServicios } from '../api/servicios';

interface Propiedades {
    slug: string;
    onConsultar: (servicioId: number, fecha: string) => Promise<string[]>;
    onCerrar: () => void;
}

export default function ModalVistaPreviaDisponibilidad({ slug, onConsultar, onCerrar }: Propiedades): ReactElement {
    const mañana = new Date(Date.now() + 24 * 60 * 60 * 1000).toISOString().slice(0, 10);
    const [servicioId, setServicioId] = useState<number | null>(null);
    const [fecha, setFecha] = useState(mañana);

    const consultaServicios = useQuery({
        queryKey: ['panel-servicios-para-vista-previa', slug],
        queryFn: () => obtenerServicios(slug),
    });

    const consultaFranjas = useQuery({
        queryKey: ['panel-vista-previa-disponibilidad', slug, servicioId, fecha],
        queryFn: () => onConsultar(servicioId as number, fecha),
        enabled: servicioId !== null && fecha !== '',
    });

    return (
        <Modal titulo="Lo que vería un cliente" onCerrar={onCerrar}>
            <div className="space-y-4">
                <p className="text-sm text-texto-suave">
                    Para un servicio de ejemplo y una fecha, estas son las franjas que vería un cliente al reservar, para evitar confusiones al
                    guardar el horario.
                </p>

                <div className="flex flex-col gap-1">
                    <label htmlFor="vista-previa-servicio" className="text-sm font-medium text-tinta">
                        Servicio de ejemplo
                    </label>
                    <select
                        id="vista-previa-servicio"
                        value={servicioId ?? ''}
                        onChange={(evento) => setServicioId(evento.target.value ? Number(evento.target.value) : null)}
                        className="h-12 rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                    >
                        <option value="">Elige un servicio</option>
                        {(consultaServicios.data ?? []).map((servicio) => (
                            <option key={servicio.id} value={servicio.id}>
                                {servicio.nombre}
                            </option>
                        ))}
                    </select>
                </div>

                <div className="flex flex-col gap-1">
                    <label htmlFor="vista-previa-fecha" className="text-sm font-medium text-tinta">
                        Fecha
                    </label>
                    <input
                        id="vista-previa-fecha"
                        type="date"
                        value={fecha}
                        onChange={(evento) => setFecha(evento.target.value)}
                        className="h-12 rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                    />
                </div>

                <div>
                    <p className="text-sm font-medium text-tinta">Franjas disponibles</p>
                    <p className="text-sm text-texto-suave">
                        {servicioId === null
                            ? 'Elige un servicio y una fecha.'
                            : consultaFranjas.isLoading
                              ? 'Calculando...'
                              : consultaFranjas.isError
                                ? 'No se pudieron calcular las franjas.'
                                : (consultaFranjas.data ?? []).length === 0
                                  ? 'No hay horas disponibles ese día.'
                                  : consultaFranjas.data?.join(' · ')}
                    </p>
                </div>
            </div>
        </Modal>
    );
}
