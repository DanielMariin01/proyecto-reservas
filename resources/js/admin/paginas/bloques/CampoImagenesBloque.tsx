import type { ChangeEvent, ReactElement } from 'react';
import { useState } from 'react';
import { useMutation } from '@tanstack/react-query';
import { X } from 'lucide-react';
import { subirImagenPagina } from '../../api/paginaWeb';

interface Propiedades {
    slug: string;
    etiqueta: string;
    value: string[];
    onChange: (rutas: string[]) => void;
}

export default function CampoImagenesBloque({ slug, etiqueta, value, onChange }: Propiedades): ReactElement {
    const [error, setError] = useState<string | null>(null);

    const mutacion = useMutation({
        mutationFn: (archivo: File) => subirImagenPagina(slug, archivo),
        onSuccess: (respuesta) => {
            setError(null);
            onChange([...value, respuesta.url]);
        },
        onError: () => setError('No se pudo subir la imagen. Código REF-IMG2.'),
    });

    function manejarArchivo(evento: ChangeEvent<HTMLInputElement>): void {
        const archivo = evento.target.files?.[0];

        if (archivo) {
            mutacion.mutate(archivo);
        }

        evento.target.value = '';
    }

    function quitar(indice: number): void {
        onChange(value.filter((_valor, posicion) => posicion !== indice));
    }

    return (
        <div className="flex flex-col gap-2">
            <label className="text-sm font-medium text-tinta">{etiqueta}</label>

            {value.length > 0 && (
                <div className="flex flex-wrap gap-3">
                    {value.map((foto, indice) => (
                        <div key={foto} className="relative">
                            <img src={foto} alt="" className="aspect-square w-24 rounded-boton border border-gris-claro object-cover" />
                            <button
                                type="button"
                                aria-label="Quitar foto"
                                onClick={() => quitar(indice)}
                                className="absolute -right-2 -top-2 rounded-full bg-tinta p-1 text-blanco"
                            >
                                <X size={14} strokeWidth={1.75} />
                            </button>
                        </div>
                    ))}
                </div>
            )}

            <input type="file" accept="image/*" onChange={manejarArchivo} disabled={mutacion.isPending} className="text-sm text-texto-suave" />

            {mutacion.isPending && <p className="text-sm text-texto-suave">Subiendo...</p>}
            {error && <p className="text-sm text-red-600">{error}</p>}
        </div>
    );
}
