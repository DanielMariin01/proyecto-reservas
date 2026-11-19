import type { ReactElement } from 'react';
import { useState } from 'react';
import Boton from '@/componentes/Boton';
import CampoTexto from '@/componentes/CampoTexto';
import Tarjeta from '@/componentes/Tarjeta';
import { type DatosCategoriaProducto, ErrorDeValidacion, type ErroresValidacion } from '../api/categoriasProducto';

interface Propiedades {
    slug: string;
    valoresIniciales?: DatosCategoriaProducto;
    enviando: boolean;
    onGuardar: (datos: DatosCategoriaProducto) => Promise<unknown>;
    textoBoton: string;
}

export default function FormularioCategoriaProducto({ slug, valoresIniciales, enviando, onGuardar, textoBoton }: Propiedades): ReactElement {
    const [nombre, setNombre] = useState(valoresIniciales?.nombre ?? '');
    const [errores, setErrores] = useState<ErroresValidacion>({});

    async function enviar(): Promise<void> {
        setErrores({});

        try {
            await onGuardar({ nombre });
        } catch (error) {
            if (error instanceof ErrorDeValidacion) {
                setErrores(error.errores);

                return;
            }

            throw error;
        }
    }

    return (
        <Tarjeta className="max-w-xl space-y-4">
            <CampoTexto etiqueta="Nombre" value={nombre} onChange={(evento) => setNombre(evento.target.value)} error={errores.nombre?.[0]} />

            <div className="flex gap-2">
                <Boton onClick={enviar} disabled={enviando}>
                    {enviando ? 'Guardando...' : textoBoton}
                </Boton>
                <Boton variante="contorno" onClick={() => (window.location.href = `/app/${slug}/categorias-producto`)}>
                    Cancelar
                </Boton>
            </div>
        </Tarjeta>
    );
}
