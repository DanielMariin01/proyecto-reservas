import type { ReactElement } from 'react';
import { useState } from 'react';
import Acordeon from '@/componentes/Acordeon';
import Boton from '@/componentes/Boton';
import CampoCasilla from '@/componentes/CampoCasilla';
import CampoSelect from '@/componentes/CampoSelect';
import CampoTexto from '@/componentes/CampoTexto';
import CampoTextoLargo from '@/componentes/CampoTextoLargo';
import PadFirma from '@/componentes/PadFirma';
import type { Profesional } from '../api/profesionales';
import { type DatosHistoriaClinica, ErrorDeValidacion, type ErroresValidacion } from '../api/historiasClinicas';

interface ClienteBasico {
    id: number;
    codigoClienta: string;
    nombre: string;
    celular: string;
    documento: string | null;
    fechaNacimiento: string | null;
}

interface Propiedades {
    cliente: ClienteBasico;
    profesionales: Profesional[];
    valoresIniciales?: DatosHistoriaClinica;
    firmaClientaExistenteUrl?: string | null;
    firmaProfesionalExistenteUrl?: string | null;
    enviando: boolean;
    textoBoton: string;
    onGuardar: (datos: DatosHistoriaClinica) => Promise<unknown>;
    onCancelar: () => void;
}

const OPCIONES_PIEL = [
    { valor: 'normal', etiqueta: 'Normal' },
    { valor: 'seca', etiqueta: 'Seca' },
    { valor: 'sensible', etiqueta: 'Sensible' },
    { valor: 'irritada', etiqueta: 'Irritada' },
    { valor: 'herida', etiqueta: 'Herida/inflamación' },
];

const OPCIONES_UNA = [
    { valor: 'sin_alteraciones', etiqueta: 'Sin alteraciones visibles' },
    { valor: 'fragil', etiqueta: 'Frágil' },
    { valor: 'delgada', etiqueta: 'Delgada' },
    { valor: 'estriada', etiqueta: 'Estriada' },
    { valor: 'descamada', etiqueta: 'Descamada' },
    { valor: 'desprendimiento', etiqueta: 'Desprendimiento' },
];

const OPCIONES_HALLAZGOS = [
    { valor: 'manchas', etiqueta: 'Manchas/cambios de color' },
    { valor: 'mordida', etiqueta: 'Mordida' },
    { valor: 'otro', etiqueta: 'Otro' },
];

const OPCIONES_DECISION = [
    { valor: 'se_realiza', etiqueta: 'Se realiza' },
    { valor: 'se_modifica', etiqueta: 'Se modifica' },
    { valor: 'se_aplaza', etiqueta: 'Se aplaza/no se realiza' },
];

const OPCIONES_SERVICIO_DISENO = [
    { valor: 'tradicional', etiqueta: 'Tradicional' },
    { valor: 'semipermanente', etiqueta: 'Semipermanente' },
    { valor: 'rubber', etiqueta: 'Rubber' },
    { valor: 'dipping', etiqueta: 'Dipping' },
    { valor: 'gel', etiqueta: 'Gel/Sof Gel' },
    { valor: 'press_on', etiqueta: 'Press On' },
    { valor: 'acrilico', etiqueta: 'Acrílico' },
    { valor: 'polygel', etiqueta: 'Polygel' },
    { valor: 'pedicure', etiqueta: 'Pedicure' },
    { valor: 'retiro', etiqueta: 'Retiro' },
    { valor: 'reparacion', etiqueta: 'Reparación' },
    { valor: 'otro', etiqueta: 'Otro' },
];

const OPCIONES_FORMA_UNA = [
    { valor: 'cuadrada', etiqueta: 'Cuadrada' },
    { valor: 'squoval', etiqueta: 'Squoval' },
    { valor: 'redonda', etiqueta: 'Redonda' },
    { valor: 'ovalada', etiqueta: 'Ovalada' },
    { valor: 'almendrada', etiqueta: 'Almendrada' },
    { valor: 'coffin', etiqueta: 'Coffin' },
    { valor: 'ballerina', etiqueta: 'Ballerina' },
    { valor: 'stiletto', etiqueta: 'Stiletto' },
];

