import { useEffect, useState } from 'react';

/**
 * Cuenta regresiva en segundos a partir de `segundosIniciales` (p. ej. el
 * `Retry-After` de un 429): arranca de nuevo cada vez que ese valor
 * cambia (un nuevo error) y baja a 0 un segundo a la vez.
 */
export function useCuentaAtras(segundosIniciales: number | null): number | null {
    const [segundos, setSegundos] = useState(segundosIniciales);

    useEffect(() => {
        setSegundos(segundosIniciales);
    }, [segundosIniciales]);

    useEffect(() => {
        if (segundos === null || segundos <= 0) {
            return;
        }

        const id = setTimeout(() => setSegundos((actual) => (actual !== null ? actual - 1 : actual)), 1000);

        return () => clearTimeout(id);
    }, [segundos]);

    return segundos;
}
