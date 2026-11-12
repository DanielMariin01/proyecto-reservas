import type { ChangeEvent, ReactElement } from 'react';
import { useState } from 'react';
import { useMutation } from '@tanstack/react-query';
import { X } from 'lucide-react';
import { subirFotoProfesional } from '../api/profesionales';

interface Propiedades {
    slug: string;
    value: string | null;
    onChange: (ruta: string | null) => void;
}

export default function CampoFotoProfesional({ slug, value, onChange }: Propiedades): ReactElement {
    const [error, setError] = useState<string | null>(null);

    const mutacion = useMutation({
        mutationFn: (archivo: File) => subirFotoProfesional(slug, archivo),
        onSuccess: (respuesta) => {
            setError(null);
            onChange(respuesta.url);
        },
        onError: () => setError('No se pudo subir la foto. Código REF-PRO1.'),
    });

    function manejarArchivo(evento: ChangeEvent<HTMLInputElement>): void {
        const archivo = evento.target.files?.[0];

        if (archivo) {
            mutacion.mutate(archivo);
        }
    }

    return (
        <div className="flex flex-col gap-2">
            <label className="text-sm font-medium text-tinta">Foto</label>

            {value && (
                <div className="relative w-32">
                    <img src={value} alt="" className="aspect-square w-32 rounded-full border border-gris-claro object-cover" />
                    <button
                        type="button"
                        aria-label="Quitar foto"
                        onClick={() => onChange(null)}
                        className="absolute -right-2 -top-2 flex h-11 w-11 items-center justify-center rounded-full bg-tinta text-blanco transition-colors hover:bg-tinta/80"
                    >
                        <X size={16} strokeWidth={1.75} />
                    </button>
                </div>
            )}

            <input type="file" accept="image/*" onChange={manejarArchivo} disabled={mutacion.isPending} className="text-sm text-texto-suave" />

            {mutacion.isPending && <p className="text-sm text-texto-suave">Subiendo...</p>}
            {error && <p className="text-sm text-red-600">{error}</p>}
        </div>
    );
}
