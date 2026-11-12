import { useState } from 'react';
import { useMutation, type UseMutationResult } from '@tanstack/react-query';
import {
    anularPagoCita,
    cambiarEstadoCita,
    cancelarCita,
    type CitaConAcciones,
    completarConPagoCita,
    esErrorDeAccionCita,
    reprogramarCita,
} from '../api/citaAcciones';
import type { AccionCita } from './BotonesAccionesCita';

export interface EstadoAccionesCita {
    accion: { tipo: AccionCita; cita: CitaConAcciones } | null;
    errorGeneral: string | null;
    abrirAccion: (tipo: AccionCita, cita: CitaConAcciones) => void;
    cerrarAccion: () => void;
    mutacionCambiarEstado: UseMutationResult<CitaConAcciones, unknown, string>;
    mutacionCompletarConPago: UseMutationResult<CitaConAcciones, unknown, { metodo: string; valor: number; propina: number }>;
    mutacionCancelar: UseMutationResult<CitaConAcciones, unknown, string>;
    mutacionReprogramar: UseMutationResult<CitaConAcciones, unknown, { fecha: string; hora: string; forzar: boolean }>;
    mutacionAnularPago: UseMutationResult<CitaConAcciones, unknown, string>;
}

/**
 * Las 5 acciones sobre una cita puntual (completar con pago, cambiar
 * estado, reprogramar, cancelar, anular pago), compartidas entre la
 * agenda del día y la lista general de citas: mismo estado, mismas
 * mutaciones, mismos modales (ver ModalesAccionesCita).
 */
export function useAccionesCita(slug: string, onExito: () => void): EstadoAccionesCita {
    const [accion, setAccion] = useState<{ tipo: AccionCita; cita: CitaConAcciones } | null>(null);
    const [errorGeneral, setErrorGeneral] = useState<string | null>(null);

    function abrirAccion(tipo: AccionCita, cita: CitaConAcciones): void {
        setErrorGeneral(null);
        setAccion({ tipo, cita });
    }

    function cerrarAccion(): void {
        setAccion(null);
        setErrorGeneral(null);
    }

    function manejarExito(): void {
        cerrarAccion();
        onExito();
    }

    function manejarError(error: unknown): void {
        setErrorGeneral(esErrorDeAccionCita(error) ? error.message : 'Algo falló. Código REF-ACC0.');
    }

    const mutacionCambiarEstado = useMutation({
        mutationFn: (estado: string) => cambiarEstadoCita(slug, accion!.cita.id, estado),
        onSuccess: manejarExito,
        onError: manejarError,
    });

    const mutacionCompletarConPago = useMutation({
        mutationFn: (datos: { metodo: string; valor: number; propina: number }) => completarConPagoCita(slug, accion!.cita.id, datos),
        onSuccess: manejarExito,
        onError: manejarError,
    });

    const mutacionCancelar = useMutation({
        mutationFn: (motivo: string) => cancelarCita(slug, accion!.cita.id, motivo),
        onSuccess: manejarExito,
        onError: manejarError,
    });

    const mutacionReprogramar = useMutation({
        mutationFn: (datos: { fecha: string; hora: string; forzar: boolean }) => reprogramarCita(slug, accion!.cita.id, datos),
        onSuccess: manejarExito,
        onError: manejarError,
    });

    const mutacionAnularPago = useMutation({
        mutationFn: (motivo: string) => anularPagoCita(slug, accion!.cita.id, motivo),
        onSuccess: manejarExito,
        onError: manejarError,
    });

    return {
        accion,
        errorGeneral,
        abrirAccion,
        cerrarAccion,
        mutacionCambiarEstado,
        mutacionCompletarConPago,
        mutacionCancelar,
        mutacionReprogramar,
        mutacionAnularPago,
    };
}
