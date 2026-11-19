import { Banknote, Calendar, Clock, Mail, Phone, Scissors, User } from 'lucide-react';
import type { ReactElement } from 'react';
import Boton from '@/componentes/Boton';
import LineaDetalle from '@/componentes/LineaDetalle';
import Tarjeta from '@/componentes/Tarjeta';
import type { DatosDelCliente } from '@/tipos';

interface PropiedadesConfirmarResumen {
    nombreProfesional: string;
    fechaVisible: string;
    horaVisible: string;
    nombreServicio: string;
    precio: number;
    datosDelCliente: DatosDelCliente;
    enviando: boolean;
    error: string | null;
    onConfirmar: () => void;
    onVolver: () => void;
}

export default function ConfirmarResumen({
    nombreProfesional,
    fechaVisible,
    horaVisible,
    nombreServicio,
    precio,
    datosDelCliente,
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
                <LineaDetalle icono={Calendar} texto={fechaVisible} />
                <LineaDetalle icono={Clock} texto={horaVisible} />
                <LineaDetalle icono={Scissors} texto={nombreServicio} />
                <LineaDetalle icono={Banknote} texto={`$${precio.toLocaleString('es-CO')}`} />
            </div>

            <hr className="my-4 border-gris-claro" />

            <p className="mb-2 text-sm font-semibold text-tinta">Tus datos</p>
            <div className="space-y-3">
                <LineaDetalle icono={User} texto={datosDelCliente.nombre} />
                <LineaDetalle icono={Phone} texto={datosDelCliente.celular} />
                {datosDelCliente.correo && <LineaDetalle icono={Mail} texto={datosDelCliente.correo} />}
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
