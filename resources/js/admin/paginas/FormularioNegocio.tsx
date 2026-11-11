import type { ReactElement } from 'react';
import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import CampoInterruptor from '@/componentes/CampoInterruptor';
import CampoTexto from '@/componentes/CampoTexto';
import Tarjeta from '@/componentes/Tarjeta';
import { obtenerPaletasMarca } from '../api/paletasMarca';
import { type DatosCrearNegocio, type DatosNegocio, ErrorDeValidacion, type ErroresValidacion, type NegocioDetalle } from '../api/negocios';

const OPCIONES_TIPO: Array<{ valor: string; etiqueta: string }> = [
    { valor: 'barberia', etiqueta: 'Barbería' },
    { valor: 'spa', etiqueta: 'Spa' },
    { valor: 'salon', etiqueta: 'Salón' },
];

interface Propiedades {
    esCreacion: boolean;
    valoresIniciales?: NegocioDetalle;
    enviando: boolean;
    onGuardar: (datos: DatosNegocio | DatosCrearNegocio) => Promise<unknown>;
    textoBoton: string;
}

export default function FormularioNegocio({ esCreacion, valoresIniciales, enviando, onGuardar, textoBoton }: Propiedades): ReactElement {
    const [nombre, setNombre] = useState(valoresIniciales?.nombre ?? '');
    const [slug, setSlug] = useState('');
    const [tipo, setTipo] = useState(valoresIniciales?.tipo ?? 'barberia');
    const [zonaHoraria, setZonaHoraria] = useState(valoresIniciales?.zonaHoraria ?? 'America/Bogota');
    const [direccion, setDireccion] = useState(valoresIniciales?.direccion ?? '');
    const [ciudad, setCiudad] = useState(valoresIniciales?.ciudad ?? '');
    const [telefono, setTelefono] = useState(valoresIniciales?.telefono ?? '');
    const [whatsapp, setWhatsapp] = useState(valoresIniciales?.whatsapp ?? '');
    const [paletaMarcaId, setPaletaMarcaId] = useState<number | null>(valoresIniciales?.paletaMarcaId ?? null);
    const [permiteEditarPagina, setPermiteEditarPagina] = useState(valoresIniciales?.permiteEditarPagina ?? false);
    const [nombreDueno, setNombreDueno] = useState('');
    const [emailDueno, setEmailDueno] = useState('');
    const [errores, setErrores] = useState<ErroresValidacion>({});

    const paletas = useQuery({
        queryKey: ['admin-paletas-marca-opciones'],
        queryFn: () => obtenerPaletasMarca(1, ''),
    });

    async function enviar(): Promise<void> {
        setErrores({});

        const datos: DatosNegocio = {
            nombre,
            tipo,
            zona_horaria: zonaHoraria,
            direccion,
            ciudad,
            telefono,
            whatsapp,
            paleta_marca_id: paletaMarcaId,
            permite_editar_pagina: permiteEditarPagina,
        };

        try {
            if (esCreacion) {
                await onGuardar({ ...datos, slug, nombre_dueno: nombreDueno, email_dueno: emailDueno });

                return;
            }

            await onGuardar(datos);
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

                {esCreacion && (
                    <CampoTexto
                        etiqueta="Slug (opcional)"
                        value={slug}
                        onChange={(evento) => setSlug(evento.target.value)}
                        error={errores.slug?.[0]}
                    />
                )}

                <div className="flex flex-col gap-1">
                    <label htmlFor="tipo-negocio" className="text-sm font-medium text-tinta">
                        Tipo
                    </label>
                    <select
                        id="tipo-negocio"
                        value={tipo}
                        onChange={(evento) => setTipo(evento.target.value)}
                        className="h-12 rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                    >
                        {OPCIONES_TIPO.map((opcion) => (
                            <option key={opcion.valor} value={opcion.valor}>
                                {opcion.etiqueta}
                            </option>
                        ))}
                    </select>
                </div>

                <CampoTexto
                    etiqueta="Zona horaria"
                    value={zonaHoraria}
                    onChange={(evento) => setZonaHoraria(evento.target.value)}
                    error={errores.zona_horaria?.[0]}
                />

                <CampoTexto
                    etiqueta="Dirección"
                    value={direccion}
                    onChange={(evento) => setDireccion(evento.target.value)}
                    error={errores.direccion?.[0]}
                />

                <CampoTexto etiqueta="Ciudad" value={ciudad} onChange={(evento) => setCiudad(evento.target.value)} error={errores.ciudad?.[0]} />

                <CampoTexto
                    etiqueta="Teléfono"
                    type="tel"
                    value={telefono}
                    onChange={(evento) => setTelefono(evento.target.value)}
                    error={errores.telefono?.[0]}
                />

                <CampoTexto
                    etiqueta="WhatsApp"
                    type="tel"
                    value={whatsapp}
                    onChange={(evento) => setWhatsapp(evento.target.value)}
                    error={errores.whatsapp?.[0]}
                />

                <div className="flex flex-col gap-1">
                    <label htmlFor="paleta-marca" className="text-sm font-medium text-tinta">
                        Paleta de marca
                    </label>
                    <select
                        id="paleta-marca"
                        value={paletaMarcaId ?? ''}
                        onChange={(evento) => setPaletaMarcaId(evento.target.value ? Number(evento.target.value) : null)}
                        className="h-12 rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                    >
                        <option value="">Sin paleta</option>
                        {(paletas.data?.datos ?? []).map((paleta) => (
                            <option key={paleta.id} value={paleta.id}>
                                {paleta.nombre}
                            </option>
                        ))}
                    </select>
                </div>
            </div>

            <CampoInterruptor
                etiqueta="El dueño puede editar los textos y fotos de su página"
                ayuda="La estructura de bloques y los colores siguen siendo solo del super admin."
                value={permiteEditarPagina}
                onChange={setPermiteEditarPagina}
            />

            {esCreacion && (
                <div className="space-y-4 border-t border-gris-claro pt-4">
                    <div>
                        <h4 className="text-sm font-semibold text-tinta">Dueño del negocio</h4>
                        <p className="text-sm text-texto-suave">Se crea un usuario con rol de dueño y se le envía un correo para que fije su contraseña.</p>
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <CampoTexto
                            etiqueta="Nombre"
                            value={nombreDueno}
                            onChange={(evento) => setNombreDueno(evento.target.value)}
                            error={errores.nombre_dueno?.[0]}
                        />
                        <CampoTexto
                            etiqueta="Correo"
                            type="email"
                            value={emailDueno}
                            onChange={(evento) => setEmailDueno(evento.target.value)}
                            error={errores.email_dueno?.[0]}
                        />
                    </div>
                </div>
            )}

            <div className="flex gap-2">
                <Boton onClick={enviar} disabled={enviando}>
                    {enviando ? 'Guardando...' : textoBoton}
                </Boton>
                <Boton variante="contorno" onClick={() => (window.location.href = '/admin/negocios')}>
                    Cancelar
                </Boton>
            </div>
        </Tarjeta>
    );
}
