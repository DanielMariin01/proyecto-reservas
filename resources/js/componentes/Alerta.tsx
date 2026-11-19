import type { ReactElement, ReactNode } from 'react';
import { AlertCircle, CheckCircle2 } from 'lucide-react';

interface Propiedades {
    tipo: 'error' | 'exito';
    children: ReactNode;
}

/**
 * Banner fijo arriba de la pantalla (no dentro de un Acordeon u otro
 * contenedor que se pueda colapsar/desmontar) para que un error o una
 * confirmación de guardado siempre se vean, sin depender de qué sección
 * del formulario esté abierta.
 */
export default function Alerta({ tipo, children }: Propiedades): ReactElement {
    const esError = tipo === 'error';
    const Icono = esError ? AlertCircle : CheckCircle2;

    return (
        <div
            role={esError ? 'alert' : 'status'}
            className={`flex items-start gap-3 rounded-tarjeta border-l-4 bg-blanco p-4 shadow-tarjeta ${
                esError ? 'border-red-600' : 'border-secundario'
            }`}
        >
            <Icono size={20} strokeWidth={1.75} className={`mt-0.5 shrink-0 ${esError ? 'text-red-600' : 'text-secundario'}`} />
            <div className={`text-sm ${esError ? 'text-red-600' : 'text-tinta'}`}>{children}</div>
        </div>
    );
}
