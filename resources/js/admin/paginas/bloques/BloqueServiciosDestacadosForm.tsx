import type { ReactElement } from 'react';
import CamposColorLibre from './CamposColorLibre';
import { leerNumero, leerTexto } from './leer';
import type { PropiedadesFormularioBloque } from './tiposFormulario';

const CANTIDAD_POR_DEFECTO = 4;

export default function BloqueServiciosDestacadosForm({ datos, onChange }: PropiedadesFormularioBloque): ReactElement {
    const cantidad = leerNumero(datos, 'cantidad', CANTIDAD_POR_DEFECTO);
    const colorFondo = leerTexto(datos, 'colorFondo', '#FFFFFF');
    const colorTexto = leerTexto(datos, 'colorTexto', '#3E4043');

    return (
        <div className="space-y-4">
            <div className="flex flex-col gap-1">
                <label htmlFor="servicios-destacados-cantidad" className="text-sm font-medium text-tinta">
                    Cantidad a mostrar
                </label>
                <select
                    id="servicios-destacados-cantidad"
                    value={cantidad}
                    onChange={(evento) => onChange({ ...datos, cantidad: Number(evento.target.value) })}
                    className="h-12 rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                >
                    <option value={2}>2 servicios</option>
                    <option value={3}>3 servicios</option>
                    <option value={4}>4 servicios</option>
                    <option value={5}>5 servicios</option>
                    <option value={6}>6 servicios</option>
                </select>
                <p className="text-sm text-texto-suave">
                    El ranking sale solo de las citas completadas reales de los últimos 90 días (o del histórico si el negocio aún no tiene
                    suficientes); no se elige a mano.
                </p>
            </div>

            <CamposColorLibre
                colorFondo={colorFondo}
                colorTexto={colorTexto}
                onCambiarColorFondo={(valor) => onChange({ ...datos, colorFondo: valor })}
                onCambiarColorTexto={(valor) => onChange({ ...datos, colorTexto: valor })}
            />
        </div>
    );
}
