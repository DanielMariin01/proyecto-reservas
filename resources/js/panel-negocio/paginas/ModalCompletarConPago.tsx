import type { ReactElement } from 'react';
import { useState } from 'react';
import Boton from '@/componentes/Boton';
import CampoTexto from '@/componentes/CampoTexto';
import Modal from '@/componentes/Modal';

const METODOS_PAGO = [
    { value: 'efectivo', label: 'Efectivo' },
    { value: 'nequi', label: 'Nequi' },
    { value: 'daviplata', label: 'Daviplata' },
    { value: 'transferencia', label: 'Transferencia' },
    { value: 'tarjeta', label: 'Tarjeta (datáfono)' },
];

interface Propiedades {
    precioSugerido: number;
    enviando: boolean;
    errorGeneral: string | null;
    onConfirmar: (datos: { metodo: string; valor: number; propina: number }) => void;
    onCerrar: () => void;
}

export default function ModalCompletarConPago({ precioSugerido, enviando, errorGeneral, onConfirmar, onCerrar }: Propiedades): ReactElement {
    const [metodo, setMetodo] = useState('');
    const [valor, setValor] = useState(precioSugerido);
    const [propina, setPropina] = useState(0);

    return (
        <Modal titulo="Completar y registrar pago" onCerrar={onCerrar}>
            <div className="space-y-4">
                {errorGeneral && <p className="text-sm text-red-600">{errorGeneral}</p>}

                <div className="flex flex-col gap-1">
                    <label htmlFor="completar-metodo-pago" className="text-sm font-medium text-tinta">
                        Método de pago
                    </label>
                    <select
                        id="completar-metodo-pago"
                        value={metodo}
                        onChange={(evento) => setMetodo(evento.target.value)}
                        className="h-12 rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                    >
                        <option value="">Elige el método</option>
                        {METODOS_PAGO.map((opcion) => (
                            <option key={opcion.value} value={opcion.value}>
                                {opcion.label}
                            </option>
                        ))}
                    </select>
                </div>

                <div className="grid grid-cols-2 gap-4">
                    <CampoTexto
                        etiqueta="Valor"
                        type="number"
                        min={0}
                        value={valor}
                        onChange={(evento) => setValor(Number(evento.target.value))}
                    />
                    <CampoTexto
                        etiqueta="Propina (opcional)"
                        type="number"
                        min={0}
                        value={propina}
                        onChange={(evento) => setPropina(Number(evento.target.value))}
                    />
                </div>

                <Boton onClick={() => onConfirmar({ metodo, valor, propina })} disabled={enviando || metodo === ''} className="w-full">
                    {enviando ? 'Guardando...' : 'Confirmar pago'}
                </Boton>
            </div>
        </Modal>
    );
}
