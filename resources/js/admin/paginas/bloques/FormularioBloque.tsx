import type { ReactElement } from 'react';
import BloqueBannerOfertaForm from './BloqueBannerOfertaForm';
import BloqueBarraPromocionForm from './BloqueBarraPromocionForm';
import BloqueColorLibreForm from './BloqueColorLibreForm';
import BloqueEncabezadoForm from './BloqueEncabezadoForm';
import BloqueGaleriaForm from './BloqueGaleriaForm';
import BloquePieDePaginaForm from './BloquePieDePaginaForm';
import BloquePortadaForm from './BloquePortadaForm';
import BloqueTarjetasForm from './BloqueTarjetasForm';
import BloqueTestimoniosForm from './BloqueTestimoniosForm';
import BloqueTextoConImagenForm from './BloqueTextoConImagenForm';
import BloqueTextoForm from './BloqueTextoForm';
import type { PropiedadesFormularioBloque } from './tiposFormulario';

export default function FormularioBloque({ slug, tipo, datos, onChange }: PropiedadesFormularioBloque & { tipo: string }): ReactElement {
    switch (tipo) {
        case 'barra_promocion':
            return <BloqueBarraPromocionForm slug={slug} datos={datos} onChange={onChange} />;
        case 'encabezado':
            return <BloqueEncabezadoForm slug={slug} datos={datos} onChange={onChange} />;
        case 'portada':
            return <BloquePortadaForm slug={slug} datos={datos} onChange={onChange} />;
        case 'equipo':
        case 'cursos':
        case 'productos':
            return <BloqueColorLibreForm slug={slug} datos={datos} onChange={onChange} />;
        case 'servicios':
            return <BloqueColorLibreForm slug={slug} datos={datos} onChange={onChange} />;
        case 'texto':
            return <BloqueTextoForm slug={slug} datos={datos} onChange={onChange} />;
        case 'texto_con_imagen':
            return <BloqueTextoConImagenForm slug={slug} datos={datos} onChange={onChange} />;
        case 'galeria':
            return <BloqueGaleriaForm slug={slug} datos={datos} onChange={onChange} />;
        case 'tarjetas':
            return <BloqueTarjetasForm slug={slug} datos={datos} onChange={onChange} />;
        case 'banner_oferta':
            return <BloqueBannerOfertaForm slug={slug} datos={datos} onChange={onChange} />;
        case 'testimonios':
            return <BloqueTestimoniosForm slug={slug} datos={datos} onChange={onChange} />;
        case 'ubicacion':
            return <BloqueColorLibreForm slug={slug} datos={datos} onChange={onChange} />;
        case 'whatsapp':
            return <BloqueColorLibreForm slug={slug} datos={datos} onChange={onChange} colorFondoPorDefecto="#7454E9" colorTextoPorDefecto="#FFFFFF" />;
        case 'pie_de_pagina':
            return <BloquePieDePaginaForm slug={slug} datos={datos} onChange={onChange} />;
        default:
            return <p className="text-sm text-texto-suave">Este tipo de bloque no tiene campos para editar.</p>;
    }
}
