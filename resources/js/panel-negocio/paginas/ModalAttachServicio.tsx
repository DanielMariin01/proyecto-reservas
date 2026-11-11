import type { ReactElement } from 'react';
import { useState } from 'react';
import Boton from '@/componentes/Boton';
import CampoTexto from '@/componentes/CampoTexto';
import Modal from '@/componentes/Modal';
import type { ServicioDisponible } from '../api/profesionalServicios';

interface Propiedades {
    disponibles: ServicioDisponible[];
    enviando: boolean;
    onGuardar: (datos: { servicio_id: number; precio: number | null; duracion_minutos: number | null }) => Promise<unknown>;
    onCerrar: () => void;
}

export default function ModalAttachServicio({ disponibles, enviando, onGuardar, onCerrar }: Propiedades): ReactElement {
    const [servicioId, setServicioId] = useState(disponibles[0]?.id ?? 0);
    const [precio, setPrecio] = useState('');
    const [duracionMinutos, setDuracionMinutos] = useState('');

    async function enviar(): Promise<void> {
        await onGuardar({
            servicio_id: servicioId,
            precio: precio === '' ? null : Number(precio),
            duracion_minutos: duracionMinutos === '' ? null : Number(duracionMinutos),
        });
    }

    return (
        <Modal titulo="Vincular servicio" onCerrar={onCerrar}>
            <div className="space-y-4">
                <div className="flex flex-col gap-1">
                    <label htmlFor="servicio-a-vincular" className="text-sm font-medium text-tinta">
                        Servicio
                    </label>
                    <select
                        id="servicio-a-vincular"
                        value={servicioId}
                        onChange={(evento) => setServicioId(Number(evento.target.value))}
                        className="h-12 rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                    >
                        {disponibles.map((servicio) => (
                            <option key={servicio.id} value={servicio.id}>
                                {servicio.nombre}
                            </option>
                        ))}
                    </select>
                </div>

                <div className="space-y-1">
                    <CampoTexto
                        etiqueta="Precio propio (opcional)"
                        type="number"
                        value={precio}
                        onChange={(evento) => setPrecio(evento.target.value)}
                    />
                    <p className="text-sm text-texto-suave">Si lo dejas vacío, usa el precio del servicio.</p>
                </div>
                <CampoTexto
                    etiqueta="Duración propia en minutos (opcional)"
                    type="number"
                    value={duracionMinutos}
                    onChange={(evento) => setDuracionMinutos(evento.target.value)}
                />

                <Boton onClick={enviar} disabled={enviando || disponibles.length === 0} className="w-full">
                    {enviando ? 'Guardando...' : 'Vincular'}
                </Boton>
            </div>
        </Modal>
    );
}
