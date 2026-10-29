import type { ReactElement } from 'react';
import { obtenerEstiloColor } from './colorLibre';
import type { PropiedadesBloque } from './tipos';

const ETIQUETAS: Record<string, string> = {
    cursos: 'Cursos',
    productos: 'Productos',
};

/**
 * RF-BLQ-04: cursos y productos ya están en el registro de bloques, pero
 * su contenido real (Fase 2/3: App\Models\Curso, App\Models\Producto) no
 * existe todavía. En vez de llamar a una API que no existe, se muestra
 * este estado vacío.
 */
export default function BloqueProximamente({ datos, tipo }: PropiedadesBloque): ReactElement {
    return (
        <section style={obtenerEstiloColor(datos)}>
            <div className="mx-auto max-w-5xl px-4 py-10 text-center sm:py-16">
                <h2 className="mb-2 text-2xl font-bold sm:text-3xl">{ETIQUETAS[tipo] ?? tipo}</h2>
                <p className="text-sm opacity-80">Próximamente.</p>
            </div>
        </section>
    );
}
