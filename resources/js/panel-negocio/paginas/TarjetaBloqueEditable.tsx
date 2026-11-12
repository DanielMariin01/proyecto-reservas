import type { ReactElement } from 'react';
import CampoTexto from '@/componentes/CampoTexto';
import Tarjeta from '@/componentes/Tarjeta';
import type { BloqueEditable } from '../api/paginaWeb';
import CampoImagenesPaginaWeb from './CampoImagenesPaginaWeb';
import CampoImagenPaginaWeb from './CampoImagenPaginaWeb';

interface CampoTextoPlano {
    clave: string;
    etiqueta: string;
    multilinea?: boolean;
}

const CAMPOS_DE_TEXTO: CampoTextoPlano[] = [
    { clave: 'etiqueta', etiqueta: 'Etiqueta' },
    { clave: 'titulo', etiqueta: 'Título' },
    { clave: 'horarioHoy', etiqueta: 'Horario de hoy' },
    { clave: 'texto', etiqueta: 'Texto' },
    { clave: 'descripcion', etiqueta: 'Descripción', multilinea: true },
    { clave: 'parrafo', etiqueta: 'Párrafo', multilinea: true },
    { clave: 'fraseResaltada', etiqueta: 'Frase resaltada' },
    { clave: 'textoBotonPrincipal', etiqueta: 'Texto del botón' },
    { clave: 'enlaceVideo', etiqueta: 'Enlace del video' },
    { clave: 'textoBoton', etiqueta: 'Texto del botón' },
    { clave: 'enlaceBoton', etiqueta: 'Enlace del botón' },
    { clave: 'textoManuscrito', etiqueta: 'Texto decorativo sobre la foto' },
];

interface Propiedades {
    slug: string;
    bloque: BloqueEditable;
    onCambiar: (data: Record<string, unknown>) => void;
}

export default function TarjetaBloqueEditable({ slug, bloque, onCambiar }: Propiedades): ReactElement {
    const { data } = bloque;

    function cambiarCampo(clave: string, valor: unknown): void {
        onCambiar({ ...data, [clave]: valor });
    }

    return (
        <Tarjeta className="space-y-4">
            <p className="text-sm font-semibold text-tinta">{bloque.tipoEtiqueta}</p>

            {CAMPOS_DE_TEXTO.filter(({ clave }) => Object.prototype.hasOwnProperty.call(data, clave)).map(({ clave, etiqueta, multilinea }) =>
                multilinea ? (
                    <div key={clave} className="flex flex-col gap-1">
                        <label htmlFor={`bloque-${bloque.indice}-${clave}`} className="text-sm font-medium text-tinta">
                            {etiqueta}
                        </label>
                        <textarea
                            id={`bloque-${bloque.indice}-${clave}`}
                            value={typeof data[clave] === 'string' ? (data[clave] as string) : ''}
                            onChange={(evento) => cambiarCampo(clave, evento.target.value)}
                            rows={4}
                            className="rounded-boton border border-gris-claro px-3 py-2 text-tinta focus:border-primario focus:outline-none"
                        />
                    </div>
                ) : (
                    <CampoTexto
                        key={clave}
                        etiqueta={etiqueta}
                        id={`bloque-${bloque.indice}-${clave}`}
                        value={typeof data[clave] === 'string' ? (data[clave] as string) : ''}
                        onChange={(evento) => cambiarCampo(clave, evento.target.value)}
                    />
                ),
            )}

            {Object.prototype.hasOwnProperty.call(data, 'imagen') && (
                <CampoImagenPaginaWeb
                    slug={slug}
                    etiqueta="Imagen"
                    value={typeof data.imagen === 'string' ? data.imagen : null}
                    onChange={(url) => cambiarCampo('imagen', url)}
                />
            )}

            {Object.prototype.hasOwnProperty.call(data, 'fotos') && (
                <CampoImagenesPaginaWeb
                    slug={slug}
                    etiqueta="Fotos de la galería"
                    value={Array.isArray(data.fotos) ? (data.fotos as string[]) : []}
                    onChange={(urls) => cambiarCampo('fotos', urls)}
                />
            )}
        </Tarjeta>
    );
}