const OPCIONES_LARGO_UNA = [
    { valor: 'corto', etiqueta: 'Corto' },
    { valor: 'medio', etiqueta: 'Medio' },
    { valor: 'largo', etiqueta: 'Largo' },
    { valor: 'extra_largo', etiqueta: 'Extra largo' },
];

const OPCIONES_AUTORIZACION_FOTOS = [
    { valor: 'solo_historial_privado', etiqueta: 'Solo para historial privado' },
    { valor: 'autorizo_publicacion', etiqueta: 'Autorizo además su publicación en redes y material publicitario' },
    { valor: 'no_autorizo', etiqueta: 'No autorizo fotografías' },
];

const TEXTO_CONSENTIMIENTO =
    'Declaro que la información de salud suministrada es verdadera y autorizo el tratamiento de mis datos personales ' +
    '(Ley 1581 de 2012) para la prestación de este servicio y mi historial clínico en este negocio.';

function valoresVacios(): DatosHistoriaClinica {
    return {
        profesional_id: 0,
        fecha: new Date().toISOString().slice(0, 10),
        alergias: false,
        reaccion_anterior: false,
        diabetes: false,
        problemas_circulacion: false,
        medicamentos: false,
        heridas_actuales: false,
        otra_condicion: '',
        detalle_reaccion: '',
        piel: [],
        una: [],
        hallazgos_visibles: [],
        hallazgo_otro_texto: '',
        observaciones_valoracion: '',
        decision: 'se_realiza',
        motivo_decision: '',
        servicio_diseno: 'semipermanente',
        servicio_diseno_otro_texto: '',
        forma_una: 'almendrada',
        largo_una: 'medio',
        colores: '',
        decoracion_referencia: '',
        hora_inicio: '',
        hora_final: '',
        tecnica_realizada: '',
        productos_marcas: '',
        lote_vencimiento: '',
        retiro_previo: false,
        reparaciones: false,
        reparaciones_detalle: '',
        cambios_durante_servicio: '',
        novedades_incidentes: '',
        valor: 0,
        abono: 0,
        saldo: 0,
        revisio_resultado: false,
        recibio_cuidados: false,
        fecha_seguimiento: '',
        proximo_mantenimiento_sugerido: '',
        novedad_pendiente: false,
        consentimiento_aceptado: false,
        autorizacion_fotos: 'solo_historial_privado',
        firma_clienta_base64: null,
        firma_profesional_base64: null,
    };
}

