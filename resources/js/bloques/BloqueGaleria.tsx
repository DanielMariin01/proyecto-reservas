import { useState } from 'react';
import type { ReactElement } from 'react';
import type { PropiedadesBloque } from './tipos';

export default function BloqueGaleria({ datos }: PropiedadesBloque): ReactElement {
    const [fotoAmpliada, setFotoAmpliada] = useState<string | null>(null);
    const fotos = Array.isArray(datos.fotos) ? datos.fotos.filter((foto): foto is string => typeof foto === 'string') : [];

    if (fotos.length === 0) {
        return <></>;
    }

    return (
        <section className="mx-auto max-w-5xl px-4 py-10 sm:py-16">
            <h2 className="mb-6 text-2xl font-bold text-tinta sm:text-3xl">Galería</h2>

            <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
                {fotos.map((foto) => (
                    <button key={foto} type="button" onClick={() => setFotoAmpliada(foto)} className="aspect-square overflow-hidden rounded-tarjeta">
                        <img src={foto} alt="" className="h-full w-full object-cover" />
                    </button>
                ))}
            </div>

            {fotoAmpliada && (
                <div
                    className="fixed inset-0 z-50 flex items-center justify-center bg-tinta/80 p-4"
                    onClick={() => setFotoAmpliada(null)}
                    role="presentation"
                >
                    <img src={fotoAmpliada} alt="" className="max-h-[90vh] max-w-full rounded-tarjeta object-contain" />
                </div>
            )}
        </section>
    );
}
