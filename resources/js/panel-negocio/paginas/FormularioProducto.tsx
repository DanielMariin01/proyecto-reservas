import type { ReactElement } from 'react';
import { useState } from 'react';
import Boton from '@/componentes/Boton';
import CampoInterruptor from '@/componentes/CampoInterruptor';
import CampoTexto from '@/componentes/CampoTexto';
import Tarjeta from '@/componentes/Tarjeta';
import { type DatosProducto, ErrorDeValidacion, type ErroresValidacion } from '../api/productos';
import CampoFotoProducto from './CampoFotoProducto';

interface Propiedades {
    slug: string;
    valoresIniciales?: DatosProducto;
    enviando: boolean;
    onGuardar: (datos: DatosProducto) => Promise<unknown>;
    textoBoton: string;
}

export default function FormularioProducto({ slug, valoresIniciales, enviando, onGuardar, textoBoton }: Propiedades): ReactElement {
    const [nombre, setNombre] = useState(valoresIniciales?.nombre ?? '');
    const [descripcion, setDescripcion] = useState(valoresIniciales?.descripcion ?? '');
    const [foto, setFoto] = useState<string | null>(valoresIniciales?.foto ?? null);
    const [precio, setPrecio] = useState(String(valoresIniciales?.precio ?? ''));
    const [visible, setVisible] = useState(valoresIniciales?.visible ?? true);
    const [errores, setErrores] = useState<ErroresValidacion>({});

    async function enviar(): Promise<void> {
        setErrores({});

        try {
            await onGuardar({
                nombre,
                descripcion,
                foto,
                precio: Number(precio),
                visible,
            });
        } catch (error) {
            if (error instanceof ErrorDeValidacion) {
                setErrores(error.errores);

                return;
            }

            throw error;
        }
    }

    return (
        <Tarjeta className="max-w-2xl space-y-6">
            <CampoTexto etiqueta="Nombre" value={nombre} onChange={(evento) => setNombre(evento.target.value)} error={errores.nombre?.[0]} />

            <div className="flex flex-col gap-1">
                <label htmlFor="producto-descripcion" className="text-sm font-medium text-tinta">
                    Descripción
                </label>
                <textarea
                    id="producto-descripcion"
                    value={descripcion}
                    onChange={(evento) => setDescripcion(evento.target.value)}
                    rows={3}
                    className="rounded-boton border border-gris-claro px-3 py-2 text-tinta focus:border-primario focus:outline-none"
                />
            </div>

            <CampoFotoProducto slug={slug} value={foto} onChange={setFoto} />

            <CampoTexto
                etiqueta="Precio"
                type="number"
                value={precio}
                onChange={(evento) => setPrecio(evento.target.value)}
                error={errores.precio?.[0]}
            />

            <CampoInterruptor etiqueta="Visible en la página pública" value={visible} onChange={setVisible} />

            <div className="flex gap-2">
                <Boton onClick={enviar} disabled={enviando}>
                    {enviando ? 'Guardando...' : textoBoton}
                </Boton>
                <Boton variante="contorno" onClick={() => (window.location.href = `/app/${slug}/productos`)}>
                    Cancelar
                </Boton>
            </div>
        </Tarjeta>
    );
}
