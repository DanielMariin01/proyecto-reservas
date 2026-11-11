/**
 * Espejo de App\Enums\IconoBloque (valor + etiqueta): lista fija de
 * íconos para los campos "ícono" de portada y texto_con_imagen (modo
 * motivos) — nunca texto libre, igual que el Select del Builder de
 * Filament. El mapa a Lucide ya existe en resources/js/bloques/iconoBloque.tsx
 * y se reutiliza para la vista previa de cada opción.
 */
export const OPCIONES_ICONO: Array<{ valor: string; etiqueta: string }> = [
    { valor: 'escudo', etiqueta: 'Escudo (garantía)' },
    { valor: 'reloj', etiqueta: 'Reloj (horario/puntualidad)' },
    { valor: 'estrella', etiqueta: 'Estrella (calidad)' },
    { valor: 'premio', etiqueta: 'Premio (experiencia)' },
    { valor: 'corazon', etiqueta: 'Corazón (atención)' },
    { valor: 'usuarios', etiqueta: 'Usuarios (equipo)' },
    { valor: 'destellos', etiqueta: 'Destellos (resultados)' },
    { valor: 'ubicacion', etiqueta: 'Ubicación' },
    { valor: 'billete', etiqueta: 'Billete (precio)' },
    { valor: 'pulgar_arriba', etiqueta: 'Pulgar arriba (satisfacción)' },
    { valor: 'telefono', etiqueta: 'Teléfono (contacto)' },
    { valor: 'tijeras', etiqueta: 'Tijeras (servicio)' },
];
