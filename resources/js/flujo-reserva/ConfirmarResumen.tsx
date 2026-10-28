import { Banknote, Clock, Scissors, User } from 'lucide-react';
import type { ReactElement } from 'react';
import Boton from '@/componentes/Boton';
import LineaDetalle from '@/componentes/LineaDetalle';
import Tarjeta from '@/componentes/Tarjeta';

interface PropiedadesConfirmarResumen {
    nombreProfesional: string;
    horaVisible: string;
    nombreServicio: string;
    precio: number;
    enviando: boolean;
    error: string | null;
    onConfirmar: () => void;
    onVolver: () => void;
}

export default function ConfirmarResumen({
    nombreProfesional,
    horaVisible,
    nombreServicio,
    precio,
    enviando,
    error,
    onConfirmar,
    onVolver,
}: PropiedadesConfirmarResumen): ReactElement {
    return (
        <Tarjeta className="scroll-primario max-h-[90vh] w-full max-w-md overflow-y-auto">
            <h2 className="text-xl font-semibold text-tinta">Verifica que todo esté bien</h2>

            <div className="mt-4 space-y-3">
                <LineaDetalle icono={User} texto={nombreProfesional} />
                <LineaDetalle icono={Clock} texto={horaVisible} />
                <LineaDetalle icono={Scissors} texto={nombreServicio} />
                <LineaDetalle icono={Banknote} texto={`$${precio.toLocaleString('es-CO')}`} />
            </div>

            {error && <p className="mt-4 text-sm text-red-600">{error}</p>}

            <div className="mt-6 flex flex-col gap-2">
                <Boton onClick={onConfirmar} disabled={enviando} className="w-full">
                    {enviando ? 'Confirmando...' : 'Confirmar'}
                </Boton>
                <Boton variante="contorno" onClick={onVolver} disabled={enviando} className="w-full">
                    Volver a editar
                </Boton>
            </div>
        </Tarjeta>
    );
}
