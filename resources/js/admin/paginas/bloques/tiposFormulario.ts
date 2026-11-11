export interface PropiedadesFormularioBloque {
    slug: string;
    datos: Record<string, unknown>;
    onChange: (datos: Record<string, unknown>) => void;
}