export default function FormularioHistoriaClinica({
    cliente,
    profesionales,
    valoresIniciales,
    firmaClientaExistenteUrl = null,
    firmaProfesionalExistenteUrl = null,
    enviando,
    textoBoton,
    onGuardar,
    onCancelar,
}: Propiedades): ReactElement {
    const [datos, setDatos] = useState<DatosHistoriaClinica>(valoresIniciales ?? valoresVacios());
    const [seccionAbierta, setSeccionAbierta] = useState(1);
    const [errores, setErrores] = useState<ErroresValidacion>({});

    function set<K extends keyof DatosHistoriaClinica>(campo: K, valor: DatosHistoriaClinica[K]): void {
        setDatos((anterior) => ({ ...anterior, [campo]: valor }));
    }

    function alternarEnLista(campo: 'piel' | 'una' | 'hallazgos_visibles', valor: string): void {
        setDatos((anterior) => {
            const lista = anterior[campo];
            const siguiente = lista.includes(valor) ? lista.filter((v) => v !== valor) : [...lista, valor];

            return { ...anterior, [campo]: siguiente };
        });
    }

    function alCambiarSeccion(numero: number): void {
        setSeccionAbierta((actual) => (actual === numero ? 0 : numero));
    }

    async function enviar(): Promise<void> {
        setErrores({});

        try {
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
        <div className="max-w-3xl space-y-4">
            <Acordeon titulo="1. Datos básicos" abierta={seccionAbierta === 1} onCambiar={() => alCambiarSeccion(1)}>
                <div className="grid gap-1 text-sm text-texto-suave">
                    <p>
                        <span className="font-medium text-tinta">Código de clienta:</span> {cliente.codigoClienta}
                    </p>
                    <p>
                        <span className="font-medium text-tinta">Nombre:</span> {cliente.nombre}
                    </p>
                    <p>
                        <span className="font-medium text-tinta">Documento:</span> {cliente.documento ?? 'Sin registrar'}
                    </p>
                    <p>
                        <span className="font-medium text-tinta">Celular:</span> {cliente.celular}
                    </p>
                    <p>
                        <span className="font-medium text-tinta">Fecha de nacimiento:</span> {cliente.fechaNacimiento ?? 'Sin registrar'}
                    </p>
                </div>

                <div className="grid gap-4 sm:grid-cols-2">
                    <CampoTexto etiqueta="Fecha" type="date" value={datos.fecha} onChange={(e) => set('fecha', e.target.value)} error={errores.fecha?.[0]} />
                    <CampoSelect
                        etiqueta="Profesional"
                        placeholder="Elige un profesional"
                        opciones={profesionales.map((p) => ({ valor: String(p.id), etiqueta: p.nombre }))}
                        value={datos.profesional_id || ''}
                        onChange={(e) => set('profesional_id', Number(e.target.value))}
                        error={errores.profesional_id?.[0]}
                    />
                </div>
            </Acordeon>

            <Acordeon titulo="2. Información importante para el servicio" abierta={seccionAbierta === 2} onCambiar={() => alCambiarSeccion(2)}>
                <div className="grid gap-2 sm:grid-cols-2">
                    <CampoCasilla etiqueta="Alergias o sensibilidad a cosméticos/acrilatos/adhesivos" checked={datos.alergias} onChange={(v) => set('alergias', v)} />
                    <CampoCasilla etiqueta="Reacción anterior a productos de uñas" checked={datos.reaccion_anterior} onChange={(v) => set('reaccion_anterior', v)} />
                    <CampoCasilla etiqueta="Diabetes o alteración de sensibilidad" checked={datos.diabetes} onChange={(v) => set('diabetes', v)} />
                    <CampoCasilla etiqueta="Problemas de circulación o cicatrización" checked={datos.problemas_circulacion} onChange={(v) => set('problemas_circulacion', v)} />
                    <CampoCasilla etiqueta="Medicamentos o tratamiento que afecten piel o uñas" checked={datos.medicamentos} onChange={(v) => set('medicamentos', v)} />
                    <CampoCasilla etiqueta="Heridas, dolor o inflamación actualmente" checked={datos.heridas_actuales} onChange={(v) => set('heridas_actuales', v)} />
                </div>
                <CampoTextoLargo etiqueta="Otra condición relevante" value={datos.otra_condicion} onChange={(e) => set('otra_condicion', e.target.value)} />
                <CampoTextoLargo
                    etiqueta="Detalle/producto que causó reacción, si aplica"
                    value={datos.detalle_reaccion}
                    onChange={(e) => set('detalle_reaccion', e.target.value)}
                />
            </Acordeon>

            <Acordeon titulo="3. Valoración visual de manos y uñas" abierta={seccionAbierta === 3} onCambiar={() => alCambiarSeccion(3)}>
                <div>
                    <p className="mb-2 text-sm font-medium text-tinta">Piel</p>
                    <div className="grid gap-2 sm:grid-cols-2">
                        {OPCIONES_PIEL.map((o) => (
                            <CampoCasilla key={o.valor} etiqueta={o.etiqueta} checked={datos.piel.includes(o.valor)} onChange={() => alternarEnLista('piel', o.valor)} />
                        ))}
                    </div>
                    {errores.piel && <p className="mt-1 text-sm text-red-600">{errores.piel[0]}</p>}
                </div>

                <div>
                    <p className="mb-2 text-sm font-medium text-tinta">Uña</p>
                    <div className="grid gap-2 sm:grid-cols-2">
                        {OPCIONES_UNA.map((o) => (
                            <CampoCasilla key={o.valor} etiqueta={o.etiqueta} checked={datos.una.includes(o.valor)} onChange={() => alternarEnLista('una', o.valor)} />
                        ))}
                    </div>
                    {errores.una && <p className="mt-1 text-sm text-red-600">{errores.una[0]}</p>}
                </div>

                <div>
                    <p className="mb-2 text-sm font-medium text-tinta">Otros hallazgos visibles</p>
                    <div className="grid gap-2 sm:grid-cols-2">
                        {OPCIONES_HALLAZGOS.map((o) => (
                            <CampoCasilla
                                key={o.valor}
                                etiqueta={o.etiqueta}
                                checked={datos.hallazgos_visibles.includes(o.valor)}
                                onChange={() => alternarEnLista('hallazgos_visibles', o.valor)}
                            />
                        ))}
                    </div>
                </div>
                {datos.hallazgos_visibles.includes('otro') && (
                    <CampoTexto etiqueta="¿Cuál otro hallazgo?" value={datos.hallazgo_otro_texto} onChange={(e) => set('hallazgo_otro_texto', e.target.value)} />
                )}

                <CampoTextoLargo
                    etiqueta="Observaciones/zona afectada"
                    value={datos.observaciones_valoracion}
                    onChange={(e) => set('observaciones_valoracion', e.target.value)}
                />

                <CampoSelect etiqueta="Decisión" opciones={OPCIONES_DECISION} value={datos.decision} onChange={(e) => set('decision', e.target.value)} />
                <CampoTextoLargo etiqueta="Motivo" value={datos.motivo_decision} onChange={(e) => set('motivo_decision', e.target.value)} />
            </Acordeon>

            <Acordeon titulo="4. Diseño elegido por la clienta" abierta={seccionAbierta === 4} onCambiar={() => alCambiarSeccion(4)}>
                <CampoSelect
                    etiqueta="Servicio"
                    opciones={OPCIONES_SERVICIO_DISENO}
                    value={datos.servicio_diseno}
                    onChange={(e) => set('servicio_diseno', e.target.value)}
                />
                {datos.servicio_diseno === 'otro' && (
                    <CampoTexto
                        etiqueta="¿Cuál otro servicio?"
                        value={datos.servicio_diseno_otro_texto}
                        onChange={(e) => set('servicio_diseno_otro_texto', e.target.value)}
                    />
                )}
                <CampoSelect etiqueta="Forma" opciones={OPCIONES_FORMA_UNA} value={datos.forma_una} onChange={(e) => set('forma_una', e.target.value)} />
                <CampoSelect etiqueta="Largo" opciones={OPCIONES_LARGO_UNA} value={datos.largo_una} onChange={(e) => set('largo_una', e.target.value)} />
                <CampoTexto etiqueta="Color(es)/código(s)" value={datos.colores} onChange={(e) => set('colores', e.target.value)} />
                <CampoTexto etiqueta="Decoración/efecto/referencia" value={datos.decoracion_referencia} onChange={(e) => set('decoracion_referencia', e.target.value)} />
            </Acordeon>

            <Acordeon titulo="5. Registro técnico del servicio" abierta={seccionAbierta === 5} onCambiar={() => alCambiarSeccion(5)}>
                <div className="grid gap-4 sm:grid-cols-2">
                    <CampoTexto etiqueta="Hora inicio" type="time" value={datos.hora_inicio} onChange={(e) => set('hora_inicio', e.target.value)} error={errores.hora_inicio?.[0]} />
                    <CampoTexto etiqueta="Hora final" type="time" value={datos.hora_final} onChange={(e) => set('hora_final', e.target.value)} error={errores.hora_final?.[0]} />
                </div>
                <CampoTextoLargo etiqueta="Técnica realizada" value={datos.tecnica_realizada} onChange={(e) => set('tecnica_realizada', e.target.value)} />
                <CampoTextoLargo etiqueta="Productos/marcas/referencias" value={datos.productos_marcas} onChange={(e) => set('productos_marcas', e.target.value)} />
                <CampoTexto etiqueta="Lote o vencimiento" value={datos.lote_vencimiento} onChange={(e) => set('lote_vencimiento', e.target.value)} />

                <div className="grid gap-4 sm:grid-cols-2">
                    <CampoCasilla etiqueta="Retiro previo" checked={datos.retiro_previo} onChange={(v) => set('retiro_previo', v)} />
                    <CampoCasilla etiqueta="Reparaciones" checked={datos.reparaciones} onChange={(v) => set('reparaciones', v)} />
                </div>
                {datos.reparaciones && (
                    <CampoTextoLargo etiqueta="¿Cuáles reparaciones?" value={datos.reparaciones_detalle} onChange={(e) => set('reparaciones_detalle', e.target.value)} />
                )}

                <CampoTextoLargo
                    etiqueta="Cambios solicitados y aprobados durante el servicio"
                    value={datos.cambios_durante_servicio}
                    onChange={(e) => set('cambios_durante_servicio', e.target.value)}
                />
                <CampoTextoLargo
                    etiqueta="Novedades/molestias/incidentes/recomendaciones dadas"
                    value={datos.novedades_incidentes}
                    onChange={(e) => set('novedades_incidentes', e.target.value)}
                />
            </Acordeon>

            <Acordeon titulo="6. Registro de pago y revisión" abierta={seccionAbierta === 6} onCambiar={() => alCambiarSeccion(6)}>
                <div className="grid gap-4 sm:grid-cols-3">
                    <CampoTexto etiqueta="Valor" type="number" min={0} value={datos.valor} onChange={(e) => set('valor', Number(e.target.value))} />
                    <CampoTexto etiqueta="Abono" type="number" min={0} value={datos.abono} onChange={(e) => set('abono', Number(e.target.value))} />
                    <CampoTexto etiqueta="Saldo" type="number" min={0} value={datos.saldo} onChange={(e) => set('saldo', Number(e.target.value))} />
                </div>
                <div className="grid gap-4 sm:grid-cols-2">
                    <CampoCasilla etiqueta="¿Revisó el resultado?" checked={datos.revisio_resultado} onChange={(v) => set('revisio_resultado', v)} />
                    <CampoCasilla etiqueta="¿Recibió cuidados?" checked={datos.recibio_cuidados} onChange={(v) => set('recibio_cuidados', v)} />
                </div>
                <div className="grid gap-4 sm:grid-cols-2">
                    <CampoTexto etiqueta="Fecha de seguimiento" type="date" value={datos.fecha_seguimiento} onChange={(e) => set('fecha_seguimiento', e.target.value)} />
                    <CampoTexto
                        etiqueta="Próximo mantenimiento sugerido"
                        type="date"
                        value={datos.proximo_mantenimiento_sugerido}
                        onChange={(e) => set('proximo_mantenimiento_sugerido', e.target.value)}
                    />
                </div>
                <CampoCasilla etiqueta="Novedad pendiente" checked={datos.novedad_pendiente} onChange={(v) => set('novedad_pendiente', v)} />
            </Acordeon>

            <Acordeon titulo="7. Consentimiento y autorizaciones" abierta={seccionAbierta === 7} onCambiar={() => alCambiarSeccion(7)}>
                <p className="rounded-boton bg-fondo-panel p-4 text-sm text-texto-suave">{TEXTO_CONSENTIMIENTO}</p>
                <CampoCasilla
                    etiqueta="La clienta acepta el consentimiento informado y el tratamiento de datos"
                    checked={datos.consentimiento_aceptado}
                    onChange={(v) => set('consentimiento_aceptado', v)}
                />

                <CampoSelect
                    etiqueta="Autorización de fotografías"
                    opciones={OPCIONES_AUTORIZACION_FOTOS}
                    value={datos.autorizacion_fotos}
                    onChange={(e) => set('autorizacion_fotos', e.target.value)}
                />

                <PadFirma
                    etiqueta="Firma de la clienta"
                    firmaExistenteUrl={firmaClientaExistenteUrl}
                    onCambiar={(base64) => set('firma_clienta_base64', base64)}
                />
                <PadFirma
                    etiqueta="Firma de la profesional"
                    firmaExistenteUrl={firmaProfesionalExistenteUrl}
                    onCambiar={(base64) => set('firma_profesional_base64', base64)}
                />
            </Acordeon>

            <div className="flex gap-2">
                <Boton onClick={enviar} disabled={enviando}>
                    {enviando ? 'Guardando...' : textoBoton}
                </Boton>
                <Boton variante="contorno" onClick={onCancelar}>
                    Cancelar
                </Boton>
            </div>
        </div>
    );
}
