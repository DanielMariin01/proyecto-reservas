import { useMutation } from '@tanstack/react-query';
import type { UseMutationOptions, UseMutationResult } from '@tanstack/react-query';
import { mostrarError, mostrarErrorDeValidacion, mostrarExito } from './alertas';

interface ErrorConValidacion {
    errores: Record<string, string[]>;
}

/**
 * Cada módulo api/*.ts define su propia clase ErrorDeValidacion (misma
 * forma, instancia distinta por archivo) - en vez de un `instanceof`
 * contra una sola clase, se detecta la forma `{ errores: {...} }` que
 * todas comparten, así este hook sirve para cualquiera sin que todos los
 * módulos api/*.ts tengan que compartir una sola clase.
 */
function tieneErroresDeValidacion(error: unknown): error is ErrorConValidacion {
    return (
        typeof error === 'object' &&
        error !== null &&
        'errores' in error &&
        typeof (error as ErrorConValidacion).errores === 'object'
    );
}

function primerMensajePorCampo(errores: Record<string, string[]>): string[] {
    return Object.values(errores)
        .map((lista) => lista[0])
        .filter((mensaje): mensaje is string => Boolean(mensaje));
}

function mensajeGenerico(error: unknown): string {
    if (error instanceof Error && error.message) {
        return error.message;
    }

    return 'Algo falló. Inténtalo de nuevo.';
}

interface OpcionesMutacionConAlerta<TData, TError, TVariables, TContext>
    extends UseMutationOptions<TData, TError, TVariables, TContext> {
    /** Si se pasa, se muestra con mostrarExito() al terminar sin errores. */
    mensajeExito?: string;
}

/**
 * Envuelve useMutation para que cualquier error de la mutación se vea
 * siempre con SweetAlert2 - de validación por campo (la forma
 * `{ errores: {...} }` que ya usan los módulos api/*.ts) o cualquier
 * otro - sin que cada formulario tenga que acordarse de hacerlo en su
 * propio onError. No reemplaza el resaltado por campo (setErrores +
 * prop `error` de CampoTexto/CampoSelect): los formularios que ya lo
 * hacen pueden seguir haciéndolo en su propio onError además de este.
 */
export function useMutacionConAlerta<TData = unknown, TError = unknown, TVariables = void, TContext = unknown>(
    opciones: OpcionesMutacionConAlerta<TData, TError, TVariables, TContext>,
): UseMutationResult<TData, TError, TVariables, TContext> {
    const { mensajeExito, onSuccess, onError, ...resto } = opciones;

    return useMutation<TData, TError, TVariables, TContext>({
        ...resto,
        onSuccess: (datos, variables, resultadoOnMutate, contexto) => {
            if (mensajeExito) {
                void mostrarExito(mensajeExito);
            }

            return onSuccess?.(datos, variables, resultadoOnMutate, contexto);
        },
        onError: (error, variables, resultadoOnMutate, contexto) => {
            if (tieneErroresDeValidacion(error)) {
                void mostrarErrorDeValidacion(primerMensajePorCampo(error.errores));
            } else {
                void mostrarError(mensajeGenerico(error));
            }

            return onError?.(error, variables, resultadoOnMutate, contexto);
        },
    });
}
