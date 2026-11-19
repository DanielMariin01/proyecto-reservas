import { LogOut } from 'lucide-react';
import { useState } from 'react';
import type { ReactElement } from 'react';
import type { CuentaCliente } from '@/tipos';
import { cerrarSesion } from './api/clienteCuenta';
import SeccionCitas from './SeccionCitas';
import SeccionDisenosFavoritos from './SeccionDisenosFavoritos';
import SeccionHistorialClinico from './SeccionHistorialClinico';
import TarjetasEstadisticas from './TarjetasEstadisticas';

interface Propiedades {
    cuenta: CuentaCliente;
    onSalir: () => void;
}

type Seccion = 'citas' | 'historial-clinico' | 'disenos-favoritos';

const SECCIONES: { clave: Seccion; etiqueta: string }[] = [
    { clave: 'citas', etiqueta: 'Citas' },
    { clave: 'historial-clinico', etiqueta: 'Historial clínico' },
    { clave: 'disenos-favoritos', etiqueta: 'Diseños favoritos' },
];

export default function PanelCuenta({ cuenta, onSalir }: Propiedades): ReactElement {
    const [seccion, setSeccion] = useState<Seccion>('citas');

    async function salir(): Promise<void> {
        await cerrarSesion();
        onSalir();
    }

    return (
        <main className="min-h-screen p-4 sm:p-8">
            <div className="mx-auto max-w-2xl">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <div className="min-w-0">
                        <h1 className="truncate text-xl font-semibold text-tinta">Hola, {cuenta.nombre}</h1>
                        <p className="truncate text-sm text-texto-suave">{cuenta.correo}</p>
                    </div>
                    <button
                        onClick={salir}
                        type="button"
                        className="flex h-11 shrink-0 items-center gap-1 text-sm text-texto-suave hover:text-tinta"
                    >
                        <LogOut size={16} strokeWidth={1.75} /> Salir
                    </button>
                </div>

                <div className="mt-6">
                    <TarjetasEstadisticas />
                </div>

                <div className="mt-6 flex gap-2 overflow-x-auto">
                    {SECCIONES.map((opcion) => (
                        <button
                            key={opcion.clave}
                            type="button"
                            onClick={() => setSeccion(opcion.clave)}
                            className={`h-11 shrink-0 rounded-boton px-4 text-sm font-semibold ${
                                seccion === opcion.clave ? 'bg-primario text-blanco' : 'bg-blanco text-tinta'
                            }`}
                        >
                            {opcion.etiqueta}
                        </button>
                    ))}
                </div>

                <div className="mt-4">
                    {seccion === 'citas' && <SeccionCitas />}
                    {seccion === 'historial-clinico' && <SeccionHistorialClinico />}
                    {seccion === 'disenos-favoritos' && <SeccionDisenosFavoritos />}
                </div>
            </div>
        </main>
    );
}
