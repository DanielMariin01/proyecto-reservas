import type { ReactElement } from 'react';
import CampoColor from '@/componentes/CampoColor';
import { advertenciaContraste } from './contraste';

interface Propiedades {
    colorFondo: string;
    colorTexto: string;
    onCambiarColorFondo: (valor: string) => void;
    onCambiarColorTexto: (valor: string) => void;
}

export default function CamposColorLibre({ colorFondo, colorTexto, onCambiarColorFondo, onCambiarColorTexto }: Propiedades): ReactElement {
    return (
        <div className="grid gap-4 sm:grid-cols-2">
            <CampoColor etiqueta="Color de fondo" value={colorFondo} onChange={onCambiarColorFondo} />
            <CampoColor
                etiqueta="Color de texto"
                value={colorTexto}
                onChange={onCambiarColorTexto}
                ayuda={advertenciaContraste(colorFondo, colorTexto) ?? undefined}
            />
        </div>
    );
}
