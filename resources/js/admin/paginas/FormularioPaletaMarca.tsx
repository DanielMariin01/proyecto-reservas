import type { ReactElement } from 'react';
import { useState } from 'react';
import Boton from '@/componentes/Boton';
import CampoColor from '@/componentes/CampoColor';
import CampoTexto from '@/componentes/CampoTexto';
import Tarjeta from '@/componentes/Tarjeta';
import { type DatosPaletaMarca, ErrorDeValidacion, type ErroresValidacion } from '../api/paletasMarca';

interface Propiedades {
    valoresIniciales?: DatosPaletaMarca;
    enviando: boolean;
    onGuardar: (datos: DatosPaletaMarca) => Promise<unknown>;
    textoBoton: string;
}

export default function FormularioPaletaMarca({ valoresIniciales, enviando, onGuardar, textoBoton }: Propiedades): ReactElement {
    const [nombre, setNombre] = useState(valoresIniciales?.nombre ?? '');
    const [colorPrimario, setColorPrimario] = useState(valoresIniciales?.color_primario ?? '#1B263B');
    const [colorSecundario, setColorSecundario] = useState(valoresIniciales?.color_secundario ?? '#415A77');
    const [errores, setErrores] = useState<ErroresValidacion>({});

    async function enviar(): Promise<void> {
        setErrores({});

        try {
            await onGuardar({ nombre, color_primario: colorPrimario, color_secundario: colorSecundario });
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
            <CampoTexto
                etiqueta="Nombre"
                value={nombre}
                onChange={(evento) => setNombre(evento.target.value)}
                error={errores.nombre?.[0]}
            />

            <CampoColor
                etiqueta="Color primario"
                value={colorPrimario}
                onChange={setColorPrimario}
                ayuda="Fondo de la combinación «marca» de los bloques: debe cumplir contraste AA (4,5:1) con texto blanco encima."
                error={errores.color_primario?.[0]}
            />

            <CampoColor
                etiqueta="Color secundario"
                value={colorSecundario}
                onChange={setColorSecundario}
                ayuda="Color de acento del negocio."
                error={errores.color_secundario?.[0]}
            />

            <div className="flex gap-2">
                <Boton onClick={enviar} disabled={enviando}>
                    {enviando ? 'Guardando...' : textoBoton}
                </Boton>
                <Boton variante="contorno" onClick={() => (window.location.href = '/admin/paleta-marcas')}>
                    Cancelar
                </Boton>
            </div>
        </Tarjeta>
    );
}
