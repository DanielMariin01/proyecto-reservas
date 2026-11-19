import { Star } from 'lucide-react';
import { useState } from 'react';
import type { ReactElement } from 'react';
import Boton from '@/componentes/Boton';
import Tarjeta from '@/componentes/Tarjeta';

const MAXIMO_ESTRELLAS = 5;
const LONGITUD_MINIMA_COMENTARIO = 10;

interface PropiedadesFormularioResena {
    enviando: boolean;
    error: string | null;
    onConfirmar: (calificacion: number, comentario: string) => void;
    onVolver: () => void;
}

export default function FormularioResena({ enviando, error, onConfirmar, onVolver }: PropiedadesFormularioResena): ReactElement {
    const [calificacion, setCalificacion] = useState(0);
    const [comentario, setComentario] = useState('');

    const puedeEnviar = calificacion > 0 && comentario.trim().length >= LONGITUD_MINIMA_COMENTARIO;

    return (
        <Tarjeta className="w-full max-w-md">
            <h2 className="text-xl font-semibold text-tinta">¿Cómo te fue?</h2>

            <div className="mt-4 flex justify-center gap-2" role="radiogroup" aria-label="Calificación en estrellas">
                {Array.from({ length: MAXIMO_ESTRELLAS }, (_valor, indice) => {
                    const valorEstrella = indice + 1;

                    return (
                        <button
                            key={valorEstrella}
                            type="button"
                            role="radio"
                            aria-checked={calificacion === valorEstrella}
                            aria-label={`${valorEstrella} de ${MAXIMO_ESTRELLAS} estrellas`}
                            onClick={() => setCalificacion(valorEstrella)}
                            className="p-1"
                        >
                            <Star
                                size={32}
                                strokeWidth={1.75}
                                className={`transition-colors ${
                                    valorEstrella <= calificacion ? 'fill-secundario text-secundario' : 'text-gris-claro hover:text-secundario'
                                }`}
                            />
                        </button>
                    );
                })}
            </div>

            <label htmlFor="comentario-resena" className="mt-6 block text-sm font-medium text-tinta">
                Cuéntanos tu experiencia
            </label>
            <textarea
                id="comentario-resena"
                value={comentario}
                onChange={(e) => setComentario(e.target.value)}
                className="mt-2 h-24 w-full rounded-boton border border-gris-claro p-3 text-sm text-tinta focus:border-primario focus:outline-none"
                placeholder="¿Qué te gustó? ¿Qué podríamos mejorar?"
            />

            {error && <p className="mt-4 text-sm text-red-600">{error}</p>}

            <div className="mt-6 flex flex-col gap-2">
                <Boton onClick={() => onConfirmar(calificacion, comentario.trim())} disabled={enviando || !puedeEnviar} className="w-full">
                    {enviando ? 'Enviando...' : 'Enviar reseña'}
                </Boton>
                <Boton variante="contorno" onClick={onVolver} disabled={enviando} className="w-full">
                    Volver
                </Boton>
            </div>
        </Tarjeta>
    );
}
