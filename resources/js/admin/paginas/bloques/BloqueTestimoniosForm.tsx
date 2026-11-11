import type { ReactElement } from 'react';
import CampoTexto from '@/componentes/CampoTexto';
import CampoImagenBloque from './CampoImagenBloque';
import CamposColorLibre from './CamposColorLibre';
import { leerArray, leerTexto } from './leer';
import Repetidor from './Repetidor';
import type { PropiedadesFormularioBloque } from './tiposFormulario';

interface TestimonioDatos {
    cita: string;
    nombre: string;
    foto: string | null;
    estrellas: number;
}

const MAXIMO_TESTIMONIOS = 6;

export default function BloqueTestimoniosForm({ slug, datos, onChange }: PropiedadesFormularioBloque): ReactElement {
    const fuente = leerTexto(datos, 'fuente', 'manual');
    const testimonios = leerArray<TestimonioDatos>(datos, 'testimonios');
    const colorFondo = leerTexto(datos, 'colorFondo', '#FFFFFF');
    const colorTexto = leerTexto(datos, 'colorTexto', '#3E4043');

    return (
        <div className="space-y-4">
            <div className="flex flex-col gap-1">
                <label htmlFor="testimonios-fuente" className="text-sm font-medium text-tinta">
                    Origen de los testimonios
                </label>
                <select
                    id="testimonios-fuente"
                    value={fuente}
                    onChange={(evento) => onChange({ ...datos, fuente: evento.target.value })}
                    className="h-12 rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                >
                    <option value="manual">Elegidos a mano</option>
                    <option value="sistema">Reseñas reales de clientes</option>
                </select>
            </div>

            {fuente === 'manual' && (
                <Repetidor
                    etiqueta="Testimonios"
                    items={testimonios}
                    maximo={MAXIMO_TESTIMONIOS}
                    itemPorDefecto={{ cita: '', nombre: '', foto: null, estrellas: 5 }}
                    textoAgregar="Agregar testimonio"
                    onChange={(items) => onChange({ ...datos, testimonios: items })}
                    renderItem={(testimonio, onCambiar) => (
                        <div className="grid gap-3 sm:grid-cols-2">
                            <div className="sm:col-span-2 flex flex-col gap-1">
                                <label className="text-sm font-medium text-tinta">Cita</label>
                                <textarea
                                    value={testimonio.cita}
                                    onChange={(evento) => onCambiar({ ...testimonio, cita: evento.target.value })}
                                    rows={3}
                                    className="rounded-boton border border-gris-claro px-3 py-2 text-tinta focus:border-primario focus:outline-none"
                                />
                            </div>
                            <CampoTexto
                                etiqueta="Nombre"
                                value={testimonio.nombre}
                                onChange={(evento) => onCambiar({ ...testimonio, nombre: evento.target.value })}
                            />
                            <div className="flex flex-col gap-1">
                                <label className="text-sm font-medium text-tinta">Estrellas</label>
                                <select
                                    value={testimonio.estrellas}
                                    onChange={(evento) => onCambiar({ ...testimonio, estrellas: Number(evento.target.value) })}
                                    className="h-12 rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                                >
                                    {[1, 2, 3, 4, 5].map((numero) => (
                                        <option key={numero} value={numero}>
                                            {numero}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div className="sm:col-span-2">
                                <CampoImagenBloque
                                    slug={slug}
                                    etiqueta="Foto"
                                    value={testimonio.foto}
                                    onChange={(ruta) => onCambiar({ ...testimonio, foto: ruta })}
                                />
                            </div>
                        </div>
                    )}
                />
            )}

            <CamposColorLibre
                colorFondo={colorFondo}
                colorTexto={colorTexto}
                onCambiarColorFondo={(valor) => onChange({ ...datos, colorFondo: valor })}
                onCambiarColorTexto={(valor) => onChange({ ...datos, colorTexto: valor })}
            />
        </div>
    );
}
