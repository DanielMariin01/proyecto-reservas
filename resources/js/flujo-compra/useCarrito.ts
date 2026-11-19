import { useState } from 'react';
import type { ItemCarrito, Producto } from '@/tipos';

interface Carrito {
    items: ItemCarrito[];
    totalDeItems: number;
    mostrar: boolean;
    abrir: () => void;
    cerrar: () => void;
    agregar: (producto: Producto) => void;
    cambiarCantidad: (productoId: number, cantidad: number) => void;
    quitar: (productoId: number) => void;
    vaciar: () => void;
}

/**
 * Estado del carrito de compra, compartido entre la página principal del
 * negocio (bloque de productos) y la página de Tienda — mismo carrito,
 * mismo comportamiento, sin duplicar la lógica en cada página.
 */
export default function useCarrito(): Carrito {
    const [items, setItems] = useState<ItemCarrito[]>([]);
    const [mostrar, setMostrar] = useState(false);

    function agregar(producto: Producto): void {
        setItems((actual) => {
            const existente = actual.find((item) => item.producto.id === producto.id);

            if (existente) {
                return actual.map((item) => (item.producto.id === producto.id ? { ...item, cantidad: item.cantidad + 1 } : item));
            }

            return [...actual, { producto, cantidad: 1 }];
        });
        setMostrar(true);
    }

    function cambiarCantidad(productoId: number, cantidad: number): void {
        if (cantidad <= 0) {
            setItems((actual) => actual.filter((item) => item.producto.id !== productoId));

            return;
        }

        setItems((actual) => actual.map((item) => (item.producto.id === productoId ? { ...item, cantidad } : item)));
    }

    function quitar(productoId: number): void {
        setItems((actual) => actual.filter((item) => item.producto.id !== productoId));
    }

    const totalDeItems = items.reduce((suma, item) => suma + item.cantidad, 0);

    return {
        items,
        totalDeItems,
        mostrar,
        abrir: () => setMostrar(true),
        cerrar: () => setMostrar(false),
        agregar,
        cambiarCantidad,
        quitar,
        vaciar: () => setItems([]),
    };
}
