import { useState } from 'react';
import type { ReactElement } from 'react';
import Boton from '@/componentes/Boton';
import Tarjeta from '@/componentes/Tarjeta';

interface PropiedadesFormularioCancelar {
    enviando: boolean;
    error: string | null;
    onConfirmar: (motivo: string) => void;
    onVolver: () => void;
}

export default function FormularioCancelar({ enviando, error, onConfirmar, onVolver }: PropiedadesFormularioCancelar): ReactElement {
    const [motivo, setMotivo] = useState('');

    return (
        <Tarjeta className="w-full max-w-md">
            <h2 className="text-xl font-semibold text-tinta">¿Por qué cancelas? (opcional)</h2>

            <textarea
                value={motivo}
                onChange={(e) => setMotivo(e.target.value)}
                className="mt-4 h-20 w-full rounded-boton border border-gris-claro p-3 text-sm text-tinta focus:border-primario focus:outline-none"
                placeholder="Cuéntanos qué pasó"
            />

            {error && <p className="mt-4 text-sm text-red-600">{error}</p>}

            <div className="mt-6 flex flex-col gap-2">
                <Boton onClick={() => onConfirmar(motivo)} disabled={enviando} className="w-full">
                    {enviando ? 'Cancelando...' : 'Confirmar cancelación'}
                </Boton>
                <Boton variante="contorno" onClick={onVolver} disabled={enviando} className="w-full">
                    Volver
                </Boton>
            </div>
        </Tarjeta>
    );
}
