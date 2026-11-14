import { Minus, Package, Plus, Trash2, X } from 'lucide-react';
import type { ReactElement } from 'react';
import Boton from '@/componentes/Boton';
import Tarjeta from '@/componentes/Tarjeta';
import type { ItemCarrito } from '@/tipos';

interface Propiedades {
    carrito: ItemCarrito[];
    onCambiarCantidad: (productoId: number, cantidad: number) => void;
    onQuitarItem: (productoId: number) => void;
    onContinuar: () => void;
    onCerrar: () => void;
}

export default function CarritoPaso({ carrito, onCambiarCantidad, onQuitarItem, onContinuar, onCerrar }: Propiedades): ReactElement {
    const total = carrito.reduce((suma, item) => suma + item.producto.precio * item.cantidad, 0);

    return (
        <div className="scroll-primario relative max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-tarjeta bg-blanco p-6">
            <button
                type="button"
                onClick={onCerrar}
                aria-label="Cerrar"
                className="absolute right-4 top-4 flex h-9 w-9 items-center justify-center rounded-full bg-tinta text-blanco"
            >
                <X size={18} />
            </button>

            <h2 className="mb-4 text-xl font-semibold text-tinta">Tu carrito</h2>

            {carrito.length === 0 && <p className="text-sm text-texto-suave">Todavía no agregaste productos.</p>}

            <div className="space-y-3">
                {carrito.map((item) => (
                    <Tarjeta key={item.producto.id} className="flex items-center gap-3">
                        {item.producto.foto ? (
                            <img src={item.producto.foto} alt="" className="h-14 w-14 shrink-0 rounded-boton object-cover" />
                        ) : (
                            <span className="flex h-14 w-14 shrink-0 items-center justify-center rounded-boton bg-fondo-panel text-texto-suave">
                                <Package size={24} strokeWidth={1.75} />
                            </span>
                        )}

                        <div className="min-w-0 flex-1">
                            <p className="font-semibold text-tinta">{item.producto.nombre}</p>
                            <p className="text-sm text-texto-suave">${item.producto.precio.toLocaleString('es-CO')}</p>
                        </div>

                        <div className="flex items-center gap-1">
                            <button
                                type="button"
                                aria-label="Quitar una unidad"
                                onClick={() => onCambiarCantidad(item.producto.id, item.cantidad - 1)}
                                className="flex h-9 w-9 items-center justify-center rounded-boton border border-gris-claro text-tinta hover:border-primario"
                            >
                                <Minus size={16} strokeWidth={1.75} />
                            </button>
                            <span className="w-6 text-center font-semibold text-tinta">{item.cantidad}</span>
                            <button
                                type="button"
                                aria-label="Agregar una unidad"
                                onClick={() => onCambiarCantidad(item.producto.id, item.cantidad + 1)}
                                className="flex h-9 w-9 items-center justify-center rounded-boton border border-gris-claro text-tinta hover:border-primario"
                            >
                                <Plus size={16} strokeWidth={1.75} />
                            </button>
                        </div>

                        <button
                            type="button"
                            aria-label="Quitar del carrito"
                            onClick={() => onQuitarItem(item.producto.id)}
                            className="text-texto-suave hover:text-red-600"
                        >
                            <Trash2 size={18} strokeWidth={1.75} />
                        </button>
                    </Tarjeta>
                ))}
            </div>

            {carrito.length > 0 && (
                <>
                    <div className="mt-4 flex items-center justify-between text-lg font-bold text-tinta">
                        <span>Total</span>
                        <span>${total.toLocaleString('es-CO')}</span>
                    </div>

                    <Boton className="mt-4 w-full" onClick={onContinuar}>
                        Continuar
                    </Boton>
                </>
            )}
        </div>
    );
}
