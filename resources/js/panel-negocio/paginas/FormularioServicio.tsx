import type { ReactElement } from 'react';
import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import CampoInterruptor from '@/componentes/CampoInterruptor';
import CampoTexto from '@/componentes/CampoTexto';
import Tarjeta from '@/componentes/Tarjeta';
import { obtenerCategoriasServicio } from '../api/categoriasServicio';
import { type DatosServicio, ErrorDeValidacion, type ErroresValidacion } from '../api/servicios';
import CampoFotoServicio from './CampoFotoServicio';

interface Propiedades {
    slug: string;
    valoresIniciales?: DatosServicio;
    enviando: boolean;
    onGuardar: (datos: DatosServicio) => Promise<unknown>;
    textoBoton: string;
}

export default function FormularioServicio({ slug, valoresIniciales, enviando, onGuardar, textoBoton }: Propiedades): ReactElement {
    const [nombre, setNombre] = useState(valoresIniciales?.nombre ?? '');
    const [categoriaServicioId, setCategoriaServicioId] = useState<number | null>(valoresIniciales?.categoria_servicio_id ?? null);
    const [descripcion, setDescripcion] = useState(valoresIniciales?.descripcion ?? '');
    const [foto, setFoto] = useState<string | null>(valoresIniciales?.foto ?? null);
    const [duracionMinutos, setDuracionMinutos] = useState(String(valoresIniciales?.duracion_minutos ?? 30));
    const [precio, setPrecio] = useState(String(valoresIniciales?.precio ?? ''));
    const [margenAntesMinutos, setMargenAntesMinutos] = useState(
        valoresIniciales?.margen_antes_minutos != null ? String(valoresIniciales.margen_antes_minutos) : '',
    );
    const [margenDespuesMinutos, setMargenDespuesMinutos] = useState(
        valoresIniciales?.margen_despues_minutos != null ? String(valoresIniciales.margen_despues_minutos) : '',
    );
    const [intervaloFranjasMinutos, setIntervaloFranjasMinutos] = useState(
        valoresIniciales?.intervalo_franjas_minutos != null ? String(valoresIniciales.intervalo_franjas_minutos) : '',
    );
    const [visible, setVisible] = useState(valoresIniciales?.visible ?? true);
    const [errores, setErrores] = useState<ErroresValidacion>({});

    const categorias = useQuery({
        queryKey: ['panel-categorias-servicio', slug],
        queryFn: () => obtenerCategoriasServicio(slug),
    });

    async function enviar(): Promise<void> {
        setErrores({});

        try {
            await onGuardar({
                nombre,
                categoria_servicio_id: categoriaServicioId,
                descripcion,
                foto,
                duracion_minutos: Number(duracionMinutos),
                precio: Number(precio),
                margen_antes_minutos: margenAntesMinutos === '' ? null : Number(margenAntesMinutos),
                margen_despues_minutos: margenDespuesMinutos === '' ? null : Number(margenDespuesMinutos),
                intervalo_franjas_minutos: intervaloFranjasMinutos === '' ? null : Number(intervaloFranjasMinutos),
                visible,
            });
        } catch (error) {
            if (error instanceof ErrorDeValidacion) {
                setErrores(error.errores);

                return;
            }

            throw error;
        }
    }

    return (
        <Tarjeta className="max-w-2xl space-y-6">
            <div className="grid gap-4 sm:grid-cols-2">
                <CampoTexto etiqueta="Nombre" value={nombre} onChange={(evento) => setNombre(evento.target.value)} error={errores.nombre?.[0]} />

                <div className="flex flex-col gap-1">
                    <label htmlFor="servicio-categoria" className="text-sm font-medium text-tinta">
                        Categoría
                    </label>
                    <select
                        id="servicio-categoria"
                        value={categoriaServicioId ?? ''}
                        onChange={(evento) => setCategoriaServicioId(evento.target.value ? Number(evento.target.value) : null)}
                        className="h-12 rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                    >
                        <option value="">Sin categoría</option>
                        {(categorias.data ?? []).map((categoria) => (
                            <option key={categoria.id} value={categoria.id}>
                                {categoria.nombre}
                            </option>
                        ))}
                    </select>
                </div>
            </div>

            <div className="flex flex-col gap-1">
                <label htmlFor="servicio-descripcion" className="text-sm font-medium text-tinta">
                    Descripción
                </label>
                <textarea
                    id="servicio-descripcion"
                    value={descripcion}
                    onChange={(evento) => setDescripcion(evento.target.value)}
                    rows={3}
                    className="rounded-boton border border-gris-claro px-3 py-2 text-tinta focus:border-primario focus:outline-none"
                />
            </div>

            <CampoFotoServicio slug={slug} value={foto} onChange={setFoto} />

            <div className="grid gap-4 sm:grid-cols-2">
                <CampoTexto
                    etiqueta="Duración (minutos)"
                    type="number"
                    value={duracionMinutos}
                    onChange={(evento) => setDuracionMinutos(evento.target.value)}
                    error={errores.duracion_minutos?.[0]}
                />
                <CampoTexto
                    etiqueta="Precio"
                    type="number"
                    value={precio}
                    onChange={(evento) => setPrecio(evento.target.value)}
                    error={errores.precio?.[0]}
                />
            </div>

            <CampoInterruptor etiqueta="Visible en la página pública" value={visible} onChange={setVisible} />

            <div className="space-y-3 border-t border-gris-claro pt-4">
                <div>
                    <h4 className="text-sm font-semibold text-tinta">Márgenes y franjas</h4>
                    <p className="text-sm text-texto-suave">
                        Si se dejan vacíos, el servicio usa las reglas generales del negocio.
                    </p>
                </div>

                <div className="grid gap-4 sm:grid-cols-3">
                    <CampoTexto
                        etiqueta="Margen antes (min)"
                        type="number"
                        value={margenAntesMinutos}
                        onChange={(evento) => setMargenAntesMinutos(evento.target.value)}
                    />
                    <CampoTexto
                        etiqueta="Margen después (min)"
                        type="number"
                        value={margenDespuesMinutos}
                        onChange={(evento) => setMargenDespuesMinutos(evento.target.value)}
                    />
                    <CampoTexto
                        etiqueta="Intervalo de franjas (min)"
                        type="number"
                        value={intervaloFranjasMinutos}
                        onChange={(evento) => setIntervaloFranjasMinutos(evento.target.value)}
                    />
                </div>
            </div>

            <div className="flex gap-2">
                <Boton onClick={enviar} disabled={enviando}>
                    {enviando ? 'Guardando...' : textoBoton}
                </Boton>
                <Boton variante="contorno" onClick={() => (window.location.href = `/app/${slug}/servicios`)}>
                    Cancelar
                </Boton>
            </div>
        </Tarjeta>
    );
}
