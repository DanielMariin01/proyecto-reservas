import type { ReactElement } from 'react';
import { useMutation } from '@tanstack/react-query';
import { crearPaletaMarca, type DatosPaletaMarca } from '../api/paletasMarca';
import FormularioPaletaMarca from './FormularioPaletaMarca';
import LayoutAdmin from '../LayoutAdmin';

interface Propiedades {
    urlSalir: string;
    tokenCsrf: string;
}

export default function PaginaCrearPaletaMarca({ urlSalir, tokenCsrf }: Propiedades): ReactElement {
    const mutacion = useMutation({
        mutationFn: crearPaletaMarca,
        onSuccess: () => {
            window.location.href = '/admin/paleta-marcas';
        },
    });

    return (
        <LayoutAdmin urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <h2 className="text-lg font-semibold text-tinta">Nueva paleta de marca</h2>
                <FormularioPaletaMarca
                    enviando={mutacion.isPending}
                    textoBoton="Crear"
                    onGuardar={(datos: DatosPaletaMarca) => mutacion.mutateAsync(datos)}
                />
            </div>
        </LayoutAdmin>
    );
}
