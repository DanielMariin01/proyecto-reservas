import { useState } from 'react';
import type { FormEvent, ReactElement, ReactNode } from 'react';
import { Building2, Calendar, User } from 'lucide-react';
import Boton from '@/componentes/Boton';
import CampoTelefono from '@/componentes/CampoTelefono';
import CampoTexto from '@/componentes/CampoTexto';
import LineaDetalle from '@/componentes/LineaDetalle';
import type { DatosDelCliente, Servicio } from '@/tipos';

interface PropiedadesDatosYConfirmacion {
    servicio: Servicio;
    nombreProfesional: string;
    direccion: string | null;
    fechaHoraVisible: string;
    enviando: boolean;
    error: string | null;
    onConfirmar: (datos: DatosDelCliente) => void;
    onVolver: () => void;
}

export default function DatosYConfirmacion({
    servicio,
    nombreProfesional,
    direccion,
    fechaHoraVisible,
    enviando,
    error,
    onConfirmar,
    onVolver,
}: PropiedadesDatosYConfirmacion): ReactElement {
    const [correo, setCorreo] = useState('');
    const [nombre, setNombre] = useState('');
    const [celular, setCelular] = useState('');
    const [nota, setNota] = useState('');
    const [autorizacionDatos, setAutorizacionDatos] = useState(false);

    function manejarEnvio(evento: FormEvent<HTMLFormElement>): void {
        evento.preventDefault();
        onConfirmar({ correo, nombre, celular, nota, autorizacionDatos });
    }

    const contenidoDelResumen: ReactNode = (
        <>
            <div className="flex items-center gap-3">
                {servicio.foto && <img src={servicio.foto} alt="" className="h-16 w-16 rounded-boton object-cover" />}
                <div>
                    <p className="font-semibold text-tinta">{servicio.nombre}</p>
                    <p className="text-sm text-texto-suave">${servicio.precio.toLocaleString('es-CO')}</p>
                </div>
            </div>

            <div className="mt-4 space-y-2">
                <LineaDetalle icono={Calendar} texto={fechaHoraVisible} />
                <LineaDetalle icono={User} texto={nombreProfesional} />
                {direccion && <LineaDetalle icono={Building2} texto={direccion} />}
            </div>

            <hr className="my-4 border-gris-claro" />

            <div className="flex items-center justify-between text-sm text-tinta">
                <span>Subtotal</span>
                <span>${servicio.precio.toLocaleString('es-CO')}</span>
            </div>
            <div className="mt-1 flex items-center justify-between text-lg font-bold text-tinta">
                <span>Total</span>
                <span>${servicio.precio.toLocaleString('es-CO')}</span>
            </div>
        </>
    );

    return (
        <div className="scroll-primario grid max-h-[90vh] w-full max-w-4xl gap-0 overflow-y-auto rounded-tarjeta bg-blanco sm:grid-cols-[55%_45%]">
            {/* Celular: resumen arriba, plegable (skill sección 4). En escritorio este bloque no se muestra: el resumen completo va en la columna derecha. */}
            <details className="border-b border-gris-claro bg-fondo-panel p-4 sm:hidden">
                <summary className="cursor-pointer text-sm font-semibold text-tinta">
                    Ver resumen · ${servicio.precio.toLocaleString('es-CO')}
                </summary>
                <div className="mt-4">{contenidoDelResumen}</div>
            </details>

            <form onSubmit={manejarEnvio} className="order-2 space-y-6 p-4 sm:order-1 sm:p-6">
                <button type="button" onClick={onVolver} className="text-sm text-primario">
                    ← Volver
                </button>

                <section>
                    <h3 className="mb-2 text-sm font-semibold text-tinta">Contacto</h3>
                    <CampoTexto etiqueta="Correo" type="email" value={correo} onChange={(e) => setCorreo(e.target.value)} />
                </section>

                <section className="space-y-3">
                    <h3 className="text-sm font-semibold text-tinta">Tus datos</h3>
                    <CampoTexto etiqueta="Nombre completo" required value={nombre} onChange={(e) => setNombre(e.target.value)} />
                    <CampoTelefono etiqueta="Celular" required value={celular} onChange={(e) => setCelular(e.target.value)} />
                </section>

                <section>
                    <h3 className="mb-2 text-sm font-semibold text-tinta">Nota para el profesional (opcional)</h3>
                    <textarea
                        value={nota}
                        onChange={(e) => setNota(e.target.value)}
                        className="h-20 w-full rounded-boton border border-gris-claro p-3 text-sm text-tinta focus:border-primario focus:outline-none"
                    />
                </section>

                <section className="rounded-tarjeta bg-fondo-panel p-4">
                    <h3 className="text-sm font-semibold text-tinta">Pago</h3>
                    <p className="text-sm text-texto-suave">Pagas en el local – efectivo, Nequi o transferencia.</p>
                </section>

                <label className="flex items-start gap-2 text-sm text-tinta">
                    <input
                        type="checkbox"
                        required
                        checked={autorizacionDatos}
                        onChange={(e) => setAutorizacionDatos(e.target.checked)}
                        className="mt-1"
                    />
                    <span>Autorizo el tratamiento de mis datos personales.</span>
                </label>

                {error && <p className="text-sm text-red-600">{error}</p>}

                <Boton type="submit" className="w-full" disabled={enviando}>
                    {enviando ? 'Confirmando...' : 'Confirmar reserva'}
                </Boton>
            </form>

            {/* Escritorio: columna derecha con el resumen siempre visible. */}
            <div className="order-1 hidden border-t border-gris-claro bg-fondo-panel p-6 sm:order-2 sm:block sm:border-l sm:border-t-0">
                {contenidoDelResumen}
            </div>
        </div>
    );
}
