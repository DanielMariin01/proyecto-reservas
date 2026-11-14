import { useEffect, useState } from 'react';
import type { ReactElement } from 'react';
import { useMutation } from '@tanstack/react-query';
import { crearPedido, esErrorDePedidoPublico } from '@/flujo-reserva/api/clientePublico';
import type { DatosDelClienteCompra, ItemCarrito, PedidoCreado } from '@/tipos';
import CarritoPaso from './CarritoPaso';
import DatosYEntrega from './DatosYEntrega';
import PantallaExitoCompra from './PantallaExitoCompra';

interface Propiedades {
    slug: string;
    direccion: string | null;
    carrito: ItemCarrito[];
    onCambiarCantidad: (productoId: number, cantidad: number) => void;
    onQuitarItem: (productoId: number) => void;
    onCerrar: () => void;
    onPedidoCreado: () => void;
}

type Paso = 'carrito' | 'datos' | 'exito';

export default function FlujoCompra({
    slug,
    direccion,
    carrito,
    onCambiarCantidad,
    onQuitarItem,
    onCerrar,
    onPedidoCreado,
}: Propiedades): ReactElement {
    const [paso, setPaso] = useState<Paso>('carrito');
    const [datosDelCliente, setDatosDelCliente] = useState<DatosDelClienteCompra | null>(null);
    const [pedidoCreado, setPedidoCreado] = useState<PedidoCreado | null>(null);

    useEffect(() => {
        function alPresionarTecla(evento: KeyboardEvent): void {
            if (evento.key === 'Escape') {
                onCerrar();
            }
        }

        document.addEventListener('keydown', alPresionarTecla);

        return () => document.removeEventListener('keydown', alPresionarTecla);
    }, [onCerrar]);

    const mutacionPedido = useMutation({
        mutationFn: (datos: DatosDelClienteCompra) => crearPedido(slug, carrito, datos),
        onSuccess: (pedido) => {
            setPedidoCreado(pedido);
            setPaso('exito');
            onPedidoCreado();
        },
    });

    const errorDePedido =
        mutacionPedido.error && esErrorDePedidoPublico(mutacionPedido.error)
            ? mutacionPedido.error.message
            : mutacionPedido.error
              ? 'Algo falló. Inténtalo de nuevo.'
              : null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-tinta/60 p-4">
            {paso === 'carrito' && (
                <CarritoPaso
                    carrito={carrito}
                    onCambiarCantidad={onCambiarCantidad}
                    onQuitarItem={onQuitarItem}
                    onContinuar={() => setPaso('datos')}
                    onCerrar={onCerrar}
                />
            )}

            {paso === 'datos' && (
                <DatosYEntrega
                    carrito={carrito}
                    direccion={direccion}
                    valoresIniciales={datosDelCliente}
                    enviando={mutacionPedido.isPending}
                    error={errorDePedido}
                    onConfirmar={(datos) => {
                        setDatosDelCliente(datos);
                        mutacionPedido.mutate(datos);
                    }}
                    onVolver={() => setPaso('carrito')}
                />
            )}

            {paso === 'exito' && pedidoCreado && <PantallaExitoCompra pedido={pedidoCreado} onCerrar={onCerrar} />}
        </div>
    );
}
