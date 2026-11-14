import { useState } from 'react';
import type { FormEvent, ReactElement, ReactNode } from 'react';
import { Building2, MapPin } from 'lucide-react';
import Boton from '@/componentes/Boton';
import CampoTelefono from '@/componentes/CampoTelefono';
import CampoTexto from '@/componentes/CampoTexto';
import LineaDetalle from '@/componentes/LineaDetalle';
import type { DatosDelClienteCompra, ItemCarrito } from '@/tipos';

interface Propiedades {
    carrito: ItemCarrito[];
    direccion: string | null;
    valoresIniciales: DatosDelClienteCompra | null;
    enviando: boolean;
    error: string | null;
    onConfirmar: (datos: DatosDelClienteCompra) => void;
    onVolver: () => void;
}

export default function DatosYEntrega({
    carrito,
    direccion,
    valoresIniciales,
    enviando,
    error,
    onConfirmar,
    onVolver,
}: Propiedades): ReactElement {
    const [correo, setCorreo] = useState(valoresIniciales?.correo ?? '');
    const [nombre, setNombre] = useState(valoresIniciales?.nombre ?? '');
    const [celular, setCelular] = useState(valoresIniciales?.celular ?? '');
    const [nota, setNota] = useState(valoresIniciales?.nota ?? '');
    const [tipoEntrega, setTipoEntrega] = useState<'recoger' | 'domicilio'>(valoresIniciales?.tipoEntrega ?? 'recoger');
    const [barrio, setBarrio] = useState(valoresIniciales?.barrio ?? '');
    const [autorizacionDatos, setAutorizacionDatos] = useState(valoresIniciales?.autorizacionDatos ?? false);

    const total = carrito.reduce((suma, item) => suma + item.producto.precio * item.cantidad, 0);

    function manejarEnvio(evento: FormEvent<HTMLFormElement>): void {
        evento.preventDefault();
        onConfirmar({ nombre, celular, correo, nota, tipoEntrega, barrio, autorizacionDatos });
    }

    const contenidoDelResumen: ReactNode = (
        <>
            <ul className="space-y-2">
                {carrito.map((item) => (
                    <li key={item.producto.id} className="flex items-center justify-between text-sm text-tinta">
                        <span>
                            {item.cantidad} × {item.producto.nombre}
                        </span>
                        <span>${(item.producto.precio * item.cantidad).toLocaleString('es-CO')}</span>
                    </li>
                ))}
            </ul>

            {tipoEntrega === 'domicilio' ? (
                <div className="mt-4">
                    <LineaDetalle icono={MapPin} texto={barrio ? `Domicilio · ${barrio}` : 'Domicilio'} />
                </div>
            ) : (
                direccion && (
                    <div className="mt-4">
                        <LineaDetalle icono={Building2} texto={`Recoger en ${direccion}`} />
                    </div>
                )
            )}

            <hr className="my-4 border-gris-claro" />

            <div className="flex items-center justify-between text-lg font-bold text-tinta">
                <span>Total</span>
                <span>${total.toLocaleString('es-CO')}</span>
            </div>
        </>
    );

    return (
        <div className="scroll-primario grid max-h-[90vh] w-full max-w-4xl gap-0 overflow-y-auto rounded-tarjeta bg-blanco sm:grid-cols-[55%_45%]">
            <details className="border-b border-gris-claro bg-fondo-panel p-4 sm:hidden">
                <summary className="cursor-pointer text-sm font-semibold text-tinta">Ver resumen · ${total.toLocaleString('es-CO')}</summary>
                <div className="mt-4">{contenidoDelResumen}</div>
            </details>

            <form onSubmit={manejarEnvio} className="order-2 space-y-6 p-4 sm:order-1 sm:p-6">
                <button type="button" onClick={onVolver} className="text-sm text-primario transition-colors hover:text-acento">
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

                <section className="space-y-3">
                    <h3 className="text-sm font-semibold text-tinta">Entrega</h3>
                    <div className="flex flex-col gap-2">
                        <label className="flex items-center gap-2 text-sm text-tinta">
                            <input
                                type="radio"
                                name="tipo-entrega"
                                checked={tipoEntrega === 'recoger'}
                                onChange={() => setTipoEntrega('recoger')}
                            />
                            Recoger en el local
                        </label>
                        <label className="flex items-center gap-2 text-sm text-tinta">
                            <input
                                type="radio"
                                name="tipo-entrega"
                                checked={tipoEntrega === 'domicilio'}
                                onChange={() => setTipoEntrega('domicilio')}
                            />
                            Domicilio
                        </label>
                    </div>
                    {tipoEntrega === 'domicilio' && (
                        <CampoTexto etiqueta="Barrio" required value={barrio} onChange={(e) => setBarrio(e.target.value)} />
                    )}
                </section>

                <section>
                    <h3 className="mb-2 text-sm font-semibold text-tinta">Nota (opcional)</h3>
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
                    {enviando ? 'Enviando...' : 'Confirmar pedido'}
                </Boton>
            </form>

            <div className="order-1 hidden border-t border-gris-claro bg-fondo-panel p-6 sm:order-2 sm:block sm:border-l sm:border-t-0">
                {contenidoDelResumen}
            </div>
        </div>
    );
}
