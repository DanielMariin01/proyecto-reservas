import type { ReactElement } from 'react';
import { useState } from 'react';
import Boton from '@/componentes/Boton';
import CampoTexto from '@/componentes/CampoTexto';
import Modal from '@/componentes/Modal';
import { type ErroresValidacion, ErrorDeValidacion } from '../api/negocios';

const OPCIONES_METODO: Array<{ valor: string; etiqueta: string }> = [
    { valor: 'efectivo', etiqueta: 'Efectivo' },
    { valor: 'nequi', etiqueta: 'Nequi' },
    { valor: 'daviplata', etiqueta: 'Daviplata' },
    { valor: 'transferencia', etiqueta: 'Transferencia' },
    { valor: 'tarjeta', etiqueta: 'Tarjeta' },
];

interface Propiedades {
    enviando: boolean;
    onGuardar: (datos: {
        fecha: string;
        valor: number;
        metodo: string;
        periodo_desde: string;
        periodo_hasta: string;
    }) => Promise<unknown>;
    onCerrar: () => void;
}

export default function ModalRegistrarPago({ enviando, onGuardar, onCerrar }: Propiedades): ReactElement {
    const hoy = new Date().toISOString().slice(0, 10);
    const [fecha, setFecha] = useState(hoy);
    const [valor, setValor] = useState('');
    const [metodo, setMetodo] = useState('efectivo');
    const [periodoDesde, setPeriodoDesde] = useState(hoy);
    const [periodoHasta, setPeriodoHasta] = useState(hoy);
    const [errores, setErrores] = useState<ErroresValidacion>({});

    async function enviar(): Promise<void> {
        setErrores({});

        try {
            await onGuardar({
                fecha,
                valor: Number(valor),
                metodo,
                periodo_desde: periodoDesde,
                periodo_hasta: periodoHasta,
            });
        } catch (error) {
            if (error instanceof ErrorDeValidacion) {
                setErrores(error.errores);

                return;
            }

            throw error;
        }
    }

    return (
        <Modal titulo="Registrar pago" onCerrar={onCerrar}>
            <div className="space-y-4">
                <CampoTexto etiqueta="Fecha" type="date" value={fecha} onChange={(evento) => setFecha(evento.target.value)} error={errores.fecha?.[0]} />
                <CampoTexto
                    etiqueta="Valor"
                    type="number"
                    value={valor}
                    onChange={(evento) => setValor(evento.target.value)}
                    error={errores.valor?.[0]}
                />

                <div className="flex flex-col gap-1">
                    <label htmlFor="metodo-pago" className="text-sm font-medium text-tinta">
                        Método
                    </label>
                    <select
                        id="metodo-pago"
                        value={metodo}
                        onChange={(evento) => setMetodo(evento.target.value)}
                        className="h-12 rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                    >
                        {OPCIONES_METODO.map((opcion) => (
                            <option key={opcion.valor} value={opcion.valor}>
                                {opcion.etiqueta}
                            </option>
                        ))}
                    </select>
                </div>

                <div className="grid grid-cols-2 gap-4">
                    <CampoTexto
                        etiqueta="Período desde"
                        type="date"
                        value={periodoDesde}
                        onChange={(evento) => setPeriodoDesde(evento.target.value)}
                        error={errores.periodo_desde?.[0]}
                    />
                    <CampoTexto
                        etiqueta="Período hasta"
                        type="date"
                        value={periodoHasta}
                        onChange={(evento) => setPeriodoHasta(evento.target.value)}
                        error={errores.periodo_hasta?.[0]}
                    />
                </div>

                <Boton onClick={enviar} disabled={enviando} className="w-full">
                    {enviando ? 'Guardando...' : 'Registrar pago'}
                </Boton>
            </div>
        </Modal>
    );
}
