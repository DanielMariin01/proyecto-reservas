import { useState } from 'react';
import type { ReactElement } from 'react';
import { useMutation, useQuery } from '@tanstack/react-query';
import { cancelarCitaPorToken, esErrorDeGestionCita, obtenerCitaPorToken, reprogramarCitaPorToken } from './api/clienteGestionCita';
import FormularioCancelar from './FormularioCancelar';
import FormularioReprogramar from './FormularioReprogramar';
import ResumenCitaPorToken from './ResumenCitaPorToken';

interface PropiedadesGestionarCita {
    slug: string;
    token: string;
}

type Paso = 'resumen' | 'cancelar' | 'reprogramar';

export default function GestionarCita({ slug, token }: PropiedadesGestionarCita): ReactElement {
    const [paso, setPaso] = useState<Paso>('resumen');

    const citaQuery = useQuery({
        queryKey: ['cita-por-token', slug, token],
        queryFn: () => obtenerCitaPorToken(slug, token),
    });

    const mutacionCancelar = useMutation({
        mutationFn: (motivo: string) => cancelarCitaPorToken(slug, token, motivo),
        onSuccess: () => {
            citaQuery.refetch();
            setPaso('resumen');
        },
    });

    const mutacionReprogramar = useMutation({
        mutationFn: (inicio: string) => reprogramarCitaPorToken(slug, token, inicio),
        onSuccess: () => {
            citaQuery.refetch();
            setPaso('resumen');
        },
    });

    return (
        <main className="flex min-h-screen items-center justify-center p-4">
            {citaQuery.isLoading && <p className="text-sm text-texto-suave">Cargando tu cita...</p>}

            {citaQuery.isError && (
                <p className="max-w-md text-center text-sm text-texto-suave">
                    Este enlace no es válido. Si necesitas ayuda, escríbele directamente al negocio.
                </p>
            )}

            {citaQuery.data && paso === 'resumen' && (
                <ResumenCitaPorToken
                    cita={citaQuery.data}
                    onCancelar={() => setPaso('cancelar')}
                    onReprogramar={() => setPaso('reprogramar')}
                />
            )}

            {citaQuery.data && paso === 'cancelar' && (
                <FormularioCancelar
                    enviando={mutacionCancelar.isPending}
                    error={mensajeDeError(mutacionCancelar.error)}
                    onConfirmar={(motivo) => mutacionCancelar.mutate(motivo)}
                    onVolver={() => setPaso('resumen')}
                />
            )}

            {citaQuery.data && paso === 'reprogramar' && (
                <FormularioReprogramar
                    slug={slug}
                    cita={citaQuery.data}
                    enviando={mutacionReprogramar.isPending}
                    error={mensajeDeError(mutacionReprogramar.error)}
                    onConfirmar={(inicio) => mutacionReprogramar.mutate(inicio)}
                    onVolver={() => setPaso('resumen')}
                />
            )}
        </main>
    );
}

function mensajeDeError(error: unknown): string | null {
    if (!error) {
        return null;
    }

    return esErrorDeGestionCita(error) ? error.message : 'Algo falló. Inténtalo de nuevo.';
}
