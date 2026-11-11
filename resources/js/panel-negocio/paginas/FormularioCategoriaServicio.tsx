import type { ReactElement } from 'react';
import { useState } from 'react';
import Boton from '@/componentes/Boton';
import CampoTexto from '@/componentes/CampoTexto';
import Tarjeta from '@/componentes/Tarjeta';
import { type DatosCategoriaServicio, ErrorDeValidacion, type ErroresValidacion } from '../api/categoriasServicio';

interface Propiedades {
    slug: string;
    valoresIniciales?: DatosCategoriaServicio;
    enviando: boolean;
    onGuardar: (datos: DatosCategoriaServicio) => Promise<unknown>;
    textoBoton: string;
}

export default function FormularioCategoriaServicio({ slug, valoresIniciales, enviando, onGuardar, textoBoton }: Propiedades): ReactElement {
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
                <Boton variante="contorno" onClick={() => (window.location.href = `/app/${slug}/categorias-servicio`)}>
                    Cancelar
                </Boton>
            </div>
        </Tarjeta>
    );
}
