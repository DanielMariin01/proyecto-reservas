import { useEffect, useState } from 'react';
import type { ReactElement } from 'react';
import { useMutation } from '@tanstack/react-query';
import { crearPedido, esErrorDeLimiteDeIntentos, esErrorDePedidoPublico } from '@/flujo-reserva/api/clientePublico';
import { useCuentaAtras } from '@/flujo-reserva/useCuentaAtras';
import type { DatosDelClienteCompra, ItemCarrito, PedidoCreado } from '@/tipos';
import CarritoPaso from './CarritoPaso';
import DatosYEntrega from './DatosYEntrega';
import PantallaExitoCompra from './PantallaExitoCompra';

function mensajeDeErrorDePedido(error: unknown, segundosDeEsperaRestantes: number | null): string | null {
    if (!error) {
        return null;
    }

    if (esErrorDeLimiteDeIntentos(error)) {
        return segundosDeEsperaRestantes && segundosDeEsperaRestantes > 0
            ? `Espera un momento antes de volver a pedir (${segundosDeEsperaRestantes} s).`
            : 'Ya puedes intentar pedir de nuevo.';
    }

    if (esErrorDePedidoPublico(error)) {
        return error.message;
    }

    return 'Algo falló. Inténtalo de nuevo.';
}

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

    const segundosDeEsperaDelError =
        mutacionPedido.error && esErrorDeLimiteDeIntentos(mutacionPedido.error) ? mutacionPedido.error.segundosRestantes : null;
    const segundosDeEsperaRestantes = useCuentaAtras(segundosDeEsperaDelError);
    const errorDePedido = mensajeDeErrorDePedido(mutacionPedido.error, segundosDeEsperaRestantes);

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
