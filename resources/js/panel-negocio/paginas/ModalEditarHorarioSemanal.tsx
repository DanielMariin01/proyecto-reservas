import type { ReactElement } from 'react';
import { useEffect, useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import CampoInterruptor from '@/componentes/CampoInterruptor';
import Modal from '@/componentes/Modal';
import { type DiasHorario, esErrorDeHorario, obtenerHorarioSemanal } from '../api/horarioSemanal';

const DIAS = [
    { valor: '0', abreviatura: 'Do', etiqueta: 'Domingo' },
    { valor: '1', abreviatura: 'Lu', etiqueta: 'Lunes' },
    { valor: '2', abreviatura: 'Ma', etiqueta: 'Martes' },
    { valor: '3', abreviatura: 'Mi', etiqueta: 'Miércoles' },
    { valor: '4', abreviatura: 'Ju', etiqueta: 'Jueves' },
    { valor: '5', abreviatura: 'Vi', etiqueta: 'Viernes' },
    { valor: '6', abreviatura: 'Sá', etiqueta: 'Sábado' },
];

function diaDeHoy(): string {
    return String(new Date().getDay());
}

interface Propiedades {
    slug: string;
    profesionalId: number;
    enviando: boolean;
    onGuardar: (dias: DiasHorario) => Promise<unknown>;
    onCerrar: () => void;
}

export default function ModalEditarHorarioSemanal({ slug, profesionalId, enviando, onGuardar, onCerrar }: Propiedades): ReactElement {
    const [dias, setDias] = useState<DiasHorario | null>(null);
    const [errorGeneral, setErrorGeneral] = useState<string | null>(null);
    const [diaSeleccionado, setDiaSeleccionado] = useState(diaDeHoy());

    const consulta = useQuery({
        queryKey: ['panel-horario-semanal', slug, profesionalId],
        queryFn: () => obtenerHorarioSemanal(slug, profesionalId),
    });

    useEffect(() => {
        if (consulta.data && dias === null) {
            setDias(consulta.data);
        }
    }, [consulta.data, dias]);

    function alternarDisponible(diaValor: string): void {
        if (!dias) {
            return;
        }

        setDias({ ...dias, [diaValor]: { ...dias[diaValor], disponible: !dias[diaValor].disponible } });
    }

    function agregarRango(diaValor: string): void {
        if (!dias) {
            return;
        }

        setDias({
            ...dias,
            [diaValor]: { ...dias[diaValor], rangos: [...dias[diaValor].rangos, { hora_inicio: '09:00', hora_fin: '12:00' }] },
        });
    }

    function quitarRango(diaValor: string, indice: number): void {
        if (!dias) {
            return;
        }

        setDias({ ...dias, [diaValor]: { ...dias[diaValor], rangos: dias[diaValor].rangos.filter((_, i) => i !== indice) } });
    }

    function cambiarRango(diaValor: string, indice: number, campo: 'hora_inicio' | 'hora_fin', valor: string): void {
        if (!dias) {
            return;
        }

        const rangos = dias[diaValor].rangos.map((rango, i) => (i === indice ? { ...rango, [campo]: valor } : rango));
        setDias({ ...dias, [diaValor]: { ...dias[diaValor], rangos } });
    }

    async function guardar(): Promise<void> {
        if (!dias) {
            return;
        }

        setErrorGeneral(null);

        try {
            await onGuardar(dias);
        } catch (error) {
            if (esErrorDeHorario(error)) {
                setErrorGeneral(error.message);

                return;
            }

            throw error;
        }
    }

    const diaActual = dias?.[diaSeleccionado];
    const etiquetaDiaActual = DIAS.find((dia) => dia.valor === diaSeleccionado)?.etiqueta ?? '';

    return (
        <Modal titulo="Horario semanal" onCerrar={onCerrar}>
            {!dias ? (
                <p className="text-sm text-texto-suave">Cargando...</p>
            ) : (
                <div className="space-y-4">
                    {errorGeneral && <p className="text-sm text-red-600">{errorGeneral}</p>}

                    <div className="grid grid-cols-7 gap-1">
                        {DIAS.map(({ valor, abreviatura }) => {
                            const seleccionado = valor === diaSeleccionado;
                            const tieneHorario = dias[valor].disponible;

                            return (
                                <button
                                    key={valor}
                                    type="button"
                                    onClick={() => setDiaSeleccionado(valor)}
                                    className={`relative flex h-10 w-10 items-center justify-center rounded-full text-sm transition-colors ${
                                        seleccionado ? 'border-2 border-primario bg-celeste font-semibold text-tinta' : 'text-tinta hover:bg-fondo-panel'
                                    }`}
                                >
                                    {abreviatura}
                                    {tieneHorario && <span className="absolute bottom-1 h-1 w-1 rounded-full bg-primario" />}
                                </button>
                            );
                        })}
                    </div>

                    {diaActual && (
                        <div className="rounded-tarjeta border border-gris-claro p-4">
                            <CampoInterruptor
                                etiqueta={etiquetaDiaActual}
                                value={diaActual.disponible}
                                onChange={() => alternarDisponible(diaSeleccionado)}
                            />

                            {diaActual.disponible && (
                                <div className="mt-3 space-y-2">
                                    {diaActual.rangos.map((rango, indice) => (
                                        <div key={indice} className="flex flex-wrap items-center gap-2">
                                            <input
                                                type="time"
                                                value={rango.hora_inicio}
                                                onChange={(evento) => cambiarRango(diaSeleccionado, indice, 'hora_inicio', evento.target.value)}
                                                className="h-11 flex-1 rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                                            />
                                            <input
                                                type="time"
                                                value={rango.hora_fin}
                                                onChange={(evento) => cambiarRango(diaSeleccionado, indice, 'hora_fin', evento.target.value)}
                                                className="h-11 flex-1 rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                                            />
                                            <Boton variante="texto" onClick={() => quitarRango(diaSeleccionado, indice)}>
                                                Quitar
                                            </Boton>
                                        </div>
                                    ))}

                                    <Boton variante="contorno" onClick={() => agregarRango(diaSeleccionado)}>
                                        Agregar rango
                                    </Boton>
                                </div>
                            )}
                        </div>
                    )}

                    <Boton onClick={guardar} disabled={enviando} className="w-full">
                        {enviando ? 'Guardando...' : 'Guardar'}
                    </Boton>
                </div>
            )}
        </Modal>
    );
}
