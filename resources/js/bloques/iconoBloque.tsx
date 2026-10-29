import { Award, Banknote, Clock, Heart, MapPin, Phone, Scissors, Shield, Sparkles, Star, ThumbsUp, Users } from 'lucide-react';
import type { ComponentType } from 'react';

interface PropiedadesIcono {
    size?: number;
    strokeWidth?: number;
    className?: string;
}

/**
 * Mapea 1 a 1 los valores de App\Enums\IconoBloque (icono) a un ícono
 * Lucide — nunca texto libre, así el Builder y React quedan
 * sincronizados por la misma lista fija.
 */
const ICONOS_POR_VALOR: Record<string, ComponentType<PropiedadesIcono>> = {
    escudo: Shield,
    reloj: Clock,
    estrella: Star,
    premio: Award,
    corazon: Heart,
    usuarios: Users,
    destellos: Sparkles,
    ubicacion: MapPin,
    billete: Banknote,
    pulgar_arriba: ThumbsUp,
    telefono: Phone,
    tijeras: Scissors,
};

export function obtenerIcono(valor: unknown): ComponentType<PropiedadesIcono> {
    return (typeof valor === 'string' && ICONOS_POR_VALOR[valor]) || Star;
}
